<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Services\NotificationService;
use App\Services\RankingService;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    public function index($topicId)
    {
        $interactions = Interaction::where('topic_id', $topicId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($interactions);
    }

    public function store(Request $request, $topicId)
    {
        $request->validate([
            'content' => 'required|string|min:3',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $interaction = Interaction::create([
            'user_id' => auth()->id(),
            'topic_id' => $topicId,
            'content' => $request->input('content'),
            'rating' => $request->input('rating'),
        ]);

        $interaction->load('user:id,name');

        $topic = \App\Models\Topic::find($topicId);
        if ($topic && $topic->work) {
            RankingService::recalculateWorkRanking($topic->work);
        }

        NotificationService::onNewComment($interaction);

        if ($request->input('rating')) {
            NotificationService::onNewInteractionRating($interaction);
        }

        return response()->json($interaction, 201);
    }
}
