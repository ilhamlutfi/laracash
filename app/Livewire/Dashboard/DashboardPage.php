<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Component;
use Livewire\Attributes\On;

class DashboardPage extends Component
{
    public int $selectedYear;
    public int $selectedMonth;

    public function mount(): void
    {
        $this->selectedYear  = now()->year;
        $this->selectedMonth = now()->month;
    }

    // Listener ini akan memicu render() ulang secara otomatis
    #[On('transaction-saved')]
    #[On('transaction-deleted')]
    public function refreshDashboard(): void
    {
        // Biarkan kosong, fungsinya hanya sebagai pemicu (trigger) render ulang
    }

    public function render(DashboardService $service)
    {
        $userId = auth()->id();

        // Ambil data secara real-time di sini
        return view('livewire.dashboard.dashboard-page', [
            'totalBalance' => $service->getTotalBalance($userId),
            'summary'      => $service->getSummary($userId, $this->selectedYear, $this->selectedMonth),
            'recentTx'     => $service->getRecentTransactions($userId, 8),
            'categoryData' => $service->getExpenseByCategory($userId, $this->selectedYear, $this->selectedMonth),
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
