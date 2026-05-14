<?php

namespace App\Livewire\Transactions;

use App\Models\Category;
use App\Models\Wallet;
use App\Services\TransactionService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    // Filters
    public string $search         = '';
    public string $filterType     = '';
    public string $filterWallet   = '';
    public string $filterCategory = '';
    public string $filterDateFrom     = '';
    public string $filterDateTo       = '';

    // Delete modal
    public bool $showDeleteModal  = false;
    public ?int $deleteTargetId   = null;
    public string $deleteTargetNote = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterType'     => ['except' => ''],
        'filterWallet'   => ['except' => ''],
        'filterCategory' => ['except' => ''],
    ];

    // Method untuk Reset Filter
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterType',
            'filterWallet',
            'filterCategory',
            'filterDateFrom',
            'filterDateTo'
        ]);

        $this->resetPage();
    }

    // Lifecycle hooks untuk reset page saat filter berubah (Opsional: Tambahkan untuk filter tanggal juga)
    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id, string $note): void
    {
        $this->deleteTargetId   = $id;
        $this->deleteTargetNote = $note ?: 'Transaksi ini';
        $this->showDeleteModal  = true;
    }

    public function delete(TransactionService $service): void
    {
        $transaction = \App\Models\Transaction::forUser(auth()->id())
            ->findOrFail($this->deleteTargetId);

        $service->delete($transaction);

        $this->showDeleteModal  = false;
        $this->deleteTargetId   = null;

        $this->dispatch('transaction-deleted');
        $this->dispatch('notify', message: 'Transaksi berhasil dihapus', type: 'success');
    }

    #[On('transaction-saved')]
    public function refreshList(): void
    {
        // Reset ke halaman 1 agar transaksi terbaru langsung muncul di paling atas
        $this->resetPage();
    }

    public function render(TransactionService $service)
    {
        $transactions = $service->getFilteredTransactions(auth()->id(), [
            'search'      => $this->search,
            'type'        => $this->filterType,
            'wallet_id'   => $this->filterWallet,
            'category_id' => $this->filterCategory,
            'date_from'   => $this->filterDateFrom,
            'date_to'     => $this->filterDateTo,
        ]);

        $wallets    = Wallet::where('user_id', auth()->id())->where('is_active', true)->get();
        $categories = Category::availableFor(auth()->id())->get();

        return view('livewire.transactions.transaction-list', compact('transactions', 'wallets', 'categories'))
            ->layout('layouts.app', ['title' => 'Transaksi']);
    }
}
