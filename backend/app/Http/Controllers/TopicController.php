<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $topics = Topic::with(['work', 'user'])->paginate(10);
        return response()->json($topics);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'work_id' => 'required|exists:works,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $topic = Topic::create([
            'work_id' => $request->input('work_id'),
            'user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return response()->json($topic, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Topic $topic): JsonResponse
    {
        $topic->load(['work', 'user']);
        return response()->json($topic);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic): JsonResponse
    {
        if ($request->user()->id !== $topic->user_id && !$request->user()->hasPermission('edit_any_topic')) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'   => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $topic->update($request->only(['title', 'content']));
        $topic->load(['user', 'work']); // Retornar com relacionamentos para o frontend

        return response()->json($topic);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Topic $topic): JsonResponse
    {
        if ($request->user()->id !== $topic->user_id && !$request->user()->hasPermission('delete_any_topic')) {
            return response()->json(['mensagem' => 'Acesso negado'], 403);
        }

        $topic->delete();

        return response()->json(['message' => 'Topico excluido com sucesso']);
    }
}
