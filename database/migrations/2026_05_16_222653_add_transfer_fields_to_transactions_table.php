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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('from_wallet_id')->nullable()->after('wallet_id');
            $table->foreignId('to_wallet_id')->nullable()->after('from_wallet_id');
            $table->foreignId('target_user_id')->nullable()->after('to_wallet_id');

            $table->boolean('is_transfer')->default(false);
            $table->boolean('is_internal_transfer')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
