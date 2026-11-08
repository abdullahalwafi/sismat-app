<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\SessionController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::get('/login', Livewire\Login::class)->middleware('guest')->name('login');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/', Livewire\Dashboard::class)->name('dashboard');

    Route::get('/assets', Livewire\Assets::class)->name('assets.index');
    Route::get('/assets/create', Livewire\Assets::class)->name('assets.create');
    Route::get('/assets/{record}/edit', Livewire\Assets::class)->whereNumber('record')->name('assets.edit');
    Route::get('/assets/{record}', Livewire\Assets::class)->whereNumber('record')->name('assets.show');

    Route::get('/categories', Livewire\Categories::class)->name('categories.index');
    Route::get('/categories/create', Livewire\Categories::class)->name('categories.create');
    Route::get('/categories/{record}/edit', Livewire\Categories::class)->whereNumber('record')->name('categories.edit');
    Route::get('/categories/{record}', Livewire\Categories::class)->whereNumber('record')->name('categories.show');

    Route::get('/buildings', Livewire\Buildings::class)->name('buildings.index');
    Route::get('/buildings/create', Livewire\Buildings::class)->name('buildings.create');
    Route::get('/buildings/{record}/edit', Livewire\Buildings::class)->whereNumber('record')->name('buildings.edit');
    Route::get('/buildings/{record}', Livewire\Buildings::class)->whereNumber('record')->name('buildings.show');

    Route::get('/rooms', Livewire\Rooms::class)->name('rooms.index');
    Route::get('/rooms/create', Livewire\Rooms::class)->name('rooms.create');
    Route::get('/rooms/{record}/edit', Livewire\Rooms::class)->whereNumber('record')->name('rooms.edit');
    Route::get('/rooms/{record}', Livewire\Rooms::class)->whereNumber('record')->name('rooms.show');

    Route::get('/suppliers', Livewire\Suppliers::class)->name('suppliers.index');
    Route::get('/suppliers/create', Livewire\Suppliers::class)->name('suppliers.create');
    Route::get('/suppliers/{record}/edit', Livewire\Suppliers::class)->whereNumber('record')->name('suppliers.edit');
    Route::get('/suppliers/{record}', Livewire\Suppliers::class)->whereNumber('record')->name('suppliers.show');

    Route::get('/maintenances', Livewire\Maintenances::class)->name('maintenances.index');
    Route::get('/maintenances/create', Livewire\Maintenances::class)->name('maintenances.create');
    Route::get('/maintenances/{record}/edit', Livewire\Maintenances::class)->whereNumber('record')->name('maintenances.edit');
    Route::get('/maintenances/{record}', Livewire\Maintenances::class)->whereNumber('record')->name('maintenances.show');

    Route::get('/schedules', Livewire\Schedules::class)->name('schedules.index');
    Route::get('/schedules/create', Livewire\Schedules::class)->name('schedules.create');
    Route::get('/schedules/{record}/edit', Livewire\Schedules::class)->whereNumber('record')->name('schedules.edit');
    Route::get('/schedules/{record}', Livewire\Schedules::class)->whereNumber('record')->name('schedules.show');

    Route::get('/movements', Livewire\Movements::class)->name('movements.index');
    Route::get('/movements/create', Livewire\Movements::class)->name('movements.create');
    Route::get('/movements/{record}/edit', Livewire\Movements::class)->whereNumber('record')->name('movements.edit');
    Route::get('/movements/{record}', Livewire\Movements::class)->whereNumber('record')->name('movements.show');

    Route::get('/documents', Livewire\Documents::class)->name('documents.index');
    Route::get('/documents/create', Livewire\Documents::class)->name('documents.create');
    Route::get('/documents/{record}/edit', Livewire\Documents::class)->whereNumber('record')->name('documents.edit');
    Route::get('/documents/{record}', Livewire\Documents::class)->whereNumber('record')->name('documents.show');

    Route::get('/disposals', Livewire\Disposals::class)->name('disposals.index');
    Route::get('/disposals/create', Livewire\Disposals::class)->name('disposals.create');
    Route::get('/disposals/{record}/edit', Livewire\Disposals::class)->whereNumber('record')->name('disposals.edit');
    Route::get('/disposals/{record}', Livewire\Disposals::class)->whereNumber('record')->name('disposals.show');

    Route::get('/reports', Livewire\Reports::class)->name('reports.index');
    Route::get('/activities', Livewire\Activities::class)->name('activities.index');

    Route::get('/users', Livewire\Users::class)->name('users.index');
    Route::get('/users/create', Livewire\Users::class)->name('users.create');
    Route::get('/users/{record}/edit', Livewire\Users::class)->whereNumber('record')->name('users.edit');
    Route::get('/users/{record}', Livewire\Users::class)->whereNumber('record')->name('users.show');

    Route::get('/roles', Livewire\Roles::class)->name('roles.index');
    Route::get('/roles/create', Livewire\Roles::class)->name('roles.create');
    Route::get('/roles/{record}/edit', Livewire\Roles::class)->whereNumber('record')->name('roles.edit');
    Route::get('/roles/{record}', Livewire\Roles::class)->whereNumber('record')->name('roles.show');

    Route::get('/profile', Livewire\Profile::class)->name('profile');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/assets/{asset}/photo', [FileController::class, 'photo'])->whereNumber('asset')->name('assets.photo');
    Route::get('/documents/{document}/download', [FileController::class, 'document'])->whereNumber('document')->name('documents.download');
    Route::get('/documents/{document}/preview', [FileController::class, 'preview'])->whereNumber('document')->name('documents.preview');
});
