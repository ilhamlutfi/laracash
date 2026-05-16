{{-- resources/views/livewire/wallets/wallet-list.blade.php --}}
<div class="p-4 lg:p-8 space-y-5">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Dompet</h1>
        <button wire:click="openCreate"
            class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            + Tambah
        </button>
    </div>

    {{-- Total Balance Summary --}}
    <div class="rounded-3xl bg-gradient-to-br from-slate-800 to-slate-900 p-5 text-white">
        <p class="text-sm text-white/60 mb-1">Total Saldo Semua Dompet</p>
        <p class="text-3xl font-bold">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
    </div>

    {{-- Wallet Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($wallets as $wallet)
            <div @class([
                'rounded-3xl p-5 border relative overflow-hidden transition-all',
                'bg-white border-slate-100 shadow-sm' => $wallet->is_active,
                'bg-slate-50 border-slate-100 opacity-60' => !$wallet->is_active,
            ])>
                {{-- Color accent - Diperbaiki: Ditambahkan z-0 agar berada di lapisan paling belakang --}}
                <div class="absolute top-0 right-0 w-24 h-24 rounded-full -translate-y-1/2 translate-x-1/3 opacity-10 z-0"
                    style="background-color: {{ $wallet->color }}"></div>

                {{-- Content Container - Diperbaiki: Ditambahkan relative z-10 agar berada di atas dekorasi warna --}}
                <div class="relative z-10">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white text-lg font-bold"
                                style="background-color: {{ $wallet->color }}">
                                {{ substr($wallet->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $wallet->name }}</p>
                                <p class="text-xs text-slate-400">{{ $wallet->type_label }}</p>
                            </div>
                        </div>

                        {{-- Action Buttons - Diperbaiki: Ukuran tombol (Hitbox) diperbesar menjadi w-8 h-8 agar mudah ditekan --}}
                        <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-2xl border border-slate-100">
                            <button wire:click="openEdit({{ $wallet->id }})" title="Edit Dompet"
                                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white active:bg-white text-sm transition-colors">
                                ✏️
                            </button>
                            <button wire:click="toggleActive({{ $wallet->id }})" title="{{ $wallet->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white active:bg-white text-sm transition-colors">
                                {{ $wallet->is_active ? '👁️' : '🚫' }}
                            </button>
                            <button wire:click="confirmDelete({{ $wallet->id }}, '{{ addslashes($wallet->name) }}')" title="Hapus Dompet"
                                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white text-red-500 hover:text-red-600 active:bg-white text-sm transition-colors">
                                🗑️
                            </button>
                        </div>
                    </div>

                    <p class="text-xl font-bold text-slate-900">
                        Rp {{ number_format($wallet->balance, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">{{ $wallet->transactions_count }} transaksi</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Wallet Form Modal --}}
    @if ($showForm)
        <div class="fixed inset-0 z-[9999]" x-data x-init="$el.querySelector('[data-modal]').focus()">
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showForm', false)"></div>

            {{-- Modal Container --}}
            <div class="relative flex items-center justify-center min-h-screen p-4">
                <div data-modal tabindex="-1"
                    class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                    <h2 class="text-lg font-bold text-slate-900">
                        {{ $editingId ? 'Edit Dompet' : 'Tambah Dompet' }}
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Nama</label>
                            <input wire:model="name" type="text" placeholder="Nama dompet..."
                                class="mt-1 w-full rounded-xl border-slate-200 text-sm" />
                            @error('name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Tipe</label>
                            <select wire:model="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                                <option value="cash">💵 Tunai</option>
                                <option value="bank">🏦 Bank</option>
                                <option value="e-wallet">📱 E-Wallet</option>
                                <option value="savings">💵 Tabungan</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Saldo Awal</label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input wire:model="balance" type="number" min="0" step="1000"
                                    class="w-full pl-10 rounded-xl border-slate-200 text-sm" />
                            </div>
                            @error('balance')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Warna</label>
                            <input wire:model="color" type="color"
                                class="mt-1 h-10 w-full rounded-xl border-slate-200 cursor-pointer" />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button wire:click="$set('showForm', false)"
                            class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="save"
                            class="flex-1 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium transition-colors">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>
            <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 text-center"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="w-20 h-20 rounded-full bg-red-50 flex items-center justify-center text-4xl mx-auto mb-4">
                    🗑️
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Hapus Dompet?</h3>
                <p class="text-sm text-slate-500 mb-6 px-4">
                    Dompet <strong class="text-red-600">{{ $deleteTargetName }}</strong> akan dihapus secara permanen.
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button wire:click="delete"
                        class="flex-1 py-3 rounded-2xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold shadow-lg shadow-red-200 transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
