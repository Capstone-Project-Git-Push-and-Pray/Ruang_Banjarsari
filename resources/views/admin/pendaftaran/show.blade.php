<x-admin-layout>
    @php
        $title = 'Tinjau Pendaftaran: ' . $user->nama_lengkap;
        $breadcrumb = 'Rincian Verifikasi';
    @endphp

    <div class="space-y-6 max-w-6xl mx-auto" x-data="{ showRejectModal: false }">

        {{-- ==================== 1. TOP HEADER & BREADCRUMB ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('admin.pendaftaran.index') }}" class="hover:text-[#1F6B4F] transition-colors flex items-center gap-1 font-semibold text-[#1F6B4F]">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Daftar Antrean</span>
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700">Rincian Verifikasi</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#EAF5EE] text-[#1F6B4F] font-extrabold text-base flex items-center justify-center shrink-0 border border-emerald-100 shadow-2xs">
                        {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="font-extrabold text-xl sm:text-2xl text-gray-900 leading-tight">
                            Tinjau Pendaftaran: {{ $user->nama_lengkap }}
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                            Periksa kesesuaian data yang diisikan warga berdampingan dengan master data kependudukan desa.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Menunggu Tinjauan</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- FLASH & ERROR MESSAGES --}}
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-xs space-y-1 shadow-xs">
                <div class="flex items-center gap-2 font-bold text-red-900">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                    <span>Terdapat kendala pada permohonan:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PERINGATAN JIKA NIK TIDAK DITEMUKAN DI MASTER KEPENDUDUKAN --}}
        @if (! $kependudukan)
            <div class="p-6 bg-red-50/80 border border-red-200 rounded-3xl flex items-start gap-4 shadow-xs">
                <div class="w-11 h-11 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5 border border-red-200">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1.5 flex-1">
                    <h4 class="font-bold text-red-900 text-sm sm:text-base">
                        NIK Pendaftar Tidak Ditemukan dalam Data Kependudukan Desa
                    </h4>
                    <p class="text-xs sm:text-sm text-red-700 leading-relaxed">
                        Nomor Induk Kependudukan (NIK) <span class="font-mono font-bold">{{ $user->nik }}</span> tidak tercatat pada basis data kependudukan resmi Desa Banjarsari. Pastikan apakah pemohon merupakan warga baru yang belum tercatat atau terdapat kekeliruan nomor NIK saat registrasi.
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <a href="{{ route('admin.kependudukan.index', ['search' => $user->nik]) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white border border-red-200 text-xs font-semibold text-red-800 hover:bg-red-50 transition shadow-2xs">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            <span>Periksa di Data Kependudukan &rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== 2. TABEL PERBANDINGAN BERDAMPINGAN (PBI #17) ==================== --}}
        <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-[#E2E8E4] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="split" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base sm:text-lg text-gray-900">
                            Perbandingan Data Pendaftar vs Master Kependudukan
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Sistem mendeteksi perbedaan otomatis pada field yang tidak cocok.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 rounded-xl bg-gray-50 border border-[#E2E8E4] text-xs">
                        <span class="text-gray-400">NIK:</span>
                        <strong class="font-mono text-gray-900 ml-1">{{ $user->nik }}</strong>
                    </div>
                    <a href="{{ route('admin.kependudukan.index', ['search' => $user->nik]) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-[#E2E8E4] bg-white text-xs font-semibold text-gray-700 hover:text-[#1F6B4F] hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="database" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        <span>Data Kependudukan</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F8FAF9] text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-[#E2E8E4]">
                        <tr>
                            <th scope="col" class="py-4 px-6 w-1/5">Field Data</th>
                            <th scope="col" class="py-4 px-6 w-2/5">Data Isian Pendaftar</th>
                            <th scope="col" class="py-4 px-6 w-2/5">Data Master Desa (SIAK)</th>
                            <th scope="col" class="py-4 px-6 text-center w-28">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8E4] text-xs sm:text-sm">
                        @foreach ($perbandingan as $key => $item)
                            <tr class="{{ $item['sama'] ? 'bg-white' : 'bg-amber-50/40' }} hover:bg-gray-50/70 transition-colors">
                                {{-- Nama Field --}}
                                <td class="py-4 px-6 font-bold text-gray-700">
                                    {{ $item['label'] }}
                                </td>

                                {{-- Isian Pendaftar --}}
                                <td class="py-4 px-6 text-gray-900 font-semibold">
                                    @if ($key === 'tanggal_lahir' && $user->tanggal_lahir)
                                        {{ $user->tanggal_lahir->format('d F Y') }} <span class="text-xs text-gray-400 font-mono font-normal">({{ $user->tanggal_lahir->format('Y-m-d') }})</span>
                                    @else
                                        {{ $item['pendaftar'] ?: '-' }}
                                    @endif
                                </td>

                                {{-- Master Kependudukan --}}
                                <td class="py-4 px-6">
                                    @if ($kependudukan)
                                        <div class="text-gray-900 font-medium">
                                            @if ($key === 'tanggal_lahir' && $kependudukan->tanggal_lahir)
                                                {{ $kependudukan->tanggal_lahir->format('d F Y') }} <span class="text-xs text-gray-400 font-mono">({{ $kependudukan->tanggal_lahir->format('Y-m-d') }})</span>
                                            @else
                                                {{ $item['kependudukan'] ?: '-' }}
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-rose-500 italic font-medium flex items-center gap-1">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            <span>Tidak ditemukan</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Status Cocok / Beda --}}
                                <td class="py-4 px-6 text-center">
                                    @if ($item['sama'])
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200/80">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <span>Cocok</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                            <span>Berbeda</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- INFORMASI KONTAK & METADATA PENDAFTAR --}}
            <div class="p-6 bg-[#F8FAF9] border-t border-[#E2E8E4] grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-[#E2E8E4] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Kontak Telepon / WhatsApp</span>
                        <span class="font-bold text-gray-900 text-sm mt-0.5 block">{{ $user->telepon ?: '-' }}</span>
                        @if ($user->whatsapp)
                            <span class="text-gray-500 text-[11px] block">WA: {{ $user->whatsapp }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-[#E2E8E4] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Alamat Email</span>
                        <span class="font-bold text-gray-900 text-sm mt-0.5 block">{{ $user->email ?: '-' }}</span>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-[#E2E8E4] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Waktu Pendaftaran</span>
                        <span class="font-bold text-gray-900 text-sm mt-0.5 block">{{ $user->created_at->format('d M Y, H:i') }} WIB</span>
                        <span class="text-gray-400 text-[11px] block">({{ $user->created_at->diffForHumans() }})</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== 3. KARTU AKSI KEPUTUSAN ADMIN (PBI #18) ==================== --}}
        <div class="bg-white rounded-3xl border border-[#E2E8E4] p-6 sm:p-7 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1">
                <h4 class="font-extrabold text-base sm:text-lg text-gray-900">Tentukan Keputusan Verifikasi</h4>
                <p class="text-xs sm:text-sm text-gray-500">
                    Setujui akun jika data valid, atau tolak dengan menyertakan alasan perbaikan yang jelas kepada warga.
                </p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end shrink-0">
                {{-- Tombol Tolak (Memicu Modal/Form) --}}
                <button type="button"
                        @click="showRejectModal = true"
                        class="px-5 py-2.5 rounded-xl border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 text-xs sm:text-sm font-semibold transition-colors flex items-center gap-2 cursor-pointer shadow-2xs">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    <span>Tolak Pendaftaran</span>
                </button>

                {{-- Form Setujui --}}
                <form method="POST" action="{{ route('admin.pendaftaran.setujui', $user) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran warga ini? Akun warga akan langsung berstatus Aktif.')"
                            class="px-6 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs sm:text-sm font-semibold transition-all duration-200 shadow-xs flex items-center gap-2 cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Setujui Pendaftaran</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL FORM PENOLAKAN DENGAN ALASAN WAJIB ==================== --}}
        <div x-show="showRejectModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;">
            <div @click.away="showRejectModal = false"
                 class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-[#E2E8E4] shadow-2xl space-y-5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 border border-rose-200">
                            <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-gray-900">Tolak Pendaftaran Akun</h3>
                            <p class="text-xs text-gray-500">Pendaftar: {{ $user->nama_lengkap }} ({{ $user->nik }})</p>
                        </div>
                    </div>
                    <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.pendaftaran.tolak', $user) }}" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label for="alasan_penolakan" class="text-xs sm:text-sm font-semibold text-gray-800 block">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="alasan_penolakan"
                                  name="alasan_penolakan"
                                  rows="4"
                                  required
                                  placeholder="Contoh: Nama lengkap atau tanggal lahir tidak sesuai dengan KTP fisik Anda. Mohon periksa kembali ejaan data Anda."
                                  class="w-full px-4 py-3 bg-white border border-[#E2E8E4] rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20"></textarea>
                        <p class="text-[11px] text-gray-400">
                            Alasan ini akan ditampilkan secara langsung di halaman status warga agar dapat diperbaiki dan diajukan ulang.
                        </p>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-[#E2E8E4]">
                        <button type="button"
                                @click="showRejectModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs sm:text-sm font-semibold transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
