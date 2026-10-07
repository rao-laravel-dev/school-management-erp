# Class Timetable Module: Project Summary and Next Plan

**Stack:** Laravel (Blade + jQuery + Bootstrap 5 + toastr + DataTables)
**Module:** Academic > Class Timetable / Teacher Timetable
**Files involved:**
- `App\Http\Controllers\Backend\ClassTimetableController`
- `App\Http\Controllers\Backend\TeacherTimetableController`
- `admin/class_timetable/index.blade.php`, `admin/class_timetable/create.blade.php`
- `admin/teacher_timetable/index.blade.php`

---

## 1. Purane (original) code ka logic, jo humne barqarar rakha

Ye aap ke apne design decisions hain. Inhein badla nahi gaya.

| Area | Aap ka logic |
|---|---|
| Index page | Class + Section select karke Search, week view (7 columns, har din ka color). khali din: `weekly_off_days` se "Weekly Off", warna "Not Scheduled" (11.1). |
| Create page | Pehle "Select Criteria" (Class, Section, Subject Group), Search ke baad hi "Set Timetable" card khulta hai. |
| Subject Group | Agar class ke liye groups hon to Group required hai, aur subjects `class_subject` se aate hain (group wale + common/compulsory). |
| Quick generate | Start Time, Duration, Interval, No. of Periods, Break After Period #, Break (min), phir **Apply**. |
| Apply | Sirf **current (khule hue) din** ke liye rows banata hai. Har din ke liye alag Apply karna hota hai. |
| Break | Sirf screen par hota hai (peeli `table-warning` row). **DB mein save nahi hota**, save mein skip hota hai. |
| Rows | Subject, Time From, Time To, Teacher, Delete. DataTable sirf search ke liye. |
| Save logic | Purani entries delete karke fresh insert (simplest approach). |
| Clash check | Teacher kisi **doosri class-section** mein usi din overlapping time par busy na ho. Apni class-section ki purani rows clash nahi maani jatin. |
| Overlap check | Ek hi class-section ke andar naye rows aapas mein overlap na karein. |
| Edit mode | Index se `?class_id=&section_id=` ke saath aata hai. Class/Section khud select, group ho to user ko pehle group chun kar Search karna hota hai. |
| Permissions | `access-class-timetable` (read routes) aur `manage-class-timetable` (create/save/delete). |
| Style | Comments Roman Urdu mein, `response()->json(['success' => ...])`, `DB::transaction`, `ClassTimetable::with([...])`. |

---

## 2. Ab tak ka kaam (Summary)

### 2.1 Teacher availability aur clash detection
- Naya endpoint: `GET /class-timetable/teacher-availability`.
- Teacher select karte hi ya time badalte hi AJAX check hota hai. Agar teacher usi din overlapping time par kisi **doosri** class mein busy ho to:
  1. Red toastr aata hai (English message).
  2. Teacher dropdown reset ho jata hai aur row par inline error lagta hai.
  3. Modal khulta hai.
- Har row ke teacher dropdown ke saath calendar button hai (kisi bhi waqt availability dekhne ke liye). Break row mein ye button nahi hota.

### 2.2 Availability modal (professional UI)
- `modal-xl`, day pills (Monday se Sunday), summary chips: **Busy / In this class / Free**.
- Timeline bar: Assembly (grey), Break (peela), Busy (laal), Free (hara, clickable), In this class (slate), This period (nila border, text ke baghair).
- School timing: 07:30 assembly, **07:50 se periods** (`SCHOOL` constant).
- Grid aap ki table ki **asli rows aur breaks** se banta hai (periods ke beech ka gap bhi break maana jata hai). Break 3 period ke baad ho, 5 ke baad ho ya na ho, sab chalta hai.
- Har period ko P1, P2... label milta hai.
- **Assign** ab us period ki row mein teacher laga deta hai (time nahi badalta). Agar row mein pehle se teacher ho to button **Replace** (peela) dikhata hai.
- Jis period mein wo teacher isi class mein pehle se hai wo "In this class" dikhta hai, aur use dubara assign nahi kiya ja sakta.

### 2.3 Day-wise save (sab se bada logic change)
- Pehle Save saare 7 din ek saath bhejta tha, is liye kisi bhi doosre din ka khali subject poora save rok deta tha.
- Ab **Save sirf current din ka hota hai**: `day` request mein aata hai, delete sirf usi din ka hota hai, baaqi din safe rehte hain.
- Button label: `Save Monday` / `Update Monday` (`savedDays` se).
- Unsaved din ke tab par `*` nishan, aur tab badalte waqt warning toastr (`dirtyDays`).

### 2.4 Bugs jo fix hue
| Masla | Wajah | Fix |
|---|---|---|
| "Tuesday - Period 7: Subject required" | Save saare dinon ka data bhejta tha | Day-wise save |
| Subject dropdown "Select" dikhana | Saved subject current group ki list mein nahi tha | `subject_name` fallback option |
| Save ke baad Break gayab | Break DB mein nahi, sirf screen par | Edit par gap se Break row dobara banti hai |
| Modal mein blur text | Nile box mein fill + neeche ka text | Nila box sirf border |
| P3/P4 free dikhna jab teacher isi class mein tha | Busy sirf doosri classes ka aata hai | "In this class" state |
| Row 2 ka time 07:00 ho kar order bigad gaya | Purana Assign time khisakata tha | Assign ab teacher lagata hai, time nahi |
| Error toast ke "Period N" ka galat number | Break rows bhi gine ja rahe the | Break rows ke baghair index |

---

## 3. Final backend code

### 3.1 Routes

```php
Route::prefix('class-timetable')->name('class_timetable.')->group(function () {

    Route::middleware('can:access-class-timetable')->group(function () {
        Route::controller(ClassTimetableController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/get-data', 'getData')->name('get_data');
            Route::get('/get-sections/{classId}', 'getSectionsByClass')->name('get_sections');
            Route::get('/get-groups/{classId}', 'getGroupsByClass')->name('get_groups');
            Route::get('/get-subjects/{classId}', 'getSubjectsByClass')->name('get_subjects');
            Route::get('/teacher-availability', 'teacherAvailability')->name('teacher_availability');
        });
    });

    Route::middleware('can:manage-class-timetable')->group(function () {
        Route::controller(ClassTimetableController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/save', 'save')->name('save');
            Route::delete('/delete/{id}', 'destroy')->name('delete');
            Route::delete('/delete-day', 'destroyDay')->name('delete_day'); // optional
        });
    });
});
```

> Agar kisi user ke paas sirf `manage-class-timetable` ho aur `access-class-timetable` na ho, to `teacher-availability` 403 dega. Us surat mein ye route manage group mein rakhein.

### 3.2 `save()` (day-wise)

