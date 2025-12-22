<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('type')->default('normal');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            
            $table->unsignedBigInteger('price_sell')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->integer('stock_cached')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
