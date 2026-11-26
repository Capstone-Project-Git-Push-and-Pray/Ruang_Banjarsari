<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk ke Akun Anda — SILAFA Banjarsari</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Logo_DesaBanjarsari.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#F8FAF9] text-gray-800 font-sans min-h-screen flex flex-col justify-between relative selection:bg-[#1F6B4F] selection:text-white">

    {{-- Ambient Background Decorative Glows --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[550px] bg-gradient-to-b from-[#1F6B4F]/10 via-[#1F6B4F]/[0.02] to-transparent blur-3xl opacity-80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#1F6B4F_1px,transparent_1px)] [background-size:24px_24px] opacity-[0.035]"></div>
    </div>

    {{-- ==================== NAVBAR ATAS ==================== --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-[#E2E8E4] sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Left: Logo & Title --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-10 h-10 object-contain">
                    <div>
                        <span class="font-display font-bold text-gray-900 text-sm sm:text-base leading-tight block group-hover:text-[#1F6B4F] transition-colors">
                            SILAFA Banjarsari
                        </span>
                        <span class="text-[11px] text-gray-500 hidden sm:block">
                            Pemerintah Desa Banjarsari
                        </span>
                    </div>
                </a>

                {{-- Right: Actions (Beranda & Daftar Warga) --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE] rounded-lg transition-colors">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Beranda</span>
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-semibold text-[#1F6B4F] bg-[#EAF5EE] hover:bg-[#d5ebe0] rounded-lg border border-[#1F6B4F]/20 transition-all duration-200">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Daftar Warga</span>
                    </a>
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Emblem" class="w-7 h-7 object-contain opacity-90 hidden sm:block">
                </div>
            </div>
        </div>
    </header>

    {{-- ==================== MAIN AUTH CONTENT ==================== --}}
    <main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
        <div class="max-w-5xl mx-auto w-full space-y-4">

            {{-- Navigasi Breadcrumb --}}
            <div class="flex items-center justify-between text-xs text-gray-500 px-1">
                <a href="{{ url('/') }}" class="tombol-kembali inline-flex items-center gap-1.5 font-semibold hover:text-[#1F6B4F] transition-colors group">
                    <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            {{-- 2-Column Auth Card (Sesuai Mockup Desain) --}}
            <div class="w-full bg-white rounded-3xl shadow-xl border border-gray-200/80 overflow-hidden flex flex-col md:flex-row"
                 x-data="{
                     roleTab: 'warga',
                     showPassword: false,
                     loginVal: '{{ old('login', old('email', '')) }}',
                     tertangguhSampai: {{ (int) session('login_tertangguh_sampai', 0) }},
                     sisaDetik: 0,
                     init() {
                         this.hitungSisa();
                         if (this.sisaDetik > 0) {
                             const timer = setInterval(() => {
                                 this.hitungSisa();
                                 if (this.sisaDetik <= 0) { clearInterval(timer); }
                             }, 1000);
                         }
                     },
                     hitungSisa() {
                         this.sisaDetik = Math.max(0, this.tertangguhSampai - Math.floor(Date.now() / 1000));
                     },
                     get waktuMundur() {
                         const menit = String(Math.floor(this.sisaDetik / 60)).padStart(2, '0');
                         const detik = String(this.sisaDetik % 60).padStart(2, '0');
                         return menit + ':' + detik;
                     }
                 }">

                {{-- ==================== KOLOM KIRI: HERO BANNER HIJAU ==================== --}}
                <div class="w-full md:w-[42%] bg-[#1A5B42] text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
                    {{-- Subtle background blurs --}}
                    <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/5 pointer-events-none blur-xl"></div>
                    <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-black/10 pointer-events-none blur-2xl"></div>

                    <div class="relative z-10 space-y-6">
                        {{-- Logo & Identitas Desa --}}
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-white/10 p-1.5 flex items-center justify-center shrink-0 border border-white/20 shadow-xs">
                                <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <h3 class="font-bold text-white text-base leading-snug">
                                    Desa Banjarsari
                                </h3>
                                <p class="text-[11px] text-emerald-100/75 leading-none mt-0.5">
                                    Kec. Banjarsari, Kab. Ciamis
                                </p>
                            </div>
                        </div>

                        {{-- Headline & Pengantar --}}
                        <div class="pt-2">
                            <h1 class="font-extrabold text-2xl sm:text-[27px] text-white leading-tight tracking-tight">
                                SILAFA Banjarsari
                            </h1>
                            <p class="text-xs sm:text-sm text-emerald-100/80 leading-relaxed mt-2.5">
                                Layanan Pengaduan dan Aspirasi warga resmi dan terenkripsi untuk transparansi pelayanan masyarakat Desa Banjarsari.
                            </p>
                        </div>

                        {{-- 3 Kartu Keunggulan / Fitur Utama --}}
                        <div class="space-y-3 pt-2">
                            {{-- Fitur 1: SLA --}}
                            <div class="p-3.5 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 text-emerald-200 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-white">Respon Cepat 1×24 Jam SLA</h4>
                                    <p class="text-[11px] text-emerald-100/75 leading-relaxed mt-0.5">Disposisi langsung ke kepala seksi teknis terkait.</p>
                                </div>
                            </div>

                            {{-- Fitur 2: UU PDP --}}
                            <div class="p-3.5 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 text-emerald-200 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-white">Keamanan NIK Terenkripsi</h4>
                                    <p class="text-[11px] text-emerald-100/75 leading-relaxed mt-0.5">Kepatuhan UU PDP No. 27/2022 perlindungan data warga.</p>
                                </div>
                            </div>

                            {{-- Fitur 3: Transparansi --}}
                            <div class="p-3.5 rounded-xl bg-white/10 border border-white/15 backdrop-blur-xs flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0 text-emerald-200 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-white">Transparansi Penanganan Aduan</h4>
                                    <p class="text-[11px] text-emerald-100/75 leading-relaxed mt-0.5">Pantau tahapan verifikasi, tanggapan, dan selesai.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== KOLOM KANAN: FORM AUTENTIKASI ==================== --}}
                <div class="w-full md:w-[58%] bg-white p-8 sm:p-10 lg:p-12 flex flex-col justify-between">
                    <div>
                        {{-- Header Formulir --}}
                        <div class="mb-5">
                            <h2 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight">
                                Masuk ke Akun Anda
                            </h2>
                            <p class="text-xs sm:text-sm text-gray-500 mt-1.5 leading-relaxed">
                                Akses layanan pengaduan dan pemantauan aspirasi warga Desa Banjarsari secara terpadu.
                            </p>
                        </div>

                        {{-- Segmented Tabs Pilihan Peran (Warga vs Aparatur) --}}
                        <div class="bg-gray-100 p-1 rounded-xl flex items-center gap-1 mb-6 border border-gray-200/80">
                            <button type="button"
                                    @click="roleTab = 'warga'"
                                    class="flex-1 py-2.5 px-3 rounded-lg text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all cursor-pointer"
                                    :class="roleTab === 'warga' ? 'bg-white text-gray-900 shadow-xs border border-gray-200' : 'text-gray-500 hover:text-gray-800'">
                                <svg class="w-4 h-4" :class="roleTab === 'warga' ? 'text-[#1F6B4F]' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Warga Masyarakat</span>
                            </button>
                            <button type="button"
                                    @click="roleTab = 'petugas'"
                                    class="flex-1 py-2.5 px-3 rounded-lg text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all cursor-pointer"
                                    :class="roleTab === 'petugas' ? 'bg-white text-gray-900 shadow-xs border border-gray-200' : 'text-gray-500 hover:text-gray-800'">
                                <svg class="w-4 h-4" :class="roleTab === 'petugas' ? 'text-[#1F6B4F]' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Aparatur Desa</span>
                            </button>
                        </div>

                        {{-- Sesi berakhir karena tidak ada aktivitas --}}
                        @if (session('sesi_berakhir'))
                            <div role="status" class="mb-4 p-3.5 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span><strong class="font-bold">Sesi Anda telah berakhir</strong> karena tidak ada aktivitas. Silakan masuk kembali.</span>
                            </div>
                        @endif

                        {{-- Status Session --}}
                        @if (session('status'))
                            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        {{-- Form Login Utama --}}
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            {{-- Input 1: NIK / Email / Username --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="login" class="text-xs font-bold text-gray-800">
                                        <span x-show="roleTab === 'warga'">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></span>
                                        <span x-show="roleTab === 'petugas'" style="display: none;">Username atau Email Resmi <span class="text-rose-500">*</span></span>
                                    </label>
                                    <span x-show="roleTab === 'warga'" class="text-xs text-gray-400 font-medium">16 Digit NIK</span>
                                    <span x-show="roleTab === 'petugas'" class="text-xs text-gray-400 font-medium" style="display: none;">Admin / Kepala Desa</span>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </div>
                                    <input id="login"
                                           type="text"
                                           name="login"
                                           x-model="loginVal"
                                           required
                                           autofocus
                                           :placeholder="roleTab === 'warga' ? 'Contoh: 3207012345678901' : 'Masukan Email/Username'"
                                           :maxlength="roleTab === 'warga' ? '16' : '100'"
                                           class="w-full pl-10 pr-4 py-2.5 sm:py-3 border {{ $errors->any() ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }} rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                                </div>
                            </div>

                            {{-- Input 2: Kata Sandi --}}
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="password" class="text-xs font-bold text-gray-800">
                                        Kata Sandi <span class="text-rose-500">*</span>
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#1F6B4F] hover:underline">
                                            Lupa Kata Sandi?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <input id="password"
                                           :type="showPassword ? 'text' : 'password'"
                                           name="password"
                                           required
                                           autocomplete="current-password"
                                           placeholder="Masukkan kata sandi akun"
                                           class="w-full pl-10 pr-11 py-2.5 sm:py-3 border {{ $errors->any() ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }} rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                                    <button type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer"
                                            tabindex="-1"
                                            aria-label="Toggle Password Visibility">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>

                                {{-- Pesan Kesalahan Login (inline di bawah kata sandi) --}}
                                @if (session('login_tertangguh_sampai'))
                                    <p x-show="sisaDetik > 0" role="alert" class="text-xs text-rose-600">
                                        Akun ditangguhkan sementara. Coba lagi dalam <span class="font-semibold tabular-nums" x-text="waktuMundur"></span>
                                    </p>
                                    <p x-show="sisaDetik <= 0" style="display: none;" class="text-xs text-[#1F6B4F]">
                                        Silakan coba masuk kembali.
                                    </p>
                                @elseif ($errors->any())
                                    <p role="alert" class="text-xs text-rose-600">
                                        {{ $errors->first() }}
                                        @if (session()->has('login_sisa_percobaan'))
                                            <span class="text-rose-500/80">Sisa {{ session('login_sisa_percobaan') }} percobaan.</span>
                                        @endif
                                    </p>
                                @endif
                            </div>

                            {{-- Checkbox Remember Me (khusus warga; aparatur wajib masuk ulang setelah 30 menit tidak aktif) --}}
                            <div x-show="roleTab === 'warga'" class="flex items-center justify-between pt-0.5">
                                <label for="remember" class="flex items-center gap-2.5 cursor-pointer select-none">
                                    <input id="remember"
                                           type="checkbox"
                                           name="remember"
                                           class="w-4 h-4 rounded border-gray-300 text-[#1F6B4F] focus:ring-[#1F6B4F]">
                                    <span class="text-xs text-gray-600">Ingat saya di perangkat ini <span class="text-gray-400">(30 hari)</span></span>
                                </label>
                            </div>

                            {{-- Tombol Masuk ke Layanan --}}
                            <div class="pt-2">
                                <button type="submit"
                                        :disabled="sisaDetik > 0"
                                        class="w-full py-3 sm:py-3.5 px-4 bg-[#1F6B4F] hover:bg-[#174B39] disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white text-sm sm:text-base font-bold rounded-xl shadow-xs transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer group">
                                    <span x-show="sisaDetik <= 0">Masuk ke Layanan</span>
                                    <span x-show="sisaDetik > 0" style="display: none;">Tunggu <span x-text="waktuMundur"></span></span>
                                    <svg x-show="sisaDetik <= 0" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </form>

                        {{-- Box Pendaftaran Warga Baru --}}
                        <div class="bg-[#EAF5EE] border border-[#1F6B4F]/20 rounded-xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-6">
                            <div>
                                <span class="text-xs text-gray-600 block">Belum memiliki akun warga desa?</span>
                                <span class="text-xs sm:text-sm font-bold text-gray-900 block mt-0.5">Pendaftaran mandiri dengan NIK</span>
                            </div>
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold text-gray-900 shadow-2xs transition-colors shrink-0">
                                <span>Daftar sebagai Warga Baru</span>
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        {{-- Bantuan WhatsApp & Balai Desa --}}
                        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-gray-500 text-center">
                            <span>Butuh bantuan saat masuk?</span>
                            <a href="https://wa.me/6289662893128?text={{ rawurlencode('Halo Admin Desa Banjarsari, saya butuh bantuan untuk masuk ke akun SILAFA.') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-800 hover:text-[#1F6B4F] inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.668-.699c.974.536 1.764.814 2.792.814 3.18 0 5.766-2.587 5.766-5.766 0-3.18-2.586-5.766-5.766-5.766zm9.969 5.766c0 5.485-4.453 9.938-9.969 9.938-1.745 0-3.385-.45-4.819-1.237l-5.212 1.361 1.39-5.078c-.887-1.488-1.359-3.21-1.359-4.984 0-5.485 4.453-9.938 9.969-9.938 5.516 0 10 4.453 10 9.938z"/></svg>
                                <span>WhatsApp: 0896-6289-3128</span>
                            </a>
                            <span class="text-gray-300">•</span>
                            <a href="https://www.google.com/maps/dir//QR6M%2BQH2+Kantor+Desa+Banjarsari,+Banjarsari,+Kec.+Bayongbong,+Kabupaten+Garut,+Jawa+Barat+44162/data=!4m6!4m5!1m1!4e2!1m2!1m1!1s0x2e68bac7ed2768fb:0xa1e17fc9b9ff1f3e" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-800 hover:text-[#1F6B4F] inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Balai Desa</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Bawah Card (Sesuai Mockup Desain) --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-gray-500 pt-3 pb-2 px-1">
                <div>
                    <p class="font-medium text-gray-700">&copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Seluruh Hak Cipta Dilindungi.</p>
                    <p class="text-[11px] text-gray-500 mt-0.5">Jam Layanan Kantor Desa: Senin - Jumat (08.00 - 16.00 WIB) | Portal Pengaduan Digital: 24 Jam</p>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-gray-600">
                    <a href="{{ url('/') }}" class="hover:text-[#1F6B4F] transition-colors">Panduan Registrasi NIK</a>
                    <span class="text-gray-300">•</span>
                    <a href="{{ url('/') }}" class="hover:text-[#1F6B4F] transition-colors">Standar Operasional (SOP)</a>
                    <span class="text-gray-300">•</span>
                    <a href="{{ url('/') }}" class="hover:text-[#1F6B4F] transition-colors">Kebijakan Privasi</a>
                    <span class="text-gray-300">•</span>
                    <a href="{{ url('/') }}#kontak" class="hover:text-[#1F6B4F] transition-colors">Kontak Balai Desa</a>
                </div>
            </div>

        </div>
    </main>

    {{-- ==================== FULL FOOTER (Sama dengan Halaman Registrasi) ==================== --}}
    <footer class="bg-white border-t border-[#E2E8E4] mt-8 lg:mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Column 1 - Brand --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-8 h-8 object-contain">
                        <span class="font-display font-bold text-gray-900 text-sm block">Desa Banjarsari</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Sistem Informasi Pelayanan Pengaduan & Aspirasi Terpadu demi keterbukaan, akuntabilitas, dan kesejahteraan masyarakat Desa Banjarsari.
                    </p>
                </div>

                {{-- Column 2 - Tautan Cepat --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-xs sm:text-sm mb-3">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-xs text-gray-500 hover:text-[#1F6B4F] transition-colors">Standar Pelayanan Minimal</a></li>
                        <li><a href="#" class="text-xs text-gray-500 hover:text-[#1F6B4F] transition-colors">Lapor Gratifikasi</a></li>
                        <li><a href="#" class="text-xs text-gray-500 hover:text-[#1F6B4F] transition-colors">Panduan Pengaduan</a></li>
                    </ul>
                </div>

                {{-- Column 3 - Jam Layanan --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-xs sm:text-sm mb-3">Jam Layanan Kantor</h4>
                    <ul class="space-y-2">
                        <li class="text-xs text-gray-500">Senin - Kamis: 08.00 - 15.00 WIB</li>
                        <li class="text-xs text-gray-500">Jumat: 08.00 - 11.30 WIB</li>
                        <li class="text-xs text-gray-500">Sabtu, Minggu & Hari Libur Nasional Tutup</li>
                    </ul>
                </div>

                {{-- Column 4 - Kontak --}}
                <div>
                    <h4 class="font-display font-semibold text-gray-900 text-xs sm:text-sm mb-3">Kontak Sekretariat</h4>
                    <ul class="space-y-2">
                        <li class="text-xs text-gray-500">Kantor Balai Desa Banjarsari</li>
                        <li class="text-xs text-gray-500">WhatsApp Siaga Warga: 0896-6289-3128</li>
                        <li class="text-xs text-gray-500">Email: aduan@banjarsari.desa.id</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright --}}
        <div class="border-t border-[#E2E8E4]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-gray-400">
                <p>&copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Seluruh Hak Cipta Dilindungi.</p>
                <p>Amanah • Transparan • Akuntabel</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    <x-tombol-ke-atas />
</body>
</html>
