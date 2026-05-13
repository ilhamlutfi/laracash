<?php

use App\Livewire\Dashboard\DashboardPage;
use App\Livewire\Transactions\TransactionList;
use App\Livewire\Archive\ArchiveList;
use App\Livewire\Wallets\WalletList;
use App\Livewire\SavingGoals\GoalList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/',            DashboardPage::class)->name('dashboard');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/archive',      ArchiveList::class)->name('archive');
    Route::get('/wallets',      WalletList::class)->name('wallets');
    Route::get('/goals',        GoalList::class)->name('goals');
});

Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';
