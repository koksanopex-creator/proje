<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuditLogService;

class PermissionMatrixController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group_name');
        $roleGroups = \App\Models\RoleGroup::orderBy('sort_order')->get();

        return view('admin.permission_matrix', compact('roles', 'permissions', 'roleGroups'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
            'state' => 'required|boolean',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        if ($request->state) {
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        } else {
            $role->permissions()->detach($permission->id);
        }

        AuditLogService::log(
            'updated',
            "Dinamik Yetki Matrisi güncellendi: '**{$role->name}**' rolüne '**{$permission->name}**' yetkisi " . ($request->state ? 'verildi' : 'kaldırıldı') . '.',
            'Permission',
            $permission->id
        );

        return response()->json(['success' => true]);
    }

    public function resetToDefaults()
    {
        $seeder = new \Database\Seeders\AssignDefaultPermissionsSeeder();
        $seeder->run();

        AuditLogService::log(
            'updated',
            'Dinamik Yetki Matrisi fabrika varsayılan standart rollerine sıfırlandı.',
            'Permission',
            0
        );

        return back()->with('success', 'Tüm rollere standart önerilen sistem yetkileri başarıyla yüklendi.');
    }
}