<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProjectBudgetCategoryService;
use Illuminate\Http\Request;

class ProjectBudgetCategoryController extends Controller
{
    protected ProjectBudgetCategoryService $service;

    public function __construct(ProjectBudgetCategoryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $categories = $this->service->getAll(activeOnly: false);
        $parents = $this->service->getParents(activeOnly: true);

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories')) {
            return response()->json(['success' => false, 'message' => 'Harcama kategorisi ekleme yetkiniz bulunmamaktadır.'], 403);
        }

        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:project_budget_categories,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $category = $this->service->create($data);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => "'{$category->name}' harcama kategorisi başarıyla eklendi.",
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories')) {
            return response()->json(['success' => false, 'message' => 'Harcama kategorisi düzenleme yetkiniz bulunmamaktadır.'], 403);
        }

        $data = $request->validate([
            'parent_id' => 'nullable|integer|exists:project_budget_categories,id',
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $category = $this->service->update((int)$id, $data);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => "'{$category->name}' harcama kategorisi başarıyla güncellendi.",
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories')) {
            return response()->json(['success' => false, 'message' => 'Harcama kategorisi silme yetkiniz bulunmamaktadır.'], 403);
        }

        $result = $this->service->delete((int)$id);

        return response()->json($result);
    }
}