```php
public function save(Request $request)
{
    $validator = Validator::make($request->all(), [
        'school_class_id' => 'required|exists:school_class,id',
        'section_id' => 'required|exists:sections,id',
        'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'periods' => 'required|array|min:1',
        'periods.*.day' => 'required|same:day',
        'periods.*.subject_id' => 'required|exists:subjects,id',
        'periods.*.teacher_id' => 'required|exists:teachers,id',
        'periods.*.time_from' => 'required|date_format:H:i',
        'periods.*.time_to' => 'required|date_format:H:i|after:periods.*.time_from',
    ], [
        'periods.*.subject_id.required' => 'is required',
        'periods.*.subject_id.exists' => 'is invalid',
        'periods.*.teacher_id.required' => 'is required',
        'periods.*.teacher_id.exists' => 'is invalid',
        'periods.*.time_from.required' => 'is required',
        'periods.*.time_from.date_format' => 'format is invalid',
        'periods.*.time_to.required' => 'is required',
        'periods.*.time_to.date_format' => 'format is invalid',
        'periods.*.time_to.after' => 'must be after Time From',
        'periods.*.day.same' => 'does not match the selected day',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $classId = $request->school_class_id;
    $sectionId = $request->section_id;
    $day = $request->day;

    // --- Clash checking ---
    foreach ($request->periods as $row) {
        // 1) Teacher clash (kisi bhi doosri class-section mein same time pe busy to nahi)
        $teacherClash = ClassTimetable::where('teacher_id', $row['teacher_id'])
            ->where('day', $day)
            ->where(function ($q) use ($row) {
                $q->where('time_from', '<', $row['time_to'])
                    ->where('time_to', '>', $row['time_from']);
            })
            ->where(function ($q) use ($classId, $sectionId) {
                // update case mein isi class-section ke purane rows ko clash mat mano
                $q->where('school_class_id', '!=', $classId)
                    ->orWhere('section_id', '!=', $sectionId);
            })
            ->exists();

        if ($teacherClash) {
            return response()->json([
                'success' => false,
                'message' => "Teacher already assigned elsewhere on {$day} at {$row['time_from']} - {$row['time_to']}",
            ], 422);
        }
    }

    // 2) Same class-section ke andar hi overlap check (sirf is din ke)
    $rows = collect($request->periods)->sortBy('time_from')->values();
    for ($i = 0; $i < $rows->count() - 1; $i++) {
        if ($rows[$i]['time_to'] > $rows[$i + 1]['time_from']) {
            return response()->json([
                'success' => false,
                'message' => "Time overlap found on {$day} within the same class-section",
            ], 422);
        }
    }

    DB::transaction(function () use ($request, $classId, $sectionId, $day) {
        // Sirf isi din ki purani entries hata ke fresh insert
        ClassTimetable::where('school_class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('day', $day)
            ->delete();

        foreach ($request->periods as $row) {
            ClassTimetable::create([
                'school_class_id' => $classId,
                'section_id' => $sectionId,
                'subject_id' => $row['subject_id'],
                'teacher_id' => $row['teacher_id'],
                'day' => $day,
                'time_from' => $row['time_from'],
                'time_to' => $row['time_to'],
            ]);
        }
    });

    return response()->json(['success' => true, 'message' => "{$day} timetable saved successfully"]);
}
```

### 3.3 `destroy()` aur `destroyDay()`

```php
public function destroy($id)
{
    $period = ClassTimetable::find($id);

    if (!$period) {
        return response()->json(['success' => false, 'message' => 'Period not found'], 404);
    }

    $day = $period->day;
    $period->delete();

    return response()->json(['success' => true, 'message' => "{$day} period deleted"]);
}

// Kisi class-section ke ek din ki saari periods delete (weekly off ya galti se bana din)
public function destroyDay(Request $request)
{
    $request->validate([
        'school_class_id' => 'required|exists:school_class,id',
        'section_id' => 'required|exists:sections,id',
        'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
    ]);

    $count = ClassTimetable::where('school_class_id', $request->school_class_id)
        ->where('section_id', $request->section_id)
        ->where('day', $request->day)
        ->delete();

    return response()->json(['success' => true, 'message' => "{$request->day} timetable cleared ({$count} periods)"]);
}
```

### 3.4 `teacherAvailability()`

`use Carbon\Carbon;` ki ab zaroorat nahi, ye method seedha strings se kaam karta hai.

```php
public function teacherAvailability(Request $request)
{
    $request->validate([
        'teacher_id'      => 'required|exists:teachers,id',
        'day'             => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'school_class_id' => 'required',
        'section_id'      => 'required',
        'time_from'       => 'nullable|date_format:H:i',
        'time_to'         => 'nullable|date_format:H:i',
    ]);

    $teacher   = Teacher::findOrFail($request->teacher_id);
    $classId   = $request->school_class_id;
    $sectionId = $request->section_id;

    $toMin = fn ($t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
    $toHi  = fn ($m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

    // Is class-section ke ilawa baaqi sab jagah ki busy rows
    $busy = ClassTimetable::with(['schoolClass', 'section', 'subject'])
        ->where('teacher_id', $teacher->id)
        ->where('day', $request->day)
        ->where(function ($q) use ($classId, $sectionId) {
            $q->where('school_class_id', '!=', $classId)
              ->orWhere('section_id', '!=', $sectionId);
        })
        ->orderBy('time_from')
        ->get()
        ->map(fn ($r) => [
            'from'    => substr($r->time_from, 0, 5),
            'to'      => substr($r->time_to, 0, 5),
            'class'   => optional($r->schoolClass)->name,
            'section' => optional($r->section)->name,
            'subject' => optional($r->subject)->name,
        ])->values();

    // Requested slot ke saath overlap?
    $conflict = null;
    if ($request->time_from && $request->time_to) {
        $conflict = $busy->first(fn ($b) =>
            $b['from'] < $request->time_to && $b['to'] > $request->time_from
        );
    }

    // Day window aur free gaps (JS ab apna period grid khud banata hai,
    // ye sirf backup/compatibility ke liye hain)
    $starts = [420];
    $ends   = [960];
    foreach ($busy as $b) { $starts[] = $toMin($b['from']); $ends[] = $toMin($b['to']); }
    if ($request->time_from) { $starts[] = $toMin($request->time_from); }
    if ($request->time_to)   { $ends[]   = $toMin($request->time_to); }
    $winStart = (int) floor(min($starts) / 60) * 60;
    $winEnd   = (int) ceil(max($ends) / 60) * 60;

    $free   = [];
    $cursor = $winStart;
    foreach ($busy as $b) {
        $bf = $toMin($b['from']);
        if ($bf - $cursor >= 15) {
            $free[] = ['from' => $toHi($cursor), 'to' => $toHi($bf), 'minutes' => $bf - $cursor];
        }
        $cursor = max($cursor, $toMin($b['to']));
    }
    if ($winEnd - $cursor >= 15) {
        $free[] = ['from' => $toHi($cursor), 'to' => $toHi($winEnd), 'minutes' => $winEnd - $cursor];
    }

    return response()->json([
        'teacher'  => ['id' => $teacher->id, 'name' => $teacher->name],
        'day'      => $request->day,
        'window'   => ['start' => $toHi($winStart), 'end' => $toHi($winEnd)],
        'busy'     => $busy,
        'free'     => $free,
        'conflict' => $conflict,
    ]);
}
```

---

## 4. Frontend (create.blade.php) ka map

Poora script `create_scripts_v2.blade.php` mein hai. Ye uske functions ka naksha hai:

