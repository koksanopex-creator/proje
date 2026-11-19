<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\ProjectTimesheet;
use App\Services\ResourceService;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'portfolio'); // portfolio, capacity, budget, timesheet, risk
        $user = auth()->user();

        $data = [];

        switch ($type) {
            case 'portfolio':
                $data['projects'] = Project::with(['bolum', 'manager'])
                    ->where('is_template', false)
                    ->forUser($user)
                    ->get();
                break;
            case 'capacity':
                $capacityData = app(ResourceService::class)->getCapacityAnalysis($user);
                $data['capacity'] = $capacityData['user_rows'] ?? $capacityData['legacy_rows'] ?? [];
                $data['capacity_summary'] = $capacityData['summary'] ?? [];
                break;
            case 'budget':
                $data['projects'] = Project::with('budgetItems')
                    ->where('is_template', false)
                    ->forUser($user)
                    ->get();
                break;
            case 'timesheet':
                $data['timesheets'] = ProjectTimesheet::with(['user', 'project', 'task'])
                    ->whereHas('project', function ($pq) use ($user) {
                        $pq->forUser($user);
                    })
                    ->latest()
                    ->take(50)
                    ->get();
                break;
            case 'risk':
                $data['risks'] = ProjectRisk::with(['project', 'owner'])
                    ->whereHas('project', function ($pq) use ($user) {
                        $pq->forUser($user);
                    })
                    ->orderBy('risk_score', 'desc')
                    ->get();
                break;
        }

        return view('reports.index', compact('type', 'data'));
    }
}