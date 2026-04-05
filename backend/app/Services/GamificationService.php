<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Interaction;
use App\Models\Notification;
use App\Models\Topic;
use App\Models\User;
use App\Models\Work;

class GamificationService
{
    public static function onTopicCreated(Topic $topic): void
    {
        $user = $topic->user;
        if (!$user) return;

        $user->increment('topics_count');
        $user->addReputation(2);

        self::checkAndAwardBadges($user, 'topics_count', $user->topics_count);
    }

    public static function onInteractionCreated(Interaction $interaction): void
    {
        $user = $interaction->user;
        if (!$user) return;

        $user->increment('interactions_count');
        $user->addReputation(1);

        self::checkAndAwardBadges($user, 'interactions_count', $user->interactions_count);

        if ($interaction->rating) {
            self::onRatingGiven($user);
        }
    }

    public static function onWorkCreated(Work $work): void
    {
        $user = $work->user;
        if (!$user) return;

        $user->addReputation(5);
        self::checkAndAwardBadges($user, 'works_count', $user->works()->count());
    }

    public static function onRatingGiven(User $user): void
    {
        $ratingsCount = Interaction::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->count();

        $user->addReputation(3);
        self::checkAndAwardBadges($user, 'ratings_count', $ratingsCount);
    }

    private static function checkAndAwardBadges(User $user, string $criteriaType, int $value): void
    {
        $badges = Badge::where('criteria->type', $criteriaType)->get();

        foreach ($badges as $badge) {
            $requiredValue = $badge->criteria['value'] ?? 0;

            if ($value >= $requiredValue) {
                $hasBadge = $user->badges()->where('badge_id', $badge->id)->exists();

                if (!$hasBadge) {
                    self::awardBadge($user, $badge);
                }
            }
        }
    }

    private static function awardBadge(User $user, Badge $badge): void
    {
        $user->badges()->attach($badge->id, ['earned_at' => now()]);
        $user->addReputation($badge->points);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'new_badge',
            'title' => 'Nova conquista desbloqueada! 🎉',
            'message' => sprintf(
                'Você ganhou o badge "%s" (%s) e +%d pontos de reputação!',
                $badge->name,
                $badge->description,
                $badge->points
            ),
            'data' => [
                'badge_id' => $badge->id,
                'badge_slug' => $badge->slug,
                'points_earned' => $badge->points,
            ],
        ]);
    }

    public static function getUserStats(User $user): array
    {
        return [
            'reputation' => $user->reputation ?? 0,
            'level' => $user->getReputationLevel(),
            'topics_count' => $user->topics_count ?? 0,
            'interactions_count' => $user->interactions_count ?? 0,
            'badges_count' => $user->badges()->count(),
            'works_count' => $user->works()->count(),
        ];
    }

    public static function getLeaderboard(int $limit = 10): array
    {
        return User::orderByDesc('reputation')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'reputation' => $user->reputation ?? 0,
                'level' => $user->getReputationLevel(),
                'badges_count' => $user->badges()->count(),
            ])
            ->toArray();
    }
}
