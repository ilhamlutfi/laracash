<div>
    @if($showModal)
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>

    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-8"
         x-transition:enter-end="opacity-100 translate-y-0">

        {{-- Type Toggle Header --}}
        <div class="flex">
            <button wire:click="$set('type', 'expense')"
                @class([
                    'flex-1 py-4 text-sm font-bold transition-colors',
                    'bg-red-500 text-white' => $type === 'expense',
                    'bg-slate-100 text-slate-500' => $type !== 'expense',
                ])>
                ↓ Pengeluaran
            </button>
            <button wire:click="$set('type', 'income')"
                @class([
                    'flex-1 py-4 text-sm font-bold transition-colors',
                    'bg-emerald-500 text-white' => $type === 'income',
                    'bg-slate-100 text-slate-500' => $type !== 'income',
                ])>
                ↑ Pemasukan
            </button>
        </div>

        <div class="p-6 space-y-4">
            <h2 class="text-lg font-bold text-slate-900">
                {{ $editingId ? 'Edit Transaksi' : 'Transaksi Baru' }}
            </h2>

            {{-- Amount --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Nominal</label>
                <div class="relative mt-1">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium">Rp</span>
                    <input wire:model="amount" type="number" min="0" step="1000"
                           placeholder="0"
                           class="w-full pl-12 text-xl font-bold rounded-2xl border-slate-200 py-3 focus:ring-brand-500" />
                </div>
                @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Wallet & Category --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium text-slate-700">Dompet</label>
                    <select wire:model="wallet_id"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                        <option value="">Pilih dompet</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('wallet_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Kategori</label>
                    <select wire:model="category_id"
                            class="mt-1 w-full rounded-xl border-slate-200 text-sm">
                        <option value="">Tanpa kategori</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Date --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Tanggal</label>
                <input wire:model="transaction_date" type="date"
                       class="mt-1 w-full rounded-xl border-slate-200 text-sm" />
                @error('transaction_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Note --}}
            <div>
                <label class="text-sm font-medium text-slate-700">Catatan (opsional)</label>
                <input wire:model="note" type="text" placeholder="Tambahkan catatan..."
                       class="mt-1 w-full rounded-xl border-slate-200 text-sm" />
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button wire:click="$set('showModal', false)"
                    class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button wire:click="save"
                    @class([
                        'flex-1 py-3 rounded-xl text-white text-sm font-bold transition-colors',
                        'bg-emerald-500 hover:bg-emerald-600' => $type === 'income',
                        'bg-red-500 hover:bg-red-600' => $type === 'expense',
                    ])>
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

</div>
