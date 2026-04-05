<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvancedSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private AdvancedSearchService $searchService
    ) {}

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:works,topics,all',
            'work_type' => 'nullable|string',
            'sort_by' => 'nullable|in:relevance,recent',
            'highlight' => 'nullable|boolean',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');

        $options = [
            'highlight' => $request->boolean('highlight', true),
            'sort_by' => $request->input('sort_by', 'relevance'),
            'filters' => [
                'work_type' => $request->input('work_type'),
            ],
        ];

        $results = $this->searchService->search($query, $type, $options);

        return response()->json($results);
    }
}
