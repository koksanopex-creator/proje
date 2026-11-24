<?php

namespace App\Services;

use App\Models\ProjectBudgetCategory;
use App\Models\ProjectBudgetItem;
use Illuminate\Database\Eloquent\Collection;

class ProjectBudgetCategoryService
{
    public function getAll(bool $activeOnly = true): Collection
    {
        $query = ProjectBudgetCategory::with(['parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->get();
    }

    public function getParents(bool $activeOnly = true): Collection
    {
        $query = ProjectBudgetCategory::parents()
            ->with(['children' => function($q) use ($activeOnly) {
                if ($activeOnly) {
                    $q->where('is_active', true);
                }
                $q->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->get();
    }

    public function create(array $data): ProjectBudgetCategory
    {
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $maxOrder = ProjectBudgetCategory::where('parent_id', $parentId)->max('sort_order') ?? 0;

        $category = ProjectBudgetCategory::create([
            'parent_id' => $parentId,
            'name' => trim($data['name']),
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#0284c7',
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? ($maxOrder + 1),
        ]);

        $parentNote = $category->parent_id ? " (Üst Kategori: '{$category->parent?->name}')" : " (Ana Kategori)";

        AuditLogService::log(
            'created',
            "Yeni Bütçe Harcama Kategorisi Eklendi: '**{$category->name}**'{$parentNote}.",
            'ProjectBudgetCategory',
            $category->id
        );

        return $category->load('parent');
    }

    public function update(int $id, array $data): ProjectBudgetCategory
    {
        $category = ProjectBudgetCategory::findOrFail($id);
        $oldName = $category->name;

        $parentId = array_key_exists('parent_id', $data)
            ? (!empty($data['parent_id']) ? (int)$data['parent_id'] : null)
            : $category->parent_id;

        // Kendi kendisinin üst kategorisi olmasını engelle
        if ($parentId === $id) {
            $parentId = null;
        }

        $category->update([
            'parent_id' => $parentId,
            'name' => trim($data['name'] ?? $category->name),
            'code' => $data['code'] ?? $category->code,
            'description' => $data['description'] ?? $category->description,
            'color' => $data['color'] ?? $category->color,
            'is_active' => $data['is_active'] ?? $category->is_active,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        AuditLogService::log(
            'updated',
            "Bütçe Harcama Kategorisi Güncellendi: '**{$oldName}**' -> '**{$category->name}**'.",
            'ProjectBudgetCategory',
            $category->id
        );

        return $category->load('parent');
    }

    public function delete(int $id): array
    {
        $category = ProjectBudgetCategory::with('children')->findOrFail($id);
        $name = $category->name;

        $usageCount = ProjectBudgetItem::where('category', $name)
            ->orWhere('sub_category', $name)
            ->orWhere('category_id', $id)
            ->orWhere('sub_category_id', $id)
            ->count();

        // Alt kategorilerin kullanımını da kontrol et
        $childIds = $category->children->pluck('id')->toArray();
        if (!empty($childIds)) {
            $usageCount += ProjectBudgetItem::whereIn('sub_category_id', $childIds)
                ->orWhereIn('category_id', $childIds)
                ->count();
        }

        if ($usageCount > 0) {
            $category->is_active = false;
            $category->save();
            $category->children()->update(['is_active' => false]);
            $category->delete(); // Soft delete
            $msg = "'{$name}' kategorisine (veya alt kırılımlarına) ait {$usageCount} adet bütçe kalemi bulunduğu için kategori güvenli şekilde arşive alındı (Soft Delete).";
            $actionType = 'soft_deleted';
        } else {
            $category->children()->forceDelete();
            $category->forceDelete();
            $msg = "'{$name}' harcama kategorisi ve alt kategorileri kalıcı olarak silindi.";
            $actionType = 'hard_deleted';
        }

        AuditLogService::log(
            'deleted',
            "Bütçe Harcama Kategorisi Silindi: '**{$name}**' ({$actionType}). {$msg}",
            'ProjectBudgetCategory',
            $id
        );

        return [
            'success' => true,
            'action_type' => $actionType,
            'message' => $msg,
        ];
    }
}
