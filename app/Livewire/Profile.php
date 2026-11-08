<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->phone = Auth::user()->phone ?? '';
    }

    public function save(): void
    {
        $this->email = mb_strtolower(trim($this->email));
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        $user = Auth::user();
        $before = $user->getAttributes();
        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }
        $user->fill($validated)->save();
        ActivityLog::record('Mengubah profil', 'Pengguna', $user, $before);
        $this->dispatch('notify', message: 'Profil berhasil diperbarui.', type: 'success');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:200', 'confirmed'],
        ]);
        $user = Auth::user();
        $user->update(['password' => $validated['password']]);
        session()->regenerate();
        ActivityLog::record('Mengubah password', 'Pengguna', $user);
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->dispatch('notify', message: 'Password berhasil diperbarui.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.profile')->layout('layouts.app', ['title' => 'Profil Saya']);
    }
}
