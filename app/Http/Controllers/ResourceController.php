<?php

namespace App\Http\Controllers;

use App\Services\ResourceService;

class ResourceController extends Controller
{
    public function index()
    {
        $data = app(ResourceService::class)->getCapacityAnalysis();
        $weeks = $data['weeks'];
        $userRows = $data['user_rows'];
        $weeklyTotals = $data['weekly_totals'];
        $summary = $data['summary'];
        $conflicts = collect($userRows)->where('has_conflict', true);
        $analysis = $data['legacy_rows'];

        return view('resources.index', compact('data', 'weeks', 'userRows', 'weeklyTotals', 'summary', 'conflicts', 'analysis'));
    }
}