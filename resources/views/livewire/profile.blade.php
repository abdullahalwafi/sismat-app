<div>
    <x-page-heading title="Profil Saya" description="Perbarui informasi akun dan password Anda." />
    <div class="detail-layout profile-layout">
        <section class="panel">
            <div class="panel-header">
                <h2>Informasi akun</h2>
            </div>
            <form wire:submit="save">
                <div class="form-grid">
                    <div class="form-field full-width"><label for="name">Nama</label><input id="name" wire:model="name" required>
                        @error('name')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-field full-width"><label for="profile-email">Email</label><input id="profile-email" type="email"
                            wire:model="email" required>
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-field full-width"><label for="phone">Telepon</label><input id="phone" wire:model="phone">
                        @error('phone')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <p>Role: <strong>{{ auth()->user()->role?->name ?? 'Belum ditentukan' }}</strong></p>
                </div>
                <div class="form-footer"><button type="submit" class="button button-primary" wire:loading.attr="disabled">Simpan
                        profil</button></div>
            </form>
        </section>
        <section class="panel">
            <div class="panel-header">
                <h2>Ubah password</h2>
            </div>
            <form wire:submit="updatePassword">
                <div class="form-grid">
                    <div class="form-field full-width"><label for="current-password">Password saat ini</label><input id="current-password"
                            type="password" wire:model="currentPassword" autocomplete="current-password" required>
                        @error('currentPassword')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-field full-width"><label for="new-password">Password baru</label><input id="new-password"
                            type="password" wire:model="password" autocomplete="new-password" minlength="8" required>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-field full-width"><label for="password-confirmation">Konfirmasi password</label><input
                            id="password-confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password"
                            required></div>
                </div>
                <div class="form-footer"><button type="submit" class="button button-primary" wire:loading.attr="disabled">Perbarui
                        password</button></div>
            </form>
        </section>
    </div>
</div>
