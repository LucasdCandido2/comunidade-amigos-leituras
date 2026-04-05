<?php

namespace App\Services;

use App\Models\Work;
use Illuminate\Support\Facades\DB;

class RankingService
{
    public static function calculateGlobalAverage()
    {
        // Média ponderada de todas as interações com rating
        $totalRating = DB::table('interactions')
            ->whereNotNull('rating')
            ->sum('rating');

        $totalCount = DB::table('interactions')
            ->whereNotNull('rating')
            ->count();

        return $totalCount > 0 ? $totalRating / $totalCount : 3; // fallback
    }

    public static function recalculateWorkRanking(Work $work)
    {
        $prior = self::calculateGlobalAverage();
        $confidence = 5; // ajustável

        $interactions = $work->topics?->flatMap->interactions->filter(fn($i) => !is_null($i->rating)) ?? collect();
        $numVotes = $interactions->count();
        $avgRating = $numVotes > 0 ? $interactions->avg('rating') : 0;

        $bayesianAverage = ($prior * $confidence + $avgRating * $numVotes) / ($confidence + $numVotes);

        $work->update(['bayesian_rating' => round($bayesianAverage, 2)]);
    }
}