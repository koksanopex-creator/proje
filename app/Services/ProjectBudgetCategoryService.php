<?php

namespace App\Services;

use App\Models\ProjectBudgetCategory;
use App\Models\ProjectBudgetItem;
use Illuminate\Database\Eloquent\Collection;

class ProjectBudgetCategoryService
{
    public function getAll(bool $activeOnly = true): Collection
    {
        $query = ProjectBudgetCategory::query()->orderBy('sort_order');
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->get();
    }

    public function create(array $data): ProjectBudgetCategory
    {
        $maxOrder = ProjectBudgetCategory::max('sort_order') ?? 0;
        $category = ProjectBudgetCategory::create([
            'name' => trim($data['name']),
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#0284c7',
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? ($maxOrder + 1),
        ]);

        AuditLogService::log(
            'created',
            "Yeni Bütçe Harcama Kategorisi Eklendi: '**{$category->name}**'.",
            'ProjectBudgetCategory',
            $category->id
        );

        return $category;
    }

    public function update(int $id, array $data): ProjectBudgetCategory
    {
        $category = ProjectBudgetCategory::findOrFail($id);
        $oldName = $category->name;

        $category->update([
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

        return $category;
    }

    public function delete(int $id): array
    {
        $category = ProjectBudgetCategory::findOrFail($id);
        $name = $category->name;

        $usageCount = ProjectBudgetItem::where('category', $name)->count();

        if ($usageCount > 0) {
            $category->is_active = false;
            $category->save();
            $category->delete(); // Soft delete
            $msg = "'{$name}' kategorisine ait {$usageCount} adet bütçe kalemi bulunduğu için kategori güvenli şekilde arşive alındı (Soft Delete).";
            $actionType = 'soft_deleted';
        } else {
            $category->forceDelete();
            $msg = "'{$name}' harcama kategorisi başarıyla kalıcı olarak silindi.";
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