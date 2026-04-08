<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->foreignId('external_source_id')
                ->nullable()
                ->constrained('external_sources')
                ->onDelete('set null')
                ->after('user_id');

            $table->string('external_id', 100)
                ->nullable()
                ->after('external_source_id');

            $table->string('external_url', 500)
                ->nullable()
                ->after('external_id');

            $table->string('cover_image_url', 500)
                ->nullable()
                ->after('external_url');
        });

        Schema::table('external_sources', function (Blueprint $table) {
            $table->string('api_endpoint', 500)->nullable()->after('url');
            $table->string('api_key', 255)->nullable()->after('api_endpoint');
            $table->enum('media_type', ['book', 'anime', 'manga', 'comic', 'movie', 'series'])->nullable()->after('api_key');
            $table->boolean('is_active')->default(true)->after('media_type');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropForeign(['external_source_id']);
            $table->dropColumn(['external_source_id', 'external_id', 'external_url', 'cover_image_url']);
        });

        Schema::table('external_sources', function (Blueprint $table) {
            $table->dropColumn(['api_endpoint', 'api_key', 'media_type', 'is_active']);
        });
    }
};