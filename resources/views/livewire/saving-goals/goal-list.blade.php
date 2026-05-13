{{-- resources/views/livewire/saving-goals/goal-list.blade.php --}}
<div class="p-4 lg:p-8 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Target Tabungan</h1>
        <button wire:click="$set('showForm', true)"
            class="bg-brand-500 hover:bg-brand-600 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
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
                {{ $summary['total_target'] > 0 ? round(($summary['total_saved'] / $summary['total_target']) * 100) : 0 }}%
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
                            style="background-color: {{ $goal->color }}20">💸</div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-slate-900">{{ $goal->name }}</p>
                                @if ($goal->is_completed)
                                    <span
                                        class="text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full font-medium">✓
                                        Tercapai</span>
                                @endif
                            </div>
                            @if ($goal->description)
                                <p class="text-xs text-slate-400 mt-0.5">{{ $goal->description }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($goal->status === 'active')
                        <div class="flex items-center gap-2">

                            <button wire:click="openEdit({{ $goal->id }})"
                                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-emerald-100 text-slate-500 hover:text-emerald-600 transition-colors">
                                ✏️
                            </button>

                            <button wire:click="delete({{ $goal->id }})"
                                wire:confirm="Yakin ingin menghapus target ini?"
                                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-red-100 text-slate-500 hover:text-red-500 transition-colors">
                                🗑️
                            </button>

                        </div>
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
                            @if ($goal->is_completed)
                                🎉 Goal tercapai!
                            @elseif($goal->days_remaining !== null)
                                {{ $goal->days_remaining }} hari lagi
                                @if ($goal->days_remaining > 0 && $goal->remaining_amount > 0)
                                    · Rp
                                    {{ number_format($goal->remaining_amount / $goal->days_remaining, 0, ',', '.') }}/hari
                                @endif
                            @else
                                Sisa Rp {{ number_format($goal->remaining_amount, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Add Funds Button --}}
                @if ($goal->status === 'active' && !$goal->is_completed)
                    <div class="mt-4 flex gap-2" x-data="{ amount: '' }">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">Rp</span>
                            <input x-model="amount" type="number" min="0" step="10000"
                                placeholder="Tambah dana..."
                                class="w-full pl-8 text-sm rounded-xl border-slate-200 py-2" />
                        </div>
                        <button
                            @click="if(amount > 0) { $wire.addFunds({{ $goal->id }}, parseFloat(amount)); amount = ''; }"
                            class="px-4 py-2 rounded-xl text-white text-sm font-medium transition-colors"
                            style="background-color: {{ $goal->color }}">
                            Tambah
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-16 text-center bg-white rounded-3xl border border-slate-100">
                <p class="text-5xl mb-3">💸</p>
                <p class="text-slate-500 font-medium">Belum ada target tabungan</p>
                <p class="text-slate-400 text-sm mt-1">Mulai tentukan tujuan keuangan Anda</p>
            </div>
        @endforelse
    </div>

    {{-- Goal Form Modal --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[85vh] overflow-y-auto">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $editingId ? 'Edit Target' : 'Tambah Target' }}
                        </h2>
                        <p class="text-sm text-slate-400 mt-1">
                            Kelola tujuan keuangan Anda
                        </p>
                    </div>

                    <button wire:click="$set('showForm', false)"
                        class="w-10 h-10 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-colors">
                        ✕
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-6 space-y-5">

                    {{-- Nama --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Target
                        </label>

                        <input wire:model.defer="name" type="text" placeholder="Contoh: Beli Mobil"
                            class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">

                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Target Dana --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Target Dana
                            </label>

                            <input wire:model.defer="target_amount" type="number" placeholder="10000000"
                                class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">

                            @error('target_amount')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Dana Saat Ini --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Dana Saat Ini
                            </label>

                            <input wire:model.defer="current_amount" type="number" placeholder="0"
                                class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">

                            @error('current_amount')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Target Date --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Target Tanggal
                        </label>

                        <input wire:model.defer="target_date" type="date"
                            class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500">

                        @error('target_date')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Deskripsi
                        </label>

                        <textarea wire:model.defer="description" rows="3" placeholder="Opsional..."
                            class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"></textarea>

                        @error('description')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-5 bg-slate-50 border-t border-slate-100">

                    <button wire:click="resetForm(); $set('showForm', false)"
                        class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                        Batal
                    </button>

                    <button wire:click="save"
                        class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold shadow-lg shadow-emerald-500/20 transition-all">
                        Simpan
                    </button>

                </div>

            </div>

        </div>
    @endif
</div>
