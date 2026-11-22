<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display font-bold text-xl sm:text-2xl text-gray-900 leading-tight">
                    Status Pendaftaran Akun Warga
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Informasi verifikasi data kependudukan dan status keaktifan akun Anda di Desa Banjarsari.
                </p>
            </div>
            <div>
                @if ($user->isMenungguTinjauan())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu Tinjauan
                    </span>
                @elseif ($user->isDitolak())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                        <span class="w-2 h-2 rounded-full bg-red-600"></span>
                        Pendaftaran Ditolak
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Akun Aktif
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-6 max-w-5xl mx-auto">

        {{-- FLASH / SESSION MESSAGES --}}
        @if (session('warning'))
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <span class="font-semibold block">Pemberitahuan Sistem</span>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-sm flex items-start gap-3 shadow-xs">
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-semibold block">Informasi</span>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        {{-- PERINGATAN FITUR PENGADUAN TERKUNCI --}}
        @if (! $user->isAktif())
            <div class="p-4 bg-gray-50 border border-gray-300 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-200 text-gray-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Pengajuan Laporan dan Aduan Terkunci</h4>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Warga hanya dapat mengajukan pengaduan atau aspirasi setelah akun diverifikasi dan berstatus <strong>Aktif</strong>.
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-md bg-gray-200 text-gray-700 text-xs font-semibold self-start sm:self-auto">
                    Terkunci
                </span>
            </div>
        @endif

        {{-- STATUS KONDISI 1: MENUNGGU TINJAUAN --}}
        @if ($user->isMenungguTinjauan())
            <div class="bg-white rounded-2xl border border-[#E2E8E4] p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pb-6 border-b border-[#E2E8E4]">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Menunggu Tinjauan Petugas Balai Desa</h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5 leading-relaxed">
                            Data NIK dan nama lengkap Anda tidak cocok secara otomatis dengan master data kependudukan desa. Saat ini berkas Anda sedang dalam antrean peninjauan manual oleh Admin Desa Banjarsari.
                        </p>
                    </div>
                </div>

                {{-- Detail Data yang Diajukan --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ringkasan Data yang Anda Daftarkan</h4>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                        <div class="p-3.5 bg-[#F8FAF9] rounded-xl border border-[#E2E8E4]">
                            <span class="text-gray-400 block mb-1">Nomor Induk Kependudukan (NIK)</span>
                            <span class="font-bold text-gray-800 text-sm font-mono">{{ $user->nik }}</span>
                        </div>
                        <div class="p-3.5 bg-[#F8FAF9] rounded-xl border border-[#E2E8E4]">
                            <span class="text-gray-400 block mb-1">Nama Lengkap</span>
                            <span class="font-bold text-gray-800 text-sm uppercase">{{ $user->nama_lengkap }}</span>
                        </div>
                        <div class="p-3.5 bg-[#F8FAF9] rounded-xl border border-[#E2E8E4]">
                            <span class="text-gray-400 block mb-1">Tanggal Lahir</span>
                            <span class="font-semibold text-gray-800">
                                {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d F Y') : '-' }}
                            </span>
                        </div>
                        <div class="p-3.5 bg-[#F8FAF9] rounded-xl border border-[#E2E8E4] sm:col-span-2">
                            <span class="text-gray-400 block mb-1">Alamat Domisili KTP</span>
                            <span class="font-semibold text-gray-800">
                                {{ $user->alamat }}, RT {{ $user->rt }}/RW {{ $user->rw }}, {{ $user->dusun }}
                            </span>
                        </div>
                        <div class="p-3.5 bg-[#F8FAF9] rounded-xl border border-[#E2E8E4]">
                            <span class="text-gray-400 block mb-1">Kontak Pengguna</span>
                            <span class="font-semibold text-gray-800 block">Telp: {{ $user->telepon ?? '-' }}</span>
                            <span class="font-semibold text-gray-800 block">WA: {{ $user->whatsapp ?? '-' }}</span>
                            <span class="text-gray-600 block truncate">Email: {{ $user->email ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bantuan & Waktu --}}
                <div class="pt-4 border-t border-[#E2E8E4] flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-gray-500">
                    <div>
                        Waktu Pendaftaran: <span class="font-semibold text-gray-700">{{ $user->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>Butuh percepatan?</span>
                        <a href="https://wa.me/6281234567890" target="_blank" class="text-[#1F6B4F] font-bold hover:underline inline-flex items-center gap-1">
                            Hubungi Admin Desa
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>

        {{-- STATUS KONDISI 2: DITOLAK (DENGAN FORM PENGAJUAN ULANG) --}}
        @elseif ($user->isDitolak())
            <div class="space-y-6">
                {{-- Kartu Pemberitahuan Penolakan --}}
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 sm:p-7 shadow-xs">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-base sm:text-lg font-bold text-red-900">
                                Pendaftaran Akun Ditolak oleh Petugas
                            </h3>
                            <p class="text-xs sm:text-sm text-red-700 leading-relaxed">
                                Mohon maaf, permohonan pendaftaran akun warga Anda belum dapat disetujui. Silakan tinjau alasan penolakan di bawah ini, perbaiki data Anda, dan kirimkan ulang pengajuan.
                            </p>
                            <div class="p-4 bg-white/80 border border-red-200 rounded-xl mt-3">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-red-500 block mb-0.5">Alasan Penolakan:</span>
                                <p class="text-sm font-semibold text-red-900">
                                    {{ $user->alasan_penolakan ?: 'Data identitas kependudukan tidak sesuai atau dokumen pendukung belum lengkap.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formulir Perbaikan Data dan Pengajuan Ulang --}}
                <form method="POST" action="{{ route('pendaftaran.ajukan-ulang') }}" class="bg-white rounded-2xl border border-[#E2E8E4] p-6 sm:p-8 shadow-xs space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="pb-4 border-b border-[#E2E8E4]">
                        <h3 class="font-display font-bold text-lg text-gray-900">Perbaiki Data dan Ajukan Ulang</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Periksa kembali ejaan nama lengkap, tanggal lahir, dan alamat sesuai KTP fisik Anda.
                        </p>
                    </div>

                    {{-- Error Display --}}
                    @if ($errors->any())
                        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs space-y-1">
                            <span class="font-bold block">Mohon perbaiki kendala berikut:</span>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- NIK (Read-Only) & Nama Lengkap --}}
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="text-xs sm:text-sm font-semibold text-gray-700 block">
                                NIK (Nomor Induk Kependudukan)
                            </label>
                            <input type="text"
                                   value="{{ $user->nik }}"
                                   disabled
                                   class="w-full px-4 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600 font-mono cursor-not-allowed">
                            <p class="text-[11px] text-gray-400">NIK terdaftar tidak dapat diubah secara mandiri.</p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="nama_lengkap" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                Nama Lengkap Sesuai KTP <span class="text-red-500">*</span>
                            </label>
                            <input id="nama_lengkap"
                                   type="text"
                                   name="nama_lengkap"
                                   value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                                   required
                                   oninput="this.value = this.value.toUpperCase()"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 uppercase focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                            <p class="text-[11px] text-gray-400">Pastikan ejaan sama persis dengan kartu tanda penduduk.</p>
                        </div>
                    </div>

                    {{-- Tanggal Lahir & Dusun --}}
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label for="tanggal_lahir" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                Tanggal Lahir <span class="text-red-500">*</span>
                            </label>
                            <input id="tanggal_lahir"
                                   type="date"
                                   name="tanggal_lahir"
                                   value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}"
                                   required
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                        </div>

                        <div class="space-y-1.5">
                            <label for="dusun" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                Dusun <span class="text-red-500">*</span>
                            </label>
                            <input id="dusun"
                                   type="text"
                                   name="dusun"
                                   value="{{ old('dusun', $user->dusun) }}"
                                   required
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                        </div>
                    </div>

                    {{-- RT, RW, Alamat --}}
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label for="rt" class="text-xs sm:text-sm font-semibold text-gray-800 block">RT <span class="text-red-500">*</span></label>
                                <input id="rt" type="text" name="rt" maxlength="3" value="{{ old('rt', $user->rt) }}" required class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                            </div>
                            <div class="space-y-1.5">
                                <label for="rw" class="text-xs sm:text-sm font-semibold text-gray-800 block">RW <span class="text-red-500">*</span></label>
                                <input id="rw" type="text" name="rw" maxlength="3" value="{{ old('rw', $user->rw) }}" required class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="alamat" class="text-xs sm:text-sm font-semibold text-gray-800 block">Alamat KTP Lengkap <span class="text-red-500">*</span></label>
                            <textarea id="alamat" name="alamat" rows="2" required class="w-full px-4 py-2 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">{{ old('alamat', $user->alamat) }}</textarea>
                        </div>
                    </div>

                    {{-- Kontak (Telepon, WhatsApp, Email) --}}
                    <div class="grid sm:grid-cols-3 gap-5 pt-2 border-t border-[#E2E8E4]">
                        <div class="space-y-1.5">
                            <label for="telepon" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                No. Telepon / HP
                            </label>
                            <input id="telepon"
                                   type="tel"
                                   name="telepon"
                                   value="{{ old('telepon', $user->telepon) }}"
                                   placeholder="081234567890"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                            <p class="text-[11px] text-gray-400">Minimal salah satu (Email / Telepon) wajib diisi.</p>
                        </div>

                        <div class="space-y-1.5">
                            <label for="whatsapp" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                WhatsApp (Opsional)
                            </label>
                            <input id="whatsapp"
                                   type="tel"
                                   name="whatsapp"
                                   value="{{ old('whatsapp', $user->whatsapp) }}"
                                   placeholder="081234567890"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                        </div>

                        <div class="space-y-1.5">
                            <label for="email" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                                Email
                            </label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="warga@email.com"
                                   class="w-full px-4 py-2.5 bg-white border border-[#E2E8E4] rounded-lg text-sm text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20">
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <span class="text-xs text-gray-500">
                            Sistem akan otomatis mencocokkan kembali data Anda dengan master kependudukan desa.
                        </span>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Perbaiki Data & Ajukan Ulang</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
