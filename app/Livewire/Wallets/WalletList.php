<?php

namespace App\Livewire\Wallets;

use App\Models\Wallet;
use DB;
use Livewire\Attributes\On;
use Livewire\Component;

class WalletList extends Component
{
    public bool $showForm = false;
    public ?int $editingId = null;
    public bool $showDeleteModal = false;
    public ?int $deleteTargetId = null;
    public string $deleteTargetName = '';

    public string $name    = '';
    public string $type    = 'cash';
    public string $balance = '';
    public string $color   = '#6366f1';
    public string $icon    = 'wallet';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:100',
            'type'    => 'required|in:cash,bank,e-wallet,savings',
            'balance' => 'required|numeric|min:0',
            'color'   => 'required|string|size:7',
            'icon'    => 'nullable|string',
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'balance', 'icon']);
        $this->type  = 'cash';
        $this->color = '#6366f1';
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($id);
        $this->editingId = $id;
        $this->name      = $wallet->name;
        $this->type      = $wallet->type;
        $this->balance   = (string) $wallet->balance;
        $this->color     = $wallet->color;
        $this->icon      = $wallet->icon;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            Wallet::where('user_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update($validated);
            $this->dispatch('notify', message: 'Wallet diperbarui', type: 'success');
        } else {
            Wallet::create([...$validated, 'user_id' => auth()->id()]);
            $this->dispatch('notify', message: 'Wallet ditambahkan', type: 'success');
        }

        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($id);
        $wallet->update(['is_active' => !$wallet->is_active]);
    }

    public function confirmDelete(int $id, string $name): void
    {
        $this->deleteTargetId = $id;
        $this->deleteTargetName = $name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($this->deleteTargetId);

        // Gunakan Database Transaction agar jika satu gagal, semua dibatalkan
        DB::transaction(function () use ($wallet) {
            // 1. Hapus semua transaksi terkait dompet ini
            $wallet->transactions()->delete();

            // 2. Hapus dompetnya
            $wallet->delete();
        });

        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->dispatch('notify', message: 'Dompet dan semua transaksinya berhasil dihapus', type: 'success');
    }

    public function render()
    {
        $wallets = Wallet::where('user_id', auth()->id())
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $totalBalance = $wallets->where('is_active', true)->sum('balance');

        return view('livewire.wallets.wallet-list', compact('wallets', 'totalBalance'))
            ->layout('layouts.app', ['title' => 'Dompet']);
    }
}
