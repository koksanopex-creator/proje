<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectBom;
use App\Models\ProjectBudgetItem;
use App\Services\AuditLogService;

class BomController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $projects = Project::where('is_template', false)->forUser($user)->orderBy('code')->get();
        $selectedProjectId = $request->get('project_id', $projects->first()?->id);
        $project = null;

        if ($selectedProjectId) {
            $candidate = Project::with(['boms', 'budgetItems'])->find($selectedProjectId);
            if ($candidate && $user->canAccessProject($candidate)) {
                $project = $candidate;
            }
        }

        return view('boms.index', compact('projects', 'project'));
    }

    public function storeBom(Request $request)
    {
        $user = auth()->user();
        if ($user->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, BOM kalemi ekleyemez.');
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'item_code' => 'nullable|string',
            'item_name' => 'required|string',
            'category' => 'required|string',
            'quantity' => 'required|numeric',
            'unit' => 'required|string',
            'unit_price' => 'required|numeric',
            'currency' => 'required|string|max:5',
            'supplier' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $project = Project::findOrFail($request->project_id);
        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projenin BOM listesine erişim yetkiniz bulunmamaktadır.');
        }

        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        $bom = ProjectBom::create($data);

        AuditLogService::log('created', "BOM Malzeme Ağacına '**{$bom->item_name}**' eklendi.", 'ProjectBom', $bom->id, $bom->project_id);

        return back()->with('success', "BOM kalemi başarıyla kaydedildi.");
    }

    public function storeBudget(Request $request)
    {
        $user = auth()->user();
        if ($user->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, bütçe kalemi ekleyemez.');
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string',
            'budget_type' => 'required|in:CAPEX,OPEX',
            'category' => 'required|string',
            'planned_amount' => 'required|numeric',
            'committed_amount' => 'nullable|numeric',
            'actual_spent' => 'nullable|numeric',
            'currency' => 'required|string|max:5',
        ]);

        $project = Project::findOrFail($request->project_id);
        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projenin bütçesine erişim yetkiniz bulunmamaktadır.');
        }

        $budget = ProjectBudgetItem::create($data);
        AuditLogService::log('created', "Bütçe kalemi eklendi: '**{$budget->title}**' ({$budget->budget_type})", 'ProjectBudgetItem', $budget->id, $budget->project_id);

        return back()->with('success', "Bütçe kalemi kaydedildi.");
    }
}