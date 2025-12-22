<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();

            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('unit_cost')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_lines');
    }
};
