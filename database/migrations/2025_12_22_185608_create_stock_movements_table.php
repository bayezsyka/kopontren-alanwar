<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('direction'); // in|out|adjust
            $table->integer('qty');      // bisa negatif untuk adjust kalau mau

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->dateTime('happened_at');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'happened_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
