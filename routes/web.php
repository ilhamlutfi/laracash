<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Archive\ArchiveList;
use App\Livewire\Dashboard\DashboardPage;
use App\Livewire\SavingGoals\GoalList;
use App\Livewire\Transactions\TransactionList;
use App\Livewire\Wallets\WalletList;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// login google
Route::get('/auth/google/redirect', [GoogleLoginController::class, 'redirect'])->name('auth.google-redirect');
Route::get('/auth/google/callback', [GoogleLoginController::class, 'callback'])->name('auth.google-callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/',            DashboardPage::class)->name('dashboard');
    Route::get('/dashboard',   DashboardPage::class)->name('dashboard');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/archive',      ArchiveList::class)->name('archive');
    Route::get('/wallets',      WalletList::class)->name('wallets');
    Route::get('/goals',        GoalList::class)->name('goals');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
