<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoaThresholdRule;
use App\Models\Committee;
use App\Models\Role;
use App\Models\User;
use App\Models\Bolum;
use App\Models\ProjectCategory;
use App\Models\SystemSetting;
use App\Models\Currency;
use App\Services\DoaThresholdService;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

class DoaThresholdController extends Controller
{
    protected DoaThresholdService $doaService;

    public function __construct(DoaThresholdService $doaService)
    {
        $this->doaService = $doaService;
    }

    protected function checkViewPermission()
    {
        $u = auth()->user();
        if (!$u || (!$u->isDeveloper() && !$u->hasRole('admin') && !$u->hasPermission('manage_doa_rules') && !$u->hasPermission('view_doa_rules'))) {
            abort(403, 'Bu sayfayı görüntüleme yetkiniz bulunmamaktadır.');
        }
    }

    protected function checkManagePermission()
    {
        $u = auth()->user();
        if (!$u || (!$u->isDeveloper() && !$u->hasRole('admin') && !$u->hasPermission('manage_doa_rules'))) {
            abort(403, 'Bu işlem için Bütçe Eşik Kademelerini Yönetme yetkiniz bulunmamaktadır.');
        }
    }

    public function index()
    {
        $this->checkViewPermission();

        $rules = DoaThresholdRule::with(['role', 'user', 'committee', 'bolum'])
            ->orderBy('priority_order', 'asc')
            ->get();

        $committees = Committee::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $categories = ProjectCategory::orderBy('name')->get();
        $bolumler = Bolum::orderBy('ad')->get();
        $currencies = Currency::where('is_active', true)->get();

        $engineActive = $this->doaService->isEngineActive();
        $baseCurrency = $this->doaService->getBaseCurrency();

        return view('admin.doa_thresholds.index', compact(
            'rules',
            'committees',
            'roles',
            'users',
            'categories',
            'bolumler',
            'currencies',
            'engineActive',
            'baseCurrency'
        ));
    }

