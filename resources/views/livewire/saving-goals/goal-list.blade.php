{{-- resources/views/livewire/saving-goals/goal-list.blade.php --}}
<div class="p-4 lg:p-8 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Target Tabungan</h1>
        <button wire:click="$set('showForm', true)"
            class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
            + Tambah
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <p class="text-xl font-bold text-brand-600">{{ $goals->where('status', 'active')->count() }}</p>
            <p class="text-xs text-slate-400 mt-1">Aktif</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <p class="text-xl font-bold text-emerald-600">{{ $summary['completed'] }}</p>
            <p class="text-xs text-slate-400 mt-1">Tercapai</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-100 text-center">
            <p class="text-lg font-bold text-slate-900">
                {{ $summary['total_target'] > 0 ? round($summary['total_saved'] / $summary['total_target'] * 100) : 0 }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">Total Progress</p>
        </div>
    </div>

    {{-- Goals List --}}
    <div class="space-y-4">
        @forelse($goals as $goal)
            <div @class([
                'bg-white rounded-3xl border border-slate-100 p-5 shadow-sm',
                'opacity-60' => $goal->status === 'cancelled',
            ])>
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        {{-- Icon Circle --}}
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                             style="background-color: {{ $goal->color }}20">🐷</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-slate-900">{{ $goal->name }}</p>
                                @if($goal->is_completed)
                                    <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full font-medium">✓ Tercapai</span>
                                @endif
                            </div>
                            @if($goal->description)
                                <p class="text-xs text-slate-400 mt-0.5">{{ $goal->description }}</p>
                            @endif
                        </div>
                    </div>
                    @if($goal->status === 'active')
                        <button wire:click="openEdit({{ $goal->id }})" class="text-slate-400 hover:text-brand-500 text-sm transition-colors">✏️</button>
                    @endif
                </div>

                {{-- Progress --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-slate-900">
                            Rp {{ number_format($goal->current_amount, 0, ',', '.') }}
                        </span>
                        <span class="text-slate-400">
                            dari Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 ease-out"
                             style="width: {{ $goal->progress_percentage }}%; background-color: {{ $goal->color }}">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold" style="color: {{ $goal->color }}">
                            {{ $goal->progress_percentage }}%
                        </span>
                        <span class="text-xs text-slate-400">
                            @if($goal->is_completed)
                                🎉 Goal tercapai!
                            @elseif($goal->days_remaining !== null)
                                {{ $goal->days_remaining }} hari lagi
                                @if($goal->days_remaining > 0 && $goal->remaining_amount > 0)
                                    · Rp {{ number_format($goal->remaining_amount / $goal->days_remaining, 0, ',', '.') }}/hari
                                @endif
                            @else
                                Sisa Rp {{ number_format($goal->remaining_amount, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Add Funds Button --}}
                @if($goal->status === 'active' && !$goal->is_completed)
                    <div class="mt-4 flex gap-2"
                         x-data="{ amount: '' }">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rp</span>
                            <input x-model="amount" type="number" min="0" step="10000"
                                   placeholder="Tambah dana..."
                                   class="w-full pl-8 text-sm rounded-xl border-slate-200 py-2" />
                        </div>
                        <button @click="if(amount > 0) { $wire.addFunds({{ $goal->id }}, parseFloat(amount)); amount = ''; }"
                            class="px-4 py-2 rounded-xl text-white text-sm font-medium transition-colors"
                            style="background-color: {{ $goal->color }}">
                            Tambah
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-16 text-center bg-white rounded-3xl border border-slate-100">
                <p class="text-5xl mb-3">🐷</p>
                <p class="text-slate-500 font-medium">Belum ada target tabungan</p>
                <p class="text-slate-400 text-sm mt-1">Mulai tentukan tujuan keuangan Anda</p>
            </div>
        @endforelse
    </div>
</div>
