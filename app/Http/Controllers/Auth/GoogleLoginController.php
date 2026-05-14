<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cari user berdasarkan email
            $user = User::where('email', $googleUser->email)->first();

            // Jika email tidak ada di sistem, tolak login
            if (!$user) {
                return redirect('/login')->with('status', 'Email tidak terdaftar di sistem.');
            }

            // Jika belum pernah login dengan Google, simpan google_id
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            }

            // Login user
            Auth::login($user);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('status', 'Login Google gagal.' . $e->getMessage());
        }
    }
}
