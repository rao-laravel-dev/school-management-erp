<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicYearController extends Controller
{
    /**
     * 1. Display Sessions List
     */
    public function index()
    {
        // Newest sessions upar dikhane ke liye orderBy lagaya hai
        $academic_years = AcademicYear::orderBy('id', 'desc')->get();
        // Class Dates (optional) ke Select2 ke liye active classes
        $classes = SchoolClass::where('status', 1)->orderBy('numeric_name')->get(['id', 'name']);
        return view('admin.academic_years.index', compact('academic_years', 'classes'));
    }

    /**
     * Class Dates (optional): khali rows hata do (keys wahi rehti hain taake error index form ki row se mile)
     */
    private function dropEmptyOverrideRows(Request $request): void
    {
        $rows = $request->input('class_date_overrides', []);
        if (!is_array($rows)) {
            return;
        }

        foreach ($rows as $i => $row) {
            if (empty($row['class_ids']) && empty($row['start_date']) && empty($row['end_date'])) {
                unset($rows[$i]);
            }
        }

        $request->merge(['class_date_overrides' => $rows]);
    }

    // Class Dates ke field rules (store + update dono me)
    private function overrideRules(): array
    {
        return [
            'class_date_overrides'                => 'nullable|array',
            'class_date_overrides.*.class_ids'    => 'required|array|min:1',
            'class_date_overrides.*.class_ids.*'  => 'integer|distinct|exists:school_class,id',
            'class_date_overrides.*.start_date'   => 'required|date',
            'class_date_overrides.*.end_date'     => 'required|date',
        ];
    }

    /**
     * Class Dates ke cross-field checks. Error 422 JSON (key = class_date_overrides.{row}.{field}).
     * Return: save ke liye saaf array ya null (koi row nahi = NULL = purana behaviour).
     */
    private function checkOverrides(Request $request, ?int $ignoreId = null): ?array
    {
        $rows = (array) $request->input('class_date_overrides', []);
        if (!$rows) {
            return null;
        }

        $sessionStart = Carbon::parse($request->start_date)->startOfDay();
        $sessionEnd   = Carbon::parse($request->end_date)->startOfDay();
        $errors = [];
        $seen   = [];
        $clean  = [];

        // Aas paas ke sessions (is session ke start se pehle wala aur baad wala)
        $prev = AcademicYear::where('id', '!=', $ignoreId ?: 0)->whereDate('start_date', '<', $sessionStart->toDateString())->orderByDesc('start_date')->first();
        $next = AcademicYear::where('id', '!=', $ignoreId ?: 0)->whereDate('start_date', '>', $sessionStart->toDateString())->orderBy('start_date')->first();

        foreach ($rows as $i => $row) {
            $start = Carbon::parse($row['start_date'])->startOfDay();
            $end   = Carbon::parse($row['end_date'])->startOfDay();
            $ids   = array_map('intval', $row['class_ids']);

            // Aik class sirf aik group me
            foreach ($ids as $id) {
                if (isset($seen[$id])) {
                    $errors["class_date_overrides.$i.class_ids"] = 'A class can be in only one class group.';
                }
                $seen[$id] = true;
            }

            if ($end->lte($start)) {
                $errors["class_date_overrides.$i.end_date"] = 'End Date must be after Start Date.';
            } elseif ($end->gt($start->copy()->addYear())) {
                $errors["class_date_overrides.$i.end_date"] = 'A class group cannot run longer than one year.';
            }

            if ($start->lt($sessionStart) || $start->gt($sessionEnd)) {
                $errors["class_date_overrides.$i.start_date"] = 'Start Date must be inside the session dates.';
            }

            // Pichle / agle session me isi class ki dates takra nahi sakti
            foreach ($ids as $id) {
                if ($prev && $prev->datesForClass($id)[1] && $prev->datesForClass($id)[1]->startOfDay()->gte($start)) {
                    $errors["class_date_overrides.$i.start_date"] = 'Start Date overlaps the previous session (' . $prev->name . ') for one of these classes.';
                }
                if ($next && $next->datesForClass($id)[0] && $next->datesForClass($id)[0]->startOfDay()->lte($end)) {
                    $errors["class_date_overrides.$i.end_date"] = 'End Date overlaps the next session (' . $next->name . ') for one of these classes.';
                }
            }

            $clean[] = [
                'class_ids'  => $ids,
                'start_date' => $start->toDateString(),
                'end_date'   => $end->toDateString(),
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $clean;
    }

    /**
     * 2. Store New Session
     */
    public function store(Request $request)
    {
        $this->dropEmptyOverrideRows($request);

        $request->validate([
            'name'       => 'required|string|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'weekly_off_days' => 'nullable|array',
            'weekly_off_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ] + $this->overrideRules(), [
            'name.required'       => 'Academic Year / Session is required.',
            'name.unique'         => 'This Academic Year / Session is already registered.',
            'start_date.required' => 'Start Date is required.',
            'end_date.required'   => 'End Date is required.',
            'end_date.after'      => 'End Date must be after Start Date.',
            'class_date_overrides.*.class_ids.required'   => 'Select at least one class.',
            'class_date_overrides.*.class_ids.*.distinct' => 'A class can be in only one class group.',
            'class_date_overrides.*.start_date.required'  => 'Start Date is required.',
            'class_date_overrides.*.end_date.required'    => 'End Date is required.',
        ]);

        // Class Dates: cross-field checks (aik class aik group, session ke andar, overlap nahi)
        $classDateOverrides = $this->checkOverrides($request);

        // Form ne checkboxes render kiye hon to jo tick hain wahi (khali bhi ho sakta hai), warna default Sunday
        $weeklyOff = $request->has('weekly_off_days_present')
            ? $request->input('weekly_off_days', [])
            : ['Sunday'];

        AcademicYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => $request->status ?? 1,
            'weekly_off_days' => $weeklyOff,
            'class_date_overrides' => $classDateOverrides,
        ]);


        // AJAX form hai: redirect nahi, JSON (page toastr dikha kar reload karta hai)
        return response()->json(['success' => true, 'message' => 'Academic Year created successfully!']);
    }

    /**
     * 3. Edit Session (Optional - Since we handle inline editing via JS, 
     * but keeping it if you ever need traditional edit page)
     */
    public function edit($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
        return view('admin.academic_years.edit', compact('academic_year'));
    }

    /**
     * 4. Update Existing Session
     */
    public function update(Request $request, $id)
    {
        $academic_year = AcademicYear::findOrFail($id);

        $this->dropEmptyOverrideRows($request);

        $request->validate([
            'name'       => 'required|string|unique:academic_years,name,' . $id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'weekly_off_days' => 'nullable|array',
            'weekly_off_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ] + $this->overrideRules(), [
            'name.required'       => 'Academic Year / Session is required.',
            'name.unique'         => 'This Academic Year / Session name is already taken.',
            'start_date.required' => 'Start Date is required.',
            'end_date.required'   => 'End Date is required.',
            'end_date.after'      => 'End Date must be after Start Date.',
            'class_date_overrides.*.class_ids.required'   => 'Select at least one class.',
            'class_date_overrides.*.class_ids.*.distinct' => 'A class can be in only one class group.',
            'class_date_overrides.*.start_date.required'  => 'Start Date is required.',
            'class_date_overrides.*.end_date.required'    => 'End Date is required.',
        ]);

        // Class Dates: cross-field checks (aik class aik group, session ke andar, overlap nahi)
        $classDateOverrides = $this->checkOverrides($request, (int) $id);

        $academic_year->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            // Unchecked checkbox request mein nahi aate, is liye sab untick = khali array
            'weekly_off_days' => $request->input('weekly_off_days', []),
            'class_date_overrides' => $classDateOverrides,
        ]);

        // AJAX form hai: redirect nahi, JSON (page toastr dikha kar reload karta hai)
        return response()->json(['success' => true, 'message' => 'Academic Year updated successfully!']);
    }

    /**
     * 5. Delete Session (Destroy)
     */
    public function destroy($id)
    {
        $academic_year = AcademicYear::findOrFail($id);
        
        // Safety Check: Agar yeh current active session hai to delete nahi karne dena
        if ($academic_year->is_current == 1) {
            return redirect()->back()->with([
                'message'    => 'Cannot delete the current active running session!',
                'alert-type' => 'error'
            ]);
        }

        $academic_year->delete();

        return redirect()->back()->with([
            'message'    => 'Academic Year removed successfully!',
            'alert-type' => 'warning'
        ]);
    }

    /**
     * 6. Special Action: Toggle Status via AJAX
     */
    public function toggleStatus($id)
    {
        $year = AcademicYear::findOrFail($id);
        
        // Status toggle toggle (1 to 0 or 0 to 1)
        $year->status = $year->status == 1 ? 0 : 1;
        $year->save();

        // AJAX response matching Toastr notifications
        return response()->json([
            'status'  => $year->status,
            'message' => 'Session status updated to ' . ($year->status == 1 ? 'Active' : 'Inactive')
        ]);
    }

    /**
     * 7. Special Action: Mark As Current Running Session (Database Transaction)
     */
    public function markAsCurrent($id)
    {
        // DB Transaction lagayi taake data consistency kharab na ho
        DB::transaction(function () use ($id) {
            // Step A: Pehle poore school system ke saare sessions ko 0 (false) karo
            AcademicYear::query()->update(['is_current' => 0]);

            // Step B: Ab selected session ko 1 (true) aur status active kar do
            $year = AcademicYear::findOrFail($id);
            $year->update([
                'is_current' => 1,
                'status'     => 1 // Current session lazmi active hona chahiye
            ]);
        });

        // Naya current year foran lagu ho: AcademicYear::getActiveSessionId() ki 1 ghanta cache clear
        cache()->forget('active_session_id');

        return response()->json([
            'success' => true,
            'message' => 'System context successfully shifted to the selected academic cycle!'
        ]);
    }
}

