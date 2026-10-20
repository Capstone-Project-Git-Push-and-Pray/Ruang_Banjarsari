<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Pengaduan Desa Banjarsari') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased text-slate-800 min-h-screen flex flex-col font-sans">
    <!-- Header / Navbar Sederhana -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-xl shadow-sm">
                    B
                </div>
                <div>
                    <span class="font-bold text-slate-900 block leading-tight text-sm sm:text-base">Desa Banjarsari</span>
                    <span class="text-xs text-slate-500 hidden sm:block">Layanan Pengaduan & Aspirasi Warga</span>
                </div>
            </div>

            <nav class="flex items-center space-x-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-emerald-600 transition">
                            Masuk
                        </a>
                        <a href="{{ Route::has('register') ? route('register') : '#daftar-info' }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                            Daftar
                        </a>
                    @endauth
                @endif
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-b from-emerald-50/50 via-white to-slate-50 pt-12 pb-16 sm:pt-20 sm:pb-24">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Flash Alert Component -->
                <x-flash-alert />

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-4">
                    Portal Resmi Warga Desa Banjarsari
                </span>

                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Sistem Layanan Pengaduan & Aspirasi Warga Desa Banjarsari
                </h1>

                <p class="mt-4 sm:mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                    Sampaikan keluhan fasilitas umum, jalan rusak, lingkungan, serta ide aspirasi pembangunan desa secara langsung dan transparan kepada Pemerintah Desa Banjarsari.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition">
                            Masuk ke Dashboard Saya
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow transition">
                            Masuk ke Akun
                        </a>
                        <a href="{{ Route::has('register') ? route('register') : '#daftar-info' }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-base font-medium text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl shadow-sm transition">
                            Daftar Warga Baru
                        </a>
                    @endauth
                </div>

                @if (!Route::has('register'))
                    <div id="daftar-info" class="mt-6 text-xs text-slate-500 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 inline-block">
                        <strong>Catatan Pendaftaran:</strong> Pendaftaran akun mandiri dengan validasi NIK otomatis akan segera aktif (Fitur PBI #6).
                    </div>
                @endif
            </div>
        </section>

        <!-- Informasi Alur Layanan -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 text-center mb-8">
                Alur Penanganan Laporan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-lg mb-4">
                        1
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-1">Diajukan</h3>
                    <p class="text-xs text-slate-600">Warga mengirimkan pengaduan atau aspirasi beserta lokasi dan foto bukti.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center text-lg mb-4">
                        2
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-1">Ditinjau</h3>
                    <p class="text-xs text-slate-600">Admin Desa memvalidasi laporan dan kelayakan informasi.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-lg mb-4">
                        3
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-1">Diproses</h3>
                    <p class="text-xs text-slate-600">Perangkat desa menindaklanjuti penanganan di lapangan.</p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-lg mb-4">
                        4
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-1">Selesai</h3>
                    <p class="text-xs text-slate-600">Laporan diselesaikan dengan dokumentasi hasil penanganan.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-1">
            <p class="font-semibold text-slate-700">Pemerintah Desa Banjarsari</p>
            <p>&copy; {{ date('Y') }} Sistem Informasi Layanan Pengaduan dan Aspirasi Warga Desa Banjarsari.</p>
        </div>
    </footer>
</body>
</html>
