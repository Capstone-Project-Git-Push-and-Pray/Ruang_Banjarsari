<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SILAFA Banjarsari — Sistem Informasi Layanan Pengaduan dan Aspirasi warga Desa Banjarsari. Sampaikan pengaduan, lacak status aduan, dan pantau kinerja pelayanan desa secara transparan.">
    <title>SILAFA Banjarsari — Portal Pengaduan & Aspirasi Desa</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Logo_DesaBanjarsari.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Lucide Icons CDN --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .animate-slide-in-right { animation: slideInRight 0.6s ease-out forwards; }
        .animate-count-up { animation: countUp 0.5s ease-out forwards; }
        .animate-pulse-subtle { animation: pulse-subtle 2s ease-in-out infinite; }

        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(24px); transition: all 0.7s ease-out; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Announcement bar marquee */
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee { animation: marquee 30s linear infinite; }

        /* Gradient text */
        .text-gradient {
            background: linear-gradient(135deg, #1F6B4F 0%, #174B39 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Step connector line */
        .step-connector::after {
            content: '';
            position: absolute;
            top: 24px;
            left: calc(50% + 24px);
            width: calc(100% - 48px);
            height: 2px;
            background: #E2E8E4;
        }
        .step-connector:last-child::after { display: none; }
    </style>
</head>
<body class="antialiased bg-canvas text-gray-800 font-sans min-h-screen flex flex-col">

    {{-- ==================== ANNOUNCEMENT BAR ==================== --}}
    <div class="bg-primary-dark text-white text-xs py-2 overflow-hidden relative">
        <div class="flex items-center animate-marquee whitespace-nowrap">
            <span class="mx-8 flex items-center gap-2">
                <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                Komitmen Pelayanan: Setiap aspirasi dan keluhan warga akan diverifikasi dan ditindaklanjuti dalam waktu maksimal 3x24 jam kerja.
            </span>
            <span class="mx-8 flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                Jam Layanan Kantor: Senin - Jumat, 08.00 - 16.00 WIB
            </span>
            <span class="mx-8 flex items-center gap-2">
                <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                Komitmen Pelayanan: Setiap aspirasi dan keluhan warga akan diverifikasi dan ditindaklanjuti dalam waktu maksimal 3x24 jam kerja.
            </span>
            <span class="mx-8 flex items-center gap-2">
                <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                Jam Layanan Kantor: Senin - Jumat, 08.00 - 16.00 WIB
            </span>
        </div>
    </div>

    {{-- ==================== NAVBAR ==================== --}}
    <header class="bg-white border-b border-border sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo Desa (Pojok Kiri) --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-10 h-10 object-contain">
                    <div>
                        <span class="font-display font-bold text-gray-900 text-sm sm:text-base leading-tight block group-hover:text-primary transition-colors">SILAFA Banjarsari</span>
                        <span class="text-[11px] text-gray-500 hidden sm:block">Sistem Informasi Layanan Pengaduan & Aspirasi</span>
                    </div>
                </a>

                {{-- Button Login / Dashboard (Pojok Kanan) --}}
                <div class="flex items-center gap-2">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl sm:rounded-lg shadow-sm transition-all duration-200">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl sm:rounded-lg shadow-sm transition-all duration-200">
                                <i data-lucide="user-circle" class="w-4 h-4"></i>
                                <span>Login</span>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">

        {{-- ==================== HERO SECTION ==================== --}}
        <section id="beranda" class="relative bg-white overflow-hidden">
            {{-- Subtle grid pattern --}}
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cdefs%3E%3Cpattern id=&quot;grid&quot; width=&quot;60&quot; height=&quot;60&quot; patternUnits=&quot;userSpaceOnUse&quot;%3E%3Cpath d=&quot;M 60 0 L 0 0 0 60&quot; fill=&quot;none&quot; stroke=&quot;%23166534&quot; stroke-width=&quot;1&quot;/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=&quot;100%25&quot; height=&quot;100%25&quot; fill=&quot;url(%23grid)&quot;/%3E%3C/svg%3E');"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
                <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                    {{-- Left Content --}}
                    <div class="space-y-6 animate-fade-in-up">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-light text-primary border border-primary/10">
                                <span class="w-1.5 h-1.5 bg-primary rounded-full mr-2 animate-pulse-subtle"></span>
                                SILAFA BANJARSARI • PORTAL RESMI
                            </span>
                        </div>

                        <h1 class="font-display text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold text-gray-900 leading-tight tracking-tight">
                            Suara Warga,<br>
                            <span class="text-gradient">Kemajuan Desa Banjarsari</span>
                        </h1>

                        <p class="text-gray-600 text-base lg:text-lg leading-relaxed max-w-xl">
                            SILAFA (Sistem Informasi Layanan Pengaduan & Aspirasi) Desa Banjarsari — sarana resmi penyampaian aspirasi dan aduan pelayanan publik, infrastruktur, dan kebutuhan masyarakat secara terstruktur, transparan, dan terpantau langsung.
                        </p>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap gap-3 pt-2">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                    Masuk ke Dashboard
                                </a>
                            @else
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                                    Daftar sebagai Warga
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-primary bg-white hover:bg-primary-light border border-primary/20 rounded-lg transition-all duration-300">
                                    <i data-lucide="log-in" class="w-4 h-4"></i>
                                    Masuk ke Layanan
                                </a>
                                <button class="inline-flex items-center gap-1 px-4 py-3 text-sm font-medium text-gray-500 hover:text-primary transition-colors">
                                    Cek Tiket Tanpa Akun
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </button>
                            @endauth
                        </div>

                        {{-- Quick Stats --}}
                        <div class="flex flex-wrap items-center gap-6 pt-4 border-t border-border mt-2">
                            <div>
                                <span class="font-display font-bold text-xl text-gray-900">1,482 <span class="text-primary text-sm">+</span></span>
                                <p class="text-xs text-gray-500">Total Aduan Warga</p>
                            </div>
                            <div class="w-px h-8 bg-border"></div>
                            <div>
                                <span class="font-display font-bold text-xl text-gray-900">94.8%</span>
                                <p class="text-xs text-gray-500">Tingkat Respon</p>
                            </div>
                            <div class="w-px h-8 bg-border"></div>
                            <div>
                                <span class="font-display font-bold text-xl text-gray-900">< 24 Jam</span>
                                <p class="text-xs text-gray-500">Rata-rata Respon</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right - Hero Image --}}
                    <div class="relative animate-slide-in-right delay-200">
                        <div class="relative rounded-2xl overflow-hidden shadow-xl border border-border">
                            <img src="{{ asset('images/hero_desa_banjarsari.jpg') }}" alt="Desa Banjarsari" class="w-full h-[340px] lg:h-[400px] object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
                            {{-- Floating badge --}}
                            <div class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-sm rounded-xl p-3 shadow-lg border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-light flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="award" class="w-5 h-5 text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-900">Penghargaan Desa Digital BPKP</p>
                                        <p class="text-[11px] text-gray-500">Pelayanan e-Tata Kelola PUBLIK Desa 2024</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Floating notification card --}}
                        <div class="absolute -top-4 -right-4 bg-white rounded-xl shadow-card-hover border border-border p-3 animate-fade-in delay-500 hidden lg:block">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900">Perbaikan Gotong Royong BPKP</p>
                                    <p class="text-[10px] text-gray-500">Diselesaikan 2 Jam Lalu oleh Tim PUBLIK Desa</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== KEUNGGULAN SISTEM ==================== --}}
        <section class="bg-canvas py-16 lg:py-20 border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 reveal">
                    <span class="text-xs font-semibold text-primary uppercase tracking-wider">KEUNGGULAN SISTEM</span>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mt-2">
                        Pelayanan Publik yang Adil, Modern, dan Cepat
                    </h2>
                    <p class="mt-3 text-gray-500 text-sm max-w-2xl mx-auto">
                        Pemerintah Desa Banjarsari menghadirkan sistem komunikasi dua arah untuk mempercepat tata usaha serta memberikan ruang aspirasi warga secara terbuka.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    {{-- Card 1 --}}
                    <div class="bg-white rounded-card border border-border p-6 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group reveal">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                            <i data-lucide="smartphone" class="w-6 h-6 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 text-lg mb-2">Kemudahan Akses Digital</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Setiap warga mudah melaporkan permasalahan tanpa perlu datang langsung ke kantor desa. Cukup lengkapi formulir dari smartphone maupun layanan web.
                        </p>
                    </div>

                    {{-- Card 2 --}}
                    <div class="bg-white rounded-card border border-border p-6 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group reveal delay-100">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                            <i data-lucide="search-check" class="w-6 h-6 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 text-lg mb-2">Pelacakan Status Transparan</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Setiap laporan memperoleh ID lacak agar warga dapat mengecek status penanganan terbaru, termasuk verifikasi langkah-langkah tindak lanjut secara real-time.
                        </p>
                    </div>

                    {{-- Card 3 --}}
                    <div class="bg-white rounded-card border border-border p-6 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group reveal delay-200">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-all duration-300">
                            <i data-lucide="bar-chart-3" class="w-6 h-6 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 text-lg mb-2">Partisipasi Pembangunan Nyata</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Aspirasi warga langsung dijadikan dasar prioritas perencanaan Pemerintah Desa tahun anggaran mendatang melalui data statistik layanan terukur.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== LANGKAH PENGADUAN ==================== --}}
        <section id="langkah" class="bg-white py-16 lg:py-20 border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-12 reveal">
                    <div>
                        <span class="text-xs font-semibold text-primary uppercase tracking-wider">PANDUAN ALUR</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mt-2">
                            4 Langkah Mudah Pengaduan Warga
                        </h2>
                    </div>
                    <p class="text-sm text-gray-500 max-w-md mt-3 sm:mt-0 sm:text-right">
                        Diperuntukkan sederhana untuk seluruh kalangan masyarakat Banjarsari, dimulai tanpa memerlukan registrasi akun yang rumit.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="relative bg-canvas rounded-card border border-border p-6 hover:border-primary/30 hover:shadow-card-hover transition-all duration-300 reveal">
                        <div class="w-10 h-10 rounded-lg bg-primary text-white font-display font-bold text-sm flex items-center justify-center mb-4">1</div>
                        <h3 class="font-display font-semibold text-gray-900 mb-2">Daftar & Verifikasi NIK</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Registrasi akun baru menggunakan KTP melalui verifikasi data kependudukan di Desa Banjarsari. Data yang sudah terverifikasi menjamin keamanan aduan.
                        </p>
                        <a href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-3 hover:underline">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            Mulai Pendaftaran
                        </a>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative bg-canvas rounded-card border border-border p-6 hover:border-primary/30 hover:shadow-card-hover transition-all duration-300 reveal delay-100">
                        <div class="w-10 h-10 rounded-lg bg-primary text-white font-display font-bold text-sm flex items-center justify-center mb-4">2</div>
                        <h3 class="font-display font-semibold text-gray-900 mb-2">Ajukan Pengaduan</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Tulis deskripsi permasalahan, sertakan foto lokasi sebagai bukti pendukung, dan pilih kategori terkait masalah. Formulir ini dapat digunakan kapan saja.
                        </p>
                        <a href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-3 hover:underline">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            Formulir Pengaduan
                        </a>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative bg-canvas rounded-card border border-border p-6 hover:border-primary/30 hover:shadow-card-hover transition-all duration-300 reveal delay-200">
                        <div class="w-10 h-10 rounded-lg bg-primary text-white font-display font-bold text-sm flex items-center justify-center mb-4">3</div>
                        <h3 class="font-display font-semibold text-gray-900 mb-2">Pantau Progress</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Login untuk melihap, verifikasi, dan monitor perkembangan atas laporan yang sudah diajukan. Setiap perubahan status akan ditampilkan secara real-time.
                        </p>
                        <a href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-3 hover:underline">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            Tracking & Monitoring
                        </a>
                    </div>

                    {{-- Step 4 --}}
                    <div class="relative bg-canvas rounded-card border border-border p-6 hover:border-primary/30 hover:shadow-card-hover transition-all duration-300 reveal delay-300">
                        <div class="w-10 h-10 rounded-lg bg-primary text-white font-display font-bold text-sm flex items-center justify-center mb-4">4</div>
                        <h3 class="font-display font-semibold text-gray-900 mb-2">Hasil & Konfirmasi</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Terima dokumentasi hasil penanganan dari petugas melalui notifikasi beserta bukti perbaikan. Warga dapat memberikan feedback atas penanganan yang diterima.
                        </p>
                        <a href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-3 hover:underline">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            Unduh Rekapitulasi
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== KATEGORI LAYANAN ==================== --}}
        <section id="layanan" class="bg-canvas py-16 lg:py-20 border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-12 reveal">
                    <div>
                        <span class="text-xs font-semibold text-primary uppercase tracking-wider">CAKUPAN LAYANAN</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mt-2">
                            Kategori Layanan Publik
                        </h2>
                    </div>
                    <a href="#" class="text-sm font-semibold text-primary hover:underline mt-3 sm:mt-0 flex items-center gap-1">
                        Lihat Panduan Detail
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Category 1 --}}
                    <div class="bg-white rounded-card border border-border p-5 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group cursor-pointer reveal">
                        <div class="w-11 h-11 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary transition-all duration-300">
                            <i data-lucide="lamp" class="w-5 h-5 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 mb-1.5">Infrastruktur Jalan & Lampu</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-3">
                            Jalan desa berlubang, penerangan jalan yang mati, saluran drainase tersumbat, perbaikan trotoar, dan pelebaran jalan perkampungan.
                        </p>
                        <span class="text-[11px] font-medium text-primary bg-primary-light px-2.5 py-1 rounded-full">412 Laporan Terserah</span>
                    </div>

                    {{-- Category 2 --}}
                    <div class="bg-white rounded-card border border-border p-5 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group cursor-pointer reveal delay-100">
                        <div class="w-11 h-11 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary transition-all duration-300">
                            <i data-lucide="leaf" class="w-5 h-5 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 mb-1.5">Kebersihan Lingkungan</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-3">
                            Tumpukan sampah, pengelolaan sampah ke TPS/TPA sesuai jadwal, penghijauan area publik, dan tata tertib kebersihan lingkungan desa.
                        </p>
                        <span class="text-[11px] font-medium text-primary bg-primary-light px-2.5 py-1 rounded-full">289 Laporan Terserah</span>
                    </div>

                    {{-- Category 3 --}}
                    <div class="bg-white rounded-card border border-border p-5 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group cursor-pointer reveal delay-200">
                        <div class="w-11 h-11 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary transition-all duration-300">
                            <i data-lucide="droplets" class="w-5 h-5 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 mb-1.5">Sarana Pertanian & Air</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-3">
                            Saluran irigasi tersumbat, ketersediaan air bersih melalui PAMSIMAS/PDAM warga, jaringan pipa air desa, serta manajemen sawah warga.
                        </p>
                        <span class="text-[11px] font-medium text-primary bg-primary-light px-2.5 py-1 rounded-full">387 Laporan Terserah</span>
                    </div>

                    {{-- Category 4 --}}
                    <div class="bg-white rounded-card border border-border p-5 shadow-card hover:shadow-card-hover hover:border-primary/30 transition-all duration-300 group cursor-pointer reveal delay-300">
                        <div class="w-11 h-11 rounded-xl bg-primary-light flex items-center justify-center mb-4 group-hover:bg-primary transition-all duration-300">
                            <i data-lucide="heart-handshake" class="w-5 h-5 text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="font-display font-semibold text-gray-900 mb-1.5">Ekonomi & Bantuan Sosial</h3>
                        <p class="text-xs text-gray-500 leading-relaxed mb-3">
                            Bantuan PKH, BLT, KIS, UMKM binaan, program padat karya desa, bantuan stimulan perumahan, dan sertifikasi tanah program PTSL.
                        </p>
                        <span class="text-[11px] font-medium text-primary bg-primary-light px-2.5 py-1 rounded-full">394 Laporan Terserah</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== STATISTIK KINERJA ==================== --}}
        <section id="statistik" class="bg-white py-16 lg:py-20 border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 reveal">
                    <span class="text-xs font-semibold text-primary uppercase tracking-wider">KETERBUKAAN INFORMASI</span>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mt-2">
                        Statistik Kinerja Penanganan Layanan
                    </h2>
                    <p class="mt-3 text-gray-500 text-sm max-w-2xl mx-auto">
                        Pemutakhiran data otomatis berdasarkan catatan sistem. Data pelayanan balai desa satu tahun terakhir.
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Stat 1 --}}
                    <div class="bg-canvas rounded-card border border-border p-6 text-center hover:shadow-card-hover transition-all duration-300 reveal">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="file-text" class="w-6 h-6 text-primary"></i>
                        </div>
                        <p class="font-display text-3xl font-bold text-gray-900" data-counter="1570">0</p>
                        <p class="text-xs text-gray-500 mt-1">Total Aduan Masuk</p>
                        <div class="flex items-center justify-center gap-1 mt-2">
                            <i data-lucide="trending-up" class="w-3 h-3 text-green-600"></i>
                            <span class="text-[11px] font-medium text-green-600">+12% dari tahun lalu</span>
                        </div>
                    </div>

                    {{-- Stat 2 --}}
                    <div class="bg-canvas rounded-card border border-border p-6 text-center hover:shadow-card-hover transition-all duration-300 reveal delay-100">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="check-circle-2" class="w-6 h-6 text-primary"></i>
                        </div>
                        <p class="font-display text-3xl font-bold text-gray-900"><span data-counter="94.4">0</span>%</p>
                        <p class="text-xs text-gray-500 mt-1">Tingkat Penyelesaian</p>
                        <div class="flex items-center justify-center gap-1 mt-2">
                            <i data-lucide="trending-up" class="w-3 h-3 text-green-600"></i>
                            <span class="text-[11px] font-medium text-green-600">Naik dari 90.1%</span>
                        </div>
                    </div>

                    {{-- Stat 3 --}}
                    <div class="bg-canvas rounded-card border border-border p-6 text-center hover:shadow-card-hover transition-all duration-300 reveal delay-200">
                        <div class="w-12 h-12 rounded-xl bg-primary-light flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="clock" class="w-6 h-6 text-primary"></i>
                        </div>
                        <p class="font-display text-3xl font-bold text-gray-900"><span data-counter="1.2">0</span> hari</p>
                        <p class="text-xs text-gray-500 mt-1">Rata-rata Respon</p>
                        <div class="flex items-center justify-center gap-1 mt-2">
                            <i data-lucide="trending-down" class="w-3 h-3 text-green-600"></i>
                            <span class="text-[11px] font-medium text-green-600">Lebih cepat dari bulan lalu</span>
                        </div>
                    </div>

                    {{-- Stat 4 - Donut Chart --}}
                    <div class="bg-canvas rounded-card border border-border p-6 text-center hover:shadow-card-hover transition-all duration-300 reveal delay-300">
                        <div class="relative w-28 h-28 mx-auto mb-3">
                            <svg viewBox="0 0 36 36" class="w-full h-full">
                                {{-- Background circle --}}
                                <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none" stroke="#E2E8E4" stroke-width="3"/>
                                {{-- Selesai (green) --}}
                                <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none" stroke="#1F6B4F" stroke-width="3"
                                    stroke-dasharray="82, 100" stroke-linecap="round"/>
                                {{-- Diproses (blue) --}}
                                <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none" stroke="#2563EB" stroke-width="3"
                                    stroke-dasharray="10, 100" stroke-dashoffset="-82"/>
                                {{-- Pending (yellow) --}}
                                <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none" stroke="#D97706" stroke-width="3"
                                    stroke-dasharray="5, 100" stroke-dashoffset="-92"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="font-display font-bold text-lg text-gray-900">1,482</span>
                                <span class="text-[10px] text-gray-500">Total</span>
                            </div>
                        </div>
                        <p class="text-xs font-semibold text-gray-900">Distribusi Status Aduan</p>
                        <div class="flex justify-center gap-3 mt-2">
                            <span class="flex items-center gap-1 text-[10px] text-gray-500">
                                <span class="w-2 h-2 rounded-full bg-primary inline-block"></span> Selesai (82%)
                            </span>
                            <span class="flex items-center gap-1 text-[10px] text-gray-500">
                                <span class="w-2 h-2 rounded-full bg-info inline-block"></span> Proses (10%)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== CTA SECTION ==================== --}}
        <section class="relative bg-primary py-16 lg:py-20 overflow-hidden">
            {{-- Background pattern --}}
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="cta-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                            <circle cx="20" cy="20" r="1.5" fill="white"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cta-pattern)"/>
                </svg>
            </div>

            {{-- Floating icon --}}
            <div class="absolute right-0 top-1/2 -translate-y-1/2 opacity-10 hidden lg:block">
                <i data-lucide="message-square-plus" class="w-64 h-64 text-white"></i>
            </div>

            <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="reveal">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white mb-4 border border-white/10">
                        Pengaduan Terbuka Untuk Warga
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-white leading-tight">
                        Melihat Permasalahan di Sekitar Dusun Anda? Laporkan Sekarang.
                    </h2>
                    <p class="mt-4 text-white/80 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
                        Setiap laporan yang masuk akan segera diverifikasi oleh tim sekretariat balai desa untuk ditinjau ke lapangan bersama RT, RW, dan perangkat desa terkait.
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-4 mt-8">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold text-primary bg-white hover:bg-gray-50 rounded-lg shadow-lg transition-all duration-300">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                Buat Pengaduan Baru
                            </a>
                        @else
                            <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold text-primary bg-white hover:bg-gray-50 rounded-lg shadow-lg transition-all duration-300">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                Mulai Buat Pengaduan
                            </a>
                        @endauth
                        <a href="#" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold text-white border border-white/30 hover:bg-white/10 rounded-lg transition-all duration-300">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            Pelajari Standar Layanan Minimal
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== INFO BAR ==================== --}}
        <section class="bg-primary-dark py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-3 gap-4 text-white">
                    <div class="flex items-center gap-3">
                        <i data-lucide="map-pin" class="w-5 h-5 text-primary-light flex-shrink-0"></i>
                        <div>
                            <p class="text-xs font-semibold">Kantor Balai Desa</p>
                            <p class="text-[11px] text-white/70">Jl. Raya Banjarsari No. 01, Kec. Banjarsari</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i data-lucide="clock" class="w-5 h-5 text-primary-light flex-shrink-0"></i>
                        <div>
                            <p class="text-xs font-semibold">Jam Layanan Langsung</p>
                            <p class="text-[11px] text-white/70">Senin - Jumat, 08.00 - 16.00 WIB</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i data-lucide="phone" class="w-5 h-5 text-primary-light flex-shrink-0"></i>
                        <div>
                            <p class="text-xs font-semibold">Saluran Bantuan Siaga</p>
                            <p class="text-[11px] text-white/70">WhatsApp: 0822-xxxx-xxxx (Jam Kerja)</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="bg-white border-t border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Column 1 - Brand --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-10 h-10 object-contain">
                        <div>
                            <span class="font-display font-bold text-gray-900 text-sm block">SILAFA Banjarsari</span>
                            <span class="text-[11px] text-gray-500 block">Pemerintah Desa Banjarsari</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed mb-4">
                        Sistem Informasi Pelayanan Pengaduan & Aspirasi warga berbasis data untuk kesejahteraan, akuntabilitas, dan tata kelola Desa Banjarsari yang lebih baik.
                    </p>
                </div>

                {{-- Column 2 - Tautan Cepat --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-sm mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-xs text-gray-500 hover:text-primary transition-colors">Website Pelayanan Minimal</a></li>
                        <li><a href="#" class="text-xs text-gray-500 hover:text-primary transition-colors">Panduan Pengaduan</a></li>
                    </ul>
                </div>

                {{-- Column 3 - Jam Layanan --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-sm mb-4">Jam Layanan Kantor</h4>
                    <ul class="space-y-2.5">
                        <li class="text-xs text-gray-500">Senin - Kamis: 08.00 - 16.00 WIB</li>
                        <li class="text-xs text-gray-500">Sabtu & Minggu & Hari Libur: Tutup</li>
                    </ul>
                </div>

                {{-- Column 4 - Kontak --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-sm mb-4">Kontak Sekretariat</h4>
                    <ul class="space-y-2.5">
                        <li class="text-xs text-gray-500">Kantor Desa Banjarsari</li>
                        <li class="text-xs text-gray-500">Telp: (0XXX) XXX-XXXX</li>
                        <li class="text-xs text-gray-500">Email: admin@banjarsari.desa.id</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="border-t border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-[11px] text-gray-400">
                    &copy; {{ date('Y') }} SILAFA Banjarsari — Pemerintah Desa Banjarsari. Seluruh Hak Cipta Dilindungi.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-[11px] text-gray-400 hover:text-primary transition-colors">Jurisdiksi</a>
                    <span class="text-gray-300">•</span>
                    <a href="#" class="text-[11px] text-gray-400 hover:text-primary transition-colors">Kebijakan Privasi</a>
                    <span class="text-gray-300">•</span>
                    <a href="#" class="text-[11px] text-gray-400 hover:text-primary transition-colors">Aksesibilitas</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ==================== SCRIPTS ==================== --}}
    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // Scroll reveal animation
            const revealElements = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Handle delay classes
                        const el = entry.target;
                        const delay = el.classList.contains('delay-100') ? 100 :
                                     el.classList.contains('delay-200') ? 200 :
                                     el.classList.contains('delay-300') ? 300 :
                                     el.classList.contains('delay-400') ? 400 :
                                     el.classList.contains('delay-500') ? 500 : 0;

                        setTimeout(() => {
                            el.classList.add('visible');
                        }, delay);

                        revealObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.1 });

            revealElements.forEach(el => revealObserver.observe(el));

            // Counter animation
            const counterElements = document.querySelectorAll('[data-counter]');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseFloat(el.dataset.counter);
                        const isDecimal = target % 1 !== 0;
                        const duration = 2000;
                        const start = performance.now();

                        function updateCounter(currentTime) {
                            const elapsed = currentTime - start;
                            const progress = Math.min(elapsed / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3);
                            const current = target * eased;

                            if (isDecimal) {
                                el.textContent = current.toFixed(1);
                            } else {
                                el.textContent = Math.floor(current).toLocaleString('id-ID');
                            }

                            if (progress < 1) {
                                requestAnimationFrame(updateCounter);
                            } else {
                                if (isDecimal) {
                                    el.textContent = target.toFixed(1);
                                } else {
                                    el.textContent = Math.floor(target).toLocaleString('id-ID');
                                }
                            }
                        }

                        requestAnimationFrame(updateCounter);
                        counterObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.5 });

            counterElements.forEach(el => counterObserver.observe(el));
        });
    </script>
</body>
</html>
