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
        Schema::create('bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('code', 6); // Código único da sacola
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Se for organizador ou se o participante estiver logado, confirma automaticamente
            $table->string('participant_name'); // Se o participante estiver logado, já preenche automaticamente
            $table->string('participant_whatsapp')->nullable(); // Sempre opcional
            $table->string('confirmation_code', 5)->nullable(); // Código para o participante ou o organizador confirmar a participação
            $table->enum('confirmed_by', ['participant', 'organizer'])->nullable(); // Se for o organizador ou se o participante estiver logado, já confirma automaticamente
            $table->datetime('confirmed_at')->nullable()->index(); // Se for o organizador ou se o participante estiver logado, já confirma automaticamente
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['campaign_id', 'code']); // Garante que o código seja único dentro da campanha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bags');
    }
};
