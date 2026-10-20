<?php

use App\Enums\Role;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Halaman Landing
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Router Dasbor Utama (Mengarahkan otomatis sesuai peran pengguna saat ini)
Route::get('/dashboard', function () {
    $user = request()->user();

    return match ($user->role) {
        Role::AdminDesa => redirect()->route('admin.dashboard'),
        Role::KepalaDesa => redirect()->route('kepala.dashboard'),
        default => redirect()->route('warga.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

// Grup Rute Warga
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/dashboard', function () {
        return view('warga.dashboard');
    })->name('dashboard');
});

// Grup Rute Admin Desa
Route::middleware(['auth', 'role:admin_desa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

// Grup Rute Kepala Desa
Route::middleware(['auth', 'role:kepala_desa'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/dashboard', function () {
        return view('kepala.dashboard');
    })->name('dashboard');
});

// Pengaturan Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
