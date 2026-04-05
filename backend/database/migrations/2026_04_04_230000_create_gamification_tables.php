<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->default('🏆');
            $table->string('category');
            $table->integer('points')->default(0);
            $table->json('criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('reputation')->default(0)->after('email');
            $table->integer('topics_count')->default(0)->after('reputation');
            $table->integer('interactions_count')->default(0)->after('topics_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reputation', 'topics_count', 'interactions_count']);
        });
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
