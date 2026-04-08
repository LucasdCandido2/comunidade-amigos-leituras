<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS topics_created_at_index ON topics (created_at)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS topics_rating_created_at_index ON topics (rating, created_at)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS interactions_created_at_index ON interactions (created_at)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS works_type_index ON works (type)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS works_rating_created_at_index ON works (bayesian_rating, created_at)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS votes_votable_type_votable_id_index ON votes (votable_type, votable_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS votes_user_votable_unique ON votes (user_id, votable_type, votable_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS notifications_user_id_is_read_index ON notifications (user_id, is_read)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS assets_topic_id_index ON assets (topic_id)');
        } catch (\Exception $e) {}

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS assets_user_id_index ON assets (user_id)');
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        // Índices não são removidos no down
    }
};