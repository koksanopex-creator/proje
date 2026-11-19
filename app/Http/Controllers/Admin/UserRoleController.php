<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleGroup;
use App\Models\Bolum;
use App\Services\AuditLogService;
use App\Services\UserSyncService;
use Illuminate\Support\Facades\Log;

class UserRoleController extends Controller
{
    /**
     * Kullanıcı yetkilendirme ve rol atama ana paneli
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'bolum'])->orderBy('name', 'asc');

        // Arama filtresi
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('sicil_no', 'like', "%{$q}%")
                    ->orWhere('unvan', 'like', "%{$q}%");
            });
        }

        // Bölüm filtresi
        if ($request->filled('bolum_id')) {
            $query->where('bolum_id', $request->bolum_id);
        }

        // Rol filtresi
        if ($request->filled('role')) {
            $roleParam = $request->role;
            if ($roleParam === 'no_role') {
                $query->doesntHave('roles');
            } else {
                $query->whereHas('roles', function ($rq) use ($roleParam) {
                    if (is_numeric($roleParam)) {
                        $rq->where('roles.id', $roleParam);
                    } else {
                        $rq->where('roles.slug', $roleParam);
                    }
                });
            }
        }

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $users = $query->get();

        // Tüm roller ve grupları
        $roles = Role::with('group')->orderBy('name', 'asc')->get();
        $roleGroups = RoleGroup::with('roles')->orderBy('sort_order', 'asc')->get();
        $bolumler = Bolum::where('is_active', true)->orderBy('ad', 'asc')->get();

        // İstatistikler
        $allUsers = User::with('roles')->get();
        $stats = [
            'total_users' => $allUsers->count(),
            'active_users' => $allUsers->where('is_active', true)->count(),
            'assigned_users' => $allUsers->filter(fn($u) => $u->roles->count() > 0)->count(),
            'unassigned_users' => $allUsers->filter(fn($u) => $u->roles->count() === 0)->count(),
            'total_roles' => $roles->count(),
        ];

        // Son senkron kaydı denetim logundan çek
        $lastSyncLog = \App\Models\ProjectAuditLog::where('action', 'sync')
            ->where('entity_type', 'User')
            ->latest()
            ->first();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'users' => $users,
                'stats' => $stats,
            ]);
        }

        return view('admin.user_roles.index', compact('users', 'roles', 'roleGroups', 'bolumler', 'stats', 'lastSyncLog'));
    }

    /**
     * Seçilen kullanıcının rollerini topluca günceller (Çoklu Rol Atama/Geri Alma).
     */
    public function updateRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $newRoleIds = $request->roles ?? [];

        // Güvenlik Koruması: Süperadmin kendi superadmin rolünü silemez
        if ($user->id === 1 || str_contains(strtolower($user->email), 'celal.karaman')) {
            $superadminRole = Role::where('slug', 'superadmin')->first();
            if ($superadminRole && !in_array($superadminRole->id, $newRoleIds)) {
                $newRoleIds[] = $superadminRole->id;
            }
        }

        $oldRoleNames = $user->roles->pluck('name')->implode(', ');
        $user->roles()->sync($newRoleIds);
        $user->load('roles');
        $newRoleNames = $user->roles->pluck('name')->implode(', ');

        $maskedUserName = $user->getMaskedNameFor(auth()->user());

        AuditLogService::log(
            'updated',
            "Kullanıcı Rolleri Güncellendi: '**{$maskedUserName}**' ({$user->email}) kullanıcısına yeni roller atandı: [{$newRoleNames}]. (Önceki roller: [{$oldRoleNames}]).",
            'User',
            $user->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "'{$user->name}' kullanıcısının rolleri başarıyla güncellendi.",
                'user' => $user->load('roles'),
            ]);
        }

        return redirect()->back()->with('success', "'{$user->name}' kullanıcısının rolleri başarıyla güncellendi.");
    }

    /**
     * Tek bir rolü hızlıca açıp kapatma (Quick Toggle).
     */
    public function toggleRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        // Güvenlik Koruması: Süperadminlik korunur
        if ($role->slug === 'superadmin' && ($user->id === 1 || str_contains(strtolower($user->email), 'celal.karaman')) && $user->roles->contains($role->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Superadmin hesabı için ana geliştirici rolü kaldırılamaz!',
            ], 422);
        }

        $changes = $user->roles()->toggle($role->id);
        $attached = !empty($changes['attached']);
        $actionWord = $attached ? 'atandı' : 'geri alındı';

        $maskedUserName = $user->getMaskedNameFor(auth()->user());

        AuditLogService::log(
            'updated',
            "Hızlı Rol Değişikliği: '**{$maskedUserName}**' ({$user->email}) kullanıcısına '**{$role->name}**' rolü {$actionWord}.",
            'User',
            $user->id
        );

        return response()->json([
            'success' => true,
            'attached' => $attached,
            'message' => "'{$user->name}' kullanıcısına '{$role->name}' rolü {$actionWord}.",
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Merkezi API / Veritabanından anında senkronizasyon tetikler.
     */
    public function syncFromCentral(Request $request)
    {
        $result = UserSyncService::syncFromCentral();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Merkezi API Webhook uç noktası (Merkezi sisteme kullanıcı eklendiğinde anında tetiklenir).
     */
    public function handleWebhook(Request $request)
    {
        // Opsiyonel Webhook Anahtar Kontrolü
        $secret = config('services.merkezi_api.secret', env('MERKEZI_WEBHOOK_SECRET'));
        if (!empty($secret)) {
            $incomingSecret = $request->header('X-Central-Secret') ?? $request->query('secret');
            if ($incomingSecret !== $secret) {
                return response()->json(['success' => false, 'message' => 'Yetkisiz erişim: Geçersiz gizli anahtar.'], 403);
            }
        }

        try {
            $user = UserSyncService::syncSingleUser($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kullanıcı başarıyla eşitlendi.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'sicil_no' => $user->sicil_no,
                    'unvan' => $user->unvan,
                    'is_active' => $user->is_active,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Merkezi Webhook Senkronizasyon Hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Yeni Test veya Sistem Kullanıcısı Oluşturur ve Rollerini Atar.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:4',
            'unvan' => 'nullable|string|max:255',
            'sicil_no' => 'nullable|string|max:50',
            'bolum_id' => 'nullable|exists:bolumler,id',
            'phone' => 'nullable|string|max:50',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        $password = !empty($validated['password']) ? $validated['password'] : '12345678';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'unvan' => $validated['unvan'] ?? null,
            'sicil_no' => $validated['sicil_no'] ?? ('KOK-' . rand(100, 999)),
            'bolum_id' => $validated['bolum_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        AuditLogService::log(
            'created',
            "Yeni Kullanıcı Eklendi: '{$user->name}' ({$user->email}), Unvan: '{$user->unvan}'.",
            'User',
            $user->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Yeni kullanıcı '{$user->name}' başarıyla oluşturuldu.",
                'user' => $user->load('roles', 'bolum'),
            ]);
        }

        return redirect()->back()->with('success', "Yeni kullanıcı '{$user->name}' başarıyla oluşturuldu.");
    }
}
