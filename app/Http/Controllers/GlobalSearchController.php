<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use App\Services\ProjectFileService;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Genel arama (Omnisearch / Spotlight) API uç noktası.
     */
    public function search(Request $request, GlobalSearchService $searchService)
    {
        $query = (string) $request->input('q', '');
        $results = $searchService->search($query, auth()->user());

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Kullanıcının erişebildiği popüler etiketleri döner.
     */
    public function popularTags(Request $request, ProjectFileService $fileService)
    {
        $projectId = $request->input('project_id') ? (int) $request->input('project_id') : null;
        $tags = $fileService->getPopularTags($projectId, auth()->user());

        return response()->json([
            'success' => true,
            'tags' => $tags,
        ]);
    }
}
