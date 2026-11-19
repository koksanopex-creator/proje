<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\User;
use App\Services\AuditLogService;

class RiskController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::where('is_template', false)->get();
        $selectedProjectId = $request->get('project_id');

        $query = ProjectRisk::with(['project', 'owner', 'mitigations.assignee']);
        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        $risks = $query->orderBy('risk_score', 'desc')->get();
        $users = User::where('is_active', true)->get();

        return view('risks.index', compact('risks', 'projects', 'selectedProjectId', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'probability' => 'required|integer|between:1,5',
            'impact' => 'required|integer|between:1,5',
            'status' => 'required|string',
            'owner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $score = $data['probability'] * $data['impact'];
        $data['risk_score'] = $score;

        if ($score >= 15) {
            $data['severity'] = 'critical';
        } elseif ($score >= 8) {
            $data['severity'] = 'medium';
        } else {
            $data['severity'] = 'low';
        }

        $risk = ProjectRisk::create($data);
        AuditLogService::log('created', "Risk tanımlandı: **{$risk->title}** (Puan: **{$score}**)", 'ProjectRisk', $risk->id, $risk->project_id);

        return back()->with('success', "Risk kaydı başarıyla eklendi.");
    }

    public function addMitigation(Request $request, int $riskId)
    {
        $risk = ProjectRisk::findOrFail($riskId);
        $data = $request->validate([
            'action_plan' => 'required|string',
            'strategy' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'target_date' => 'nullable|date',
        ]);

        $risk->mitigations()->create($data);
        AuditLogService::log('created', "**{$risk->title}** riskine karşı azaltma eylem planı eklendi.", 'ProjectRiskMitigation', $risk->id, $risk->project_id);

        return back()->with('success', "Risk azaltma planı kaydedildi.");
    }
}