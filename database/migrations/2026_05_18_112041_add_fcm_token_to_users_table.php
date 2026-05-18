<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            // Menambahkan kolom fcm_token setelah kolom password
            // Dibuat nullable karena tidak semua user langsung punya token saat mendaftar
            $blueprint->string('fcm_token')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            // Untuk merollback jika diperlukan
            $blueprint->dropColumn('fcm_token');
        });
    }
};
