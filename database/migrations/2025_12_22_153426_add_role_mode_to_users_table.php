<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('kasir')->after('password');      // owner|kasir
            $table->string('ui_mode')->nullable()->after('role');            // owner|kasir (khusus owner switch UI)
            $table->dateTime('last_login_at')->nullable()->after('ui_mode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'ui_mode', 'last_login_at']);
        });
    }
};
