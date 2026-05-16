<div class="p-4 lg:p-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Arsip Bulanan</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Month List (Sisi Kiri) --}}
        <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm h-fit">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="font-semibold text-slate-900 text-sm uppercase tracking-wider">Pilih Bulan</h2>
            </div>
            <div class="divide-y divide-slate-50 max-h-[600px] overflow-y-auto">
                @forelse($months as $month)
                    <button wire:click="selectMonth({{ $month['year'] }}, {{ $month['month'] }})"
                        @class([
                            'w-full flex items-center justify-between px-5 py-4 text-left transition-all text-sm',
                            'bg-emerald-50 text-emerald-700 font-bold border-r-4 border-emerald-500' =>
                                $selectedYear == $month['year'] && $selectedMonth == $month['month'],
                            'text-slate-600 hover:bg-slate-50' => !(
                                $selectedYear == $month['year'] && $selectedMonth == $month['month']
                            ),
                        ])>
                        <span>{{ $month['label'] }}</span>
                        <span class="text-xs opacity-50">→</span>
                    </button>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data</div>
                @endforelse
            </div>
        </div>

        {{-- Month Detail (Sisi Kanan) --}}
        <div class="lg:col-span-2">
            @if ($selectedYear && $transactions->count() > 0)
                <div class="space-y-4">
                    {{-- Summary Cards dengan Rincian Statistik Transfer --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        {{-- Card Pemasukan --}}
                        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pemasukan</p>
                                <p class="text-xl font-black text-emerald-600">
                                    Rp {{ number_format($income, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-50 flex justify-between text-[11px] text-slate-400 font-medium">
                                <span>Rincian Transfer:</span>
                                <span class="text-emerald-500 font-bold">+Rp {{ number_format($transfer_in, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Card Pengeluaran --}}
                        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pengeluaran</p>
                                <p class="text-xl font-black text-red-500">
                                    Rp {{ number_format($expense, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-50 flex justify-between text-[11px] text-slate-400 font-medium">
                                <span>Rincian Transfer:</span>
                                <span class="text-red-400 font-bold">-Rp {{ number_format($transfer_out, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Card Saldo Bersih --}}
                        <div @class([
                            'rounded-2xl p-4 border shadow-sm flex flex-col justify-between',
                            'bg-emerald-50 border-emerald-100' => $balance >= 0,
                            'bg-red-50 border-red-100' => $balance < 0,
                        ])>
                            <div>
                                <p @class([
                                    'text-[10px] font-bold uppercase tracking-widest mb-1',
                                    'text-emerald-600' => $balance >= 0,
                                    'text-red-500' => $balance < 0,
                                ])>Saldo Bersih</p>
                                <p @class([
                                    'text-lg font-black',
                                    'text-emerald-700' => $balance >= 0,
                                    'text-red-600' => $balance < 0,
                                ])>
                                    {{ $balance >= 0 ? '+' : '' }}Rp {{ number_format($balance, 0, ',', '.') }}
                                </p>
                            </div>
                            <div @class([
                                'mt-2 pt-2 border-t text-[11px] font-medium flex justify-between',
                                'border-emerald-200/50 text-emerald-600/80' => $balance >= 0,
                                'border-red-200/50 text-red-600/80' => $balance < 0,
                            ])>
                                <span>Arus Transfer:</span>
                                <span class="font-bold">
                                    {{ ($transfer_in - $transfer_out) >= 0 ? '+' : '' }}Rp {{ number_format($transfer_in - $transfer_out, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                    </div>

                    {{-- Transaction List --}}
                    <div class="bg-white rounded-3xl border border-slate-100 divide-y divide-slate-50 overflow-hidden shadow-sm">
                        @foreach ($transactions as $tx)
                            @php
                                // Mengatur Warna dan Ikon dinamis di Arsip
                                $iconColor = '#6b7280';
                                $iconSymbol = '↑';
                                $isIncomeContext = false;

                                if ($tx->type === 'income') {
                                    $iconColor = '#10b981';
                                    $iconSymbol = '↓';
                                    $isIncomeContext = true;
                                } elseif ($tx->type === 'expense') {
                                    $iconColor = '#ef4444';
                                    $iconSymbol = '↑';
                                } elseif ($tx->type === 'transfer') {
                                    if ($tx->user_id !== auth()->id()) {
                                        // Transfer Masuk dari orang lain
                                        $iconColor = '#10b981';
                                        $iconSymbol = '↓';
                                        $isIncomeContext = true;
                                    } elseif ($tx->user_id === auth()->id() && !$tx->is_internal_transfer && $tx->target_user_id) {
                                        // Transfer Keluar ke orang lain
                                        $iconColor = '#ef4444';
                                        $iconSymbol = '↑';
                                    } else {
                                        // Transfer internal dompet sendiri
                                        $iconColor = $tx->category?->color ?? '#6b7280';
                                        $iconSymbol = '⇄';
                                    }
                                }
                            @endphp

                            <div wire:key="tx-{{ $tx->id }}" class="flex items-center gap-3 px-5 py-4">
                                {{-- Icon Container --}}
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-bold flex-shrink-0"
                                    style="background-color: {{ $iconColor }}20; color: {{ $iconColor }}">
                                    {{ $iconSymbol }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">
                                        {{ $tx->note ?: $tx->category?->name ?? 'Transaksi' }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <p class="text-[11px] text-slate-400 font-medium">
                                            {{ $tx->transaction_date->translatedFormat('d M Y') }} • {{ $tx->wallet->name }}
                                        </p>

                                        {{-- Badge Pembantu --}}
                                        @if($tx->type === 'transfer')
                                            @if($tx->user_id !== auth()->id())
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-600 font-semibold">Transfer Masuk</span>
                                            @elseif($tx->user_id === auth()->id() && !$tx->is_internal_transfer && $tx->target_user_id)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-red-50 text-red-500 font-semibold">Transfer Keluar</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Nominal --}}
                                <p @class([
                                    'text-sm font-black flex-shrink-0',
                                    'text-emerald-600' => $isIncomeContext,
                                    'text-red-500' => !$isIncomeContext,
                                ])>
                                    {{-- Cek paksa penambahan tanda + jika text accessor belum menyediakannya --}}
                                    @if($isIncomeContext && !str_contains($tx->formatted_amount, '+'))
                                        +{{ $tx->formatted_amount }}
                                    @else
                                        {{ $tx->formatted_amount }}
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8 pb-20 sm:pb-0">
                        {{ $transactions->links() }}
                    </div>
                </div>
            @else
                <div class="h-64 bg-white rounded-[2.5rem] border border-slate-100 flex items-center justify-center shadow-sm">
                    <div class="text-center">
                        <div class="text-4xl mb-4 text-slate-200">📅</div>
                        <p class="text-slate-500 font-bold">Pilih bulan yang tersedia</p>
                        <p class="text-slate-400 text-xs mt-1">Gunakan panel kiri untuk melihat detail arsip</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
