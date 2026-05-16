<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(array $data, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $transaction = Transaction::create([...$data, 'user_id' => $userId]);

            // MANIFESTASI SALDO: Jalankan mutasi saldo terintegrasi
            $this->applyTransactionEffect($transaction);

            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // 1. Batalkan efek saldo dari transaksi lama terlebih dahulu
            $this->rollbackTransactionEffect($transaction);

            // 2. Perbarui data transaksi
            $transaction->update($data);

            // 3. Terapkan efek saldo dari data transaksi yang baru
            // Dipanggil menggunakan fresh() agar membawa data to_wallet_id terbaru hasil update
            $this->applyTransactionEffect($transaction->fresh());

            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Batalkan efek saldo sebelum data dihapus dari database
            $this->rollbackTransactionEffect($transaction);

            $transaction->delete();
        });
    }

    /**
     * Menangani Penambahan & Pengurangan Saldo Saat Transaksi Baru / Setelah di-Update
     */
    private function applyTransactionEffect(Transaction $transaction): void
    {
        // 1. JIKA TRANSAKSI BERTIPE TRANSFER
        if ($transaction->type === 'transfer') {
            // Kurangi saldo dompet asal (Pengirim) jika ada
            if ($transaction->wallet_id) {
                $fromWallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
                if ($fromWallet) {
                    $fromWallet->decrement('balance', $transaction->amount);
                }
            }
            // Tambah saldo dompet tujuan (Penerima/Diri sendiri wallet lain) jika ada
            if ($transaction->to_wallet_id) {
                $toWallet = Wallet::lockForUpdate()->find($transaction->to_wallet_id);
                if ($toWallet) {
                    $toWallet->increment('balance', $transaction->amount);
                }
            }
        }
        // 2. JIKA TRANSAKSI BIASA (INCOME / EXPENSE)
        else {
            $wallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
            if ($wallet) {
                if ($transaction->type === 'income') {
                    $wallet->increment('balance', $transaction->amount);
                } elseif ($transaction->type === 'expense') {
                    $wallet->decrement('balance', $transaction->amount);
                }
            }
        }
    }

    /**
     * Menangani Pembalikan (Rollback) Saldo Saat Transaksi Dihapus atau Sebelum Diedit
     */
    private function rollbackTransactionEffect(Transaction $transaction): void
    {
        // 1. BALIKKAN EFEK TRANSFER (Hapus Transfer)
        if ($transaction->type === 'transfer') {
            // KEMBALIKAN UANG: Dompet asal (Pengirim) harusnya bertambah lagi
            if ($transaction->wallet_id) {
                $fromWallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
                if ($fromWallet) {
                    $fromWallet->increment('balance', $transaction->amount);
                }
            }
            // TARIK KEMBALI UANG: Dompet tujuan (Penerima) harusnya berkurang lagi
            if ($transaction->to_wallet_id) {
                $toWallet = Wallet::lockForUpdate()->find($transaction->to_wallet_id);
                if ($toWallet) {
                    $toWallet->decrement('balance', $transaction->amount);
                }
            }
        }
        // 2. BALIKKAN EFEK INCOME / EXPENSE
        else {
            $wallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
            if ($wallet) {
                if ($transaction->type === 'income') {
                    // Jika awalnya pemasukan, saat dihapus saldo dikurangi lagi
                    $wallet->decrement('balance', $transaction->amount);
                } elseif ($transaction->type === 'expense') {
                    // Jika awalnya pengeluaran, saat dihapus saldo dikembalikan (ditambah)
                    $wallet->increment('balance', $transaction->amount);
                }
            }
        }
    }

    // --- SISANYA METHOD FILTER & SUMMARY TETAP SAMA (TIDAK ADA PERUBAHAN) ---
    public function getFilteredTransactions(int $userId, array $filters = [], int $perPage = 6)
    {
        $query = Transaction::forUser($userId)
            ->with(['category', 'wallet'])
            ->latest('transaction_date')
            ->latest('id');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['search'])) {
            $query->where('note', 'like', "%{$filters['search']}%");
        }

        return $query->paginate($perPage);
    }

    public function getMonthlySummary(int $userId, int $year, int $month, int $perPage = 7): array
    {
        $query = Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->with(['category', 'wallet']);

        $income  = (clone $query)->where('type', 'income')->sum('amount');
        $expense = (clone $query)->where('type', 'expense')->sum('amount');

        return [
            'transactions' => $query->latest('transaction_date')->simplePaginate($perPage),
            'income'       => $income,
            'expense'      => $expense,
            'balance'      => $income - $expense,
        ];
    }

    public function getArchivedMonths(int $userId): array
    {
        return Transaction::forUser($userId)
            ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(fn($row) => [
                'year'  => $row->year,
                'month' => $row->month,
                'label' => \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->translatedFormat('F Y'),
            ])
            ->toArray();
    }
}
