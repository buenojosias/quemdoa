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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nome da campanha ou evento
            $table->string('institution')->nullable(); // Nome da instituição beneficiada
            $table->string('group')->nullable(); // Grupo ou comunidade beneficiada
            $table->text('description')->nullable();
            $table->date('confirmation_deadline'); // Data limite para confirmação da doação
            $table->date('delivery_deadline'); // Data limite para entrega dos itens
            $table->boolean('is_active')->default(true); // Indica se a campanha está ativa ou não
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
