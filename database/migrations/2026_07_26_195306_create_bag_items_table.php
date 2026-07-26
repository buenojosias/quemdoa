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
        Schema::create('bag_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 4, 1);
            $table->string('status', 20)->default('pending')->index(); // Muda para confirmed automaticamente com o confirmed de bag
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bag_items');
    }
};
