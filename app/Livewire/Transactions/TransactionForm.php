<?php

namespace App\Livewire\Transactions;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use Livewire\Attributes\On;
use Livewire\Component;

class TransactionForm extends Component
{
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $type             = 'expense';
    public string $amount           = '';
    public string $note             = '';
    public string $transaction_date = '';
    public string $wallet_id        = '';
    public string $category_id      = '';

    protected function rules(): array
    {
        return [
            'type'             => 'required|in:income,expense',
            'amount'           => 'required|numeric|min:1',
            'note'             => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'wallet_id'        => 'required|exists:wallets,id',
            'category_id'      => 'nullable|exists:categories,id',
        ];
    }

    #[On('open-transaction-form')]
    public function openCreate(): void
    {
        $this->reset();
        $this->transaction_date = now()->format('Y-m-d');
        $this->wallet_id = Wallet::where('user_id', auth()->id())
            ->where('is_active', true)->value('id') ?? '';
        $this->showModal = true;
    }

    #[On('edit-transaction')]
    public function openEdit(int $id): void
    {
        $tx = Transaction::forUser(auth()->id())->findOrFail($id);
        $this->editingId        = $id;
        $this->type             = $tx->type;
        $this->amount           = (string) $tx->amount;
        $this->note             = $tx->note ?? '';
        $this->transaction_date = $tx->transaction_date->format('Y-m-d');
        $this->wallet_id        = (string) $tx->wallet_id;
        $this->category_id      = (string) ($tx->category_id ?? '');
        $this->showModal        = true;
    }

    public function save(TransactionService $service): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $tx = Transaction::forUser(auth()->id())->findOrFail($this->editingId);
            $service->update($tx, $validated);
            $this->dispatch('notify', message: 'Transaksi berhasil diperbarui', type: 'success');
        } else {
            $service->create($validated, auth()->id());
            $this->dispatch('notify', message: 'Transaksi berhasil ditambahkan', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('transaction-saved');
    }

    public function render()
    {
        $wallets    = Wallet::where('user_id', auth()->id())->where('is_active', true)->get();
        $categories = Category::availableFor(auth()->id())->forType($this->type)->get();

        return view('livewire.transactions.transaction-form', compact('wallets', 'categories'));
    }
}
