<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;

class SystemHealthService
{
    /**
     * Tüm sistem sağlığı metriklerini toplar ve kapsamlı tanı raporu üretir.
     */
    public function getSystemHealth(): array
    {
        $startTime = microtime(true);

        // 1. Veritabanı Bağlantı ve Latency Testi
        $dbStatus = 'OK';
        $dbLatency = 0;
        $dbDriver = config('database.default', 'mysql');
        $dbDatabase = config("database.connections.{$dbDriver}.database", '-');
        try {
            $t0 = microtime(true);
            DB::select('SELECT 1');
            $dbLatency = round((microtime(true) - $t0) * 1000, 2);
        } catch (\Exception $e) {
            $dbStatus = 'ERROR: ' . $e->getMessage();
        }

        // 2. Storage / Disk I/O Testi
        $storageStatus = 'OK';
        $storageDisk = 'public';
        try {
            $testFile = 'health_ping_' . time() . '.txt';
            Storage::disk($storageDisk)->put($testFile, 'KOKSAN_PMS_HEALTH_CHECK_OK');
            $readContent = Storage::disk($storageDisk)->get($testFile);
            Storage::disk($storageDisk)->delete($testFile);
            if ($readContent !== 'KOKSAN_PMS_HEALTH_CHECK_OK') {
                $storageStatus = 'WARNING: Yazılan içerik okunamadı';
            }
        } catch (\Exception $e) {
            $storageStatus = 'ERROR: ' . $e->getMessage();
        }

        // 3. Cache & Session Bellek Testi
        $cacheStatus = 'OK';
        $cacheLatency = 0;
        $cacheDriver = config('cache.default', 'file');
        try {
            $t0 = microtime(true);
            Cache::put('_system_health_probe', 'active', 10);
            $cachedVal = Cache::get('_system_health_probe');
            Cache::forget('_system_health_probe');
            $cacheLatency = round((microtime(true) - $t0) * 1000, 2);
            if ($cachedVal !== 'active') {
                $cacheStatus = 'WARNING: Önbellek anahtarı okunamadı';
            }
        } catch (\Exception $e) {
            $cacheStatus = 'ERROR: ' . $e->getMessage();
        }

        // 4. Kuyruk (Queue Worker) Durumu
        $queueDriver = config('queue.default', 'database');
        $pendingJobs = 0;
        $failedJobs = 0;
        try {
            if (Schema::hasTable('jobs')) {
                $pendingJobs = DB::table('jobs')->count();
            }
            if (Schema::hasTable('failed_jobs')) {
                $failedJobs = DB::table('failed_jobs')->count();
            }
        } catch (\Exception $e) {
            // Sessizce yut
        }

        // 5. Sunucu & Bellek Metrikleri
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryLimit = ini_get('memory_limit') ?: 'Bilinmiyor';

        // 6. Yeni Eklenen Modüller & Alt Motor Tanıları (Subsystem Health Probes)
        // A. Bütçe Eşik Kademeleri & DOA Motoru Tanısı
        $doaEngineActive = \App\Models\SystemSetting::get('doa_budget_approval_engine_active', '1') === '1';
        $doaBaseCurrency = \App\Models\SystemSetting::get('doa_base_currency', 'USD');
        $doaRulesCount = 0;
        $doaActiveRulesCount = 0;
        $doaCurrenciesCount = 0;
        try {
            if (Schema::hasTable('doa_threshold_rules')) {
                $doaRulesCount = DB::table('doa_threshold_rules')->count();
                $doaActiveRulesCount = DB::table('doa_threshold_rules')->where('is_active', true)->count();
            }
            if (Schema::hasTable('currencies')) {
                $doaCurrenciesCount = DB::table('currencies')->count();
            }
        } catch (\Exception $e) {
            // sessizce yut
        }

        // B. Kurul & Komite Mekanizması Tanısı
        $committeesCount = 0;
        $committeeMembersCount = 0;
        try {
            if (Schema::hasTable('committees')) {
                $committeesCount = DB::table('committees')->count();
            }
            if (Schema::hasTable('committee_members')) {
                $committeeMembersCount = DB::table('committee_members')->where('is_active', true)->count();
            }
        } catch (\Exception $e) {
            // sessizce yut
        }

        // C. Kategori & Ekip Rolleri Tanısı
        $categoriesCount = 0;
        $teamRolesCount = 0;
        $budgetCategoriesCount = 0;
        try {
            if (Schema::hasTable('project_categories')) {
                $categoriesCount = DB::table('project_categories')->count();
            }
            if (Schema::hasTable('project_team_roles')) {
                $teamRolesCount = DB::table('project_team_roles')->count();
            }
            if (Schema::hasTable('project_budget_categories')) {
                $budgetCategoriesCount = DB::table('project_budget_categories')->count();
            }
        } catch (\Exception $e) {
            // sessizce yut
        }

        // D. Proje İçi Ekip Yetki Matrisi Tanısı
        $totalPrjPerms = 0;
        $grantedPrjPerms = 0;
        $orphanedPrjPerms = 0;
        try {
            if (Schema::hasTable('project_stakeholder_permissions')) {
                $totalPrjPerms = DB::table('project_stakeholder_permissions')->count();
                $grantedPrjPerms = DB::table('project_stakeholder_permissions')->where('is_granted', true)->count();
                // Pasif üye üzerinde kalmış açık yetki kontrolü
                $orphanedPrjPerms = DB::table('project_stakeholder_permissions as psp')
                    ->join('project_stakeholders as ps', function($join) {
                        $join->on('psp.project_id', '=', 'ps.project_id')
                             ->on('psp.user_id', '=', 'ps.user_id');
                    })
                    ->where('ps.is_active', false)
                    ->where('psp.is_granted', true)
                    ->count();
            }
        } catch (\Exception $e) {}

        // E. Kurumsal Vekalet & Devir Sistemi Tanısı
        $totalDelegations = 0;
        $activeDelegations = 0;
        $expiredDelegations = 0;
        $handoverRecords = 0;
        try {
            if (Schema::hasTable('user_delegations')) {
                $totalDelegations = DB::table('user_delegations')->count();
                $now = now();
                $activeDelegations = DB::table('user_delegations')
                    ->where('is_active', true)
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->count();
                $expiredDelegations = DB::table('user_delegations')
                    ->where('ends_at', '<', $now)
                    ->count();
            }
            if (Schema::hasTable('project_stakeholders')) {
                $handoverRecords = DB::table('project_stakeholders')
                    ->whereNotNull('handover_to_user_id')
                    ->count();
            }
        } catch (\Exception $e) {}

        // 7. Rota ve Rol Denetimleri
        $routesReport = $this->analyzeRoutes();
        $totalRoutes = count($routesReport);
        $brokenRoutes = count(array_filter($routesReport, fn($r) => $r['has_error']));
        $healthyRoutes = $totalRoutes - $brokenRoutes;

        // 8. Rol Bazlı Güvenlik & Yetki Kapsama Kartları
        $roleAudits = $this->generateRoleAudits($routesReport);

        $executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'db_status' => $dbStatus,
            'db_latency_ms' => $dbLatency,
            'db_driver' => $dbDriver,
            'db_database' => $dbDatabase,
            'storage_status' => $storageStatus,
            'storage_disk' => $storageDisk,
            'cache_status' => $cacheStatus,
            'cache_latency_ms' => $cacheLatency,
            'cache_driver' => $cacheDriver,
            'queue_driver' => $queueDriver,
            'queue_pending_jobs' => $pendingJobs,
            'queue_failed_jobs' => $failedJobs,
            'memory_usage_mb' => $memoryUsage,
            'memory_limit' => $memoryLimit,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => now()->format('d.m.Y H:i:s'),
            'execution_time_ms' => $executionTimeMs,
            'total_routes' => $totalRoutes,
            'healthy_routes' => $healthyRoutes,
            'broken_routes' => $brokenRoutes,
            'routes' => $routesReport,
            'role_audits' => $roleAudits,
            'doa_engine' => [
                'is_active' => $doaEngineActive,
                'base_currency' => $doaBaseCurrency,
                'total_rules' => $doaRulesCount,
                'active_rules' => $doaActiveRulesCount,
                'currencies_count' => $doaCurrenciesCount,
                'status' => ($doaRulesCount > 0 && $doaCurrenciesCount > 0) ? 'HEALTHY' : 'WARNING',
            ],
            'committees_subsystem' => [
                'total_committees' => $committeesCount,
                'total_members' => $committeeMembersCount,
                'status' => ($committeesCount > 0 && $committeeMembersCount > 0) ? 'HEALTHY' : 'WARNING',
            ],
            'taxonomy_subsystem' => [
                'categories_count' => $categoriesCount,
                'team_roles_count' => $teamRolesCount,
                'budget_categories_count' => $budgetCategoriesCount,
                'status' => 'HEALTHY',
            ],
            'delegation_matrix_subsystem' => [
                'total_permissions' => $totalPrjPerms,
                'granted_permissions' => $grantedPrjPerms,
                'orphaned_permissions' => $orphanedPrjPerms,
                'status' => ($orphanedPrjPerms === 0) ? 'HEALTHY' : 'WARNING',
            ],
            'user_delegations_subsystem' => [
                'total_delegations' => $totalDelegations,
                'active_delegations' => $activeDelegations,
                'expired_delegations' => $expiredDelegations,
                'handover_records' => $handoverRecords,
                'status' => 'HEALTHY',
            ],
            'overall_status' => ($dbStatus === 'OK' && $storageStatus === 'OK' && $cacheStatus === 'OK')
                ? ($brokenRoutes > 0 ? 'WARNING' : 'HEALTHY')
                : 'CRITICAL',
        ];
    }

    /**
     * Uygulamadaki tüm web rotalarını ve erişim/bütünlük haritasını çıkarır.
     */
    public function analyzeRoutes(): array
    {
        $allRoutes = Route::getRoutes();
        $report = [];

        $allRoles = Role::with('permissions', 'group')->get();

        foreach ($allRoutes as $route) {
            $uri = $route->uri();

            // Dahili rotaları filtrele (_ignition, telescope, sanctum vb.)
            if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum')) {
                continue;
            }

            $methods = implode('|', array_diff($route->methods(), ['HEAD']));
            $actionName = $route->getActionName();
            $routeName = $route->getName() ?? '-';
            $middleware = implode(', ', $route->gatherMiddleware());

            // Hata & Bütünlük Denetimi (Controller & Metot kontrolü)
            $hasError = false;
            $errorType = null;
            $errorMessage = null;
            $debugHint = null;
            $simulatedTrace = null;

            if ($actionName !== 'Closure' && str_contains($actionName, '@')) {
                [$ctrlClass, $method] = explode('@', $actionName);
                if (!class_exists($ctrlClass)) {
                    $hasError = true;
                    $errorType = 'CLASS_NOT_FOUND';
                    $errorMessage = "Hedef Controller sınıfı sistemde bulunamadı: {$ctrlClass}";
                    $debugHint = "Controller sınıfı silinmiş, yeniden adlandırılmış veya namespace yolu eksik olabilir.";
                    $simulatedTrace = "ReflectionException: Class \"{$ctrlClass}\" does not exist\nat " . base_path('routes/web.php') . "\nAction: {$actionName}\nURI: /{$uri}";
                } elseif (!method_exists($ctrlClass, $method)) {
                    $hasError = true;
                    $errorType = 'METHOD_NOT_FOUND';
                    $errorMessage = "Controller metodu bulunamadı: {$ctrlClass}@{$method}";
                    $debugHint = "{$ctrlClass} dosyası içerisine 'public function {$method}()' metodunu tanımlayın veya routes/web.php dosyasındaki rota bağlamını güncelleyin.";
                    $simulatedTrace = "BadMethodCallException: Method {$ctrlClass}::{$method}() does not exist.\nat " . base_path('routes/web.php') . "\nController: {$ctrlClass}\nMethod: {$method}\nURI: /{$uri}";
                }
            }

            // İzin & Modül Eşleştirme (Yetki slug ve modül tayini)
            $matchedPermission = null;
            $module = 'Genel & Sistem';

            if (str_contains($uri, 'doa-esikleri')) {
                $matchedPermission = 'manage_doa_rules';
                $module = 'DOA Eşik Matrisi';
            } elseif (str_contains($uri, 'kurullar')) {
                $matchedPermission = 'manage_committees';
                $module = 'Kurullar & Komiteler';
            } elseif (str_contains($uri, 'currencies')) {
                $matchedPermission = 'manage_doa_rules';
                $module = 'DOA Eşik Matrisi';
            } elseif (str_contains($uri, 'budget-categories')) {
                $matchedPermission = 'butce.manage';
                $module = 'Finans & BOM';
            } elseif (str_contains($uri, 'kategoriler')) {
                $matchedPermission = 'proje.manage_categories';
                $module = 'Proje Yönetimi';
            } elseif (str_contains($uri, 'team-roles')) {
                $matchedPermission = 'proje.manage_team_roles';
                $module = 'Proje Yönetimi';
            } elseif (str_contains($uri, 'rol-gruplari') || str_contains($uri, 'roller') || str_contains($uri, 'kullanici-rolleri')) {
                $matchedPermission = 'matrix.manage';
                $module = 'Rol & Yetki Yönetimi';
            } elseif (str_contains($uri, 'yetki-matrisi') || str_contains($uri, 'bildirim-matrisi') || str_contains($uri, 'sync-bolumler') || str_contains($uri, 'bildirim-sablonlari')) {
                $matchedPermission = 'matrix.manage';
                $module = 'Yönetim Matrisleri';
            } elseif (str_contains($uri, 'sistem-sagligi')) {
                $matchedPermission = 'health.view';
                $module = 'Sistem Sağlığı & Tanı';
            } elseif (str_contains($uri, 'denetim-loglari')) {
                $matchedPermission = 'log.view';
                $module = 'Denetim & Loglar';
            } elseif (str_contains($uri, 'ayarlar')) {
                $matchedPermission = 'settings.manage';
                $module = 'Sistem Ayarları';
            } elseif (str_contains($uri, 'kanban')) {
                $matchedPermission = 'kanban.view';
                $module = 'Proje Yönetimi';
            } elseif (str_contains($uri, 'gantt')) {
                $matchedPermission = 'proje.view';
                $module = 'Proje Yönetimi';
            } elseif (str_contains($uri, 'kaynaklar')) {
                $matchedPermission = 'proje.view';
                $module = 'Kapasite & Kaynak';
            } elseif (str_contains($uri, 'riskler')) {
                $matchedPermission = 'proje.view';
                $module = 'Risk Yönetimi (5x5)';
            } elseif (str_contains($uri, 'zaman-takibi')) {
                $matchedPermission = 'timesheet.create';
                $module = 'Zaman Takibi';
            } elseif (str_contains($uri, 'butce-bom') || str_contains($uri, 'bom')) {
                $matchedPermission = 'butce.view';
                $module = 'Finans & BOM';
            } elseif (str_contains($uri, 'toplantilar')) {
                $matchedPermission = 'meeting.view';
                $module = 'Toplantı & Kararlar';
            } elseif (str_contains($uri, 'raporlar')) {
                $matchedPermission = 'report.view';
                $module = 'Raporlama';
            } elseif (str_contains($uri, 'projeler') || str_contains($uri, 'projects')) {
                if (str_contains($uri, 'approve')) {
                    $matchedPermission = 'proje.approve';
                } elseif (str_contains($uri, 'isg') || str_contains($uri, 'ehs')) {
                    $matchedPermission = 'isg.view';
                } else {
                    $matchedPermission = 'proje.view';
                }
                $module = 'Proje Yönetimi';
            } elseif (str_contains($uri, 'login') || str_contains($uri, 'logout') || str_contains($uri, 'forgot-password')) {
                $matchedPermission = null;
                $module = 'Kimlik & Oturum';
            } elseif (str_contains($uri, 'taslak-proje') || str_contains($uri, 'api/central')) {
                $matchedPermission = null;
                $module = 'Dış Paydaş & Webhook';
            } elseif (str_contains($uri, 'dashboard') || $uri === '/') {
                $matchedPermission = 'proje.view';
                $module = 'Dashboard & Özet';
            }

            // Hangi roller erişebilir?
            $allowedRoles = [];
            foreach ($allRoles as $role) {
                if ($role->slug === 'superadmin') {
                    $allowedRoles[] = $role->name;
                    continue;
                }
                if ($matchedPermission && $role->permissions->contains('slug', $matchedPermission)) {
                    $allowedRoles[] = $role->name;
                }
            }

            $report[] = [
                'uri' => '/' . ltrim($uri, '/'),
                'methods' => $methods,
                'name' => $routeName,
                'action' => class_basename($actionName),
                'full_action' => $actionName,
                'middleware' => $middleware,
                'module' => $module,
                'permission' => $matchedPermission ?? 'Genel Erişim',
                'allowed_roles' => $allowedRoles,
                'has_error' => $hasError,
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'debug_hint' => $debugHint,
                'simulated_trace' => $simulatedTrace,
            ];
        }

        return $report;
    }

    /**
     * Rol bazlı güvenlik & yetki kapsam denetim kartlarını oluşturur.
     */
    protected function generateRoleAudits(array $routesReport): array
    {
        $allRoles = Role::with('permissions', 'group')->get();
        $totalRoutes = count($routesReport);
        $roleAudits = [];

        foreach ($allRoles as $role) {
            $accessibleCount = 0;
            foreach ($routesReport as $r) {
                if ($role->slug === 'superadmin') {
                    $accessibleCount++;
                } elseif (in_array($role->name, $r['allowed_roles']) || $r['permission'] === 'Genel Erişim') {
                    $accessibleCount++;
                }
            }

            $coveragePct = $totalRoutes > 0 ? round(($accessibleCount / $totalRoutes) * 100) : 0;

            // Rolün temel modülleri
            $modules = [];
            foreach ($role->permissions as $p) {
                $group = $p->group_name ?? 'Genel';
                if (!in_array($group, $modules)) {
                    $modules[] = $group;
                }
            }

            $roleAudits[] = [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'group_name' => $role->group ? $role->group->name : ($role->role_group ?? 'Diğer'),
                'group_color' => $role->group ? $role->group->color : '#64748b',
                'badge_color' => $role->badge_color ?? '#0284c7',
                'permissions_count' => $role->permissions->count(),
                'accessible_routes' => $accessibleCount,
                'coverage_pct' => $coveragePct,
                'is_superadmin' => $role->slug === 'superadmin',
                'modules' => array_slice($modules, 0, 4),
            ];
        }

        return $roleAudits;
    }
}
