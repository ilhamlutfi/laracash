<?php

namespace App\Livewire\SavingGoals;

use App\Models\SavingGoal;
use Livewire\Component;

class GoalList extends Component
{
    public bool $showForm  = false;
    public ?int $editingId = null;

    public string $name           = '';
    public string $description    = '';
    public string $target_amount  = '';
    public string $current_amount = '';
    public string $target_date    = '';
    public string $color          = '#10b981';
    public string $icon           = 'piggy-bank';

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'target_amount'  => 'required|numeric|min:1',
            'current_amount' => 'required|numeric|min:0',
            'target_date'    => 'nullable|date|after:today',
            'color'          => 'required|string|size:7',
            'icon'           => 'nullable|string',
        ];
    }

    public function addFunds(int $id, float $amount): void
    {
        $goal = SavingGoal::where('user_id', auth()->id())->findOrFail($id);
        $goal->increment('current_amount', $amount);

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->update(['status' => 'completed']);
            $this->dispatch('notify', message: '🎉 Target tercapai!', type: 'success');
        } else {
            $this->dispatch('notify', message: 'Dana berhasil ditambahkan', type: 'success');
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            SavingGoal::where('user_id', auth()->id())
                ->findOrFail($this->editingId)
                ->update($validated);
        } else {
            SavingGoal::create([...$validated, 'user_id' => auth()->id()]);
        }

        $this->showForm = false;
        $this->dispatch('notify', message: 'Target tabungan disimpan', type: 'success');
    }

    public function render()
    {
        $goals = SavingGoal::where('user_id', auth()->id())
            ->orderByRaw("FIELD(status, 'active', 'completed', 'cancelled')")
            ->orderBy('target_date')
            ->get();

        $summary = [
            'total_target'  => $goals->where('status', 'active')->sum('target_amount'),
            'total_saved'   => $goals->where('status', 'active')->sum('current_amount'),
            'completed'     => $goals->where('status', 'completed')->count(),
        ];

        return view('livewire.saving-goals.goal-list', compact('goals', 'summary'))
            ->layout('layouts.app', ['title' => 'Target Tabungan']);
    }
}
