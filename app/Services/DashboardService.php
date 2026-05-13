<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSummary(int $userId, int $year, int $month): array
    {
        $base = Transaction::forUser($userId)->forMonth($year, $month);

        $income  = (clone $base)->income()->sum('amount');
        $expense = (clone $base)->expense()->sum('amount');

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    public function getTotalBalance(int $userId): float
    {
        return Wallet::where('user_id', $userId)
            ->where('is_active', true)
            ->sum('balance');
    }

    public function getRecentTransactions(int $userId, int $limit = 10)
    {
        return Transaction::forUser($userId)
            ->with(['category', 'wallet'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyChart(int $userId, int $months = 6): array
    {
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $year   = $date->year;
            $month  = $date->month;
            $base   = Transaction::forUser($userId)->forMonth($year, $month);

            $data[] = [
                'label'   => $date->format('M'),
                'income'  => (clone $base)->income()->sum('amount'),
                'expense' => (clone $base)->expense()->sum('amount'),
            ];
        }
        return $data;
    }

    public function getExpenseByCategory(int $userId, int $year, int $month): array
    {
        return Transaction::forUser($userId)
            ->forMonth($year, $month)
            ->expense()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'name'  => $t->category?->name ?? 'Lainnya',
                'color' => $t->category?->color ?? '#6b7280',
                'total' => $t->total,
            ])
            ->toArray();
    }
}
