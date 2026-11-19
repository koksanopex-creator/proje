<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectCategory;
use App\Models\Project;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class ProjectCategoryController extends Controller
{
    public function index()
    {
        $categories = ProjectCategory::orderBy('sort_order')->get()->map(function ($cat) {
            $projectCount = Project::where('category', $cat->slug)
                ->orWhereJsonContains('categories', $cat->slug)
                ->count();
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'color' => $cat->color,
                'description' => $cat->description,
                'is_active' => (bool)$cat->is_active,
                'sort_order' => $cat->sort_order,
                'projects_count' => $projectCount,
            ];
        });

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('proje.manage_categories') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Proje kategorisi ekleme yetkiniz bulunmamaktadır.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->name, '_');
        $originalSlug = $slug;
        $counter = 1;
        while (ProjectCategory::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '_' . $counter;
            $counter++;
        }

        $maxOrder = ProjectCategory::max('sort_order') ?? 0;

        $category = ProjectCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
            'description' => $request->description,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        AuditLogService::log(
            'created',
            "Yeni Proje Kategorisi Eklendi: '**{$category->name}**' ({$category->slug}).",
            'ProjectCategory',
            $category->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => "'{$category->name}' kategorisi başarıyla eklendi.",
            ]);
        }

        return redirect()->back()->with('success', "'{$category->name}' kategorisi eklendi.");
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('proje.manage_categories') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Kategori düzenleme yetkiniz bulunmamaktadır.'], 403);
        }

        $category = ProjectCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:255',
        ]);

        $category->update([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
        ]);

        AuditLogService::log(
            'updated',
            "Proje Kategorisi Güncellendi: '**{$category->name}**' ({$category->slug}).",
            'ProjectCategory',
            $category->id
        );

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => "'{$category->name}' kategorisi başarıyla güncellendi.",
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('proje.manage_categories') && !auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isDeveloper()) {
            return response()->json(['success' => false, 'message' => 'Kategori silme yetkiniz bulunmamaktadır.'], 403);
        }

        $category = ProjectCategory::withTrashed()->findOrFail($id);

        // Kategoriye bağlı proje var mı kontrolü
        $hasProjects = Project::where('category', $category->slug)
            ->orWhereJsonContains('categories', $category->slug)
            ->exists();

        if ($hasProjects) {
            $category->is_active = false;
            $category->save();
            $category->delete(); // Soft delete: sets deleted_at
            $actionType = 'soft_deleted';
            $message = "'{$category->name}' kategorisine bağlı projeler bulunduğu için güvenli şekilde arşive alındı (soft delete).";
        } else {
            $category->forceDelete(); // Hard delete: permanently removed
            $actionType = 'hard_deleted';
            $message = "'{$category->name}' kategorisine bağlı proje bulunmadığı için veritabanından kalıcı olarak silindi.";
        }

        AuditLogService::log(
            'deleted',
            "Proje Kategorisi Silindi: '**{$category->name}**' ({$actionType}). {$message}",
            'ProjectCategory',
            $id
        );

        return response()->json([
            'success' => true,
            'action_type' => $actionType,
            'message' => $message,
        ]);
    }
}
