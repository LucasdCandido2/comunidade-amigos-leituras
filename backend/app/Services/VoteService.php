<?php

namespace App\Services;

use App\Models\Vote;
use Illuminate\Support\Facades\Cache;

class VoteService
{
    private const COOLDOWN_SECONDS = 60;

    public static function vote(int $userId, string $votableType, int $votableId, bool $isUpvote): array
    {
        $cacheKey = "vote_cooldown:{$userId}:{$votableType}:{$votableId}";

        if (Cache::has($cacheKey)) {
            $remaining = Cache::get($cacheKey);
            return [
                'success' => false,
                'message' => 'Aguarde para votar novamente',
                'remaining_seconds' => $remaining,
            ];
        }

        $existingVote = Vote::where('user_id', $userId)
            ->where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->first();

        if ($existingVote) {
            if ($existingVote->is_upvote === $isUpvote) {
                $existingVote->delete();
                Cache::forget($cacheKey);
                return [
                    'success' => true,
                    'message' => 'Voto removido',
                    'action' => 'removed',
                    'vote_count' => self::getVoteCount($votableType, $votableId),
                ];
            }

            $existingVote->update(['is_upvote' => $isUpvote]);
            Cache::put($cacheKey, self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);
            return [
                'success' => true,
                'message' => $isUpvote ? 'Voto positivo' : 'Voto negativo',
                'action' => 'changed',
                'vote_count' => self::getVoteCount($votableType, $votableId),
            ];
        }

        Vote::create([
            'user_id' => $userId,
            'votable_type' => $votableType,
            'votable_id' => $votableId,
            'is_upvote' => $isUpvote,
        ]);

        Cache::put($cacheKey, self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);

        return [
            'success' => true,
            'message' => $isUpvote ? 'Voto positivo registrado' : 'Voto negativo registrado',
            'action' => 'created',
            'vote_count' => self::getVoteCount($votableType, $votableId),
        ];
    }

    public static function getVoteCount(string $votableType, int $votableId): int
    {
        $result = Vote::where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->selectRaw('SUM(CASE WHEN is_upvote = true THEN 1 ELSE -1 END) as vote_sum')
            ->first();

        return (int) ($result->vote_sum ?? 0);
    }

    public static function getUserVote(int $userId, string $votableType, int $votableId): ?bool
    {
        $vote = Vote::where('user_id', $userId)
            ->where('votable_type', $votableType)
            ->where('votable_id', $votableId)
            ->first();

        return $vote ? $vote->is_upvote : null;
    }

    public static function decrementCooldownCache(int $userId, string $votableType, int $votableId): void
    {
        $cacheKey = "vote_cooldown:{$userId}:{$votableType}:{$votableId}";
        $remaining = Cache::get($cacheKey);

        if ($remaining !== null && $remaining > 0) {
            Cache::put($cacheKey, $remaining - 1, now()->addSeconds($remaining));
        }
    }
}
