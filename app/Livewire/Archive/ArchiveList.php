<?php

namespace App\Livewire\Archive;

use App\Services\TransactionService;
use Livewire\Component;

class ArchiveList extends Component
{
    public ?int $selectedYear  = null;
    public ?int $selectedMonth = null;

    public array $months  = [];
    public array $detail  = [];

    public function mount(TransactionService $service): void
    {
        $this->months = $service->getArchivedMonths(auth()->id());
    }

    public function selectMonth(int $year, int $month, TransactionService $service): void
    {
        $this->selectedYear  = $year;
        $this->selectedMonth = $month;
        $this->detail        = $service->getMonthlySummary(auth()->id(), $year, $month);
    }

    public function render()
    {
        return view('livewire.archive.archive-list')
            ->layout('layouts.app', ['title' => 'Arsip Bulanan']);
    }
}
