<div class="p-4 lg:p-8 space-y-5">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Transaksi</h1>
        <button @click="$dispatch('open-transaction-form')"
            class="bg-brand-500 hover:bg-brand-600 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            + Tambah
        </button>
    </div>

    <div x-data="{ open: false }" class="space-y-3">
        {{-- Tombol Toggle Filter --}}
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700"></h3>
            <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                <span x-text="open ? 'Sembunyikan Filter' : 'Tampilkan Filter'"></span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        {{-- Bagian yang di-Collapse --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="bg-white rounded-2xl border border-slate-100 p-4 space-y-4 shadow-sm" style="display: none;">
            {{-- inline style agar tidak kedip saat refresh --}}

            {{-- Baris Atas: Search & Date Range --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                {{-- Search --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Cari catatan transaksi..."
                        class="w-full pl-9 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                {{-- Date From --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">DR:</span>
                    <input wire:model.live="filterDateFrom" type="date"
                        class="w-full pl-10 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                {{-- Date To --}}
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">SD:</span>
                    <input wire:model.live="filterDateTo" type="date"
                        class="w-full pl-10 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            {{-- Baris Bawah: Dropdowns & Reset --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <select wire:model.live="filterType"
                    class="rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Tipe</option>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>

                <select wire:model.live="filterWallet"
                    class="rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Dompet</option>
                    @foreach ($wallets as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterCategory"
                    class="rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                <button wire:click="resetFilters"
                    class="text-xs font-medium text-red-500 bg-red-50 border border-red-100 rounded-xl px-3 hover:bg-red-100 transition-colors">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>



    {{-- Transaction List --}}
    <div class="bg-white rounded-3xl border border-slate-100 divide-y divide-slate-50 overflow-hidden">

        @forelse($transactions as $tx)
            <div wire:key="tx-{{ $tx['id'] }}"
                class="flex items-center gap-3 px-5 py-4 hover:bg-slate-50 transition-colors group">

                {{-- Category Icon --}}
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 text-base font-bold"
                    style="background-color: {{ $tx->category?->color ?? '#6b7280' }}20; color: {{ $tx->category?->color ?? '#6b7280' }}">
                    @if ($tx->type === 'income')
                        ↓
                    @elseif($tx->type === 'transfer')
                        ⇄
                    @else
                        ↑
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 truncate">
                        {{ $tx->note ?: $tx->category?->name ?? 'Transaksi' }}
                    </p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-slate-400">
                            {{ $tx->transaction_date->translatedFormat('d M Y') }}
                        </span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="text-xs text-slate-400">{{ $tx->wallet->name }}</span>
                        @if ($tx->category)
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                style="background-color: {{ $tx->category->color }}20; color: {{ $tx->category->color }}">
                                {{ $tx->category->name }}
                            </span>
                            {{-- Jika transfer, tampilkan badge khusus --}}
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium text-slate-400 bg-slate-100">
                                Transfer
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Amount --}}
                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                    <p @class([
                        'text-sm font-bold',
                        'text-emerald-600' => $tx->type === 'income',
                        'text-red-500' => $tx->type === 'expense',
                    ])>
                        {{ $tx->formatted_amount }}
                    </p>
                    {{-- Action buttons (shown on hover) --}}
                    <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-2xl border border-slate-100">
                        <button @click="$dispatch('edit-transaction', { id: {{ $tx->id }} })"
                            class="text-xs text-slate-400 hover:text-brand-500 transition-colors">✏️</button>
                        <button wire:click="confirmDelete({{ $tx->id }}, '{{ addslashes($tx->note) }}')"
                            class="text-xs text-slate-400 hover:text-red-500 transition-colors">🗑️</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <p class="text-4xl mb-3">💸</p>
                <p class="text-slate-500 font-medium">Belum ada transaksi</p>
                <p class="text-slate-400 text-sm mt-1">Mulai catat transaksi pertama Anda</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8 pb-20 sm:pb-0"> {{-- pb-20 khusus mobile agar menjauh dari bottom nav --}}
        {{ $transactions->links() }}
    </div>

    {{-- ===== DELETE CONFIRMATION MODAL ===== --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)">
            </div>
            <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 text-center"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-3xl mx-auto mb-4">
                    🗑️</div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Hapus Transaksi?</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Transaksi <strong class="text-slate-700">{{ $deleteTargetNote }}</strong> akan dihapus secara
                    permanen dan saldo dompet akan dikembalikan.
                </p>
                <div class="flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button wire:click="delete"
                        class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Transaction Form & Edit Modal --}}
    @livewire('transactions.transaction-form')
</div>
