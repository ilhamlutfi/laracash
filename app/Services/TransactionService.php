<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Mail\TransactionNotificationMail; // Import Mailable
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function create(array $data, int $userId): Transaction
    {
        // 1. Jalankan operasi database terlebih dahulu
        $transaction = DB::transaction(function () use ($data, $userId) {
            $transaction = Transaction::create([...$data, 'user_id' => $userId]);
            $this->applyTransactionEffect($transaction);
            return $transaction;
        });

        // 2. Panggil fungsi kirim email langsung dari sini (Diluar DB Transaction)
        $this->sendEmailNotification($transaction);

        return $transaction;
    }

    /**
     * Method Khusus untuk menghandle Notifikasi Email
     */
    public function sendEmailNotification(Transaction $transaction): void
    {
        // Load relasi agar data di template email lengkap
        $transaction->load(['wallet', 'category', 'toWallet']);

        // Definisikan daftar email manual sesuai kebutuhan Anda
        $listEmail = [
            'ilhamlutfi153@gmail.com',
            'risarahmayani@gmail.com',
        ];

        $listEmail = array_filter($listEmail);

        if (empty($listEmail)) {
            return;
        }

        $emailView = new TransactionNotificationMail($transaction);

        try {
            // Coba masukkan ke dalam antrean (Queue) agar aplikasi instan/cepat
            Mail::to($listEmail)->queue($emailView);
        } catch (\Exception $e) {
            // Jika driver queue error/tidak siap, fallback ke pengiriman langsung (send)
            Log::error('Queue gagal, mengirim email secara langsung: ' . $e->getMessage());
            Mail::to($listEmail)->send($emailView);
        }
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $this->rollbackTransactionEffect($transaction);
            $transaction->update($data);
            $this->applyTransactionEffect($transaction->fresh());
            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $this->rollbackTransactionEffect($transaction);
            $transaction->delete();
        });
    }

    private function applyTransactionEffect(Transaction $transaction): void
    {
        if ($transaction->type === 'transfer') {
            if ($transaction->wallet_id) {
                Wallet::lockForUpdate()->find($transaction->wallet_id)?->decrement('balance', $transaction->amount);
            }
            if ($transaction->to_wallet_id) {
                Wallet::lockForUpdate()->find($transaction->to_wallet_id)?->increment('balance', $transaction->amount);
            }
        } else {
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

    private function rollbackTransactionEffect(Transaction $transaction): void
    {
        if ($transaction->type === 'transfer') {
            if ($transaction->wallet_id) {
                Wallet::lockForUpdate()->find($transaction->wallet_id)?->increment('balance', $transaction->amount);
            }
            if ($transaction->to_wallet_id) {
                Wallet::lockForUpdate()->find($transaction->to_wallet_id)?->decrement('balance', $transaction->amount);
            }
        } else {
            $wallet = Wallet::lockForUpdate()->find($transaction->wallet_id);
            if ($wallet) {
                if ($transaction->type === 'income') {
                    $wallet->decrement('balance', $transaction->amount);
                } elseif ($transaction->type === 'expense') {
                    $wallet->increment('balance', $transaction->amount);
                }
            }
        }
    }

    // public function getMonthlySummary(int $userId, int $year, int $month, int $perPage = 7): array
    // {
    //     $myWalletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();
    //     $baseQuery = Transaction::forUser($userId)->forMonth($year, $month);

    //     // Pemasukan murni + Transfer dari orang lain ke dompet kita
    //     $income = (clone $baseQuery)->where(function ($query) use ($myWalletIds) {
    //         $query->where('type', 'income')
    //             ->orWhere(function ($q) use ($myWalletIds) {
    //                 $q->where('type', 'transfer')
    //                     ->whereIn('to_wallet_id', $myWalletIds)
    //                     ->whereNotIn('wallet_id', $myWalletIds);
    //             });
    //     })->sum('amount');

    //     // Pengeluaran murni + Transfer dari dompet kita ke orang lain
    //     $expense = (clone $baseQuery)->where(function ($query) use ($myWalletIds) {
    //         $query->where('type', 'expense')
    //             ->orWhere(function ($q) use ($myWalletIds) {
    //                 $q->where('type', 'transfer')
    //                     ->whereIn('wallet_id', $myWalletIds)
    //                     ->whereNotIn('to_wallet_id', $myWalletIds);
    //             });
    //     })->sum('amount');

    //     $transactions = Transaction::forUser($userId)
    //         ->forMonth($year, $month)
    //         ->with(['category', 'wallet'])
    //         ->latest('transaction_date')
    //         ->latest('id')
    //         ->simplePaginate($perPage);

    //     return [
    //         'transactions' => $transactions,
    //         'income'       => $income,
    //         'expense'      => $expense,
    //         'balance'      => $income - $expense,
    //     ];
    // }

    public function getMonthlySummary(int $userId, int $year, int $month, int $perPage = 7): array
    {
        $myWalletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();

        // Base Query terproteksi mencakup transaksi pengirim & penerima
        $baseQuery = Transaction::forMonth($year, $month)->where(function ($query) use ($userId, $myWalletIds) {
            $query->where('user_id', $userId)
                ->orWhere(function ($q) use ($myWalletIds) {
                    $q->where('type', 'transfer')
                        ->whereIn('to_wallet_id', $myWalletIds);
                });
        });

        // 1. Pemasukan Murni (Tanpa Transfer)
        $pureIncome = (clone $baseQuery)->where('type', 'income')->sum('amount');

        // 2. Transfer Masuk dari orang lain
        $transferIn = (clone $baseQuery)->where('type', 'transfer')
            ->whereIn('to_wallet_id', $myWalletIds)
            ->whereNotIn('wallet_id', $myWalletIds)
            ->sum('amount');

        // Total Pemasukan Gabungan (Murni + Transfer Masuk)
        $income = $pureIncome + $transferIn;


        // 3. Pengeluaran Murni (Tanpa Transfer)
        $pureExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');

        // 4. Transfer Keluar ke orang lain
        $transferOut = (clone $baseQuery)->where('type', 'transfer')
            ->whereIn('wallet_id', $myWalletIds)
            ->whereNotIn('to_wallet_id', $myWalletIds)
            ->sum('amount');

        // Total Pengeluaran Gabungan (Murni + Transfer Keluar)
        $expense = $pureExpense + $transferOut;


        // Ambil data list transaksi
        $transactions = (clone $baseQuery)
            ->with(['category', 'wallet', 'toWallet'])
            ->latest('transaction_date')
            ->latest('id')
            ->simplePaginate($perPage);

        return [
            'transactions' => $transactions,
            'income'       => $income,
            'expense'      => $expense,
            'balance'      => $income - $expense,
            'transfer_in'  => $transferIn,  // Data baru
            'transfer_out' => $transferOut, // Data baru
        ];
    }

    // --- Method penunjang lainnya tetap dipertahankan ---
    public function getFilteredTransactions(int $userId, array $filters = [], int $perPage = 6)
    {
        // 1. Ambil semua ID dompet milik user ini
        $myWalletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();

        // 2. Modifikasi query utama agar mencakup transfer masuk
        $query = Transaction::query()
            ->where(function ($q) use ($userId, $myWalletIds) {
                // Transaksi murni milik user tersebut
                $q->where('user_id', $userId)
                    // ATAU Transaksi transfer dari orang lain ke dompet user ini
                    ->orWhere(function ($sub) use ($myWalletIds) {
                        $sub->where('type', 'transfer')
                            ->whereIn('to_wallet_id', $myWalletIds);
                    });
            })
            ->with(['category', 'wallet', 'toWallet']) // Tambahkan toWallet jika perlu di tampilan
            ->latest('transaction_date')
            ->latest('id');

        // 3. Filter lainnya tetap dipertahankan
        if (!empty($filters['type'])) {
            // Jika user memfilter 'income', pastikan transfer masuk juga ikut atau sesuaikan keinginan
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['wallet_id'])) {
            // Filter dompet harus mengecek apakah dompet tersebut sebagai pengirim ATAU penerima
            $walletId = $filters['wallet_id'];
            $query->where(function ($q) use ($walletId) {
                $q->where('wallet_id', $walletId)
                    ->orWhere('to_wallet_id', $walletId);
            });
        }

        if (!empty($filters['category_id'])) $query->where('category_id', $filters['category_id']);
        if (!empty($filters['date_from'])) $query->whereDate('transaction_date', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('transaction_date', '<=', $filters['date_to']);
        if (!empty($filters['search'])) $query->where('note', 'like', "%{$filters['search']}%");

        return $query->paginate($perPage);
    }

    public function getArchivedMonths(int $userId): array
    {
        return Transaction::forUser($userId)
            ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month')
            ->groupBy('year', 'month')->orderByDesc('year')->orderByDesc('month')->get()
            ->map(fn($row) => [
                'year'  => $row->year,
                'month' => $row->month,
                'label' => \Carbon\Carbon::createFromDate($row->year, $row->month, 1)->translatedFormat('F Y'),
            ])->toArray();
    }
}
