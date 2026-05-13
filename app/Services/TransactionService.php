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

            // Update wallet balance
            $this->updateWalletBalance($transaction->wallet_id, $transaction->type, $transaction->amount);

            return $transaction;
        });
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Reverse old effect
            $reverseType = $transaction->type === 'income' ? 'expense' : 'income';
            $this->updateWalletBalance($transaction->wallet_id, $reverseType, $transaction->amount);

            $transaction->update($data);

            // Apply new effect
            $this->updateWalletBalance($transaction->wallet_id, $transaction->type, $transaction->amount);

            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Reverse the effect on wallet
            $reverseType = $transaction->type === 'income' ? 'expense' : 'income';
            $this->updateWalletBalance($transaction->wallet_id, $reverseType, $transaction->amount);

            $transaction->delete();
        });
    }

    private function updateWalletBalance(int $walletId, string $type, float $amount): void
    {
        $wallet = Wallet::lockForUpdate()->find($walletId);
        if ($type === 'income') {
            $wallet->increment('balance', $amount);
        } else {
            $wallet->decrement('balance', $amount);
        }
    }

    public function getFilteredTransactions(int $userId, array $filters = [], int $perPage = 15)
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

    public function getMonthlySummary(int $userId, int $year, int $month): array
    {
        $transactions = Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->with(['category', 'wallet'])
            ->latest('transaction_date')
            ->get();

        return [
            'transactions' => $transactions,
            'income'       => $transactions->where('type', 'income')->sum('amount'),
            'expense'      => $transactions->where('type', 'expense')->sum('amount'),
            'balance'      => $transactions->where('type', 'income')->sum('amount')
                - $transactions->where('type', 'expense')->sum('amount'),
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
