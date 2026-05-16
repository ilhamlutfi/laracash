<?php

namespace App\Livewire\Transactions;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\TransactionService;

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

    // Transfer-specific fields
    public bool $is_transfer = false;
    public string $to_wallet_id = '';
    public string $target_user_id = '';

    protected function rules(): array
    {
        $rules = [
            'type'             => 'required|in:income,expense,transfer',
            'amount'           => 'required|numeric|min:1',
            'note'             => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'wallet_id'        => 'required|exists:wallets,id',
            'category_id'      => 'required|exists:categories,id',
        ];

        if ($this->type === 'transfer') {
            $rules['category_id']   = 'nullable';
            $rules['target_user_id'] = 'required|exists:users,id';
            $rules['to_wallet_id']   = 'required|exists:wallets,id|different:wallet_id';
        }

        return $rules;
    }

    // Otomatis mengosongkan dompet tujuan jika penerima diubah
    public function updatedTargetUserId(): void
    {
        $this->to_wallet_id = '';
    }

    // Jika dompet asal diubah, dan tujuannya adalah diri sendiri, reset dompet tujuan agar tidak bentrok
    public function updatedWalletId(): void
    {
        if ($this->type === 'transfer' && $this->target_user_id == auth()->id()) {
            $this->to_wallet_id = '';
        }
    }

    public function updatedType($value): void
    {
        if ($value !== 'transfer') {
            $this->target_user_id = '';
            $this->to_wallet_id = '';
        }
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

        if ($tx->type === 'transfer') {
            $this->to_wallet_id   = (string) ($tx->to_wallet_id ?? '');
            $this->target_user_id = (string) (Wallet::find($tx->to_wallet_id)?->user_id ?? '');
        }

        $this->showModal        = true;
    }

    public function save(TransactionService $service): void
    {
        $validated = $this->validate();

        // PERBAIKAN: Jika transfer, paksa category_id menjadi null
        if ($this->type === 'transfer') {
            $validated['category_id'] = null;
        } else {
            // Jika bukan transfer tetapi string kosong, ubah juga jadi null
            $validated['category_id'] = $this->category_id ?: null;
        }

        if ($this->editingId) {
            $tx = Transaction::forUser(auth()->id())->findOrFail($this->editingId);
            $service->update($tx, $validated);
            $this->dispatch('notify', message: 'Berhasil diperbarui', type: 'success');
        } else {
            $service->create($validated, auth()->id());
            $this->dispatch('notify', message: 'Berhasil ditambahkan', type: 'success');
        }

        $this->showModal = false;
        $this->dispatch('transaction-saved');
    }

    public function render()
    {
        // 1. Dompet milik user login (Dompet Asal)
        $wallets = Wallet::where('user_id', auth()->id())->where('is_active', true)->get();

        $categories = Category::availableFor(auth()->id())->forType($this->type)->get();

        // 2. Daftar user lain untuk opsi transfer ke orang lain
        $otherUsers = User::where('id', '!=', auth()->id())
            ->whereHas('wallets', function ($query) {
                $query->where('is_active', true);
            })->get();

        // 3. Logika penentuan dompet tujuan (Target Wallets)
        $targetWallets = collect();
        if ($this->type === 'transfer' && $this->target_user_id) {
            if ($this->target_user_id == auth()->id()) {
                // Jika pilih diri sendiri, ambil dompet pribadi yang BUKAN dompet asal terpilih
                $targetWallets = Wallet::where('user_id', auth()->id())
                    ->where('is_active', true)
                    ->where('id', '!=', $this->wallet_id)
                    ->get();
            } else {
                // Jika pilih orang lain, ambil semua dompet aktif milik orang tersebut
                $targetWallets = Wallet::where('user_id', $this->target_user_id)
                    ->where('is_active', true)
                    ->get();
            }
        }

        return view('livewire.transactions.transaction-form', compact('wallets', 'categories', 'otherUsers', 'targetWallets'));
    }
}