| Function / Block | Kaam |
|---|---|
| `loadClassDependents()` | Sections + groups load, Deferred return karta hai |
| `loadSubjects()` | Group ke hisab se subjects, `subjectsCache` mein |
| `runSearch()` | Existing timetable load, `savedDays` set, tabs aur label refresh |
| `renderRowsForDay()` | Din ki rows render, periods ke beech ke gap par **Break row** dobara banata hai |
| `addRow()` | Row banata hai. `is_break` ho to peeli row, `.input-group` replace |
| `subjectOptionsHtml()` | Saved subject group mein na ho to bhi option dikhata hai |
| `collectCurrentDayRows()` | Break ke baghair rows (`subject_name` ke saath) |
| `validateRows()` | Sirf current din ki rows validate |
| `#btnApply` | Current din ke periods + break generate |
| `markDirty()`, `refreshTabMarks()`, `updateSaveLabel()` | Unsaved `*` aur Save/Update label |
| `buildDayGrid()` | Table ki rows se Assembly/Period/Break grid |
| `renderAvailability()` | Modal ka poora UI (chips, timeline, busy list, available list) |
| `checkTeacherClash()` | Teacher/time change par AJAX check, toastr + modal |
| `.btnAssignPeriod` handler | Us period ki row mein teacher lagata hai |
| `#btnSave` | Sirf current din ka POST (`day` + `periods`) |

Constants:
```js
const SCHOOL = { start: '07:30', periodStart: '07:50' };  // assembly 07:30, periods 07:50 se
```

---

## 5. Daily workflow (naya data enter karne ka tareeqa)

1. Class, Section, Subject Group chun kar **Search**.
2. **Monday tab:** quick-generate fields bharein, **Apply**, phir har row mein Subject aur Teacher chunein, phir **Save Monday**.
3. **Tuesday tab** par jayein, dobara Apply, subject/teacher, **Save Tuesday**. Aise har din alag.
4. Teacher select karte waqt agar wo doosri class mein busy ho to modal khulega, wahan free period chun kar **Assign** karein.
5. Pehle ek class poori save karein, phir doosri class, kyunke clash check **saved data** par chalta hai.

**Zaroori rules:**
- Break ka waqt poore school mein ek jaisa rakhein (jaise 15 min). Alag break length se periods ke time aage-peeche ho kar teacher clash banate hain.
- Apply us din ki rows reset kar deta hai (subject aur teacher khali). Sirf time theek karna ho to rows mein manually badlein.
- Break DB mein nahi hota, gap se pehchana jata hai. Agar Interval > 0 ho to chhote gap bhi break row ban jayenge.

---

## 6. Purana saara class timetable data delete karna (fresh start)

**Pehle backup lein** (kam az kam table ka export):
```bash
mysqldump -u root -p your_database class_timetables > class_timetables_backup.sql
```
Table ka naam apne model/migration se confirm karein (aam taur par `class_timetables`).

### Tareeqa 1: tinker (sab se saaf)
```bash
php artisan tinker
```
```php
use App\Models\ClassTimetable;

ClassTimetable::count();            // pehle dekh lein kitne records hain
ClassTimetable::query()->delete();  // sab records delete
ClassTimetable::count();            // 0 aana chahiye
```
`delete()` foreign key constraints ke saath bhi chalta hai. IDs dobara 1 se shuru nahi hongi.

### Tareeqa 2: IDs bhi reset karne hon
```php
use Illuminate\Support\Facades\DB;
use App\Models\ClassTimetable;

Schema::disableForeignKeyConstraints();
ClassTimetable::truncate();
Schema::enableForeignKeyConstraints();
```
Is ke liye `use Illuminate\Support\Facades\Schema;` bhi chahiye. Agar kuch aur table `class_timetables` ko reference karti ho to truncate se pehle uski dependency check karein.

### Tareeqa 3: sirf ek class-section ka
```php
ClassTimetable::where('school_class_id', 1)->where('section_id', 1)->delete();
```

### Tareeqa 4: sirf ek din ka (class-section ka)
```php
ClassTimetable::where('school_class_id', 1)->where('section_id', 1)->where('day', 'Monday')->delete();
```
Ya UI se: `destroyDay` route lagane ke baad "Clear Day" button.

### Delete ke baad
1. `Ctrl+F5` se create page reload karein.
2. Class > Section > Group chun kar Search karein. Ab har din khali hoga aur label "Save Monday" dikhega.
3. Pehle **Class 1** ka poora hafta save karein, phir Class 2.
4. Clash check khud kaam karega: doosri class mein wahi teacher usi time par busy hoga to modal khulega.

---

## 7. Agla plan (Next topics)
> Pending tasks ab docs/ai/BACKLOG.md mein.

Priority ke hisab se:

### Phase A: Is module ko mukammal karna
1. ~~**Clear Day button** (`destroyDay`)~~ **DONE 04-10-2026**: alag route ki jagah `save()` ka `day_off` flag (section 9.1).
2. ~~**Index page week view mein Edit/Delete** har period ke liye (abhi `destroy($id)` UI mein use nahi ho raha).~~ **DONE 05-10-2026** (section 11.2)
3. ~~**Room number**~~ **DONE 04-10-2026**: Rooms master + class-wise room (section 9.2 aur 9.3).
4. ~~**Copy Day:** "Monday ko Tuesday-Friday par copy karo" taake har din alag Apply na karna pade (subject/teacher bhi copy hon, aur clash check chale).~~ **DONE 05-10-2026** (section 11.8)
5. ~~**Teacher dropdown mein busy teachers pehle se disable** (time select hote hi), taake clash ka intezar na karna pade.~~ **DONE 05-10-2026** (section 11.7)

### Phase B: Data safety
6. **Database constraint/index:** `(teacher_id, day, time_from, time_to)` aur `(school_class_id, section_id, day)` par index lagana (clash queries tez hon).
7. **Academic year:** `ClassTimetable` mein abhi `academic_year_id` nahi, jab ke Teacher assignments active session par chalte hain. Naya saal aane par purana timetable mix ho sakta hai. `academic_year_id` add karna ya naye saal par reset ka flow banana.
8. ~~**Concurrency:** do admin ek saath save karein to race condition ho sakti hai. Save ke andar transaction mein `lockForUpdate()` ya clash check dobara commit se pehle.~~ **DONE 06-10-2026** (section 14)
9. **Feature tests:** save, clash, day-wise delete, destroyDay ke Laravel tests.

### Phase C: Naye features
10. **Teacher Timetable view** ko class timetable jaisa polish (printable, per-teacher weekly load, total periods count).
11. **Student/Parent portal timetable** (read-only, class-section ke hisab se).
12. **Print / PDF export** (class aur teacher timetable).
13. **Substitute teacher:** kisi din ke liye temporary replacement, aur teacher leave se link.
14. **Timetable reports:** teacher workload, free periods, kaun si class mein koi period khali hai.
15. **Settings page:** `SCHOOL` timing (assembly, period start, break length) ko JS constant ki jagah DB/settings se lana.

---

## 8. Known limitations (abhi ke)

