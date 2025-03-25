<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'home')->name('home');

Volt::route('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function (): void {

    // Impersonations
    Route::post('/impersonate/{user}', [\App\Http\Controllers\ImpersonationController::class, 'store'])->name('impersonate.store')->middleware('can:impersonate');
    Route::delete('/impersonate/stop', [\App\Http\Controllers\ImpersonationController::class, 'destroy'])->name('impersonate.destroy');

    // Settings
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('settings/locale', 'settings.locale')->name('settings.locale');

    // Admin
    Route::prefix('admin')->as('admin.')->group(function (): void {
        Volt::route('/', 'admin.index')->middleware(['auth', 'verified'])->name('index')->middleware('can:access dashboard');
        Volt::route('/users', 'admin.users')->name('users.index')->middleware('can:view users');
        Volt::route('/users/create', 'admin.users.create-user')->name('users.create')->middleware('can:create users');
        Volt::route('/users/{user}', 'admin.users.view-user')->name('users.show')->middleware('can:view users');
        Volt::route('/users/{user}/edit', 'admin.users.edit-user')->name('users.edit')->middleware('can:update users');
        Volt::route('/roles', 'admin.roles')->name('roles.index')->middleware('can:view roles');
        Volt::route('/roles/create', 'admin.roles.create-role')->name('roles.create')->middleware('can:create roles');
        Volt::route('/roles/{role}/edit', 'admin.roles.edit-role')->name('roles.edit')->middleware('can:update roles');
        Volt::route('/permissions', 'admin.permissions')->name('permissions.index')->middleware('can:view permissions');
        Volt::route('/permissions/create', 'admin.permissions.create-permission')->name('permissions.create')->middleware('can:create permissions');
        Volt::route('/permissions/{permission}/edit', 'admin.permissions.edit-permission')->name('permissions.edit')->middleware('can:update permissions');
    });
});

Volt::route('/proteins', 'proteins')->name('proteins.index');

require __DIR__ . '/auth.php';
