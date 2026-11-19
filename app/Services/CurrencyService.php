<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Project;
use App\Models\ProjectBudgetItem;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    public function getAll(bool $activeOnly = true): Collection
    {
        $query = Currency::query()->orderBy('sort_order');
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->get();
    }

    public function create(array $data): Currency
    {
        $code = strtoupper(trim($data['code']));
        $maxOrder = Currency::max('sort_order') ?? 0;

        if (!empty($data['is_default'])) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency = Currency::create([
            'code' => $code,
            'name' => trim($data['name']),
            'symbol' => trim($data['symbol'] ?? $code),
            'exchange_rate' => $data['exchange_rate'] ?? 1.0000,
            'is_default' => !empty($data['is_default']),
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? ($maxOrder + 1),
        ]);

        AuditLogService::log(
            'created',
            "Yeni Para Birimi Eklendi: '**{$currency->code}** - {$currency->name}' ({$currency->symbol}).",
            'Currency',
            $currency->id
        );

        return $currency;
    }

    public function update(int $id, array $data): Currency
    {
        $currency = Currency::findOrFail($id);

        if (!empty($data['is_default']) && !$currency->is_default) {
            Currency::where('id', '!=', $id)->update(['is_default' => false]);
        }

        $currency->update([
            'code' => strtoupper(trim($data['code'] ?? $currency->code)),
            'name' => trim($data['name'] ?? $currency->name),
            'symbol' => trim($data['symbol'] ?? $currency->symbol),
            'exchange_rate' => $data['exchange_rate'] ?? $currency->exchange_rate,
            'is_default' => isset($data['is_default']) ? (bool)$data['is_default'] : $currency->is_default,
            'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : $currency->is_active,
            'sort_order' => $data['sort_order'] ?? $currency->sort_order,
        ]);

        AuditLogService::log(
            'updated',
            "Para Birimi Güncellendi: '**{$currency->code}** - {$currency->name}'.",
            'Currency',
            $currency->id
        );

        return $currency;
    }

    public function delete(int $id): array
    {
        $currency = Currency::findOrFail($id);
        $code = $currency->code;

        if ($currency->is_default) {
            return [
                'success' => false,
                'message' => "Varsayılan para birimi olan '{$code}' silinemez. Lütfen önce başka bir para birimini varsayılan yapınız.",
            ];
        }

        $inProjects = Project::where('currency', $code)->count();
        $inBudget = ProjectBudgetItem::where('currency', $code)->count();
        $totalUsage = $inProjects + $inBudget;

        if ($totalUsage > 0) {
            $currency->is_active = false;
            $currency->save();
            $currency->delete();
            $msg = "'{$code}' para birimi projelere veya bütçe kalemlerine bağlı olduğu için ({$totalUsage} kayıt) güvenli şekilde arşive alındı (Soft Delete).";
            $actionType = 'soft_deleted';
        } else {
            $currency->forceDelete();
            $msg = "'{$code}' para birimi başarıyla kalıcı olarak silindi.";
            $actionType = 'hard_deleted';
        }

        AuditLogService::log(
            'deleted',
            "Para Birimi Silindi: '**{$code}**' ({$actionType}). {$msg}",
            'Currency',
            $id
        );

        return [
            'success' => true,
            'action_type' => $actionType,
            'message' => $msg,
        ];
    }
}