- Break DB mein save nahi hota, sirf gap se pehchana jata hai.
- ~~Ek hi din ko poora khali nahi chhoda ja sakta jab tak `destroyDay` na ho (`min:1` rule).~~ Resolved: `day_off` flag (9.1).
- Clash check sirf **saved** data par hota hai. Unsaved changes alag class mein nazar nahi aate.
- Edit mode mein agar class ke multiple subject groups hon to user ko Group chun kar Search karna padta hai.
- Controller ka `free` / `window` ab sirf backup hai, modal ka grid JS banata hai.
- Modal ka `SCHOOL` constant (07:30 / 07:50) JS mein hardcoded hai.

---

## 9. Update: 04-10-2026 (aaj ka kaam)

### 9.1 Weekly Off (Saturday/Sunday off ho to save)
- **Masla:** off din khali hota tha, lekin JS ("Add at least one period") aur backend (`periods` `required|min:1`) dono save rok dete the.
- **Fix:** `ClassTimetableController::save()` ke shuru mein `day_off` branch. `day_off=1` aaye to sirf us din ki rows delete hoti hain (clash/overlap/transaction logic nahi chheda). `destroy($id)` pehle jaisa.
- **create.blade.php:** khali din par `#btnSave` SweetAlert confirm ("Mark X as Weekly Off?") dikhata hai, phir `sendSave(day, classId, sectionId, periods, isDayOff)` se POST. Weekly off ke baad `savedDays` se wo din nikal jata hai.
- **Bug (isi se nikla):** DataTables ki "No periods added yet" `<tr>` (`td.dataTables_empty`) ko `validateRows()` aur `collectCurrentDayRows()` period samajh rahe the, is liye "Please fix highlighted fields" aata tha. Dono jagah ye row skip hoti hai.
- Routes file ([front_office.php](routes/front_office.php)) mein ek stray backtick tha jis se poora parse error aa raha tha, hata diya.

### 9.2 Rooms master module (Academic > Manage Room)
- **Nayi files:** `RoomController`, `Room` model, `rooms/index|modals|fields.blade.php`, migrations `2026_10_05_000001_create_rooms_table`, `..._000002_add_room_id_to_class_timetables_table`, `..._000003_add_class_section_to_rooms_table`.
- **Columns:** `room_no` (unique), `name`, `building`, `floor`, `capacity`, `type`, `status`, `school_class_id`, `section_id`. `unique(school_class_id, section_id)` (NULL wale Lab/Hall duplicate nahi maane jate).
- **Constants:** `Room::TYPES` (Classroom, Lab, Hall, Library, Other), `Room::FLOORS` (Ground Floor, 1st..5th Floor). Floor string mein save hota hai; purana free-text floor Edit mein option ban kar pre-selected rehta hai aur validation us ko allow karti hai.
- **Rules:** sirf Room No. required. Class aur Section dono ya dono khali; section us class se mapped ho; ek class-section ko sirf ek room. Timetable mein use hone wala room delete nahi hota (error toastr), Inactive kiya ja sakta hai.
- **UI:** label `for` + unique ids (`add_*`, `edit_{id}_*`), placeholders, class se section ka AJAX dropdown (`rooms/get-sections/{classId}`), empty submit par sab missing fields red + neeche `.invalid-feedback` message + ek hi toastr + focus, server errors usi modal mein field ke neeche, modal band hone par errors saaf, `modal-lg`.
- **Permissions:** `access-rooms`, `manage-rooms` PermissionSeeder + RoleSeeder (admin, receptionist). Existing DB ke liye RoleSeeder dobara nahi chalana, tinker snippet (neeche 9.5).

### 9.3 Room class-wise (period-wise nahi)
- Faisla: room period ka nahi, class-section ka hota hai. Create page se period-wise Room select/column/payload/validation hata diya.
- `class_timetables.room_id` column aur `ClassTimetable::room()` rehne diye (future lab exception ke liye), UI aur save ab set nahi karte.
- Cards par Room No.: `ClassTimetableController::getData()` aur `TeacherTimetableController::getData()` class-section ke assigned room ko `assigned_room_no` ke naam se rows par jodte hain (1 query, N+1 nahi). Teeno index views (class timetable, teacher timetable, teacher portal) `item.assigned_room_no || '-'` dikhate hain. Teacher portal bhi `TeacherTimetableController::getData` use karta hai.

### 9.4 Jo faisle ya sawal khule hain
- ~~**Migration `teacher_id` FK:** `class_timetables.teacher_id` abhi `constrained('users')` hai, jab ke baaki code (`exists:teachers,id`, model, AGENTS.md) `teachers.id` maanta hai.~~ **DONE 05-10-2026** (migration `2026_10_05_000005_fix_teacher_id_foreign_keys`, dekhein 11.10).
- ~~**Index "Weekly Off" vs "Not Scheduled":** abhi sirf Sunday khali ho to "Weekly Off" dikhta hai, Saturday off save karne ke baad "Not Scheduled". Do raste: (1) har khali din "Weekly Off", ya (2) off day DB mein yaad rakhna (nayi migration). Meri raay: pehle (1). Faisla baqi hai.~~ Resolved by Task 1 (11.1).
- Teacher timetable par khali din "Weekly Off" nahi, "No periods/Free" behtar hai.

### 9.5 Commands (jo chalane hain, agar abhi tak nahi chalaye)
```bash
php artisan migrate
```
`2026_10_05_000003_add_class_section_to_rooms_table` pichli baar pending thi. Check: `php artisan migrate:status`.

