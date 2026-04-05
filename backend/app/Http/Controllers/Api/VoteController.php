<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Interaction;
use App\Services\VoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function vote(Request $request, string $type, int $id): JsonResponse
    {
        $request->validate([
            'is_upvote' => 'required|boolean',
        ]);

        $validTypes = ['topics', 'interactions'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Tipo inválido'], 400);
        }

        $votable = $type === 'topics' ? Topic::find($id) : Interaction::find($id);

        if (!$votable) {
            return response()->json(['message' => ucfirst(rtrim($type, 's')) . ' não encontrado'], 404);
        }

        $votableType = $type === 'topics' ? Topic::class : Interaction::class;
        $result = VoteService::vote(auth()->id(), $votableType, $id, $request->boolean('is_upvote'));

        if (!$result['success']) {
            return response()->json($result, 429);
        }

        return response()->json($result);
    }

    public function getVoteStatus(string $type, int $id): JsonResponse
    {
        $validTypes = ['topics', 'interactions'];
        if (!in_array($type, $validTypes)) {
            return response()->json(['message' => 'Tipo inválido'], 400);
        }

        $votable = $type === 'topics' ? Topic::find($id) : Interaction::find($id);

        if (!$votable) {
            return response()->json(['message' => ucfirst(rtrim($type, 's')) . ' não encontrado'], 404);
        }

        $votableType = $type === 'topics' ? Topic::class : Interaction::class;
        $userVote = VoteService::getUserVote(auth()->id(), $votableType, $id);
        $voteCount = VoteService::getVoteCount($votableType, $id);

        return response()->json([
            'vote_count' => $voteCount,
            'user_vote' => $userVote,
        ]);
    }
}
