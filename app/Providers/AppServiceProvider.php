<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Carbon\Carbon::setLocale('tr');
        app()->setLocale('tr');
        // Modern kurumsal sayfalama (Köksan PPM şablonu)
        Paginator::defaultView('vendor.pagination.koksan');

        // View ErrorBag varsayılanı (tüm ortamlarda dayanıklılık)
        View::share('errors', session('errors', new ViewErrorBag()));

        // Madde 15: Superadmin her şeye yetkilidir (Gate::before)
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Dinamik İzinler (Permissions): Veritabanındaki her izin slug'ı için Gate tanımla
        try {
            $permissions = Permission::all();
            foreach ($permissions as $permission) {
                Gate::define($permission->slug, function (User $user) use ($permission) {
                    return $user->hasPermission($permission->slug);
                });
            }
        } catch (\Exception $e) {
            // DB henüz hazır değilse sessizce geç
        }
    }
}