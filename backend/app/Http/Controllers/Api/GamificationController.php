<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $stats = GamificationService::getUserStats($request->user());
        $badges = $request->user()->badges()->orderBy('earned_at', 'desc')->get();

        return response()->json([
            'stats' => $stats,
            'badges' => $badges->map(fn($badge) => [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'category' => $badge->category,
                'points' => $badge->points,
                'earned_at' => $badge->pivot->earned_at,
            ]),
        ]);
    }

    public function leaderboard(): JsonResponse
    {
        $leaderboard = GamificationService::getLeaderboard();

        return response()->json($leaderboard);
    }

    public function allBadges(): JsonResponse
    {
        $badges = \App\Models\Badge::all()->map(fn($badge) => [
            'id' => $badge->id,
            'name' => $badge->name,
            'slug' => $badge->slug,
            'description' => $badge->description,
            'icon' => $badge->icon,
            'category' => $badge->category,
            'points' => $badge->points,
            'criteria' => $badge->criteria,
        ]);

        return response()->json($badges);
    }
}
