# Off Days / Holidays summary (payroll, staff attendance, aage student attendance)

**Status (05-10-2026):** Step 1 investigation DONE. Step 2 implement + tested (sirf Mark Holiday toggle ka test pending). Deduction/net-cap fix aur `update()` validation bhi DONE. Ye file ab sab modules ke off-days/holidays ka ek hi summary hai (class timetable ki file `class_timetable_project_summary.md` alag rehti hai).
**Rules:** class_timetable_project_summary.md section 11.6 (STRICT RULES). Koi migration/data-delete command nahi, `.env` nahi, unrelated refactor nahi, JS mein `??`/`?.` nahi.
> Rules ab CLAUDE.md mein.

---

## 1. Masla
Is school mein "Holiday" naam ka event type hai hi nahi. Off din `event_types.is_off_day = 1` se mark hain (Quaid Day, Muharram ul Haram, Eid ul Azha, Eid ul Fitar, Labour Day, Independence Day). Staff attendance aur salary slip code naam `'Holiday'` match karta hai, is liye in off-days ko bilkul nahi gintay (11.3 mein likha tha).

## 2. Findings (Step 1)

### 2.1 Jahan "Holiday" naam match hota hai
| File:line | Kya karta hai |
|---|---|
| `StaffAttendanceController::isHoliday($date)` `:323-334` | `AcademicCalendar` jahan `eventType.name = 'Holiday'`, `status = 1`, `whereDate(start_date) <= date`, `end_date >= date` ya `end_date` NULL. **Year scope nahi.** Sirf `CreateAttendance()` `:87` se call hota hai. |
| `StaffAttendanceController::markHoliday()` `:339-390` | `EventType::firstOrCreate(['name'=>'Holiday'], ['color'=>'#dc3545','status'=>1])` (**`is_off_day` set nahi**, default 0). Phir active year (`is_current=1`) ki ek single-day `AcademicCalendar` entry (`title 'Staff Holiday'`, start=end=date, status 1). Koi attendance row nahi banti. |
| `SalarySlipService::getWorkingDays()` `:214-240` | `EventType::where('name','Holiday')->first()` (na mile to koi holiday nahi). Calendar entries `whereBetween('start_date', [month start, month end])`, sirf **`start_date`** ka din (multi-day range ka sirf pehla din), **`status` filter nahi**, year filter nahi. |
| `StaffAttendance` views `staff_attendance/create.blade.php:63` (toggle `#holidayToggle`, `:247-280` JS) | `$isHoliday` se toggle checked; click par `markHoliday` POST. |
| `AttendanceController.php:219-268` (student attendance) | `is_holiday` checkbox, naam match **nahi** (remarks 'Holiday', status 4). Is task mein **nahi chhedna**. |
| `admin/event_type/index.blade.php`, sidebar, student attendance views | Sirf label/text ("Holiday Type List" etc). Logic nahi. |

`TimetableDayStatus` (class timetable) pehle se `is_off_day` use karta hai, wahi sahi pattern hai.

### 2.2 Staff attendance par asar
- `isHoliday()` sirf UI hai: create page par "Mark Today as Holiday" switch ko pehle se **checked** dikhata hai. Status (`present/absent/half_day/leave`) par koi asar nahi, aur page load par radios disabled bhi nahi hote (disable sirf click ke JS mein hota hai).
- `markHoliday()` sirf calendar entry banata hai; staff ki attendance rows nahi. **Duplicate guard nahi**: toggle baar baar karne se dobara entries.
- **Side effect (naya):** jaise hi ye entry `is_off_day = 1` type ki hogi, class timetable mein bhi poore school ka wo din "Not Scheduled" ho jayega (TimetableDayStatus rule 1). "Staff holiday" asal mein school-wide off day hai.
- `markHoliday()` ka `'color' => $eventType->color` mass-assign **silently drop** hota hai (`AcademicCalendar::$fillable` mein `color` nahi), entry default `#0d6efd` rang ki banti hai. Chhota alag masla, is task mein nahi.

