<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class CardVerifyController extends Controller
{
    // ID card ka QR (/verify/{code}): guest ko basic page, permission wale logged-in user ko existing details partial
    public function show(Request $request, $code)
    {
        $school = SiteSetting::current();

        // Exact match sirf: AttendanceController STEP 1b wala id-only fallback yahan nahi (warna id badal kar sab students browse hon)
        $qr = QrCode::where('code', $code)->first();
        $owner = null;
        if ($qr) {
            $owner = $qr->owner_type === Student::class
                ? Student::withTrashed()->with(['enrollments' => fn($q) => $q->latest('id')->limit(1), 'enrollments.schoolClass', 'enrollments.section'])->find($qr->owner_id)
                : ($qr->owner_type === User::class ? User::withTrashed()->with('roles')->find($qr->owner_id) : null);
        }

        if (!$owner) {
            return response()->view('card_verify.public', ['school' => $school, 'card' => null], 404)
                ->header('X-Robots-Tag', 'noindex');
        }

        $user = auth()->user();
        $detailsUrl = null;

        if ($owner instanceof Student) {
            $enrollment = $owner->enrollments->first(); // latest enrollment
            $valid = !$owner->trashed() && (int) $owner->status === 1;
            $card = [
                'type'   => 'student',
                'name'   => trim(ucfirst($owner->first_name) . ' ' . ucfirst($owner->last_name)),
                'class'  => $enrollment ? (($enrollment->schoolClass->name ?? 'N/A') . ' (' . ($enrollment->section->name ?? 'N/A') . ')') : 'N/A',
                'id_no'  => $owner->admission_no,
                'valid'  => $valid,
            ];
            if ($user && $user->can('access-students')) {
                $detailsUrl = url('/students/get-details') . '/' . $owner->id;
            }
        } else {
            $roleName = $owner->activeRoleName();
            $valid = (int) $owner->status === 1 && !$owner->isStaffTrashed();
            $card = [
                'type'   => 'staff',
                'name'   => $owner->name,
                'role'   => $roleName ? ucfirst($roleName) : 'Staff',
                'id_no'  => $owner->staff_code,
                'valid'  => $valid,
            ];

            // Role -> (permission, profile relation, details route); get-details profile id leta hai, users.id nahi
            $map = [
                'teacher'      => ['access-teacher', 'teacher', 'teacher.get-details'],
                'receptionist' => ['access-receptionist', 'receptionist', 'receptionist.get-details'],
                'accountant'   => ['access-accountant', 'accountant', 'accountant.get-details'],
                'librarian'    => ['access-librarian', 'librarian', 'librarians.get-details'],
            ];
            if ($user && $roleName && isset($map[$roleName]) && $user->can($map[$roleName][0])) {
                $profile = $owner->{$map[$roleName][1]};
                if ($profile) {
                    $detailsUrl = route($map[$roleName][2], $profile->id);
                }
            }
        }

        $data = ['school' => $school, 'card' => $card, 'detailsUrl' => $detailsUrl];

        // Permission nahi = 403 nahi, basic public page
        $view = $detailsUrl ? 'card_verify.full' : 'card_verify.public';

        return response()->view($view, $data)->header('X-Robots-Tag', 'noindex');
    }
}
