<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Akun Warga Baru — SILAFA Banjarsari</title>

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
<body class="antialiased bg-[#F8FAF9] text-gray-800 font-sans min-h-screen flex flex-col justify-between">

    {{-- ==================== NAVBAR ==================== --}}
    <header class="bg-white border-b border-[#E2E8E4] sticky top-0 z-50 shadow-sm" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-10 h-10 object-contain">
                    <div>
                        <span class="font-display font-bold text-gray-900 text-sm sm:text-base leading-tight block group-hover:text-[#1F6B4F] transition-colors">SILAFA Banjarsari</span>
                        <span class="text-[11px] text-gray-500 hidden sm:block">Pemerintah Desa Banjarsari</span>
                    </div>
                </a>

                {{-- Right: Actions (Beranda & Login) --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE] rounded-lg transition-colors">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Beranda</span>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-semibold text-white bg-[#1F6B4F] hover:bg-[#174B39] rounded-lg shadow-sm transition-all duration-200">
                        <i data-lucide="user-circle" class="w-4 h-4"></i>
                        <span>Login</span>
                    </a>
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Emblem" class="w-7 h-7 object-contain opacity-90 hidden sm:block">
                </div>
            </div>
        </div>
    </header>

    {{-- ==================== MAIN REGISTRATION CONTENT ==================== --}}
    <main class="flex-1 py-8 sm:py-12"
          x-data="{
              currentStep: {{ $errors->hasAny(['password', 'password_confirmation', 'persetujuan']) ? 4 : ($errors->hasAny(['email', 'telepon', 'whatsapp']) ? 3 : ($errors->hasAny(['alamat', 'rt', 'rw', 'dusun']) ? 2 : 1)) }},
              nik: '{{ old('nik', '') }}',
              gender: '{{ old('jenis_kelamin', 'Laki-laki') }}',
              showPassword: false,
              showConfirmPassword: false,
              nextStep() {
                  const panel = this.$root.querySelector(`[data-step='${this.currentStep}']`);
                  const fieldTidakValid = panel ? [...panel.querySelectorAll('input, select, textarea')].find((field) => ! field.checkValidity()) : null;
                  if (fieldTidakValid) {
                      fieldTidakValid.reportValidity();
                      return;
                  }
                  if (this.currentStep < 4) this.currentStep++;
                  window.scrollTo({ top: 120, behavior: 'smooth' });
              },
              prevStep() {
                  if (this.currentStep > 1) this.currentStep--;
                  window.scrollTo({ top: 120, behavior: 'smooth' });
              }
          }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. HEADER CARD (Logo, Title, & Help desk) --}}
            <div class="bg-white rounded-2xl border border-[#E2E8E4] p-5 sm:p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-white border border-[#E2E8E4] p-2 flex items-center justify-center flex-shrink-0 shadow-sm">
                        <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-semibold bg-[#EAF5EE] text-[#1F6B4F]">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                Portal Resmi Pelayanan Terpadu
                            </span>
                        </div>
                        <h1 class="font-display text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 leading-tight">
                            Registrasi Akun Warga Baru
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                            Desa Banjarsari • Kecamatan Ciamis • Kabupaten Ciamis
                        </p>
                    </div>
                </div>

                {{-- Butuh bantuan pendaftaran? --}}
                <div class="bg-[#F8FAF9] border border-[#E2E8E4] rounded-xl p-3 sm:p-4 flex items-center gap-3.5 w-full md:w-auto">
                    <div class="w-10 h-10 rounded-full bg-[#1F6B4F] text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i data-lucide="help-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block leading-tight">Butuh bantuan pendaftaran?</span>
                        <a href="https://wa.me/6289662893128" target="_blank" rel="noopener noreferrer" class="text-xs sm:text-sm font-bold text-gray-900 hover:text-[#1F6B4F] transition-colors block">
                            Sekretariat Balai Desa
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. STEPPER PROGRESS BAR (Tahap 01 to 04) --}}
            <div class="bg-white rounded-xl border border-[#E2E8E4] p-4 shadow-sm">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    {{-- Tahap 1 --}}
                    <button type="button"
                            @click="currentStep = 1"
                            class="text-left flex items-center gap-3 p-2 rounded-lg transition-colors cursor-pointer"
                            :class="currentStep === 1 ? 'border-b-2 border-[#1F6B4F] bg-[#EAF5EE]/40' : (currentStep > 1 ? 'text-gray-900' : 'text-gray-400')">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
                             :class="currentStep === 1 ? 'bg-[#1F6B4F] text-white' : (currentStep > 1 ? 'bg-[#1F6B4F] text-white' : 'bg-gray-100 text-gray-500')">
                            <span x-show="currentStep <= 1">1</span>
                            <i x-show="currentStep > 1" data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider block"
                                  :class="currentStep === 1 ? 'text-[#1F6B4F]' : 'text-gray-400'">TAHAP 01</span>
                            <span class="text-xs sm:text-sm font-bold block"
                                  :class="currentStep === 1 ? 'text-gray-900' : (currentStep > 1 ? 'text-gray-700' : 'text-gray-400')">
                                Identitas Diri
                            </span>
                        </div>
                    </button>

                    {{-- Tahap 2 --}}
                    <button type="button"
                            @click="currentStep = 2"
                            class="text-left flex items-center gap-3 p-2 rounded-lg transition-colors cursor-pointer"
                            :class="currentStep === 2 ? 'border-b-2 border-[#1F6B4F] bg-[#EAF5EE]/40' : (currentStep > 2 ? 'text-gray-900' : 'text-gray-400')">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
                             :class="currentStep === 2 ? 'bg-[#1F6B4F] text-white' : (currentStep > 2 ? 'bg-[#1F6B4F] text-white' : 'bg-gray-100 text-gray-500')">
                            <span x-show="currentStep <= 2">2</span>
                            <i x-show="currentStep > 2" data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider block"
                                  :class="currentStep === 2 ? 'text-[#1F6B4F]' : 'text-gray-400'">TAHAP 02</span>
                            <span class="text-xs sm:text-sm font-bold block"
                                  :class="currentStep === 2 ? 'text-gray-900' : (currentStep > 2 ? 'text-gray-700' : 'text-gray-400')">
                                Domisili
                            </span>
                        </div>
                    </button>

                    {{-- Tahap 3 --}}
                    <button type="button"
                            @click="currentStep = 3"
                            class="text-left flex items-center gap-3 p-2 rounded-lg transition-colors cursor-pointer"
                            :class="currentStep === 3 ? 'border-b-2 border-[#1F6B4F] bg-[#EAF5EE]/40' : (currentStep > 3 ? 'text-gray-900' : 'text-gray-400')">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
                             :class="currentStep === 3 ? 'bg-[#1F6B4F] text-white' : (currentStep > 3 ? 'bg-[#1F6B4F] text-white' : 'bg-gray-100 text-gray-500')">
                            <span x-show="currentStep <= 3">3</span>
                            <i x-show="currentStep > 3" data-lucide="check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider block"
                                  :class="currentStep === 3 ? 'text-[#1F6B4F]' : 'text-gray-400'">TAHAP 03</span>
                            <span class="text-xs sm:text-sm font-bold block"
                                  :class="currentStep === 3 ? 'text-gray-900' : (currentStep > 3 ? 'text-gray-700' : 'text-gray-400')">
                                Kontak Aktif
                            </span>
                        </div>
                    </button>

                    {{-- Tahap 4 --}}
                    <button type="button"
                            @click="currentStep = 4"
                            class="text-left flex items-center gap-3 p-2 rounded-lg transition-colors cursor-pointer"
                            :class="currentStep === 4 ? 'border-b-2 border-[#1F6B4F] bg-[#EAF5EE]/40' : 'text-gray-400'">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0"
                             :class="currentStep === 4 ? 'bg-[#1F6B4F] text-white' : 'bg-gray-100 text-gray-500'">
                            <span>4</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider block"
                                  :class="currentStep === 4 ? 'text-[#1F6B4F]' : 'text-gray-400'">TAHAP 04</span>
                            <span class="text-xs sm:text-sm font-bold block"
                                  :class="currentStep === 4 ? 'text-gray-900' : 'text-gray-400'">
                                Keamanan
                            </span>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Validation Errors Display --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl p-4 space-y-1">
                    <div class="font-semibold flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        <span>Mohon periksa kembali formulir pendaftaran:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 3. FORM CARD --}}
            <form method="POST" action="{{ route('register') }}" novalidate class="bg-white rounded-2xl border border-[#E2E8E4] p-6 sm:p-8 shadow-sm space-y-6">
                @csrf

                {{-- ==================== STEP 1: IDENTITAS DIRI ==================== --}}
                <div x-show="currentStep === 1" data-step="1" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-[#E2E8E4] gap-2">
                        <div>
                            <h2 class="font-display font-bold text-lg sm:text-xl text-gray-900">
                                Langkah 1: Identitas Diri
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Pastikan data sesuai dengan Kartu Tanda Penduduk (KTP-el) atau Kartu Keluarga resmi.
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EAF5EE] text-[#1F6B4F] border border-[#1F6B4F]/20">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                                Validasi SIAK
                            </span>
                        </div>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="nama_lengkap" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-gray-400">Huruf kapital sesuai KTP</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <input id="nama_lengkap"
                                   type="text"
                                   name="nama_lengkap"
                                   value="{{ old('nama_lengkap') }}"
                                   required
                                   autofocus
                                   placeholder="Contoh: AHMAD HIDAYATUL MUSTOFA"
                                   oninput="this.value = this.value.toUpperCase()"
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all uppercase">
                        </div>
                    </div>

                    {{-- NIK & Jenis Kelamin --}}
                    <div class="grid sm:grid-cols-2 gap-5">
                        {{-- NIK --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="nik" class="text-xs sm:text-sm font-semibold text-gray-800">
                                    Nomor Induk Kependudukan (NIK) <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-gray-400">
                                    <span x-text="nik.length"></span>/16 Digit
                                </span>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                                </div>
                                <input id="nik"
                                       type="text"
                                       name="nik"
                                       x-model="nik"
                                       maxlength="16"
                                       required
                                       placeholder="16 digit angka NIK"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                            <p class="text-[11px] text-gray-400">NIK terdaftar pada data Dinas Kependudukan & Pencatatan Sipil Ciamis.</p>
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="space-y-1.5">
                            <label class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" name="jenis_kelamin" :value="gender">
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button"
                                        @click="gender = 'Laki-laki'"
                                        class="h-[42px] px-3 py-2 rounded-lg border text-xs sm:text-sm font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer"
                                        :class="gender === 'Laki-laki' ? 'border-[#1F6B4F] bg-[#EAF5EE] text-[#1F6B4F] ring-1 ring-[#1F6B4F]' : 'border-[#E2E8E4] bg-white text-gray-700 hover:bg-gray-50'">
                                    <i data-lucide="circle-dot" class="w-4 h-4 text-[#1F6B4F]" x-show="gender === 'Laki-laki'"></i>
                                    <i data-lucide="circle" class="w-4 h-4 text-gray-400" x-show="gender !== 'Laki-laki'"></i>
                                    <span>♂ Laki-laki</span>
                                </button>
                                <button type="button"
                                        @click="gender = 'Perempuan'"
                                        class="h-[42px] px-3 py-2 rounded-lg border text-xs sm:text-sm font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer"
                                        :class="gender === 'Perempuan' ? 'border-[#1F6B4F] bg-[#EAF5EE] text-[#1F6B4F] ring-1 ring-[#1F6B4F]' : 'border-[#E2E8E4] bg-white text-gray-700 hover:bg-gray-50'">
                                    <i data-lucide="circle-dot" class="w-4 h-4 text-[#1F6B4F]" x-show="gender === 'Perempuan'"></i>
                                    <i data-lucide="circle" class="w-4 h-4 text-gray-400" x-show="gender !== 'Perempuan'"></i>
                                    <span>♀ Perempuan</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tempat Lahir & Tanggal Lahir --}}
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label for="tempat_lahir" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Tempat Lahir <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </div>
                                <input id="tempat_lahir"
                                       type="text"
                                       name="tempat_lahir"
                                       value="{{ old('tempat_lahir') }}"
                                       placeholder="Kabupaten / Kota kelahiran"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="tanggal_lahir" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <input id="tanggal_lahir"
                                       type="date"
                                       name="tanggal_lahir"
                                       value="{{ old('tanggal_lahir') }}"
                                       class="w-full pl-10 pr-4 py-2 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Button Step 1 --}}
                    <div class="pt-4 flex justify-end">
                        <button type="button"
                                @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                            <span>Lanjutkan ke Domisili</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- ==================== STEP 2: DOMISILI ==================== --}}
                <div x-show="currentStep === 2" data-step="2" class="space-y-6" style="display: none;">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-[#E2E8E4] gap-2">
                        <div>
                            <h2 class="font-display font-bold text-lg sm:text-xl text-gray-900">
                                Langkah 2: Domisili Wilayah
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Informasi dusun, RT, dan RW tempat tinggal Anda di Desa Banjarsari.
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EAF5EE] text-[#1F6B4F] border border-[#1F6B4F]/20">
                                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                                Wilayah Desa Banjarsari
                            </span>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-5">
                        <div class="space-y-1.5">
                            <label for="dusun" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Dusun <span class="text-red-500">*</span>
                            </label>
                            <input id="dusun"
                                   type="text"
                                   name="dusun"
                                   value="{{ old('dusun') }}"
                                   placeholder="Contoh: Dusun Sukasari"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label for="rt" class="text-xs sm:text-sm font-semibold text-gray-800">
                                RT <span class="text-red-500">*</span>
                            </label>
                            <input id="rt"
                                   type="text"
                                   name="rt"
                                   maxlength="3"
                                   value="{{ old('rt') }}"
                                   placeholder="001"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label for="rw" class="text-xs sm:text-sm font-semibold text-gray-800">
                                RW <span class="text-red-500">*</span>
                            </label>
                            <input id="rw"
                                   type="text"
                                   name="rw"
                                   maxlength="3"
                                   value="{{ old('rw') }}"
                                   placeholder="005"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="alamat" class="text-xs sm:text-sm font-semibold text-gray-800">
                            Alamat Lengkap KTP <span class="text-red-500">*</span>
                        </label>
                        <textarea id="alamat"
                                  name="alamat"
                                  rows="3"
                                  placeholder="Jl. Raya Banjarsari No. XX, Gang..."
                                  class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">{{ old('alamat') }}</textarea>
                    </div>

                    {{-- Button Step 2 --}}
                    <div class="pt-4 flex items-center justify-between">
                        <button type="button"
                                @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 border border-[#E2E8E4] bg-white text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Identitas</span>
                        </button>
                        <button type="button"
                                @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                            <span>Lanjutkan ke Kontak Aktif</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- ==================== STEP 3: KONTAK AKTIF ==================== --}}
                <div x-show="currentStep === 3" data-step="3" class="space-y-6" style="display: none;">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-[#E2E8E4] gap-2">
                        <div>
                            <h2 class="font-display font-bold text-lg sm:text-xl text-gray-900">
                                Langkah 3: Kontak Aktif
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Kontak yang dapat dihubungi untuk pembaruan dan tindak lanjut aduan warga.
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EAF5EE] text-[#1F6B4F] border border-[#1F6B4F]/20">
                                <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>
                                Notifikasi Pelayanan
                            </span>
                        </div>
                    </div>

                    {{-- Info banner kontak minimal salah satu --}}
                    <div class="p-3.5 bg-[#EAF5EE] border border-[#1F6B4F]/20 rounded-xl flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-[#1F6B4F] shrink-0 mt-0.5"></i>
                        <p class="text-xs text-[#1F6B4F] leading-relaxed">
                            <strong>Ketentuan Kontak:</strong> <strong>Nomor Telepon / HP</strong> dan <strong>Email</strong> wajib diisi. Email digunakan untuk mengirim tautan reset kata sandi jika Anda lupa kata sandi.
                        </p>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-5">
                        {{-- Telepon --}}
                        <div class="space-y-1.5">
                            <label for="telepon" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Nomor Telepon / HP <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="phone" class="w-4 h-4"></i>
                                </div>
                                <input id="telepon"
                                       type="tel"
                                       name="telepon"
                                       value="{{ old('telepon') }}"
                                       required
                                       placeholder="Contoh: 081234567890"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                            <p class="text-[11px] text-gray-400">Nomor aktif yang dapat dihubungi.</p>
                        </div>

                        {{-- WhatsApp --}}
                        <div class="space-y-1.5">
                            <label for="whatsapp" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Nomor WhatsApp <span class="text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="message-square" class="w-4 h-4"></i>
                                </div>
                                <input id="whatsapp"
                                       type="tel"
                                       name="whatsapp"
                                       value="{{ old('whatsapp') }}"
                                       placeholder="Contoh: 081234567890"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                            <p class="text-[11px] text-gray-400">Untuk menerima notifikasi instan.</p>
                        </div>

                        {{-- Email --}}
                        <div class="space-y-1.5">
                            <label for="email" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                </div>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="nama@email.com"
                                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                            </div>
                            <p class="text-[11px] text-gray-400">Untuk menerima tautan reset kata sandi.</p>
                        </div>
                    </div>

                    {{-- Button Step 3 --}}
                    <div class="pt-4 flex items-center justify-between">
                        <button type="button"
                                @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 border border-[#E2E8E4] bg-white text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Domisili</span>
                        </button>
                        <button type="button"
                                @click="nextStep()"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                            <span>Lanjutkan ke Keamanan</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                {{-- ==================== STEP 4: KEAMANAN ==================== --}}
                <div x-show="currentStep === 4" data-step="4" class="space-y-6" style="display: none;">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-[#E2E8E4] gap-2">
                        <div>
                            <h2 class="font-display font-bold text-lg sm:text-xl text-gray-900">
                                Langkah 4: Keamanan Akun
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Tentukan kata sandi untuk login ke portal layanan warga desa.
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#EAF5EE] text-[#1F6B4F] border border-[#1F6B4F]/20">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                Enkripsi Standar Nasional
                            </span>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label for="password" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Kata Sandi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <input id="password"
                                       :type="showPassword ? 'text' : 'password'"
                                       name="password"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Minimal 8 karakter"
                                       class="w-full pl-10 pr-10 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                                <button type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i x-show="!showPassword" data-lucide="eye" class="w-4 h-4"></i>
                                    <i x-show="showPassword" data-lucide="eye-off" class="w-4 h-4" style="display: none;"></i>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="text-xs sm:text-sm font-semibold text-gray-800">
                                Konfirmasi Kata Sandi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </div>
                                <input id="password_confirmation"
                                       :type="showConfirmPassword ? 'text' : 'password'"
                                       name="password_confirmation"
                                       required
                                       autocomplete="new-password"
                                       placeholder="Ulangi kata sandi"
                                       class="w-full pl-10 pr-10 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                                <button type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i x-show="!showConfirmPassword" data-lucide="eye" class="w-4 h-4"></i>
                                    <i x-show="showConfirmPassword" data-lucide="eye-off" class="w-4 h-4" style="display: none;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Persetujuan Kebijakan Privasi & Ketentuan --}}
                    <div class="pt-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox"
                                   name="persetujuan"
                                   value="1"
                                   {{ old('persetujuan') ? 'checked' : '' }}
                                   required
                                   class="mt-0.5 w-4 h-4 rounded border-gray-300 text-[#1F6B4F] focus:ring-[#1F6B4F]">
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Saya menyetujui <strong>Kebijakan Privasi</strong> dan <strong>Ketentuan Layanan</strong> yang berlaku, serta menyatakan bahwa seluruh data identitas, domisili, dan kontak yang saya isikan adalah benar dan sah sesuai data kependudukan resmi saya di Desa Banjarsari.
                            </span>
                        </label>
                    </div>

                    {{-- Button Step 4 (Submit) --}}
                    <div class="pt-4 flex items-center justify-between">
                        <button type="button"
                                @click="prevStep()"
                                class="inline-flex items-center gap-2 px-5 py-2.5 border border-[#E2E8E4] bg-white text-gray-700 hover:bg-gray-50 text-sm font-semibold rounded-lg transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Kembali ke Kontak</span>
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-7 py-3 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                            <span>Selesaikan Pendaftaran</span>
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </form>

            {{-- 4. JAMINAN KERAHASIAAN & KEAMANAN DATA BANNER --}}
            <div class="bg-white border border-[#E2E8E4] rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center flex-shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-display font-bold text-xs sm:text-sm text-gray-900">
                            Jaminan Kerahasiaan & Keamanan Data
                        </h4>
                        <p class="text-[11px] sm:text-xs text-gray-500 leading-relaxed mt-0.5 max-w-2xl">
                            Data Anda dijamin kerahasiaannya dan hanya diverifikasi dengan database kependudukan internal Pemerintah Desa Banjarsari serta SIAK Disdukcapil Ciamis demi kepentingan administrasi kenegaraan.
                        </p>
                    </div>
                </div>

                {{-- UU PDP Badge --}}
                <div class="flex-shrink-0 self-start sm:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-gray-50 border border-[#E2E8E4] text-xs font-semibold text-gray-700 shadow-2xs">
                        <i data-lucide="scale" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        UU PDP No. 27 / 2022
                    </span>
                </div>
            </div>

            {{-- Sudah Punya Akun --}}
            <div class="text-center text-xs sm:text-sm text-gray-500 pt-2 pb-6">
                Sudah memiliki akun warga terdaftar?
                <a href="{{ route('login') }}" class="text-[#1F6B4F] font-bold hover:underline ml-1">
                    Masuk ke Layanan
                </a>
            </div>
        </div>
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="bg-white border-t border-[#E2E8E4]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Column 1 - Brand --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-9 h-9 object-contain">
                        <div>
                            <span class="font-display font-bold text-gray-900 text-sm block">Desa Banjarsari</span>
                        </div>
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
                <p>&copy; 2024 Pemerintah Desa Banjarsari. Seluruh Hak Cipta Dilindungi.</p>
                <p>Amanah • Transparan • Akuntabel</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</body>
</html>
