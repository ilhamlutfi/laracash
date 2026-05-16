<div class="p-4 lg:p-8 space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Selamat datang,</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ auth()->user()->name }} 👋</h1>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="selectedMonth" class="text-sm rounded-xl border-slate-200 focus:ring-slate-500 focus:border-slate-500">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ===== BALANCE CARD ===== --}}
    <div class="rounded-3xl bg-gradient-to-br from-slate-800 to-slate-900 p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 backdrop-blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4 backdrop-blur-3xl"></div>
        <div class="relative z-10">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Total Saldo Anda</p>
            <p class="text-4xl font-black tracking-tight">
                Rp {{ number_format($totalBalance, 0, ',', '.') }}
            </p>
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-400 bg-white/10 w-fit px-2.5 py-1 rounded-full backdrop-blur-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Semua dompet aktif
            </div>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm font-bold">↓</span>
                <span class="text-xs font-semibold text-slate-500">Pemasukan</span>
            </div>
            <p class="text-md font-black text-emerald-600 tracking-tight">
                Rp {{ number_format($summary['income'] ?? 0, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center text-red-500 text-sm font-bold">↑</span>
                <span class="text-xs font-semibold text-slate-500">Pengeluaran</span>
            </div>
            <p class="text-md font-black text-red-500 tracking-tight">
                Rp {{ number_format($summary['expense'] ?? 0, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- ===== RECENT TRANSACTIONS ===== --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
        <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-slate-50">
            <h2 class="text-slate-800 tracking-tight">Transaksi Terbaru</h2>
            <a href="{{ route('transactions') }}" class="text-xs text-slate-500">Lihat Semua</a>
        </div>

        <div class="divide-y divide-slate-50">
            @php $myWallets = \App\Models\Wallet::where('user_id', auth()->id())->pluck('id')->toArray(); @endphp

            @forelse($recentTx as $tx)
                @php
                    // Logika Visualisasi Tipe Mutasi Dinamis untuk Transfer
                    $txClass = 'text-slate-700';
                    $txSign = '';

                    if ($tx->type === 'income') {
                        $txClass = 'text-emerald-600';
                        $txSign = '+';
                    } elseif ($tx->type === 'expense') {
                        $txClass = 'text-red-500';
                        $txSign = '-';
                    } elseif ($tx->type === 'transfer') {
                        if (in_array($tx->wallet_id, $myWallets) && in_array($tx->to_wallet_id, $myWallets)) {
                            $txClass = 'text-indigo-600'; // Antar dompet sendiri (Netral)
                            $txSign = '⇄ ';
                        } elseif (in_array($tx->wallet_id, $myWallets)) {
                            $txClass = 'text-red-500'; // Kirim ke luar (Pengeluaran)
                            $txSign = '-';
                        } else {
                            $txClass = 'text-emerald-600'; // Terima dari luar (Pemasukan)
                            $txSign = '+';
                        }
                    }
                @endphp

                <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50/50 transition-colors">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-base font-bold flex-shrink-0"
                        style="background-color: {{ $tx->type === 'transfer' ? '#6366f1' : ($tx->category->color ?? '#6b7280') }}15;
                               color: {{ $tx->type === 'transfer' ? '#6366f1' : ($tx->category->color ?? '#6b7280') }}">
                        @if ($tx->type === 'income') ↓
                        @elseif($tx->type === 'transfer') ⇄
                        @else ↑
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">
                            {{ $tx->note ?: ($tx->type === 'transfer' ? 'Transfer Saldo' : $tx->category->name ?? 'Transaksi') }}
                        </p>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">
                            {{ \Carbon\Carbon::parse($tx->transaction_date)->translatedFormat('d M Y') }}
                            · <span class="text-slate-500">{{ $tx->wallet->name ?? 'Wallet' }}</span>
                            @if($tx->type === 'transfer')
                                → <span class="text-slate-500">{{ \App\Models\Wallet::find($tx->to_wallet_id)?->name ?? 'Tujuan' }}</span>
                            @endif
                        </p>
                    </div>

                    <p class="text-sm font-extrabold flex-shrink-0 {{ $txClass }}">
                        {{ $txSign }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </p>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <p class="text-slate-400 text-sm font-medium">Belum ada catatan transaksi pada bulan ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== EXPENSE BY CATEGORY ===== --}}
    @if (!empty($categoryData))
        <div class="rounded-3xl bg-white shadow-sm border border-slate-100 p-5">
            <h2 class=" text-slate-800 tracking-tight mb-4">Kategori Pengeluaran</h2>
            @php $maxCat = collect($categoryData)->max('total'); @endphp
            <div class="space-y-4">
                @foreach ($categoryData as $cat)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="font-semibold text-slate-700 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                {{ $cat['name'] }}
                            </span>
                            <span class="text-slate-400">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
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

    {{-- ===== FAB (Mobile Button) ===== --}}
    <div class="fixed bottom-20 right-4 lg:bottom-8 lg:right-8 z-40">
        <button @click="$dispatch('open-transaction-form')"
            class="w-14 h-14 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-200 flex items-center justify-center text-3xl font-light hover:scale-110 active:scale-95 transition-all duration-200">
            +
        </button>
    </div>

    {{-- Form Modal Include --}}
    @livewire('transactions.transaction-form')
</div>
