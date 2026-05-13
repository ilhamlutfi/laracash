<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Component;

class DashboardPage extends Component
{
    public int $selectedYear;
    public int $selectedMonth;

    public array $summary      = [];
    public float $totalBalance = 0;
    public array $recentTx     = [];
    public array $chartData    = [];
    public array $categoryData = [];

    public function mount(DashboardService $service): void
    {
        $this->selectedYear  = now()->year;
        $this->selectedMonth = now()->month;
        $this->loadData($service);
    }

    public function updatedSelectedMonth(DashboardService $service): void
    {
        $this->loadData($service);
    }

    private function loadData(DashboardService $service): void
    {
        $userId = auth()->id();

        $this->totalBalance  = $service->getTotalBalance($userId);
        $this->summary       = $service->getSummary($userId, $this->selectedYear, $this->selectedMonth);
        $this->recentTx      = $service->getRecentTransactions($userId, 8)->toArray();
        $this->chartData     = $service->getMonthlyChart($userId, 6);
        $this->categoryData  = $service->getExpenseByCategory($userId, $this->selectedYear, $this->selectedMonth);
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-page')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
