<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Superadmin (ID=1), Admin (ID=20) ve Developer tam yetkilidir
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isDeveloper()) {
            return $next($request);
        }

        // 2. Madde 19: Yonetim rolü (ID=19) yalnızca denetim loglarını ve sistem sağlığını salt okunur izleyebilir
        if ($user->isYonetim()) {
            $allowedRoutes = ['admin.audit_logs', 'admin.system_health'];
            $currentRoute = $request->route() ? $request->route()->getName() : null;

            if ($request->isMethod('GET') && in_array($currentRoute, $allowedRoutes)) {
                return $next($request);
            }

            abort(403, 'Yönetim rolü salt okunur izleme yetkisine sahiptir. Sistem yönetimi, rol ve yetki ayarlarını değiştiremez.');
        }

        // 3. Diğer tüm roller için erişim kesinlikle yasaktır
        abort(403, 'Bu yönetim paneline erişim yetkiniz bulunmamaktadır. Yalnızca Sistem Yöneticileri ve Superadmin erişebilir.');
    }
}
