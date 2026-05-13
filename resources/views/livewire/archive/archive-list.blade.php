{{-- resources/views/livewire/archive/archive-list.blade.php --}}
<div class="p-4 lg:p-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Arsip Bulanan</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Month List --}}
        <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Pilih Bulan</h2>
            </div>
            <div class="divide-y divide-slate-50 max-h-[600px] overflow-y-auto">
                @forelse($months as $month)
                    <button wire:click="selectMonth({{ $month['year'] }}, {{ $month['month'] }})"
                        @class([
                            'w-full flex items-center justify-between px-5 py-3.5 text-left transition-colors text-sm',
                            'bg-brand-50 text-brand-700 font-semibold' =>
                                $selectedYear == $month['year'] && $selectedMonth == $month['month'],
                            'text-slate-700 hover:bg-slate-50' =>
                                !($selectedYear == $month['year'] && $selectedMonth == $month['month']),
                        ])>
                        <span>{{ $month['label'] }}</span>
                        <span class="text-xs text-slate-400">→</span>
                    </button>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data</div>
                @endforelse
            </div>
        </div>

        {{-- Month Detail --}}
        <div class="lg:col-span-2">
            @if($selectedYear && !empty($detail))
                <div class="space-y-4">
                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                            <p class="text-xs font-medium text-emerald-600 mb-1">Pemasukan</p>
                            <p class="text-base font-bold text-emerald-700">
                                Rp {{ number_format($detail['income'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-red-50 rounded-2xl p-4 border border-red-100">
                            <p class="text-xs font-medium text-red-500 mb-1">Pengeluaran</p>
                            <p class="text-base font-bold text-red-600">
                                Rp {{ number_format($detail['expense'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div @class([
                            'rounded-2xl p-4 border',
                            'bg-emerald-50 border-emerald-100' => $detail['balance'] >= 0,
                            'bg-red-50 border-red-100' => $detail['balance'] < 0,
                        ])>
                            <p @class([
                                'text-xs font-medium mb-1',
                                'text-emerald-600' => $detail['balance'] >= 0,
                                'text-red-500' => $detail['balance'] < 0,
                            ])>Saldo Bersih</p>
                            <p @class([
                                'text-base font-bold',
                                'text-emerald-700' => $detail['balance'] >= 0,
                                'text-red-600' => $detail['balance'] < 0,
                            ])>
                                {{ $detail['balance'] >= 0 ? '+' : '' }}Rp {{ number_format($detail['balance'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Transaction List --}}
                    <div class="bg-white rounded-3xl border border-slate-100 divide-y divide-slate-50 overflow-hidden">
                        @foreach($detail['transactions'] as $tx)
                            <div class="flex items-center gap-3 px-5 py-3.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0"
                                     style="background-color: {{ $tx->category?->color ?? '#6b7280' }}20; color: {{ $tx->category?->color ?? '#6b7280' }}">
                                    {{ $tx->type === 'income' ? '↑' : '↓' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate">
                                        {{ $tx->note ?: ($tx->category?->name ?? 'Transaksi') }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ $tx->transaction_date->format('d M') }} · {{ $tx->wallet->name }}
                                    </p>
                                </div>
                                <p @class([
                                    'text-sm font-bold flex-shrink-0',
                                    'text-emerald-600' => $tx->type === 'income',
                                    'text-red-500' => $tx->type === 'expense',
                                ])>{{ $tx->formatted_amount }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="h-64 bg-white rounded-3xl border border-slate-100 flex items-center justify-center">
                    <div class="text-center">
                        <p class="text-3xl mb-2">📅</p>
                        <p class="text-slate-400 text-sm">Pilih bulan untuk melihat detail</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