    public function store(Request $request)
    {
        $this->checkManagePermission();

        // Binlik ayracı (nokta) ve virgül normalizasyonu
        if ($request->has('min_amount')) {
            $val = str_replace('.', '', (string)$request->input('min_amount'));
            $val = str_replace(',', '.', $val);
            $request->merge(['min_amount' => $val !== '' ? (float)$val : 0]);
        }
        if ($request->filled('max_amount')) {
            $val = str_replace('.', '', (string)$request->input('max_amount'));
            $val = str_replace(',', '.', $val);
            $request->merge(['max_amount' => $val !== '' ? (float)$val : null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rule_type' => 'required|string|in:project_approval,finance_payment,budget_revision',
            'currency_code' => 'required|string|max:10',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'requires_approval' => 'required|boolean',
            'approver_type' => 'required|string|in:role,user,committee,team',
            'role_id' => 'nullable|required_if:approver_type,role|exists:roles,id',
            'user_id' => 'nullable|required_if:approver_type,user|exists:users,id',
            'committee_id' => 'nullable|required_if:approver_type,committee|exists:committees,id',
            'role_group_slug' => 'nullable|string',
            'project_category_slug' => 'nullable|string',
            'bolum_id' => 'nullable|exists:bolumler,id',
            'priority_order' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $slugBase = Str::slug($validated['name']);
        $slug = $slugBase;
        $counter = 1;
        while (DoaThresholdRule::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $rule = DoaThresholdRule::create(array_merge($validated, [
            'slug' => $slug,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]));

        AuditLogService::log(
            'created',
            "Yeni Bütçe Eşik Kademesi oluşturuldu: '{$rule->name}' ({$rule->amount_range_formatted})",
            'DoaThresholdRule',
            $rule->id,
            null,
            null,
            $rule->toArray()
        );

        return redirect()->route('admin.doa_thresholds.index')
            ->with('success', "'{$rule->name}' kademesi başarıyla oluşturuldu.");
    }

    public function update(Request $request, $id)
    {
        $this->checkManagePermission();

        $rule = DoaThresholdRule::findOrFail($id);

        // Binlik ayracı (nokta) ve virgül normalizasyonu
        if ($request->has('min_amount')) {
            $val = str_replace('.', '', (string)$request->input('min_amount'));
            $val = str_replace(',', '.', $val);
            $request->merge(['min_amount' => $val !== '' ? (float)$val : 0]);
        }
        if ($request->filled('max_amount')) {
            $val = str_replace('.', '', (string)$request->input('max_amount'));
            $val = str_replace(',', '.', $val);
            $request->merge(['max_amount' => $val !== '' ? (float)$val : null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rule_type' => 'required|string|in:project_approval,finance_payment,budget_revision',
            'currency_code' => 'required|string|max:10',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'requires_approval' => 'required|boolean',
            'approver_type' => 'required|string|in:role,user,committee,team',
            'role_id' => 'nullable|required_if:approver_type,role|exists:roles,id',
            'user_id' => 'nullable|required_if:approver_type,user|exists:users,id',
            'committee_id' => 'nullable|required_if:approver_type,committee|exists:committees,id',
            'role_group_slug' => 'nullable|string',
            'project_category_slug' => 'nullable|string',
            'bolum_id' => 'nullable|exists:bolumler,id',
            'priority_order' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $oldValues = $rule->toArray();
        $rule->update($validated);

        AuditLogService::log(
            'updated',
            "Bütçe Eşik Kademesi güncellendi: '{$rule->name}' ({$rule->amount_range_formatted})",
            'DoaThresholdRule',
            $rule->id,
            null,
            $oldValues,
            $rule->toArray()
        );

        return redirect()->route('admin.doa_thresholds.index')
            ->with('success', "'{$rule->name}' kademesi başarıyla güncellendi.");
    }

    public function destroy($id)
    {
        $this->checkManagePermission();

        $rule = DoaThresholdRule::findOrFail($id);
        $name = $rule->name;
        $rule->delete();

        AuditLogService::log(
            'deleted',
            "Bütçe Eşik Kademesi silindi: '{$name}'",
            'DoaThresholdRule',
            $id
        );

        return redirect()->route('admin.doa_thresholds.index')
            ->with('success', "'{$name}' kademesi silindi.");
    }

    public function toggleActive($id)
    {
        $this->checkManagePermission();

        $rule = DoaThresholdRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        $statusText = $rule->is_active ? 'Aktife Alındı' : 'Devre Dışı Bırakıldı';

        AuditLogService::log(
            'status_changed',
            "Bütçe Eşik Kademesi durumu değiştirildi: '{$rule->name}' - {$statusText}",
            'DoaThresholdRule',
            $rule->id,
            null,
            null,
            ['is_active' => $rule->is_active]
        );

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
            'message' => "'{$rule->name}' kademesi {$statusText}.",
        ]);
    }

    public function toggleGlobalEngine(Request $request)
    {
        $this->checkManagePermission();

        $current = $this->doaService->isEngineActive();
        $newState = !$current;

        SystemSetting::updateOrCreate(
            ['key' => 'doa_budget_approval_engine_active'],
            ['value' => $newState ? '1' : '0', 'type' => 'boolean']
        );

        $statusText = $newState ? 'Genel Olarak Devreye Alındı' : 'Genel Olarak Kapatıldı (Standart Onay Devrede)';

        AuditLogService::log(
            'status_changed',
            "Bütçe Eşik Motoru (DOA) {$statusText}",
            'SystemSetting',
            null,
            null,
            ['engine_active' => $current],
            ['engine_active' => $newState]
        );

        return response()->json([
            'success' => true,
            'engine_active' => $newState,
            'message' => "Bütçe Eşik Motoru {$statusText}.",
        ]);
    }

    public function simulate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string',
            'category' => 'nullable|string',
            'bolum_id' => 'nullable|integer',
        ]);

        $amount = (float)$request->amount;
        $currency = $request->currency;
        $baseCurrency = $this->doaService->getBaseCurrency();
        $baseAmount = $this->doaService->convertToBase($amount, $currency, $baseCurrency);

        // Sahte proje nesnesiyle kural motorunu test et
        $dummyProject = new \App\Models\Project();
        $dummyProject->planned_budget = $amount;
        $dummyProject->currency = $currency;
        $dummyProject->category = $request->category;
        $dummyProject->bolum_id = $request->bolum_id;

        $matchedRule = $this->doaService->matchRule($dummyProject);

        if (!$matchedRule) {
            return response()->json([
                'matched' => false,
                'base_amount' => number_format($baseAmount, 2, ',', '.') . " {$baseCurrency}",
                'message' => 'Bu tutar ve parametreler için tanımlı bir eşik kuralı bulunamadı. Standart kurumsal hiyerarşi uygulanır.',
            ]);
        }

        return response()->json([
            'matched' => true,
            'base_amount' => number_format($baseAmount, 2, ',', '.') . " {$baseCurrency}",
            'rule_name' => $matchedRule->name,
            'rule_slug' => $matchedRule->slug,
            'requires_approval' => $matchedRule->requires_approval,
            'approver_type' => $matchedRule->approver_type,
            'approver_name' => $matchedRule->approver_name,
            'priority_order' => $matchedRule->priority_order,
            'message' => $matchedRule->requires_approval
                ? "'{$matchedRule->name}' eşiği tetiklenir: {$matchedRule->approver_name} onayı gerekir."
                : "'{$matchedRule->name}' eşiği tetiklenir: Doğrudan devreye alınır (onaysız / hızlı geçiş).",
        ]);
    }
}