### 2.3 Payroll (salary slip) par asar: paison par asal asar
- `total_days` = working days (calendar days − Sundays − holidays). `per_day_rate = basic_salary / total_days`.
- `attendance_deduction = absent_days × rate + half_days × rate/2`.
- **Unmarked working day = absent:** `unmarkedDays = max(totalDays − records.count(), 0)` aur wo `absent_days` mein jata hai (`:197-200`).
- Chunke off-days gine nahi jaate, working days **zyada** hote hain (rate kam), aur har off-day jis par attendance mark nahi hui wo **absent** ban kar deduction deta hai. Yaani staff ka salary holidays par kat raha hai.
- `leave_days` par koi deduction nahi.
- Pehle se maujood (alag) masla: `markedDays = records->count()` mein off-day/Sunday par mark hui rows bhi shamil hain, to unmarked count ghat sakta hai. **Is task mein nahi chheda**, sirf note.

### 2.4 Sunday / weekly off
- **Hardcoded:** `SalarySlipService::getWorkingDays()` `:230` `$date->isSunday()` skip. `StaffAttendanceController` mein weekly off ka koi zikr nahi (Sunday ko holiday nahi maanta).
- `academic_years.weekly_off_days` (JSON, default `["Sunday"]`, Task 1) abhi payroll use nahi karta.

### 2.5 Active year / date range / status
- **Nahi.** Dono jagah academic year filter nahi (sirf `markHoliday` naya entry active year mein banata hai). Date range: `isHoliday` start/end sahi (`whereDate`, end NULL allowed); payroll sirf `start_date`. `status = 1`: `isHoliday` haan, payroll **nahi** (inactive/disabled holiday bhi gina jata).
- Decision (neeche): year filter **nahi** lagana, kyunke past mahine ki slip puranay year ke calendar par hoti hai aur active year se match nahi karegi. Har calendar entry apni date par apni jagah sahi hai.

### 2.6 Salary slips stored hain ya recalculate?
- **Stored.** `generate()` `SalarySlip::create([...])` mein `total_days, present_days, absent_days, half_days, leave_days, per_day_rate, attendance_deduction, deduction, net_salary` sab save hota hai. `show`, `index`, teacher portal (`teacher/salary/index`) sab **stored columns** padhte hain. `update()` (sirf pending) allowance/manual deduction se net dobara nikalta hai lekin `total_days`/rate nahi chhedta.
- Isliye code badalne se **purani generated slips apne aap nahi badlengi.** `generate()` duplicate (same user + month) par exception deta hai, aur paid slips edit/delete nahi hoti, pending delete ho sakti hain (`destroy()`, advance wapas). Purana mahina theek karna ho to pending slip UI se delete + dobara generate (aap ka faisla, auto kuch nahi).

## 3. Plan (Step 2): minimal edits (approved, implemented)

### 3.1 Naya chhota helper `app/Services/OffDays.php` (naya file, `TimetableDayStatus` jaisa style)
`OffDays::dates(Carbon $start, Carbon $end): array` -> `['Y-m-d' => true, ...]`:
- **Ek hi query:** `AcademicCalendar::active()` + `whereHas('eventType', is_off_day = true)` + `whereDate('start_date', '<=', end)` + `whereRaw('DATE(COALESCE(end_date, start_date)) >= ?', [start])` (date-only compare, `with` ki zaroorat nahi, N+1 nahi).
- Multi-day entries har din ke liye expand, range `[start, end]` se clip (mahine ke beech se shuru/khatam hone wali Eid bhi theek).
- `OffDays::weeklyOffDays(Carbon $date): array` -> us date ko cover karne wale `AcademicYear` (`start_date <= date <= end_date`) ke `weekly_off_days`, warna `is_current` year, warna `['Sunday']` (**payroll safety fallback**, neeche decision B).