Existing DB par permissions (`php artisan tinker`):
```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

foreach (['access-rooms', 'manage-rooms'] as $p) {
    Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
}
foreach (['admin', 'receptionist'] as $r) {
    Role::findByName($r, 'web')->givePermissionTo(['access-rooms', 'manage-rooms']);
}
```
```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

---

## 10. Kal ka plan (05-10-2026)

Ek ek step, har step ke baad test:

1. **Pehle verify (5 min):** `migrate:status` mein teeno rooms migrations "Ran", permissions tinker se lag chuki hon, receptionist se `/rooms` khule. Rooms page par 9.2 ki pichli test checklist (empty submit, duplicate room_no, Edit ka server error sahi modal mein) browser mein dohrao.
2. **Rooms + timetable end-to-end test:** Class 1-A ko room assign karo, teeno cards (class timetable, teacher timetable, teacher portal) par Room No. dekho, room badal kar cards update hone ka check.
3. **Weekly Off display faisla + implement:** index.blade.php (aur teacher_timetable) ke khali din ka text. Pehle un files ko parho, phir chhota plan.
4. **`teacher_id` FK masla (manzoori ke baad):** purani migration edit ya nayi migration ka tareeqa tay karo, `lesson_plans.class_timetable_id` FK ka khayal rakho (rollback par masla aa sakta hai).
5. **Phase A baaqi:** A.2 index week view mein Edit/Delete har period ke liye, A.4 Copy Day (clash logic ke qareeb, pehle puchna), A.5 busy teachers pehle se disable.
6. **TEACHER_ASSIGNMENT_WORKFLOW.md:** tracker abhi sab unchecked hai. Code se dekh kar tay karo kaunse steps (A1-A7, B1-B3, C, D) pehle se ho chuke hain, aur tracker update karo.
7. **Agar waqt bache:** Phase B (`academic_year_id`, indexes `(teacher_id, day, time_from, time_to)`, concurrency, feature tests).

**Yaad rakhne wali rules:** `ClassTimetableController` ka clash/overlap/transaction logic bina poochay nahi chhedna. Nayi migration bina manzoori nahi. Koi data-delete command khud nahi chalani (command likh kar do). JS mein `??`/`?.` nahi, `||` aur ternary.

### Faisle (04-10-2026, shaam)
- **Index khali din:** sirf **"Not Scheduled"** rahega. Weekly Off wala alag display ya DB mein off day yaad rakhne ki zaroorat nahi. Isi liye kal ke plan ka **step 3 (Weekly Off display) cancel**. Dhyan: purane code mein Sunday ke liye hardcoded "Weekly Off" hai, wo ab bhi "Not Scheduled" hona chahiye ya nahi, ye kal index.blade.php parh kar aap se confirm karna.
- **`teacher_id` FK:** baad mein. Kal ke plan ka **step 4 abhi nahi**.
- **Commands:** `php artisan migrate` aur permissions wali tinker commands + `permission:cache-reset` aap chala chuke hain. Kal ka verify step 1 mein sirf browser test baqi (migrate/permissions dobara nahi).

---

## 11. Update: 05-10-2026

### 11.1 Task 1: Weekly Off / Holiday display (Academic Year + Calendar se, no hardcode)
- **Rule (har date ke liye, is order mein):**
  1. Active (`status=1`) calendar entry, active year, event type `is_off_day=1` -> **"Not Scheduled" + event title** (periods hon tab bhi).
  2. Us din ke periods saved hain -> **periods dikhao** (weekly off din par bhi, e.g. Sunday practice).
  3. Din active year ke `weekly_off_days` mein hai -> **"Weekly Off"**.
  4. Warna -> **"Not Scheduled"**.
- Hardcoded `day === 'Sunday'` "Weekly Off" teeno views se hata diya. Active year na ho to weekly off khali maana jata hai (koi hardcoded Sunday nahi).
- **Migration `2026_10_05_000004_add_off_day_settings` (RUN ho chuki):** `academic_years.weekly_off_days` (JSON, existing years `["Sunday"]`), `event_types.is_off_day` (boolean default 0, "Holiday" naam wale rows 1).
- **`AcademicYear` model:** `weekly_off_days` fillable + `array` cast, aur `protected $attributes = ['weekly_off_days' => '["Sunday"]']` taake seeder/tinker se bane records NULL na hon.
- **Week context:** `getData()` (class aur teacher dono) mein optional `week` (koi bhi `Y-m-d`). `week` na ho to response bilkul purani shape (create page `create.blade.php:428` is par chalta hai, safe). `week` ho to `{timetable, day_status, week}`. Index views mein Prev / This Week / Next aur har column par date.
- **`TimetableDayStatus::forWeek()`** (naya `app/Services/TimetableDayStatus.php`): 2 queries (active year + off-type calendar entries jo hafte se overlap karti hain), date-only compare (`whereDate`, `DATE(COALESCE(end_date, start_date))`), Monday explicit (`Carbon::MONDAY`).
- **Academic Year form:** 7 weekly-off checkboxes (Sunday pre-ticked); `store()` mein `weekly_off_days_present` hidden marker, na ho to default `['Sunday']`; `update()` mein `$request->input('weekly_off_days', [])` (sab untick = khali array). Validation `nullable|array` + `weekly_off_days.*` `in:` (7 din).
- **Event Types page:** "Off Day" checkbox (`for=` label), table mein "Off Day" badge column, Edit mein pre-select. (Ye page modal nahi, inline form hai; stale `event_type/create|edit.blade.php` nahi chhede.)
- **Files:** new `database/migrations/2026_10_05_000004_add_off_day_settings.php`, `app/Services/TimetableDayStatus.php`. Edit: `AcademicYear.php`, `EventType.php`, `AcademicYearController.php`, `EventTypeController.php`, `admin/academic_years/index.blade.php`, `admin/event_type/index.blade.php`, `ClassTimetableController::getData()`, `TeacherTimetableController::getData()`, `admin/class_timetable/index.blade.php`, `admin/teacher_timetable/index.blade.php`, `teacher/timetable/index.blade.php`.

### 11.2 Task 2 (A.2): Edit / Delete per period (class timetable index)
- Har asli period card par Edit + Delete (sirf `periods` branch mein, "Not Scheduled"/"Weekly Off" cards par nahi), `@can('manage-class-timetable')` se (`canManageTT`). URLs route helpers se (`editBaseUrl`, `deleteUrlTpl`), koi hardcoded path nahi.
- Delete: SweetAlert confirm ("removed from EVERY {day}, not only this date. Linked lesson plans will lose their timetable link"), phir existing `destroy($id)` route, toastr, aur usi dikhaye hue hafte ka card refresh (`currentWeek`), full reload nahi. 404 ("Period not found") par bhi card refresh.
- Edit: `create?class_id=&section_id=&day=` kholta hai; create page `?day=` se usi din ka tab active karta hai (`currentDay`). Wo block `loadClassDependents().always()` ke andar hai, taake sections/groups ka flow bina `day` jaisa rahe.
- Class timetable cards par **Class** aur **Section** rows add hui (selected dropdown ke text se, escaped, `getData()` mein koi change nahi). Ye rows is view mein pehle kabhi thi hi nahi; teacher timetable mein hain.
- `lesson_plans.class_timetable_id` FK `nullOnDelete()` hai: period delete par error nahi, lekin lesson plan ka timetable link NULL.
- **Note (by design, code change nahi):** beech ka period delete karne se time gap banta hai; create page edit mode mein wo gap **Break row** ke taur par dikhta hai.
- **Files:** `admin/class_timetable/index.blade.php`, `admin/class_timetable/create.blade.php` (sirf `&day=` handling). `destroy()`, `save()`, clash logic, routes untouched.

### 11.3 HIGH PRIORITY (alag task): holiday logic galat
- Is school mein **"Holiday" naam ka event type hai hi nahi**. Off Day manually Quaid Day, Muharram ul Haram, Eid ul Azha, Eid ul Fitar, Labour Day, Independence Day par tick kiya gaya.
- `StaffAttendanceController::isHoliday()` (name = 'Holiday' match) aur `SalarySlipService` (working days ka holiday count, name = 'Holiday') is liye ye holidays **bilkul count nahi kar rahe**: staff attendance aur salary working days galat ho sakte hain.
- **Task:** dono ko `event_types.is_off_day = 1` par switch karna. Dhyan: `markHoliday()` bhi naya "Holiday" type `firstOrCreate` karta hai, uska bhi dekhna (us type par `is_off_day = true` set ho). Abhi **chhedna nahi**, alag task mein.
- **UPDATE 05-10-2026:** ye task alag file mein hai; off-days payroll fix DONE aur tested (`off_days_holidays_summary.md`). Sirf Mark Holiday toggle ka test baqi.

### 11.4 Local environment: `config:cache` zaroori
- **Masla:** "Failed to load subject groups" (aur dusre random AJAX failures) ka sabab `?day=` code nahi tha. Parallel AJAX requests par kabhi kabhi 500 "No application encryption key has been specified" (`MissingAppKeyException`, `laravel.log` mein) aata tha (APP_KEY race). Fix: `php artisan config:cache`.
- **Ab local par `config:cache` zaroori hai.** `.env` badalne ke baad ya `php artisan optimize:clear` ke baad dobara chalana:
  ```bash
  php artisan config:cache
  ```
  (`optimize:clear` config cache bhi hata deta hai, phir ye race wapas aa sakti hai.)
- **`env()` ka audit:** `app/`, `routes/`, `resources/`, `database/`, `bootstrap/`, `public/` ke `*.php` mein `env(` ka **koi** istemal nahi mila (grep). Yani `env()` sirf `config/` mein hai, config-cache ke saath safe. (`.env` file parhi nahi gayi.)

### 11.5 Remaining (agla kaam)
> Pending tasks ab docs/ai/BACKLOG.md mein.
- ~~**Task 3 (A.5):** busy teachers disable~~ **DONE 05-10-2026** (11.7).
- ~~**Task 4 (A.4):** Copy Day~~ **DONE 05-10-2026 (browser tested)** (11.8).
- ~~**Task 4.1:** Save/tab switch par search filter se data loss~~ **DONE 05-10-2026** (11.9). `collectCurrentDayRows()` ab collect se pehle search filter clear karta hai.
- **Low priority (abhi nahi):** periods ka search box (`#periodSearchInput`) practically useless hai. DataTables row ka poora text match karta hai, jis mein dropdowns ki saari `<option>` lists shamil hain, to koi bhi subject/teacher naam har row se match hota hai; input values (jaise Break row ka "Break") match nahi hoti. Option: search box hata do, ya sirf selected values par search karo.
- **Task 5:** `TEACHER_ASSIGNMENT_WORKFLOW.md` tracker: code se dekh kar A1-A7, B1-B3, C, D ka evidence (file/method), checkboxes aap ke confirm ke baad.
- ~~**Optional:** index par "Edit Timetable" ko `manage-class-timetable` ke peeche chhupana~~ **DONE 05-10-2026** (11.7, Task 2.1).
- ~~**Postponed:** `teacher_id` FK masla~~ **DONE 05-10-2026** (migration `2026_10_05_000005`, dekhein 11.10).

### 11.6 STRICT RULES (naya session inhein follow kare)
> Rules ab CLAUDE.md mein.
- Ek task ek waqt: files parho, chhota plan (files + exact changes) do, **approval ka intezar**, phir edit; task ke baad test checklist, aur agla task aap ke confirm ke baad.
- Minimal edits; aap ka code, logic aur Roman Urdu comments rakho; koi method/block aap ke kahe baghair rewrite/remove nahi.
- `ClassTimetableController::save()` ka clash/overlap/transaction logic nahi chhedna (`teacherAvailability()` / `checkTeacherClash()` bhi Task 3 mein nahi).
- Nayi migration aap ki manzoori ke baghair nahi, aur file bana kar rakho, khud na chalao. `migrate:fresh/reset`, `db:wipe`, `DROP TABLE`, ya koi bhi data-delete command khud na chalao, command likh kar do.
- JS mein `??` aur `?.` nahi (editor formatter `??` ko `? ?` bana deta hai); `||` aur ternary use karo. Blade se JS mein value `@json(...)` se.
- Conventions: `response()->json(['success' => ...])`, toastr, Bootstrap sm-size fields, `can:` middleware route groups, `Route::controller()` style, Spatie permissions, `with([...])` (no N+1), `DB::transaction()` multi-table writes. Koi nayi library/package nahi.
- Teacher ke saare `teacher_id` columns `teachers.id` hain, `users.id` nahi. `is_class_teacher` flag aur `has_subjects` logic nahi.
- Ye project git repo nahi: git commands nahi. Har task ke end mein changed/new files ki list (one-line summary) manually do.
- `.env` na parho na likho. Unrelated files refactor/format nahi.
- Output: sirf badla hua hissa dikhao, har change ke saath ek line (kya aur kyun); end mein (a) kya badla (b) kya test karna hai (c) agla step (d) workflow tracker update.

### 11.7 Task 2.1 + Task 3 (A.5): DONE 05-10-2026 (browser mein tested)
- **Task 2.1:** `admin/class_timetable/index.blade.php` card header ka "Edit Timetable" link ab `canManageTT` (jo `@can('manage-class-timetable')` se set hota hai) ke peeche hai. Link JS template string mein hai, isliye Blade `@can` seedha nahi lagaya.
- **Task 3 (A.5):** create page par busy teachers per-slot disable, naam ke saath "(Busy)". Row ka apna selected teacher kabhi disable nahi hota.
- **Route:** `GET /class-timetable/busy-teachers` (`class_timetable.busy_teachers`) **`manage-class-timetable` group** mein (`teacher-availability` ke saath), taake create page (manage-only) par silent 403 na aaye.
- **Controller:** naya read-only `ClassTimetableController::busyTeachers()`, `save()` wali exclusion/overlap condition, ek query. Response **slot-keyed**: `{success, busy: {"08:00-08:45": [3,7], ...}}` (unique slots; row-index key nahi, kyunke `sortRowsByTime` rows reorder kar sakta hai).
- **JS (`create.blade.php`):** `refreshBusyTeachers()` (ek batched request per din, `busyToken` se stale response ignore, har row apni `time_from-time_to` key se lookup). Triggers: time change, `renderRowsForDay()` ke end mein (Search + day tab switch), Apply ke baad. Fail par chup; `save()`, `teacherAvailability()`, `checkTeacherClash()` aur clash modal fallback untouched.
- **Files:** `admin/class_timetable/index.blade.php`, `routes/front_office.php`, `app/Http/Controllers/Backend/ClassTimetableController.php` (sirf naya method), `admin/class_timetable/create.blade.php` (sirf JS).
- **Agla:** Task 4 (Copy Day), aap ke confirm par.

### 11.8 Task 4 (A.4): Copy Day, DONE 05-10-2026 (browser tested)
- **Frontend-only.** `save()`, clash/overlap, `teacherAvailability()`, `busyTeachers()`, routes: untouched. Koi naya route nahi.
- **Data structure:** `allData[day] = [{subject_id, subject_name, time_from, time_to, teacher_id}]` (break rows kabhi nahi). Source = current din ki on-screen rows (`collectCurrentDayRows()`); target din ke liye `allData[target]` ki deep copy set hoti hai, `dirtyDays.add(target)` (tab par `*`), tab kholne par `renderRowsForDay()` render karta hai aur gap se Break row rebuild hoti hai. Kuch save nahi hota; har din maujooda day-wise Save se.
- **UI:** toolbar mein "Copy Day" button (Add New ke bagal), `#copyDayModal` (`modal-sm`) mein current din ke ilawa sab din ke checkboxes (clickable labels). Active year ke `weekly_off_days` wale din "(Weekly Off)" hint ke saath unchecked, aur "Select all" unhein select nahi karta. Active year na ho to koi weekly off nahi.
- **Overwrite:** jin target dinon mein rows hon (`savedDays` ya `allData`), un ke naam SweetAlert confirm mein.
- **Busy re-check:** har target din ke liye `busy-teachers` ki ek request (existing endpoint, `day=target`, unique slots); busy teacher wali row par `busy_flag` -> teacher select `is-invalid` + "Busy in another class on this day", aur ek combined warning toastr ("Tuesday: P2, P4"). `copyToken` se stale response ignore. Limitation: flag tab chhodne par dobara collect nahi hoti (sirf pehli baar tab kholne par dikhti hai); save par backend clash check pakadta hai.
- **Guard:** DataTables search box mein text ho to Copy block (adhoori copy se bachne ke liye). Save/tab switch ka same masla Task 4.1 (11.9) mein fix ho chuka hai.
- **`create()`:** read-only `$weeklyOffDays` (AcademicYear `is_current=1`, `weekly_off_days`) view ko pass hota hai, JS mein `@json`.
- **Files:** `admin/class_timetable/create.blade.php` (button, modal, JS block, `addRow` mein `busy_flag`), `ClassTimetableController::create()` (+ `use App\Models\AcademicYear`).

### 11.9 Task 4.1: search filter se data loss fix, DONE 05-10-2026 (browser mein tested)
- **Masla:** DataTables search filter hidden rows ko DOM se hata deta hai; `collectCurrentDayRows()` sirf visible rows deta tha. Save par baaqi saved periods mit sakte the, aur tab switch par hidden rows memory (`allData`) se bhi ghayab ho jati thin.
- **Fix:** `collectCurrentDayRows()` ke shuru mein search box khali + `periodDataTable.search('').draw()` (agar text ho). Tab switch, Save aur Copy teeno isi se rows lete hain, isliye teeno theek. `save()` backend untouched. Copy Day ka "Clear the search box" guard waise hi rakha.
- **UI:** "Yes, overwrite" (Copy Day) aur "Yes, mark off" (Weekly Off) SweetAlert confirm buttons ab `confirmButtonColor: '#d33'` (index delete confirm jaisa; wahan cancelButtonColor set nahi).
- **Files:** `admin/class_timetable/create.blade.php` (3 chhote edits).

### 11.10 `teacher_id` FK fix: DONE 05-10-2026 (migration chal chuki, browser mein tested)
- **Findings:** `class_timetables.teacher_id` aur `lesson_plans.teacher_id` dono `constrained('users')->cascadeOnDelete()` the, jab ke saara code `teachers.id` save karta hai (`exists:teachers,id`, `ClassTimetable::teacher()`, lesson plan mein `$classTimetable->teacher_id` copy). Risk: (1) teachers.id kisi users.id se na mile to insert FK error 1452; (2) users.id jo kisi teachers.id ke barabar ho, us user ke force delete par us teacher ke periods/lesson plans cascade se mit jate. `teacher_assignments.teacher_id` pehle se `teachers` par sahi tha. Baaki `*_by` / `user_id` columns asal mein users hain (theek).
- **Migration:** `database/migrations/2026_10_05_000005_fix_teacher_id_foreign_keys.php`. `up()`: dono tables mein orphans ho to exception (data khud change nahi karti), phir `Schema::getForeignKeys()` se `teacher_id` ka purana FK naam se independent drop, naya FK `teachers.id` par `restrictOnDelete()`. `down()`: sirf naya FK hatata hai, purana users-cascade FK wapas nahi lagata. Rollback sirf `php artisan migrate:rollback --step=1`.
- **RESTRICT ka faisla:** `TeacherDestroy` pehle se periods/assignments wale teacher ko block karta hai, RESTRICT wahi rule DB level par pakka karta hai. Cascade se history chupke mit sakti thi; SET NULL column NOT NULL hone ki wajah se nahi. `lesson_plans.class_timetable_id` FK (`nullOnDelete`) untouched.
- **Verify (SQL A):** dono tables `teacher_id -> teachers`, `DELETE_RULE = RESTRICT`. Browser: Class 2 Saturday timetable save, teacher portal lesson plan save + edit, admin lesson plan delete, teacher delete block ("assigned as a Class Teacher or has timetable periods"), `laravel.log` mein SQLSTATE 23000 nahi.
- **Orphan cleanup (migrate se pehle):** 4 test lesson plans (ids 1-4, `teacher_id = 13` = user Maria Khan jis ki `teachers` row nahi, `class_timetable_id` NULL) full backup ke baad user ne khud delete kiye. Migration ne koi data delete nahi kiya.
- **Code fix:** `app/Models/LessonPlan.php` `teacher()` ab `belongsTo(Teacher::class, 'teacher_id')` (pehle `User::class`). Kahin `with('teacher')` use nahi hota tha, isliye bug chupa tha.
- **Files:** new `2026_10_05_000005_fix_teacher_id_foreign_keys.php`; edit `app/Models/LessonPlan.php` (1 line).

**Baad ke liye notes (abhi fix nahi kiye):**
- `User::timetables()` (`app/Models/User.php:178`) `hasMany(ClassTimetable::class, 'teacher_id')` users.id use karta hai: dead code (kahin call nahi). Jaan boojh kar nahi chheda.
- **Storage cleanup:** ids 1-4 ke attachment / lecture_video files shayad ab bhi `public/uploads/lesson_plan/` mein hain (dono controllers `public_path('uploads/lesson_plan')` mein save karte hain; SQL se delete hone ki wajah se controller ne files nahi hatayi). Pehle un 4 rows ke `attachment` / `lecture_video` naam backup SQL se dekhein, phir manually saaf karein. Abhi kuch delete nahi kiya.
- **Data mismatch:** user Maria Khan (id 13) ko teacher salary slips milti hain, lekin `teachers` row nahi. Alag jaanch. **Update 06-10-2026:** `teachers` rows 4 aur 5 app ke bahar hard-delete ho chuki thin. Users 12 aur 13 ki `teachers` rows tinker se dobara bana di gayin (06-10-2026, verified). User 6 seeder demo account, teacher role removed 07-10-2026 (verified).
- **UI:** teacher Lesson Plan Edit modal ka button "Save" (yellow) hai; convention ke mutabiq "Update" (`bg-success`) hona chahiye.
- **UI:** teacher aur admin Lesson Plan "Weekly Schedule" views Sunday ko "Not Scheduled" dikhate hain; baaki timetable views ki tarah `weekly_off_days` (11.1 ka rule) use karein.
- **Env:** 11:06 par `MissingAppKeyException` ("production" env, `.env` load nahi hui) migration ke baad dobara aayi; `config:cache` dobara chalaya. Agar config cache hote hue bhi repeat ho to alag se investigate karein (dekhein 11.4).

## 12. Update: 05-10-2026 (Task: `academic_year_id` on `class_timetables`, Step 1)

### 12.1 Masla
`class_timetables` mein saal nahi tha. Naya academic year current mark karne par purana timetable naye saal ke saath mix hota aur teacher clash / busy check purane saal ki rows ko bhi busy maanta.

### 12.2 Kya badla
- **Migration `2026_10_05_000006_add_academic_year_id_to_class_timetables_table` (RUN ho chuki):** exactly ek `is_current=1` year ka assert (warna `RuntimeException`, data khud theek nahi karti), nullable column + backfill (purani saari rows current year ki), phir NOT NULL, FK `academic_years.id` par `restrictOnDelete()`, aur index `class_timetables_year_class_section_day_index` `(academic_year_id, school_class_id, section_id, day)`. `down()` FK, index aur column drop karta hai.
- **Year source:** `Controller::getActiveSessionId()` (uncached `is_current=1`). `AcademicYear::getActiveSessionId()` (1 ghanta cache) Step 1 ke naye code mein use nahi hua, kyunki `markAsCurrent()` cache clear nahi karta.
- **`ClassTimetableController`:** `teacherAvailability()`, `busyTeachers()`, `getData()` mein sirf year filter. `save()` mein shuru mein `$yearId` (na ho to 422 "No current academic year set. Mark a session as current first."), day_off delete / teacher clash / transaction delete mein year filter, `create()` mein `academic_year_id`. Clash, overlap aur transaction logic same. `destroy()` untouched (id se delete).
- **`TeacherTimetableController`:** `getData()` aur `getTeacherInfo()` ki `$teachingClasses` query mein year filter.
- **`ClassTimetable` model:** `academic_year_id` fillable + `academicYear()` relation.
- **`TimetableDayStatus`:** `class_timetables` query nahi karta, change nahi. Quick Generate sirf client-side JS hai (server query nahi).

### 12.3 RESTRICT FK ka faisla
- CASCADE se saal delete hone par timetable mit jata aur `lesson_plans.class_timetable_id` (`nullOnDelete`) ka link toot jata. Isliye `restrictOnDelete()`.
- Side effect: jis saal ka timetable ho, `AcademicYearController::destroy()` us par SQL error dega (guard BACKLOG mein).
- `SHOW INDEX` (pehle): slot/teacher columns par koi unique key nahi thi, is liye kisi unique key mein year add nahi karni padi. `academic_years.id` aur `class_timetables.id` dono `bigint unsigned`, InnoDB.

### 12.4 Test results (browser, tested and confirmed 05-10-2026)
- Migration chali: 73 rows `academic_year_id = 1`, NOT NULL, FK restrict, composite index maujood.
- Class timetable index, Prev/Next week, naya period save (id 164, year 1), busy teacher disabled, availability modal, JS clash toastr, admin + teacher portal timetable: sab OK.
- `storage/logs/laravel.log` mein SQLSTATE nahi.

### 12.5 Files
- New: `database/migrations/2026_10_05_000006_add_academic_year_id_to_class_timetables_table.php`
- Edit: `app/Models/ClassTimetable.php`, `app/Http/Controllers/Backend/ClassTimetableController.php`, `app/Http/Controllers/Backend/TeacherTimetableController.php`, `docs/ai/BACKLOG.md`

### 12.6 Baaki
- **Step 2: code DONE 05-10-2026, browser tested 06-10-2026 (MarkSheet teacher path ke ilawa).** Pass: admin Lesson Plan grid, teacher portal Lesson Plan grid + save/edit, teacher Syllabus Status Section 2, MarkSheet admin unchanged, `laravel.log` mein SQLSTATE nahi. Skip: MarkSheet teacher (own class + save, doosre teacher ki class, save 403), kyunke teacher ke paas abhi `manage-marksheets` nahi (F1 pending); BACKLOG F1 line mein note. Optional "naya khali year" test skip. Year filter: `LessonPlanController:38`, `TeacherLessonPlanController:65`, `TeacherProfileController:144` (`getActiveSessionId()`), `MarkSheetController:58,164` (`$exam->academic_year_id`, faisla: purane saal ke exam par marks entry block na ho). `findOrFail` slot lookups jaan boojh kar unfiltered (BACKLOG P3). `php -l` clean, blade/JS change nahi.
- "Copy timetable from previous year", AcademicYear destroy guard, aur class/section/subject FK ke CASCADE ka review: BACKLOG mein.
- ~~`AcademicYear::markAsCurrent()` cache clear nahi karta: BACKLOG P1.~~ FIXED 06-10-2026 (dekhein 13).

## 13. Update: 06-10-2026 (Task: `active_session_id` cache clear on mark as current)

### 13.1 Masla
`AcademicYear::getActiveSessionId()` `cache()->remember('active_session_id', 3600, ...)` use karta hai. `AcademicYearController::markAsCurrent()` cache clear nahi karta tha, is liye `TeacherTimetableController::getTeacherInfo()` (is ka akela caller) 1 ghanta tak purana year use kar sakta tha.

### 13.2 Kya badla
- `AcademicYearController::markAsCurrent()`: `DB::transaction()` ke **baad** `cache()->forget('active_session_id');`. Transaction ke baad is liye, taake commit se pehle koi request purani value cache kar de to woh bhi clear ho jaye.
- `AcademicYear.php` (key + 3600s TTL) same. `store()` `is_current` set nahi karta (explicit `create()` array), `update()` / `destroy()` / `toggleStatus()` bhi nahi, is liye wahan forget ki zaroorat nahi.
- Repo mein `active_session_id` key sirf `AcademicYear.php:61` par. `Controller::getActiveSessionId()` uncached hai, change nahi.

### 13.3 Test results (browser, tested and confirmed 06-10-2026)
- Doosra year current mark kiya: teacher timetable info foran naya year dikhati hai (1 ghanta wait nahi). Wapis purana year current: purana year wapis. OK.

### 13.4 Files
- Edit: `app/Http/Controllers/Backend/AcademicYearController.php`

## 14. Update: 06-10-2026 (Task: `save()` concurrency, teacher clash race)

### 14.1 Masla
Do admin same teacher ko same time slot mein (alag class-section) ek saath save karte to dono ka teacher clash check (transaction se pehle, bina lock) pass ho jata, kyunke dusre ki rows abhi commit nahi hui hoti. Nateeja: teacher double booked.

### 14.2 Kya badla (sirf `ClassTimetableController::save()` ka `DB::transaction` block)
- Transaction ka **pehla statement:** request ke distinct `teacher_id`s ki `teachers` rows `lockForUpdate()` (ids sorted, `orderBy('id')`, deadlock se bachne ke liye; `Teacher::withTrashed()` kyunke `exists:teachers,id` soft-deleted id bhi pass karta hai). Clash rows maujood hi nahi hoti, isliye lock `teachers` row par hai, `class_timetables` par nahi.
- Lock ke baad wahi teacher clash query dobara. Clash mile to closure kuch likhe baghair clashing `$row` return karta hai (khali commit), bahar wahi 422 message aur same JSON shape.
- Bahar wala pehla clash check (fast path), overlap check, `day_off` branch, validation aur response shapes untouched. Koi migration / route / JS / blade change nahi.
- Deadlock retry (`DB::transaction(..., 3)`) jaan boojh kar add nahi kiya.

### 14.3 Test results (browser + MySQL CLI lock, tested and confirmed 06-10-2026)
- Race test (CLI mein `SELECT ... FROM teachers WHERE id = ? FOR UPDATE`, do tabs mein same teacher + same slot, phir `ROLLBACK`): slot mein sirf 1 row, dusri save par 422 "Teacher already assigned elsewhere".
- Normal save, busy teacher disable, alag teachers wali saves, same class-section update: sab OK.
- `laravel.log` mein sirf lock test ka expected 1205 (lock wait timeout), koi aur SQLSTATE nahi. Test rows saaf kar di gayin.

### 14.4 Files
- Edit: `app/Http/Controllers/Backend/ClassTimetableController.php`
