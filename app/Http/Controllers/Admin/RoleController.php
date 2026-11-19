<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->with([
                'users' => function($q) {
                    $q->with('bolum');
                },
                'permissions'
            ])
            ->get();
        $roleGroups = \App\Models\RoleGroup::withCount('roles')->orderBy('sort_order')->get();
        
        $stats = [
            'total' => $roles->count(),
            'system' => $roles->where('is_system', 1)->count(),
            'custom' => $roles->where('is_system', 0)->count(),
        ];

        return view('admin.roles.index', compact('roles', 'stats', 'roleGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:roles,slug',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'role_group' => 'required|string|exists:role_groups,slug',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug, '_')
            : Str::slug($request->name, '_');

        // Slug benzersizlik kontrolü
        $originalSlug = $slug;
        $counter = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }

        $role = Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'role_group' => $request->role_group,
            'description' => $request->description,
            'is_system' => false,
        ]);

        AuditLogService::log(
            'created',
            "Yeni Rol Oluşturuldu: '**{$role->name}**' ({$role->slug}), Grubu: '**{$role->group_title}**', Rengi: {$role->color}.",
            'Role',
            $role->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => $role,
                'message' => 'Yeni rol başarıyla eklendi.',
            ]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "'{$role->name}' rolü başarıyla oluşturuldu.");
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:roles,slug,' . $role->id,
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'role_group' => 'required|string|exists:role_groups,slug',
            'description' => 'nullable|string|max:255',
        ]);

        $oldName = $role->name;
        $oldColor = $role->color;

        $updateData = [
            'name' => $request->name,
            'color' => $request->color,
            'role_group' => $request->role_group,
            'description' => $request->description,
        ];

        // Sistem rolleri için slug değiştirilemez
        if (!$role->is_system && $request->filled('slug')) {
            $updateData['slug'] = Str::slug($request->slug, '_');
        }

        $role->update($updateData);

        AuditLogService::log(
            'updated',
            "Rol Güncellendi: '**{$oldName}**' -> '**{$role->name}**'. Renk: {$oldColor} -> {$role->color}.",
            'Role',
            $role->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => $role,
                'message' => 'Rol bilgileri ve etiketi başarıyla güncellendi.',
            ]);
        }

        return redirect()->back()
            ->with('success', "'{$role->name}' rolü başarıyla güncellendi.");
    }

    public function destroy(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system || $role->isSuperAdmin() || $role->isYonetim()) {
            $errorMsg = 'Sistem korumalı roller (Superadmin, Yönetim vb.) silinemez!';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $assignedUsersCount = $role->users()->count();
        if ($assignedUsersCount > 0) {
            $errorMsg = "Bu role atanmış {$assignedUsersCount} kullanıcı bulunmaktadır. Önce kullanıcıların rollerini değiştirin!";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $roleName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        AuditLogService::log(
            'deleted',
            "Rol Silindi: '**{$roleName}**' (ID: {$id}).",
            'Role',
            $id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "'{$roleName}' rolü başarıyla silindi.",
            ]);
        }

        return redirect()->back()
            ->with('success', "'{$roleName}' rolü başarıyla silindi.");
    }
}