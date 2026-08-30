<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SuperAdmin ko "God Mode" dene ke liye (Sab kuch access milega)
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // 2. Dynamic Layout Selector (Helper)
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();

                // 1. Role mapping
                $layoutMap = [
                    'superadmin'   => 'admin.layout.app',
                    'admin'        => 'admin.layout.app',
                    'accountant'   => 'accountant.layout.app',
                    'receptionist' => 'reception.layout.app',
                    'librarian'    => 'librarian.layout.app',
                    'teacher'      => 'teacher.layout.app',
                    'parent'       => 'parent.layout.app',
                    'student'      => 'student.layout.app',
                ];

                // 2. User ka role nikalen
                $roleName = $user->getRoleNames()->first() ?? 'user';

                // 3. Layout choose karein
                $layout = $layoutMap[$roleName] ?? 'user.layout.app';

                // 4. Fallback: Agar file exist na kare to default par le jaye
                if (!view()->exists($layout)) {
                    $layout = 'admin.layout.app';
                }

                // --- DEBUGGING KE LIYE (Test karne ke baad ise hatayein) ---
                // dd($layout, $roleName); 

                // 5. Layout pass karein
                $view->with('current_layout', $layout);
            }
        });
    }
}
