<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserIsActive
{
    // API token wale user ka status har request par check: inactive/pending/trashed/school-locked = token revoke + 403
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isLoginBlocked() && ! $user->isStaffTrashed())) {
            return $next($request);
        }

        $user->tokens()->delete();

        return response()->json(['message' => 'Your account is inactive or pending approval.'], 403);
    }
}
