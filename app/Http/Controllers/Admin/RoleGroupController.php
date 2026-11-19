<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoleGroup;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class RoleGroupController extends Controller
{
    /**
     * Tüm rol gruplarını listele (JSON)
     */
    public function index()
    {
        $groups = RoleGroup::withCount('roles')->orderBy('sort_order')->get();
        return response()->json([
            'success' => true,
            'groups' => $groups,
        ]);
    }

    /**
     * Yeni Dinamik Rol Grubu Ekle
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50|unique:role_groups,slug',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->slug, '_')
            : Str::slug($request->name, '_');

        // Slug benzersizliği
        $originalSlug = $slug;
        $counter = 1;
        while (RoleGroup::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }

        $maxOrder = RoleGroup::max('sort_order') ?? 0;

        $group = RoleGroup::create([
            'name' => $request->name,
            'slug' => $slug,
            'icon' => $request->icon ?: '🏷️',
            'color' => $request->color ?: '#0284c7',
            'description' => $request->description,
            'sort_order' => $request->filled('sort_order') ? (int)$request->sort_order : ($maxOrder + 1),
            'is_system' => false,
        ]);

        AuditLogService::log(
            'created',
            "Yeni Rol Grubu Tanımlandı: '**{$group->name}**' (Slug: {$group->slug}, İkon: {$group->icon}).",
            'RoleGroup',
            $group->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'group' => $group,
                'message' => "'{$group->name}' rol grubu başarıyla eklendi.",
            ]);
        }

        return redirect()->back()->with('success', "'{$group->name}' rol grubu eklendi.");
    }

    /**
     * Rol Grubu Güncelle
     */
    public function update(Request $request, $id)
    {
        $group = RoleGroup::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $oldName = $group->name;

        $updateData = [
            'name' => $request->name,
            'icon' => $request->icon ?: $group->icon,
            'color' => $request->color ?: $group->color,
            'description' => $request->description,
        ];

        if ($request->filled('sort_order')) {
            $updateData['sort_order'] = (int)$request->sort_order;
        }

        $group->update($updateData);

        AuditLogService::log(
            'updated',
            "Rol Grubu Güncellendi: '**{$oldName}**' -> '**{$group->name}**' (Slug: {$group->slug}).",
            'RoleGroup',
            $group->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'group' => $group,
                'message' => "'{$group->name}' rol grubu güncellendi.",
            ]);
        }

        return redirect()->back()->with('success', "'{$group->name}' rol grubu güncellendi.");
    }

    /**
     * Rol Grubu Sil
     */
    public function destroy($id)
    {
        $group = RoleGroup::findOrFail($id);

        if ($group->is_system) {
            return response()->json([
                'success' => false,
                'message' => "Korumalı sistem rol grubu ('{$group->name}') silinemez.",
            ], 422);
        }

        // Bu grupta rol var mı?
        $assignedRolesCount = Role::where('role_group', $group->slug)->count();
        if ($assignedRolesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Bu grupta {$assignedRolesCount} adet tanımlı rol bulunmaktadır. Önce rolleri başka bir gruba taşıyınız.",
            ], 422);
        }

        $groupName = $group->name;
        $group->delete();

        AuditLogService::log(
            'deleted',
            "Rol Grubu Silindi: '**{$groupName}**'.",
            'RoleGroup',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => "'{$groupName}' rol grubu başarıyla silindi.",
        ]);
    }
}