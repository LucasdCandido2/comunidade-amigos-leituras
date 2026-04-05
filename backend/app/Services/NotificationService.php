<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Topic;
use App\Models\Interaction;
use Illuminate\Support\Facades\Event;

class NotificationService
{
    public static function onNewComment(Interaction $interaction): void
    {
        $topic = $interaction->topic;
        if (!$topic || !$topic->user_id || $topic->user_id === $interaction->user_id) {
            return;
        }

        Notification::send(
            $topic->user_id,
            'new_comment',
            'Novo comentário no seu tópico',
            sprintf(
                '%s comentou no seu tópico "%s"',
                $interaction->user->name ?? 'Alguém',
                mb_strlen($topic->title) > 50 ? mb_substr($topic->title, 0, 50) . '...' : $topic->title
            ),
            [
                'topic_id' => $topic->id,
                'interaction_id' => $interaction->id,
            ]
        );
    }

    public static function onNewTopic(Topic $topic): void
    {
        // Notificar admins/moderadores sobre novo tópico
        $moderators = $topic->work?->user;
        if ($moderators && $moderators->id !== $topic->user_id) {
            Notification::send(
                $moderators->id,
                'new_topic',
                'Novo tópico na sua obra',
                sprintf(
                    '%s criou um tópico na obra "%s"',
                    $topic->user->name ?? 'Alguém',
                    $topic->work->title ?? 'Obra'
                ),
                [
                    'topic_id' => $topic->id,
                ]
            );
        }
    }

    public static function onNewInteractionRating(Interaction $interaction): void
    {
        if (!$interaction->rating || !$interaction->topic) {
            return;
        }

        $work = $interaction->topic->work;
        if (!$work || !$work->user_id || $work->user_id === $interaction->user_id) {
            return;
        }

        Notification::send(
            $work->user_id,
            'new_rating',
            'Nova avaliação na sua obra',
            sprintf(
                '%s avaliou sua obra "%s" com %d estrelas',
                $interaction->user->name ?? 'Alguém',
                mb_strlen($work->title) > 40 ? mb_substr($work->title, 0, 40) . '...' : $work->title,
                $interaction->rating
            ),
            [
                'work_id' => $work->id,
                'rating' => $interaction->rating,
            ]
        );
    }
}
