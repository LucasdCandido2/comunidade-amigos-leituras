<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function index(Request $request)
    {
        // ?all=true → todas as obras (para gerenciamento)
        // padrão   → top 10 por ranking bayesiano (para exibição pública)
        if ($request->query('all') === 'true') {
            $works = Work::orderBy('title')->get();
        } else {
            $works = Work::orderBy('bayesian_rating', 'desc')->limit(10)->get();
        }

        return response()->json($works);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:book,manga,anime,comic,hq',
            'canonical_url' => 'nullable|url'
        ]);

        $work = Work::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'canonical_url' => $request->canonical_url,
            'user_id' => auth()->id(),
            'is_user_suggested' => true
        ]);

        return response()->json($work, 201);
    }

    public function show($id)
    {
        $work = Work::findOrFail($id);
        return response()->json($work);
    }

    public function update(Request $request, $id)
    {
        $work = Work::findOrFail($id);

        // Check ownership
        if ($work->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|required|in:book,manga,anime,comic,hq',
            'canonical_url' => 'nullable|url'
        ]);

        $work->update($request->only(['title', 'description', 'type', 'canonical_url']));

        return response()->json($work);
    }

    public function destroy($id)
    {
        $work = Work::findOrFail($id);

        // Check ownership
        if ($work->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $work->delete();

        return response()->json(['message' => 'Work deleted successfully']);
    }
}