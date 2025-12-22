<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_quick')->default(false)->after('is_active');
            $table->integer('quick_order')->default(0)->after('is_quick');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['is_quick', 'quick_order']);
        });
    }
};
