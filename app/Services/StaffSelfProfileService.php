<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffSelfProfileService
{
    // Staff roles jin ka apna profile page hai
    public const STAFF_ROLES = ['teacher', 'receptionist', 'accountant', 'librarian'];

    // Photo folders (role models ka photo_url accessor yahi folders padhta hai)
    public const PHOTO_FOLDERS = [
        'teacher'      => 'teacher_images',
        'receptionist' => 'uploads/receptionist_images',
        'accountant'   => 'uploads/accountant_images',
        'librarian'    => 'uploads/librarian_images',
    ];

    // Staff apna password change kare (current password FormRequest mein verify hota hai)
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }

    // Active (pehla enabled) role ka staff role;  staff role na ho to 403, profile row na mile to 404
    public function profileFor(User $user): array
    {
        $role = $user->activeRoleName();
        if (! in_array($role, self::STAFF_ROLES, true)) {
            abort(403);
        }

        $profile = $user->{$role}()->with(['bankDetail', 'salary'])->first();
        if (! $profile) {
            abort(404, ucfirst($role) . ' profile not found.');
        }

        return [$role, $profile];
    }

    // Sirf phone, address, gender, photo: role table + users mirror (photo sirf role table mein)
    public function updateProfile(User $user, $profile, string $role, array $data, ?UploadedFile $photo, ImageService $imageService): void
    {
        $fields = [
            'phone'   => $data['phone'],
            'address' => $data['address'] ?? null,
            'gender'  => $data['gender'],
        ];

        $folder = self::PHOTO_FOLDERS[$role];
        $oldPhoto = $profile->photo;
        $newPhoto = null;
        if ($photo) {
            $newPhoto = $imageService->upload($photo, $folder, 300, 300);
            $fields['photo'] = $newPhoto;
        }

        DB::transaction(function () use ($user, $profile, $fields) {
            $profile->update($fields);

            // users table mein bhi mirror (photo sirf role table mein)
            $user->update([
                'phone'   => $fields['phone'],
                'address' => $fields['address'],
                'gender'  => $fields['gender'],
            ]);
        });

        // Purani photo sirf save success ke baad delete
        if ($newPhoto && $oldPhoto) {
            $imageService->delete($oldPhoto, $folder);
        }
    }
}
