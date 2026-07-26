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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40); // Categoria do item (ex: Comidas, Bebidas, Descartáveis, Higiene, Limpeza) - virá de class Enum
            $table->string('name'); // Nome do item necessário
            $table->string('complement')->nullable(); // Complemento do item (ex: marca, tipo, sabor)
            $table->string('unit', 20); // Unidade de medida (ex: kg, un, l) - virá de class Enum
            $table->decimal('required_quantity', 4, 1); // Quantidade necessária do item
            $table->decimal('bagged_quantity', 4, 1)->default(0); // Quantidade ensacada do item
            $table->decimal('received_quantity', 4, 1)->default(0); // Quantidade recebida do item
            $table->date('delivery_date')->nullable(); // Data limite para entrega do item específico
            $table->mediumText('note')->nullable(); // Observações adicionais sobre o item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
