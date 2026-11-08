<main class="auth-card">
    <div class="auth-brand"><span class="brand-mark"><i aria-hidden="true" class="ri-hospital-line"></i></span><strong>SISMAT</strong></div>
    <h1>Selamat datang kembali</h1>
    <p>Masuk untuk mengelola aset RS UMMI.</p>
    @if (session('error'))
        <div class="field-error" role="alert">{{ session('error') }}</div>
    @endif
    <form wire:submit="login" class="auth-form">
        <div class="form-field"><label for="email">Email</label><input id="email" type="email" wire:model="email"
                autocomplete="username" required autofocus>
            @error('email')
                <span class="field-error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-field"><label for="password">Password</label><input id="password" type="password" wire:model="password"
                autocomplete="current-password" required>
            @error('password')
                <span class="field-error" role="alert">{{ $message }}</span>
            @enderror
        </div>
        <label class="remember-label"><input type="checkbox" wire:model="remember"> Ingat saya</label>
        <button type="submit" class="button button-primary" wire:loading.attr="disabled">Masuk <i aria-hidden="true"
                class="ri-arrow-right-line"></i></button>
    </form>
    <small>Akses diberikan sesuai role dan status akun Anda.</small>
</main>
