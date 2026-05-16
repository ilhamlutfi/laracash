<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getTotalBalance(int $userId): float
    {
        return (float) Wallet::where('user_id', $userId)->where('is_active', true)->sum('balance');
    }

    public function getSummary(int $userId, int $year, int $month): array
    {
        $transactionService = new TransactionService();
        $summary = $transactionService->getMonthlySummary($userId, $year, $month);

        return [
            'income'  => $summary['income'],
            'expense' => $summary['expense'],
        ];
    }

    public function getRecentTransactions(int $userId, int $limit = 8)
    {
        return Transaction::forUser($userId)
            ->with(['category', 'wallet'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function getExpenseByCategory(int $userId, int $year, int $month): array
    {
        $myWalletIds = Wallet::where('user_id', $userId)->pluck('id')->toArray();

        // 1. Ambil pengeluaran murni lewat Kategori
        $normalExpenses = Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->where('type', 'expense')
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $data = [];
        foreach ($normalExpenses as $tx) {
            if ($tx->category) {
                $data[$tx->category_id] = [
                    'name'  => $tx->category->name,
                    'color' => $tx->category->color ?? '#ef4444',
                    'total' => (float) $tx->total
                ];
            }
        }

        // 2. Tambahkan transfer ke luar akun (orang lain) sebagai kategori virtual "Transfer Keluar"
        $externalTransferTotal = Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->where('type', 'transfer')
            ->whereIn('wallet_id', $myWalletIds)
            ->whereNotIn('to_wallet_id', $myWalletIds)
            ->sum('amount');

        if ($externalTransferTotal > 0) {
            $data['virtual_transfer'] = [
                'name'  => 'Transfer Keluar',
                'color' => '#6366f1', // Indigo
                'total' => (float) $externalTransferTotal
            ];
        }

        return array_values($data);
    }
}
