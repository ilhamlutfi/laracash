<div>
    @if ($showModal)
        <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity"
                wire:click="$set('showModal', false)"></div>

            {{-- Modal --}}
            <div
                class="relative w-full max-w-lg mb-20 sm:mb-0 bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-y-auto max-h-[85vh] transition-all transform">

                {{-- Type Toggle Header (3 Opsi Bersandingan) --}}
                <div class="grid grid-cols-3 p-1.5 bg-slate-100 rounded-t-3xl">
                    <button wire:click="$set('type', 'expense')" @class([
                        'py-3 text-xs sm:text-sm font-bold rounded-2xl transition-all duration-200 flex items-center justify-center gap-1',
                        'bg-white text-red-600 shadow-sm' => $type === 'expense',
                        'text-slate-500 hover:text-slate-800 hover:bg-white/50' =>
                            $type !== 'expense',
                    ])>
                        <span>↑</span> Pengeluaran
                    </button>

                    <button wire:click="$set('type', 'income')" @class([
                        'py-3 text-xs sm:text-sm font-bold rounded-2xl transition-all duration-200 flex items-center justify-center gap-1',
                        'bg-white text-emerald-600 shadow-sm' => $type === 'income',
                        'text-slate-500 hover:text-slate-800 hover:bg-white/50' =>
                            $type !== 'income',
                    ])>
                        <span>↓</span> Pemasukan
                    </button>

                    <button wire:click="$set('type', 'transfer')" @class([
                        'py-3 text-xs sm:text-sm font-bold rounded-2xl transition-all duration-200 flex items-center justify-center gap-1',
                        'bg-white text-indigo-600 shadow-sm' => $type === 'transfer',
                        'text-slate-500 hover:text-slate-800 hover:bg-white/50' =>
                            $type !== 'transfer',
                    ])>
                        <span>⇄</span> Transfer
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                        {{ $editingId ? 'Edit Transaksi' : 'Transaksi Baru' }}
                    </h2>

                    {{-- Amount --}}
                    <div>
                        <label
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Nominal</label>
                        <div class="relative rounded-2xl shadow-sm">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">Rp</span>
                            <input wire:model="amount" type="number" min="0" step="1000" placeholder="0"
                                class="w-full pl-12 pr-4 text-2xl font-black rounded-2xl border-slate-200 py-3.5 focus:border-slate-300 focus:ring-4 focus:ring-slate-100 transition-all placeholder-slate-300" />
                        </div>
                        @error('amount')
                            <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">⚠️ {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alur Input Sesuai Tipe Transaksi --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- SISI KIRI: Dompet Asal --}}
    <div>
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">
            {{ $type === 'transfer' ? 'Dari Dompet' : 'Dompet' }}
        </label>
        <select wire:model.live="wallet_id" class="w-full rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-300 transition-all">
            <option value="" hidden>- pilih dompet -</option>
            @foreach ($wallets as $w)
                <option value="{{ $w->id }}">{{ $w->name }}</option>
            @endforeach
        </select>
        @error('wallet_id')
            <p class="text-xs text-red-500 mt-1.5">⚠️ {{ $message }}</p>
        @enderror
    </div>

    {{-- SISI KANAN JIKA BUKAN TRANSFER: Kategori --}}
    @if ($type !== 'transfer')
        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Kategori</label>
            <select wire:model="category_id" class="w-full rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-300 transition-all">
                <option value="" hidden>- pilih kategori -</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-xs text-red-500 mt-1.5">⚠️ {{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- SISI KANAN JIKA TRANSFER: Pilihan Penerima (Diri sendiri / Orang lain) --}}
    @if ($type === 'transfer')
        <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Penerima Transfer</label>
            <select wire:model.live="target_user_id" class="w-full rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-50 focus:border-indigo-300 transition-all">
                <option value="" hidden>- pilih penerima -</option>

                <option value="{{ auth()->id() }}" class="font-bold text-indigo-600">✨ Diri Sendiri (Antar Dompet)</option>

                <optgroup label="Pengguna Lain">
                    @foreach ($otherUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </optgroup>
            </select>
            @error('target_user_id')
                <p class="text-xs text-red-500 mt-1.5">⚠️ {{ $message }}</p>
            @enderror
        </div>
    @endif
</div>

{{-- BARIS SELEKSI DOMPET TUJUAN --}}
@if ($type === 'transfer')
    <div class="mt-4 transition-all duration-300">
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">
            {{ $target_user_id == auth()->id() ? 'Ke Dompet Saya yang Lain' : 'Transfer Ke Dompet Tujuan' }}
        </label>
        <select wire:model="to_wallet_id"
            @disabled(empty($target_user_id))
            @class([
                'w-full rounded-xl border-slate-200 py-2.5 text-sm font-medium transition-all',
                'bg-slate-100 text-slate-400 cursor-not-allowed' => empty($target_user_id),
                'bg-slate-50 text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-50 focus:border-indigo-300' => !empty($target_user_id)
            ])>

            @if (empty($target_user_id))
                <option value="">- Silakan pilih penerima terlebih dahulu -</option>
            @else
                <option value="" hidden>- pilih dompet tujuan -</option>
                @forelse ($targetWallets as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @empty
                    @if($target_user_id == auth()->id())
                        <option value="">⚠️ Anda tidak memiliki dompet cadangan lain</option>
                    @else
                        <option value="">⚠️ Pengguna ini tidak memiliki dompet aktif</option>
                    @endif
                @endforelse
            @endif
        </select>

        @error('to_wallet_id')
            <p class="text-xs text-red-500 mt-1.5">⚠️ {{ $message }}</p>
        @enderror
    </div>
@endif

                    {{-- Date --}}
                    <div>
                        <label
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Tanggal</label>
                        <input wire:model="transaction_date" type="date"
                            class="w-full rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-slate-100 transition-all" />
                        @error('transaction_date')
                            <p class="text-xs text-red-500 mt-1.5">⚠️ {{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Note --}}
                    <div>
                        <label
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Catatan
                            (opsional)</label>
                        <input wire:model="note" type="text" placeholder="Contoh: Beli kopi susu hangat..."
                            class="w-full rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-slate-100 transition-all placeholder-slate-400" />
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-3">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 py-3 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="save" @class([
                            'flex-1 py-3 rounded-xl text-white text-sm font-bold transition-all shadow-md',
                            'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200' =>
                                $type === 'income',
                            'bg-red-500 hover:bg-red-600 shadow-red-200' => $type === 'expense',
                            'bg-indigo-500 hover:bg-indigo-600 shadow-indigo-200' =>
                                $type === 'transfer',
                        ])>
                            Simpan {{ $type === 'transfer' ? 'Transfer' : '' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