### 3.2 `SalarySlipService::getWorkingDays()` (sirf us method ka andar)
- `EventType::where('name','Holiday')` + calendar query hata kar `OffDays::dates($start, $end)`; hardcoded `isSunday()` ki jagah `OffDays::weeklyOffDays($start)` ke din-naam (`$date->englishDayOfWeek`). Baaqi (`generate()`, `getAttendanceSummary()`, rates, deductions) **untouched**. `use EventType`/`AcademicCalendar` ab bekar ho to hata dena.

### 3.3 `StaffAttendanceController`
- `isHoliday($date)`: `OffDays::dates(Carbon::parse($date), Carbon::parse($date))` mein date ka hona (ek query). Weekly off yahan **shamil nahi** (create page ka toggle sirf calendar off-day dikhaye, jaisa ab).
- `markHoliday()`: 
  - Type: `EventType::firstOrCreate(['name'=>'Holiday'], ['color'=>'#dc3545','status'=>1,'is_off_day'=>true])`; agar mil jaye aur `is_off_day` false ho to `update(['is_off_day'=>true])` (is naam ka type by definition off day hai).
  - **Duplicate guard:** agar `isHoliday($request->date)` pehle se true ho to naya entry nahi banta, success message "Already a holiday".
  - Baaqi (active year, entry fields, transaction-free single create) jaisa hai.

### 3.4 Jo nahi chhedna
Migration (nahi chahiye), `AttendanceController` (student), `TimetableDayStatus`, class timetable files, generated slips, `SalarySlipController`, views/JS (toggle ka "page load par radios disabled" masla alag).

### 3.5 Decisions (aap ne confirm kar diye)
- **A. Year filter:** nahi. Sirf `status = 1` aur `is_off_day = 1`. Past mahine ke liye zaroori.
- **B. Weekly off payroll mein:** `weekly_off_days` us academic year ka jis ki `start_date/end_date` payroll mahine ki **pehli tareekh** ko cover kare (active year nahi). Koi year cover na kare to **FALLBACK `['Sunday']`** (sirf payroll; class timetable ka "year na ho to khali" rule yahan nahi, kyunke khali = har din working = salary par asar). Year mile magar `weekly_off_days` NULL ho to bhi `['Sunday']`; khali array = koi weekly off nahi.
- **C. Staff attendance page:** weekly off (Sunday) holiday toggle nahi banata, sirf calendar off-day.
- **D. Purani slips:** auto kuch nahi. Pending slips aap UI se delete + regenerate karenge; paid slips jaisi hain waisi.
- **E. School-wide asar:** marked holiday school-wide hai; timetable mein "Not Scheduled" sahi hai.

## 4. Test plan (purana mahina, jaise Aug 2026)
Pehle (code badalne se pehle): ek staff ke liye **test slip** ek purane mahine (jis mein ek weekday off-day ho, jaise 14 Aug Independence Day) ke liye generate kar ke `total_days`, `per_day_rate`, `absent_days` note karo, phir **pending slip UI se delete** (aap karenge). Phir:
1. **Weekday off-day:** code ke baad dobara generate: `total_days` ek kam, `per_day_rate` ziada, aur us din ki unmarked absence deduction mein nahi.
2. **Multi-day off-day** (Eid 3 din, ya do din ki test entry): teeno din nikle; mahine ke start/end ko cross karne wali entry sirf us mahine ke dinon ki.
3. **Inactive entry** (`status = 0`): count nahi. **`is_off_day = 0` type**: count nahi.
4. **Sunday par off-day:** double count nahi (working days sirf ek baar ghate).
5. **Weekly off:** active year mein Saturday bhi tick karo, naye mahine ka `total_days` Saturdays ke saath kam. Year na ho (ya test) to fallback Sunday.
6. **Staff attendance page:** off-day date par `?date=` kholo: "Mark Today as Holiday" switch checked. Naye din par toggle: ek hi entry (dobara toggle par duplicate nahi), type `Holiday` ka `is_off_day = 1`, aur class timetable index mein wo din "Not Scheduled".
7. **Purani generated slips** (pehle ke mahine) bilkul nahi badleen (`show` page par purane `total_days` waise hi).
8. Commands (aap chalayen): `php artisan optimize:clear` phir `php artisan config:cache`.

