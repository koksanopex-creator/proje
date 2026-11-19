<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectTeamRole;
use App\Services\AuditLogService;

class ProjectTeamRoleController extends Controller
{
    public function index()
    {
        $roles = ProjectTeamRole::where('is_active', true)->orderBy('sort_order')->get();
        return response()->json(['success' => true, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('proje.manage_team_roles') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Ekip rolü ekleme yetkiniz bulunmamaktadır.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
        ]);

        $maxOrder = ProjectTeamRole::max('sort_order') ?? 0;

        $role = ProjectTeamRole::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        AuditLogService::log('created', "Yeni Ekip Sorumluluk Rolü Eklendi: '**{$role->name}**'", 'ProjectTeamRole', $role->id);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => "'{$role->name}' ekip rolü başarıyla eklendi.",
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('proje.manage_team_roles') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Ekip rolü düzenleme yetkiniz bulunmamaktadır.'], 403);
        }

        $role = ProjectTeamRole::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        AuditLogService::log('updated', "Ekip Sorumluluk Rolü Güncellendi: '**{$role->name}**'", 'ProjectTeamRole', $role->id);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => "'{$role->name}' ekip rolü başarıyla güncellendi.",
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('proje.manage_team_roles') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Ekip rolü silme yetkiniz bulunmamaktadır.'], 403);
        }

        $role = ProjectTeamRole::findOrFail($id);
        $name = $role->name;
        $role->delete();

        AuditLogService::log('deleted', "Ekip Sorumluluk Rolü Silindi: '**{$name}**'", 'ProjectTeamRole', $id);

        return response()->json([
            'success' => true,
            'message' => "'{$name}' ekip rolü başarıyla silindi.",
        ]);
    }
}
