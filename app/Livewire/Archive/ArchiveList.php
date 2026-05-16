<?php

namespace App\Livewire\Archive;

use App\Services\TransactionService;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveList extends Component
{
    use WithPagination;

    public int $selectedYear;
    public int $selectedMonth;
    public array $months = [];

    public function mount(TransactionService $service): void
    {
        $this->selectedYear  = now()->year;
        $this->selectedMonth = now()->month;
        $this->months = $service->getArchivedMonths(auth()->id());
    }

    public function selectMonth(int $year, int $month): void
    {
        $this->selectedYear  = $year;
        $this->selectedMonth = $month;
        $this->resetPage(); // Reset halaman ke 1 saat ganti bulan
    }

    public function render(TransactionService $service)
    {
        $detail = $service->getMonthlySummary(
            auth()->id(),
            $this->selectedYear,
            $this->selectedMonth,
            7
        );

        return view('livewire.archive.archive-list', [
            'transactions' => $detail['transactions'],
            'income'       => $detail['income'],
            'expense'      => $detail['expense'],
            'balance'      => $detail['balance'],
            'transfer_in'  => $detail['transfer_in'],  // Tambahan
            'transfer_out' => $detail['transfer_out'], // Tambahan
        ])->layout('layouts.app', ['title' => 'Arsip Bulanan']);
    }
}
