<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ReportCardTemplate;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportCardTemplateController extends Controller
{
    protected $imageService;
    protected $folder = 'uploads/report_card_template';

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $report_card_templates = ReportCardTemplate::orderBy('template_name')->get();

        return view('admin.report_card_template.index', compact('report_card_templates'));
    }

    protected function rules(): array
    {
        return [
            'template_name' => 'required|string|max:255',
            'body_text'     => 'required|string',
            'footer_text'   => 'required|string',

            'header_image'     => 'nullable|image|max:2048',
            'left_sign'        => 'nullable|image|max:2048',
            'middle_sign'      => 'nullable|image|max:2048',
            'right_sign'       => 'nullable|image|max:2048',
            'background_image' => 'nullable|image|max:2048',

            'show_name'                 => 'nullable|boolean',
            'show_father_name'          => 'nullable|boolean',
            'show_mother_name'          => 'nullable|boolean',
            'show_admission_no'         => 'nullable|boolean',
            'show_roll_number'          => 'nullable|boolean',
            'show_photo'                => 'nullable|boolean',
            'show_class'                => 'nullable|boolean',
            'show_section'              => 'nullable|boolean',
            'show_dob'                  => 'nullable|boolean',
            'show_attendance_summary'   => 'nullable|boolean',
            'show_co_curricular'        => 'nullable|boolean',
            'show_behavioral'           => 'nullable|boolean',
            'show_class_teacher_remark' => 'nullable|boolean',
        ];
    }

    protected $imageFields = ['header_image', 'left_sign', 'middle_sign', 'right_sign', 'background_image'];
    protected $toggleFields = [
        'show_name',
        'show_father_name',
        'show_mother_name',
        'show_admission_no',
        'show_roll_number',
        'show_photo',
        'show_class',
        'show_section',
        'show_dob',
        'show_attendance_summary',
        'show_co_curricular',
        'show_behavioral',
        'show_class_teacher_remark',
    ];

    public function save(Request $request)
    {
        $validated = $request->validate($this->rules());

        foreach ($this->toggleFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        foreach ($this->imageFields as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $this->imageService->upload($request->file($field), $this->folder, 600, 400, 80);
            }
        }

        ReportCardTemplate::create($validated);

        return response()->json(['success' => true, 'message' => 'Report Card Template saved successfully.']);
    }

    public function edit(ReportCardTemplate $report_card_template)
    {
        return response()->json($report_card_template);
    }

    public function update(Request $request, ReportCardTemplate $report_card_template)
    {
        $validated = $request->validate($this->rules());

        foreach ($this->toggleFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        foreach ($this->imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($report_card_template->{$field}) {
                    $this->imageService->delete($report_card_template->{$field}, $this->folder);
                }
                $validated[$field] = $this->imageService->upload($request->file($field), $this->folder, 600, 400, 80);
            }
        }

        $report_card_template->update($validated);

        return response()->json(['success' => true, 'message' => 'Report Card Template updated successfully.']);
    }

    public function destroy(Request $request, ReportCardTemplate $report_card_template)
    {
        // FK print_report_cards.template_id: printed cards hain to pehle confirm (force=1), phir history + template ek transaction me
        $printedCount = $report_card_template->printReportCards()->count();
        if ($printedCount > 0) {
            if (!$request->boolean('force')) {
                return response()->json([
                    'success'       => false,
                    'needs_confirm' => true,
                    'count'         => $printedCount,
                    'message'       => "This template is used in {$printedCount} printed report cards. Delete the template and its print history?",
                ], 422);
            }

            // Files transaction commit ke baad hi delete hongi
            $files = [];
            foreach ($this->imageFields as $field) {
                if ($report_card_template->{$field}) {
                    $files[] = $report_card_template->{$field};
                }
            }

            DB::transaction(function () use ($report_card_template) {
                $report_card_template->printReportCards()->delete();
                $report_card_template->delete();
            });

            foreach ($files as $file) {
                $this->imageService->delete($file, $this->folder);
            }

            return response()->json(['success' => true, 'message' => 'Report Card Template deleted successfully.']);
        }

        foreach ($this->imageFields as $field) {
            if ($report_card_template->{$field}) {
                $this->imageService->delete($report_card_template->{$field}, $this->folder);
            }
        }

        $report_card_template->delete();

        return response()->json(['success' => true, 'message' => 'Report Card Template deleted successfully.']);
    }
}