## 5. Files (implemented)
- **New:** `app/Services/OffDays.php` (`dates()` = off-day dates ek query mein, multi-day expand + clip; `weeklyOffDays()` = month ko cover karne wale year ka weekly off, fallback `['Sunday']`).
- **Edit:** `app/Services/SalarySlipService.php` (sirf `getWorkingDays()` + 2 unused `use` hataye; `generate()` etc untouched), `app/Http/Controllers/Backend/StaffAttendanceController.php` (`isHoliday()` ab `OffDays` se; `markHoliday()`: type `is_off_day = true` (create ya update), duplicate guard "already a holiday"; `use App\Services\OffDays`).
- `php -l` teeno files par clean. Koi migration/route/view change nahi.

## 6. NEXT tasks (abhi nahi karne)
> Pending tasks ab docs/ai/BACKLOG.md mein.
1. **Student attendance:** weekly off / holidays check. `AttendanceController` (student) mein `is_holiday` checkbox hai (status 4, remarks 'Holiday'), calendar `is_off_day` ya `weekly_off_days` se link nahi. Dekhna: off-day par attendance block/auto-holiday, aur reports/percentage mein off-days ka asar.
2. **Fee module:** quick check ke fee module din (days) use karta hai ya nahi (late fee, due date, monthly fee days); agar nahi to is file mein "N/A" likh do.
3. ~~`markedDays` (`SalarySlipService::getAttendanceSummary`) off-day/Sunday rows~~ DONE 05-10-2026.
4. (Chhota) `AcademicCalendar::$fillable` mein `color` nahi, `markHoliday()` ka color silently drop hota hai.

## 7. Changelog
- 05-10-2026: investigation + plan; file `staff_attendance_holiday_fix.md` se `off_days_holidays_summary.md` rename.
- 05-10-2026: A-E decisions approve, implementation (files upar). Test baqi.
- 05-10-2026: getAttendanceSummary() off-day rows filter (markedDays fix). `SalarySlipService::getAttendanceSummary()` ab weekly off / calendar off-day ki rows ignore karta hai (`OffDays` reuse). Tested: Danish Aug 2026, sirf Sunday (02-08) ki "present" row: total 25, present 0, absent 25 (purana code present 1, absent 24 deta). DONE.
- 05-10-2026: staff_attendance/index.blade.php par session flash (success/error/info) toastr add (layout sirf `message` / `alert-type` padhta hai). Tested: Save Attendance ke baad green toast, refresh par dobara nahi, console clean. DONE.

## 8. Test results (off-days fix, 05-10-2026)
- Aug 2026 naya slip: 25 working days (14 Aug + 5 Sundays), per day 600.00. OK.
- May 2026 naya slip: 22 working days (1 May + Eid 27-29 multi-day + 5 Sundays). OK.
- Purani Aug slip (Maria Saleem): 26, unchanged. OK.
- **PASSED 06-10-2026:** staff attendance "Mark Today as Holiday" toggle (`markHoliday()`): type `Holiday` `is_off_day = 1`, sirf ek calendar entry (dobara toggle par duplicate nahi), reload par toggle checked, class timetable mein "Not Scheduled". Test entry calendar se delete, cleanup done.
- **Note:** toggle OFF karne par server ko koi request nahi jaati (sirf radios enable), calendar entry rehti hai. UX masla BACKLOG P3 mein.

## 9. Salary deduction rounding + net >= 0 (bug alag, off-days fix se nahi nikla)
- **Masla:** `generate()` rounded `per_day_rate` se deduction nikalta tha (error x days): 692.31 x 26 = 18,000.06 on 18,000 basic (net -0.06), 681.82 x 22 = 15,000.04 on 15,000 (net -0.04). Aur `advance_balance` poora kat'ta tha, gross mein jagah ho ya na ho.
- **Fix DONE 05-10-2026 (tested), sirf `SalarySlipService::generate()`:**
  - `attendance_deduction = round(basic * (absent + half*0.5) / total_days, 2)` (ek baar round). `per_day_rate` sirf display ke liye waise hi rounded.
  - Total deduction gross se zyada nahi: priority attendance > manual > advance (`min(..., gross - pichli deductions)`). `advance_deduction` sirf wahi jo recover hua; baqi `advance_balance` agle mahine ke liye rehta hai. Slip par `manual_deduction` cap ke baad wala store hota hai.
