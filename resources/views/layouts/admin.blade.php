<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAF9]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Panel Admin Desa' }} — {{ config('app.name', 'SILAFA Banjarsari') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Logo_DesaBanjarsari.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#F8FAF9] min-h-screen flex flex-col selection:bg-[#1F6B4F] selection:text-white"
      x-data="{ sidebarOpen: false }">

    @php
        $sidebarPendingCount = \App\Models\User::where('role', \App\Enums\Role::Warga)
            ->where('status_akun', \App\Enums\StatusAkun::MenungguTinjauan)
            ->count();

        $sidebarActivePengaduanCount = \App\Models\Pengaduan::where('jenis', \App\Enums\JenisLaporan::Pengaduan)
            ->whereNotIn('status', [\App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::Ditolak])
            ->count();
    @endphp

    <div class="min-h-screen flex">
        {{-- ==================== BACKDROP MOBILE ==================== --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs z-40 lg:hidden"
             style="display: none;"></div>

        {{-- ==================== NAVBAR BAGIAN KIRI (SIDEBAR) ==================== --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:sticky top-0 left-0 z-50 h-screen w-72 bg-white border-r border-[#E2E8E4] flex flex-col justify-between transition-transform duration-200 ease-in-out shrink-0 overflow-y-auto">

            <div>
                {{-- Header Sidebar: Logo & Nama Layanan --}}
                <div class="h-16 px-6 border-b border-[#E2E8E4] flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                        <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-9 h-9 object-contain shrink-0">
                        <span class="font-bold text-gray-900 text-base tracking-tight group-hover:text-[#1F6B4F] transition-colors whitespace-nowrap">
                            SILAFA Banjarsari
                        </span>
                    </a>

                    {{-- Close Mobile Sidebar --}}
                    <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Navigasi Menu Sidebar Sesuai Skema Terbaru --}}
                <div class="p-4 space-y-6">
                    {{-- Grup 1: RINGKASAN --}}
                    <div>
                        <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block mb-2">
                            RINGKASAN
                        </span>
                        <nav class="space-y-1">
                            {{-- 1. Ringkasan Dashboard --}}
                            <a href="{{ route('admin.dashboard') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-[#1F6B4F] text-white shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="layout-grid" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Ringkasan Dashboard</span>
                                </div>
                            </a>
                        </nav>
                    </div>

                    {{-- Grup 2: PELAYANAN & VERIFIKASI --}}
                    <div>
                        <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block mb-2">
                            PELAYANAN & VERIFIKASI
                        </span>
                        <nav class="space-y-1">
                            {{-- 2. Tinjau Pendaftaran Warga --}}
                            <a href="{{ route('admin.pendaftaran.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.pendaftaran.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="user-check" class="w-4 h-4 {{ request()->routeIs('admin.pendaftaran.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Tinjau Pendaftaran</span>
                                </div>
                                @if ($sidebarPendingCount > 0)
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ request()->routeIs('admin.pendaftaran.*') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $sidebarPendingCount }} Baru
                                    </span>
                                @endif
                            </a>

                            {{-- 3. Data Kependudukan --}}
                            <a href="{{ route('admin.kependudukan.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.kependudukan.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="contact-round" class="w-4 h-4 {{ request()->routeIs('admin.kependudukan.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Data Kependudukan</span>
                                </div>
                            </a>

                            {{-- 4. Kelola Pengaduan --}}
                            <a href="{{ route('admin.pengaduan.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.pengaduan.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="headset" class="w-4 h-4 {{ request()->routeIs('admin.pengaduan.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Kelola Pengaduan</span>
                                </div>
                                @if ($sidebarActivePengaduanCount > 0)
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ request()->routeIs('admin.pengaduan.*') ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $sidebarActivePengaduanCount }} Aktif
                                    </span>
                                @endif
                            </a>

                            {{-- 5. Kelola Aspirasi --}}
                            <a href="{{ route('admin.aspirasi.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.aspirasi.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="message-square" class="w-4 h-4 {{ request()->routeIs('admin.aspirasi.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Kelola Aspirasi</span>
                                </div>
                            </a>

                            {{-- 6. Rekapitulasi & Analitik --}}
                            <a href="{{ route('admin.rekapitulasi.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.rekapitulasi.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4 {{ request()->routeIs('admin.rekapitulasi.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Rekapitulasi & Analitik</span>
                                </div>
                            </a>
                        </nav>
                    </div>

                    {{-- Grup 3: PENGATURAN & AKSES --}}
                    <div>
                        <span class="px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block mb-2">
                            PENGATURAN & AKSES
                        </span>
                        <nav class="space-y-1">
                            {{-- 7. Pengaturan Kategori --}}
                            <a href="{{ route('admin.kategori.index') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('admin.kategori.*') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="git-fork" class="w-4 h-4 {{ request()->routeIs('admin.kategori.*') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Pengaturan Kategori</span>
                                </div>
                            </a>

                            {{-- 8. Bantuan Reset Akun / Profil --}}
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ request()->routeIs('profile.edit') ? 'bg-[#1F6B4F] text-white font-semibold shadow-xs' : 'text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE]' }}">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="shield-check" class="w-4 h-4 {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-gray-400' }}"></i>
                                    <span>Bantuan Reset Akun</span>
                                </div>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Footer Sidebar: Profil Administrator & Tombol Keluar --}}
            <div class="p-4 border-t border-[#E2E8E4] bg-gray-50/70">
                <div class="flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-gray-100 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-[#1F6B4F] text-white font-bold flex items-center justify-center shrink-0 text-sm shadow-2xs">
                            {{ strtoupper(substr(Auth::user()->nama_lengkap ?? Auth::user()->name ?? 'A', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-xs text-gray-900 truncate block">
                                {{ Auth::user()->nama_lengkap ?? Auth::user()->name }}
                            </span>
                            <span class="text-[11px] text-[#1F6B4F] font-semibold block">
                                Operator Desa
                            </span>
                        </div>
                    </div>

                    {{-- Form Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun Anda?')" title="Keluar dari Akun" class="p-2 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-white transition cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ==================== AREA KONTEN UTAMA (KANAN) ==================== --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- TOP HEADER BAR --}}
            @php
                $pageNavbarTitle = match(true) {
                    request()->routeIs('admin.dashboard') => 'Ringkasan Dashboard',
                    request()->routeIs('admin.pendaftaran.*') => 'Tinjau Pendaftaran',
                    request()->routeIs('admin.kependudukan.*') => 'Data Kependudukan',
                    request()->routeIs('admin.pengaduan.*') => 'Kelola Pengaduan',
                    request()->routeIs('admin.aspirasi.*') => 'Kelola Aspirasi',
                    request()->routeIs('admin.rekapitulasi.*') => 'Rekapitulasi & Analitik',
                    request()->routeIs('admin.kategori.*') => 'Pengaturan Kategori',
                    request()->routeIs('profile.*') => 'Bantuan Reset Akun',
                    default => ($title ?? 'Panel Admin Desa'),
                };
            @endphp
            <header class="h-16 bg-white border-b border-[#E2E8E4] px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
                {{-- Left: Mobile Toggle & Page Title according to Navbar Menu --}}
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition" aria-label="Buka Menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <h1 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">
                        {{ $pageNavbarTitle }}
                    </h1>
                </div>

                {{-- Right: Notifikasi & Avatar Profil --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Notifikasi Lonceng --}}
                    <div class="relative">
                        <a href="{{ route('admin.pendaftaran.index') }}" class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition inline-block relative" title="Notifikasi Baru">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            @if ($sidebarPendingCount > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                            @endif
                        </a>
                    </div>

                    {{-- Mini Profile Avatar --}}
                    <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-full bg-gray-200 border border-gray-300 overflow-hidden shrink-0" title="Profil Saya">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama_lengkap ?? Auth::user()->name) }}&background=1F6B4F&color=fff&size=128"
                             alt="Avatar" class="w-full h-full object-cover">
                    </a>
                </div>
            </header>

            {{-- Flash Alert Global --}}
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                <x-flash-alert />
            </div>

            {{-- Optional Page Header Slot --}}
            @isset($header)
                <div class="px-4 sm:px-6 lg:px-8 pt-6 pb-2 max-w-7xl w-full mx-auto">
                    {{ $header }}
                </div>
            @endisset

            {{-- MAIN CONTENT --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 max-w-7xl w-full mx-auto space-y-6">
                {{ $slot }}
            </main>

            {{-- FOOTER ADMIN --}}
            <footer class="bg-white border-t border-[#E2E8E4] py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-gray-400">
                <p>&copy; {{ date('Y') }} SILAFA Banjarsari — Sistem Informasi Layanan Pengaduan dan Aspirasi Warga Desa Banjarsari • Panel Administrator.</p>
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
</body>
</html>