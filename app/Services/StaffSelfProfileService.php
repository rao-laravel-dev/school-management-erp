<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffSelfProfileService
{
    // Staff apna password change kare (current password FormRequest mein verify hota hai)
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => Hash::make($newPassword)]);
    }
}
