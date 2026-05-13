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
    public string $search      = '';
    public string $filterType  = '';
    public string $filterWallet   = '';
    public string $filterCategory = '';
    public string $filterFrom  = '';
    public string $filterTo    = '';

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterType(): void
    {
        $this->resetPage();
    }
    public function updatingFilterWallet(): void
    {
        $this->resetPage();
    }
    public function updatingFilterCategory(): void
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
        $this->resetPage();
    }

    public function render(TransactionService $service)
    {
        $transactions = $service->getFilteredTransactions(auth()->id(), [
            'search'      => $this->search,
            'type'        => $this->filterType,
            'wallet_id'   => $this->filterWallet,
            'category_id' => $this->filterCategory,
            'date_from'   => $this->filterFrom,
            'date_to'     => $this->filterTo,
        ]);

        $wallets    = Wallet::where('user_id', auth()->id())->where('is_active', true)->get();
        $categories = Category::availableFor(auth()->id())->get();

        return view('livewire.transactions.transaction-list', compact('transactions', 'wallets', 'categories'))
            ->layout('layouts.app', ['title' => 'Transaksi']);
    }
}
