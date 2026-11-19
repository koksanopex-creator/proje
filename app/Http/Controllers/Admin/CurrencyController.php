<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected CurrencyService $service;

    public function __construct(CurrencyService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $currencies = $this->service->getAll(activeOnly: false);
        return response()->json([
            'success' => true,
            'currencies' => $currencies,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories') && !$user->hasPermission('manage_doa_rules') && !$user->hasPermission('settings.manage')) {
            return response()->json(['success' => false, 'message' => 'Para birimi ekleme yetkiniz bulunmamaktadır.'], 403);
        }

        $data = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0.0001',
            'is_default' => 'nullable|boolean',
        ]);

        $currency = $this->service->create($data);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'message' => "'{$currency->code}' ({$currency->symbol}) para birimi başarıyla eklendi.",
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories') && !$user->hasPermission('manage_doa_rules') && !$user->hasPermission('settings.manage')) {
            return response()->json(['success' => false, 'message' => 'Para birimi düzenleme yetkiniz bulunmamaktadır.'], 403);
        }

        $data = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'nullable|numeric|min:0.0001',
            'is_default' => 'nullable|boolean',
        ]);

        $currency = $this->service->update((int)$id, $data);

        return response()->json([
            'success' => true,
            'currency' => $currency,
            'message' => "'{$currency->code}' ({$currency->symbol}) para birimi başarıyla güncellendi.",
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.manage_categories') && !$user->hasPermission('manage_doa_rules') && !$user->hasPermission('settings.manage')) {
            return response()->json(['success' => false, 'message' => 'Para birimi silme yetkiniz bulunmamaktadır.'], 403);
        }

        $result = $this->service->delete((int)$id);

        return response()->json($result);
    }
}