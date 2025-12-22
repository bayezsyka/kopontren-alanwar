<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->dateTime('sold_at');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('payment_method')->nullable(); // cash|qris|transfer dsb
            $table->unsignedBigInteger('total')->default(0);

            $table->timestamps();
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
