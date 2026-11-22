<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AspirasiController as AdminAspirasiController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataKependudukanController;
use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\PengaduanController as AdminPengaduanController;
use App\Http\Controllers\Admin\PeninjauanPendaftaranController;
use App\Http\Controllers\Admin\RekapitulasiController as AdminRekapitulasiController;
use App\Http\Controllers\Kepala\DashboardController as KepalaDashboardController;
use App\Http\Controllers\Kepala\PengaduanController as KepalaPengaduanController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Warga\DashboardController as WargaDashboardController;
use App\Http\Controllers\Warga\PengaduanController as WargaPengaduanController;
use App\Http\Controllers\Warga\StatusPendaftaranController;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

// Halaman Landing
Route::get('/', function () {
    return view('welcome');
})->name('landing');

// Router Dasbor Utama (Mengarahkan otomatis sesuai peran pengguna saat ini)
Route::get('/dashboard', function () {
    $user = request()->user();

    if ($user->isWarga() && ! $user->isAktif()) {
        return redirect()->route('pendaftaran.status');
    }

    return match ($user->role) {
        Role::AdminDesa => redirect()->route('admin.dashboard'),
        Role::KepalaDesa => redirect()->route('kepala.dashboard'),
        default => redirect()->route('warga.dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

// Akses Langsung Laporan Lewat URL dengan Otorisasi Policy (PBI #22)
Route::get('/pengaduan/{pengaduan}', function (Pengaduan $pengaduan) {
    Gate::authorize('view', $pengaduan);

    $user = request()->user();
    if ($user->isAdminDesa()) {
        return redirect()->route('admin.pengaduan.show', $pengaduan);
    }
    if ($user->isKepalaDesa()) {
        return redirect()->route('kepala.pengaduan.show', $pengaduan);
    }

    return redirect()->route('warga.pengaduan.show', $pengaduan);
})->middleware('auth')->name('pengaduan.show');

// Komentar pada Laporan (PBI #33, #34)
Route::middleware('auth')->group(function () {
    Route::post('/pengaduan/{pengaduan}/komentar', [KomentarController::class, 'store'])->name('komentar.store');
    Route::put('/komentar/{komentar}', [KomentarController::class, 'update'])->name('komentar.update');
    Route::delete('/komentar/{komentar}', [KomentarController::class, 'destroy'])->name('komentar.destroy');
});

// Grup Rute Warga
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/dashboard', [WargaDashboardController::class, 'index'])->name('dashboard');

    // Rute fitur khusus warga yang mewajibkan status akun aktif (pengajuan laporan terkunci jika belum aktif)
    Route::middleware(['akun.aktif'])->group(function () {
        Route::get('/pengaduan', [WargaPengaduanController::class, 'index'])->name('pengaduan.index');
        Route::get('/pengaduan/buat', [WargaPengaduanController::class, 'create'])->name('pengaduan.create');
        Route::post('/pengaduan', [WargaPengaduanController::class, 'store'])->name('pengaduan.store');
        Route::get('/pengaduan/{pengaduan}', [WargaPengaduanController::class, 'show'])->name('pengaduan.show');
    });
});

// Status Pendaftaran & Pengajuan Ulang Akun Warga
Route::middleware(['auth', 'role:warga'])->group(function () {
    Route::get('/pendaftaran/status', [StatusPendaftaranController::class, 'show'])
        ->name('pendaftaran.status');
    Route::put('/pendaftaran/ajukan-ulang', [StatusPendaftaranController::class, 'ajukanUlang'])
        ->name('pendaftaran.ajukan-ulang');
});

// Grup Rute Admin Desa
Route::middleware(['auth', 'role:admin_desa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Master Data Kependudukan
    Route::prefix('kependudukan')->name('kependudukan.')->group(function () {
        Route::get('/', [DataKependudukanController::class, 'index'])->name('index');
        Route::post('/', [DataKependudukanController::class, 'store'])->name('store');
        Route::put('/{kependudukan}', [DataKependudukanController::class, 'update'])->name('update');
        Route::delete('/{kependudukan}', [DataKependudukanController::class, 'destroy'])->name('destroy');
    });

    // Peninjauan Pendaftaran Warga (PBI #16, #17, #18)
    Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
        Route::get('/', [PeninjauanPendaftaranController::class, 'index'])->name('index');
        Route::get('/{user}', [PeninjauanPendaftaranController::class, 'show'])->name('show');
        Route::post('/{user}/setujui', [PeninjauanPendaftaranController::class, 'setujui'])->name('setujui');
        Route::post('/{user}/tolak', [PeninjauanPendaftaranController::class, 'tolak'])->name('tolak');
    });

    // Pengelolaan Pengaduan Desa (PBI #27, #28, #29)
    Route::prefix('pengaduan')->name('pengaduan.')->group(function () {
        Route::get('/', [AdminPengaduanController::class, 'index'])->name('index');
        Route::get('/{pengaduan}', [AdminPengaduanController::class, 'show'])->name('show');
        Route::patch('/{pengaduan}/status', [AdminPengaduanController::class, 'updateStatus'])->name('status.update');
    });

    // Pengelolaan Aspirasi Warga
    Route::prefix('aspirasi')->name('aspirasi.')->group(function () {
        Route::get('/', [AdminAspirasiController::class, 'index'])->name('index');
        Route::get('/{pengaduan}', [AdminAspirasiController::class, 'show'])->name('show');
    });

    // Manajemen Kategori (PBI #30)
    Route::prefix('kategori')->name('kategori.')->group(function () {
        Route::get('/', [AdminKategoriController::class, 'index'])->name('index');
        Route::post('/', [AdminKategoriController::class, 'store'])->name('store');
        Route::put('/{kategori}', [AdminKategoriController::class, 'update'])->name('update');
        Route::patch('/{kategori}/toggle', [AdminKategoriController::class, 'toggle'])->name('toggle');
        Route::delete('/{kategori}', [AdminKategoriController::class, 'destroy'])->name('destroy');
    });

    // Rekapitulasi Laporan (PBI #35)
    Route::get('/rekapitulasi', [AdminRekapitulasiController::class, 'index'])->name('rekapitulasi.index');
});

// Grup Rute Kepala Desa
Route::middleware(['auth', 'role:kepala_desa'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('dashboard');

    // Monitoring & Supervisi Pengaduan Desa
    Route::prefix('pengaduan')->name('pengaduan.')->group(function () {
        Route::get('/', [KepalaPengaduanController::class, 'index'])->name('index');
        Route::get('/{pengaduan}', [KepalaPengaduanController::class, 'show'])->name('show');
    });
});

// Pengaturan Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
