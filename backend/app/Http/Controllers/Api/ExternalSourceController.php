<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalSource;
use Illuminate\Http\JsonResponse;

class ExternalSourceController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = ExternalSource::orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type')
            ->map(function ($group) {
                return $group->map(fn($source) => [
                    'id' => $source->id,
                    'name' => $source->name,
                    'url' => $source->url,
                    'type' => $source->type,
                    'icon' => $source->type_icon,
                ]);
            });

        return response()->json([
            'sources' => $sources,
            'types' => ExternalSource::types(),
        ]);
    }

    public function store(): JsonResponse
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'type' => 'required|in:' . implode(',', ExternalSource::types()),
        ]);

        $source = ExternalSource::create([
            'name' => request('name'),
            'url' => request('url'),
            'type' => request('type'),
        ]);

        return response()->json([
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
                'url' => $source->url,
                'type' => $source->type,
                'icon' => $source->type_icon,
            ],
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $source = ExternalSource::find($id);

        if (!$source) {
            return response()->json(['message' => 'Fonte não encontrada'], 404);
        }

        $source->delete();

        return response()->json(['message' => 'Fonte removida com sucesso']);
    }
}
