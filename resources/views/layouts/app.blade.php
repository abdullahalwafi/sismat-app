<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14243d">
    <title>{{ $title ?? 'Dashboard' }} · SISMAT</title>
    <link rel="stylesheet" href="{{ asset('vendor/admina/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/admina/style.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/admina/remixicon.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="{ sidebarOpen: false, toast: '', toastType: 'success', toastTimer: null }"
    @notify.window="toast = $event.detail.message; toastType = $event.detail.type; clearTimeout(toastTimer); toastTimer = setTimeout(() => toast = '', 6000)"
    @keydown.escape.window="sidebarOpen = false">
    <a class="skip-link" href="#main-content">Lewati navigasi</a>
    <div class="sidebar-overlay" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>
    <aside class="sismat-sidebar" :class="{ 'is-open': sidebarOpen }" aria-label="Navigasi utama">
        <a class="brand brand-ummi" href="{{ route('dashboard') }}" aria-label="Dashboard SISMAT RS UMMI">
            <img class="brand-logo" src="{{ asset('rs-ummi.png') }}" alt="RS UMMI — We Care &amp; Cure with Heart" width="2090" height="549">
        </a>
        <div class="workspace">
            <span class="workspace-icon">
                <i aria-hidden="true" class="ri-building-4-line"></i>
            </span>
            <div>RS UMMI<small>Manajemen aset rumah sakit</small>
            </div>
            <span class="workspace-dot"></span>
        </div>
        <nav>
            <div class="nav-caption">UTAMA</div>
            <x-nav-item route="dashboard" icon="ri-dashboard-line" label="Dashboard" />
            @foreach ([
        'MASTER DATA' => ['assets', 'categories', 'buildings', 'rooms', 'suppliers'],
        'OPERASIONAL' => ['maintenances', 'schedules', 'movements', 'documents', 'disposals'],
    ] as $group => $modules)
                <div class="nav-caption">{{ $group }}</div>
                @foreach ($modules as $module)
                    <x-nav-item :route="$module . '.index'" :icon="config('inventory.modules.' . $module . '.icon')" :label="config('inventory.modules.' . $module . '.title')" />
                @endforeach
            @endforeach
            <div class="nav-caption">MONITORING</div>
            <x-nav-item route="reports.index" icon="ri-bar-chart-box-line" label="Laporan Aset" />
            <x-nav-item route="activities.index" icon="ri-history-line" label="Aktivitas" />
            @can('module-view', 'users')
                <div class="nav-caption">PENGATURAN</div>
            @endcan
            <x-nav-item route="users.index" icon="ri-user-settings-line" label="Pengguna" />
            <x-nav-item route="roles.index" icon="ri-shield-user-line" label="Role & Hak Akses" />
        </nav>
        <div class="sidebar-note">
            <i aria-hidden="true" class="ri-database-2-line"></i>
            <div>Data terpusat<small>Tersimpan di database</small></div>
        </div>
        <a class="sidebar-footer" href="{{ route('profile') }}">
            <span class="avatar initials">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            <div>{{ auth()->user()->name }}<small>{{ auth()->user()->role?->name }}</small></div>
            <i aria-hidden="true" class="ri-shield-check-line"></i>
        </a>
    </aside>
    <div class="sismat-main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="icon-button mobile-menu" @click="sidebarOpen = !sidebarOpen" aria-label="Buka navigasi"
                    :aria-expanded="sidebarOpen">
                    <i aria-hidden="true" class="ri-menu-line"></i>
                </button>
                <span class="topbar-label">Workspace <i aria-hidden="true" class="ri-arrow-right-s-line"></i>
                    <strong>{{ $title ?? 'Dashboard' }}</strong>
                </span>
            </div>
            <div class="topbar-right">
                <span class="demo-chip">
                    <span></span> Database aktif</span>
                <span class="topbar-date">{{ now()->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</span>
                <a href="{{ route('profile') }}" class="avatar initials"
                    aria-label="Profil saya">{{ mb_substr(auth()->user()->name, 0, 1) }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="icon-button" aria-label="Keluar"
                        title="Keluar"><i aria-hidden="true" class="ri-logout-box-r-line"></i></button></form>
            </div>
        </header>
        <main id="main-content" class="page-content">
            @if (session('success'))
                <div class="flash-success" role="status">
                    <i aria-hidden="true" class="ri-checkbox-circle-line"></i>{{ session('success') }}
                </div>
            @endif
            {{ $slot }}
        </main>
        <footer class="page-footer">
            <span>© {{ now()->year }} SISMAT · Sistem Manajemen Aset Rumah
                Sakit</span>
            <span>Antarmuka Admina</span>
        </footer>
    </div>
    <div x-cloak x-show="toast" x-transition class="toast" :class="toastType === 'error' ? 'toast-error' : ''" role="status">
        <i aria-hidden="true" :class="toastType === 'error' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line'"></i>
        <span x-text="toast"></span>
        <button type="button" @click="toast = ''" aria-label="Tutup notifikasi">
            <i aria-hidden="true" class="ri-close-line"></i>
        </button>
    </div>
    @livewireScripts
</body>

</html>
