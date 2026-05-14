<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
        'color',
        'icon',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Tambahkan ini di dalam class Wallet (App\Models\Wallet.php)

    protected static function booted()
    {
        static::deleting(function ($wallet) {
            if ($wallet->isForceDeleting()) {
                // Jika hapus permanen, hapus permanen juga transaksinya
                $wallet->transactions()->forceDelete();
            } else {
                // Jika soft delete, soft delete juga transaksinya
                $wallet->transactions()->delete();
            }
        });

        static::restoring(function ($wallet) {
            // Opsional: Jika dompet di-restore, transaksi juga kembali
            $wallet->transactions()->restore();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'cash'     => 'banknotes',
            'bank'     => 'building-library',
            'e-wallet' => 'device-phone-mobile',
            'savings'  => 'archive-box',
            default    => 'wallet',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cash'     => 'Tunai',
            'bank'     => 'Bank',
            'e-wallet' => 'E-Wallet',
            'savings'  => 'Tabungan',
            default    => 'Lainnya',
        };
    }
}
