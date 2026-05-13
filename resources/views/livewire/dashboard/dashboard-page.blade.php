<div class="p-4 lg:p-8 space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500 ">Selamat datang,</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ auth()->user()->name }} 👋</h1>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="selectedMonth" class="text-sm rounded-xl border-slate-200">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ===== BALANCE CARD ===== --}}
    <div
        class="rounded-3xl bg-gradient-to-br from-slate-800 to-slate-900 p-5 text-white shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full translate-y-1/2 -translate-x-1/4"></div>
        <div class="relative">
            <p class="text-sm mb-1">Total Saldo</p>
            <p class="text-4xl font-bold tracking-tight">
                Rp {{ number_format($totalBalance, 0, ',', '.') }}
            </p>
            <p class="text-xs mt-2">Semua dompet aktif</p>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
            <div class="flex items-center gap-2 mb-2">
                <span
                    class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 text-sm">↓</span>
                <span class="text-xs font-medium text-slate-500">Pemasukan</span>
            </div>
            <p class="text-lg font-bold text-emerald-600">
                Rp {{ number_format($summary['income'] ?? 0, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
            <div class="flex items-center gap-2 mb-2">
                <span
                    class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center text-red-500 text-sm">↑</span>
                <span class="text-xs font-medium text-slate-500">Pengeluaran</span>
            </div>
            <p class="text-lg font-bold text-red-500">
                Rp {{ number_format($summary['expense'] ?? 0, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- ===== RECENT TRANSACTIONS ===== --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h2 class="font-semibold text-slate-900">Transaksi Terbaru</h2>
            <a href="{{ route('transactions') }}" class="text-xs text-brand-600 font-medium">Lihat Semua</a>
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($recentTx as $tx)
                <div class="flex items-center gap-3 px-5 py-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg flex-shrink-0"
                        style="background-color: {{ $tx['category']['color'] ?? '#6b7280' }}20; color: {{ $tx['category']['color'] ?? '#6b7280' }}">
                        {{ $tx['type'] === 'income' ? '↓' : '↑' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">
                            {{ $tx['note'] ?: $tx['category']['name'] ?? 'Transaksi' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ \Carbon\Carbon::parse($tx['transaction_date'])->translatedFormat('d M Y') }}
                            · {{ $tx['wallet']['name'] ?? '' }}
                        </p>
                    </div>
                    <p @class([
                        'text-sm font-bold flex-shrink-0',
                        'text-emerald-600' => $tx['type'] === 'income',
                        'text-red-500' => $tx['type'] === 'expense',
                    ])>
                        {{ $tx['type'] === 'income' ? '+' : '-' }}Rp {{ number_format($tx['amount'], 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <div class="px-5 py-10 text-center">
                    <p class="text-slate-400 text-sm">Belum ada transaksi</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== EXPENSE BY CATEGORY ===== --}}
    @if (!empty($categoryData))
        <div class="rounded-3xl shadow-sm border border-slate-100 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Pengeluaran per Kategori</h2>
            @php $maxCat = collect($categoryData)->max('total'); @endphp
            <div class="space-y-3">
                @foreach ($categoryData as $cat)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="font-medium text-slate-700">{{ $cat['name'] }}</span>
                            <span class="text-slate-500">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500"
                                style="width: {{ $maxCat > 0 ? ($cat['total'] / $maxCat) * 100 : 0 }}%; background-color: {{ $cat['color'] }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== FAB (Mobile) ===== --}}
    <div class="fixed bottom-20 right-4 lg:bottom-8 lg:right-8 z-40">
        <button @click="$dispatch('open-transaction-form')"
            class="w-14 h-14 rounded-2xl bg-gradient-to-br bg-emerald-500 hover:bg-emerald-600 text-white shadow-xl shadow-brand-500/40 flex items-center justify-center text-2xl hover:scale-105 active:scale-95 transition-transform">+</button>
    </div>

    {{-- Transaction Form Modal --}}
    @livewire('transactions.transaction-form')
</div>
