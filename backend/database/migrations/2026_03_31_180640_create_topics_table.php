<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->onDelete('cascade'); //Relaciona com obra
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); //Criador do topico
            $table->string('title');
            $table->text('content'); //Texto do topico, pode ser markdown ou HTML sanitizado
            $table->float('rating')->default(0); //Nota media (0-5)
            $table->integer('rating_count')->default(0); //Quantidade de avaliações
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
