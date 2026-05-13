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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 15, 2);
            $table->string('note')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type', 'transaction_date']);
            $table->index(['user_id', 'transaction_date']);
            $table->index(['wallet_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
