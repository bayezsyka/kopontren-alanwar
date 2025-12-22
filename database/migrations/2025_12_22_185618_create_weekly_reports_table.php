<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');         // 1..12
            $table->unsignedTinyInteger('week_of_month'); // 1..5

            $table->date('start_date'); // start week (Mon)
            $table->date('end_date');   // end week (Sun)

            $table->string('status')->default('draft'); // draft|final
            $table->dateTime('generated_at')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->unique(['year', 'month', 'week_of_month']);
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
