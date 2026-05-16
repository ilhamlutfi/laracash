<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'from_wallet_id',
        'to_wallet_id',
        'target_user_id',
        'category_id',
        'type',
        'amount',
        'note',
        'transaction_date',
        'is_transfer',
        'is_internal_transfer'
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Relasi ke dompet tujuan (Ditambahkan untuk kebutuhan transfer ke orang lain)
     */
    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // --- Scopes ---

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);
    }

    public function scopeForDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    // --- Accessors ---

    public function getIsIncomeAttribute(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Disesuaikan agar tanda + atau - dinamis tergantung siapa yang melihat
     */
    public function getFormattedAmountAttribute(): string
    {
        $currentUserId = auth()->id();

        // Default penentuan prefix awal
        $prefix = '-';

        if ($this->type === 'income') {
            $prefix = '+';
        } elseif ($this->type === 'transfer') {
            // Jika user_id transaksi BUKAN milik user yang sedang login,
            // berarti ini adalah transfer masuk bagi user tersebut.
            if ($this->user_id !== $currentUserId) {
                $prefix = '';
            }
        }

        return $prefix . 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