- **Math check (php, DB change nahi):** 18000/26/absent 26: purana 18000.06, naya 18000.00. 15000/22/absent 22: purana 15000.04, naya 15000.00. absent 3 + half 1 (18000/26): purana 2423.09, naya 2423.08. absent 2: dono 1384.62.
- Purani generated slips untouched; pending slips UI se delete + regenerate, paid jaisi hain.
- **Tested:** Danish, May 2026 naya slip: attendance deduction 15,000.00, net 0.00. OK.
- **Purani slips:** Maria Aug abhi bhi net -0.06 (unchanged, jaisa tay hua). Uski stored deduction gross se zyada hai, is liye ab `update()` se edit nahi hoti: zaroorat ho to pending slip delete + regenerate (UI se).
- **`SalarySlipController::update()` (pending slip edit) DONE 05-10-2026 (tested):** `manual + attendance + advance` gross (`basic + allowance`) se zyada ho to `ValidationException` ("Total deductions cannot exceed gross salary", field `manual_deduction`, save nahi hota; form mein field ke neeche error + toastr, AJAX par 422). Comparison 2 decimals par round kar ke. Tested: fully deducted slip par manual 1.00 = error, kuch save nahi; manual 0 save ho gaya.
- **Follow-up DONE 05-10-2026 (tested):** `getAttendanceSummary()` ka `markedDays` masla (section 7 changelog).
- **Files:** `app/Services/SalarySlipService.php` (sirf `generate()`), `app/Http/Controllers/Backend/SalarySlipController.php` (sirf `update()`).

## Kal ka plan (06-10-2026)
> Pending tasks ab docs/ai/BACKLOG.md mein.
Priority order mein (koi abhi shuru nahi, har ek pehle files parh kar chhota plan, approval ke baad edit):
1. ~~**Pending test: "Mark Today as Holiday" toggle** (staff attendance).~~ DONE 06-10-2026 (section 8). Aisi date par jise baad mein calendar se delete kar sako: type `Holiday` ka `is_off_day = 1`, sirf ek calendar entry (dobara toggle par duplicate nahi), class timetable mein wo din "Not Scheduled", aur staff attendance page par toggle checked.
2. **Student attendance:** weekly off / holidays check (**pehle investigate**). `AttendanceController` ka `is_holiday` checkbox (status 4, remarks 'Holiday') calendar `is_off_day` ya `weekly_off_days` se linked nahi; off-day par attendance block/auto-holiday aur reports/percentage par asar dekhna.
3. **Fee module:** quick check ke ye din (days) use karta hai ya nahi (late fee, due date, monthly fee days). Na kare to is file mein "N/A".
4. **Chhote masle (section 6 NEXT list se):**
   - ~~`SalarySlipService::getAttendanceSummary()` ka `markedDays`~~ DONE 05-10-2026 (tested).
   - `AcademicCalendar::$fillable` mein `color` nahi, `markHoliday()` ka color silently drop hota hai.
5. **Class timetable leftovers** (`class_timetable_project_summary.md` 11.5):
   - Task 5: `TEACHER_ASSIGNMENT_WORKFLOW.md` tracker (code se A1-A7, B1-B3, C, D ka evidence, checkboxes aap ke confirm ke baad).
   - Low priority: periods search box (`#periodSearchInput`) practically useless (dropdown options match karte hain), hata do ya sirf selected values par search.
   - Postponed: `class_timetables.teacher_id` FK (`constrained('users')`, baaki code `teachers.id`), aap ki manzoori ke baghair nahi.
