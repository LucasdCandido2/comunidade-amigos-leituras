<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Works table indexes (mais críticos para performance)
        Schema::table('works', function (Blueprint $table) {
            $table->index('type', 'idx_works_type');
            $table->index('bayesian_rating', 'idx_works_rating');
            $table->index('created_at', 'idx_works_created_at');
            $table->index('title', 'idx_works_title');
            $table->index(['type', 'bayesian_rating'], 'idx_works_type_rating');
        });

        // Topics table indexes
        Schema::table('topics', function (Blueprint $table) {
            $table->index('work_id', 'idx_topics_work_id');
            $table->index('user_id', 'idx_topics_user_id');
            $table->index('created_at', 'idx_topics_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropIndex('idx_works_type');
            $table->dropIndex('idx_works_rating');
            $table->dropIndex('idx_works_created_at');
            $table->dropIndex('idx_works_title');
            $table->dropIndex('idx_works_type_rating');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->dropIndex('idx_topics_work_id');
            $table->dropIndex('idx_topics_user_id');
            $table->dropIndex('idx_topics_created_at');
        });
    }
};
