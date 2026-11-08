<div class="form-grid">
    <div class="form-field" wire:key="field-roles-code">
        <label for="field-code">Nama role <span class="required">*</span>
        </label>
        <input id="field-code" type="text" wire:model="form.code" required maxlength="50">
        @error('form.code')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field full-width" wire:key="field-roles-description">
        <label for="field-description">Deskripsi</label>
        <textarea id="field-description" wire:model="form.description" rows="3" maxlength="2000"></textarea>
        @error('form.description')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-field" wire:key="field-roles-permissions">
        <label for="field-permissions">Hak akses</label>
        <div class="permissions-grid">
            @foreach ([
                'activities:view' => 'Aktivitas · Lihat',
                'assets:manage' => 'Data Aset · Kelola',
                'assets:view' => 'Data Aset · Lihat',
                'buildings:manage' => 'Gedung · Kelola',
                'buildings:view' => 'Gedung · Lihat',
                'categories:manage' => 'Kategori Aset · Kelola',
                'categories:view' => 'Kategori Aset · Lihat',
                'disposals:approve' => 'Penghapusan Aset · Setujui',
                'disposals:manage' => 'Penghapusan Aset · Kelola',
                'disposals:view' => 'Penghapusan Aset · Lihat',
                'documents:manage' => 'Dokumen Aset · Kelola',
                'documents:view' => 'Dokumen Aset · Lihat',
                'maintenances:manage' => 'Pemeliharaan · Kelola',
                'maintenances:view' => 'Pemeliharaan · Lihat',
                'movements:approve' => 'Mutasi Aset · Setujui',
                'movements:manage' => 'Mutasi Aset · Kelola',
                'movements:view' => 'Mutasi Aset · Lihat',
                'reports:view' => 'Laporan Aset · Lihat',
                'roles:manage' => 'Role & Hak Akses · Kelola',
                'roles:view' => 'Role & Hak Akses · Lihat',
                'rooms:manage' => 'Ruangan · Kelola',
                'rooms:view' => 'Ruangan · Lihat',
                'schedules:manage' => 'Jadwal Pemeliharaan · Kelola',
                'schedules:view' => 'Jadwal Pemeliharaan · Lihat',
                'suppliers:manage' => 'Supplier · Kelola',
                'suppliers:view' => 'Supplier · Lihat',
                'users:manage' => 'Pengguna · Kelola',
                'users:view' => 'Pengguna · Lihat',
            ] as $permission => $label)
                <label class="permission-option" wire:key="permission-{{ $permission }}">
                    <input type="checkbox" wire:model="form.permissions" value="{{ $permission }}"> {{ $label }}
                </label>
            @endforeach
        </div>
        @error('form.permissions')
            <span class="field-error" role="alert">{{ $message }}</span>
        @enderror
    </div>
</div>
