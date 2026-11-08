<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->email = mb_strtolower(trim($this->email));
        $this->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = 'login:'.mb_strtolower($this->email).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.');

            return;
        }
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'Email atau password salah, atau akun tidak aktif.');

            return;
        }
        RateLimiter::clear($key);
        session()->regenerate();
        ActivityLog::record('Login', 'Akses pengguna');
        $this->redirectIntended(route('dashboard'));
    }

    public function render(): View
    {
        return view('livewire.login')->layout('layouts.guest', ['title' => 'Masuk']);
    }
}
