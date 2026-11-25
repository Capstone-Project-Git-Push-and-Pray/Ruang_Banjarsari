<x-app-layout>
    @php
        $userId = Auth::id();
        $totalLaporan = $totalLaporan ?? \App\Models\Pengaduan::where('user_id', $userId)->count();
        $countDiajukan = $countDiajukan ?? \App\Models\Pengaduan::where('user_id', $userId)->where('status', \App\Enums\StatusLaporan::Diajukan)->count();
        $countDitinjau = $countDitinjau ?? \App\Models\Pengaduan::where('user_id', $userId)->where('status', \App\Enums\StatusLaporan::Ditinjau)->count();
        $countDiproses = $countDiproses ?? \App\Models\Pengaduan::where('user_id', $userId)->whereIn('status', [
            \App\Enums\StatusLaporan::Diproses,
            \App\Enums\StatusLaporan::Ditampung,
            \App\Enums\StatusLaporan::DalamPembahasan,
        ])->count();
        $countSelesai = $countSelesai ?? \App\Models\Pengaduan::where('user_id', $userId)->whereIn('status', [
            \App\Enums\StatusLaporan::Selesai,
            \App\Enums\StatusLaporan::Direalisasikan,
        ])->count();
        $countDitolak = $countDitolak ?? \App\Models\Pengaduan::where('user_id', $userId)->whereIn('status', [
            \App\Enums\StatusLaporan::Ditolak,
            \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti,
        ])->count();
        $laporanTerbaru = $laporanTerbaru ?? \App\Models\Pengaduan::where('user_id', $userId)
            ->with(['kategori'])
            ->withCount('komentar')
            ->latest()
            ->take(5)
            ->get();
    @endphp

    <div class="max-w-5xl mx-auto space-y-8">
        <span class="sr-only">Dashboard Warga</span>

        {{-- Pemberitahuan Sanksi (PBI #48) --}}
        @php
            $sanksiAktifList = collect(\App\Enums\FiturSanksi::cases())
                ->map(fn ($fitur) => Auth::user()->sanksiBerlaku($fitur))
                ->filter();
            $jumlahSanksiBerlaku = $sanksiAktifList->count();
        @endphp
        @foreach ($sanksiAktifList as $sanksiAktif)
            @include('warga.sanksi.partials.banner', ['sanksi' => $sanksiAktif])
        @endforeach
        @if ($sanksiAktifList->isEmpty() && Auth::user()->sanksi()->where('mulai_pada', '>=', now()->subDays(30))->exists())
            <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-2xl text-xs shadow-xs flex flex-wrap items-center justify-between gap-2">
                <span><strong class="font-bold">Peringatan.</strong> Konten Anda pernah dihapus Admin Desa dalam 30 hari terakhir. Pelanggaran berikutnya dapat membatasi fitur akun.</span>
                <a href="{{ route('warga.sanksi.index') }}" class="font-semibold text-amber-800 hover:underline">Lihat rincian &rarr;</a>
            </div>
        @endif

        {{-- ==================== 1. KARTU HERO UTAMA (FOREST GREEN) ==================== --}}
        <div class="relative overflow-hidden bg-[#185840] rounded-[28px] p-6 sm:p-8 lg:p-10 shadow-sm text-white">
            {{-- Hiasan Garis Gelombang Kontur (SVG Waves) --}}
            <svg class="absolute top-0 right-0 h-full w-2/3 max-w-lg pointer-events-none opacity-20" viewBox="0 0 500 280" fill="none" preserveAspectRatio="none">
                <path d="M-50 40 C 120 10, 240 70, 520 20" stroke="white" stroke-width="1.75" fill="none" />
                <path d="M-30 85 C 140 55, 260 115, 520 65" stroke="white" stroke-width="1.75" fill="none" />
                <path d="M-10 130 C 160 100, 280 160, 520 110" stroke="white" stroke-width="1.75" fill="none" />
                <path d="M10 175 C 180 145, 300 205, 520 155" stroke="white" stroke-width="1.75" fill="none" />
                <path d="M30 220 C 200 190, 320 250, 520 200" stroke="white" stroke-width="1.75" fill="none" />
            </svg>

            <div class="relative z-10">
                {{-- Sapaan Nama Warga --}}
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Halo, {{ Auth::user()->nama_lengkap }}!
                </h1>

                {{-- Deskripsi Sambutan --}}
                <p class="text-xs sm:text-sm text-white/80 leading-relaxed mt-2.5 max-w-xl">
                    Sampaikan aspirasi atau laporkan permasalahan lingkungan Anda di Desa Banjarsari secara transparan, mudah, dan terpantau langsung oleh aparatur desa.
                </p>

                {{-- Tombol Aksi Utama --}}
                <div class="flex items-center gap-3 mt-6 flex-wrap">
                    <a href="{{ route('warga.pengaduan.create') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white hover:bg-gray-100 text-[#185840] text-xs sm:text-sm font-bold rounded-full shadow-sm transition-all duration-200">
                        <svg class="w-4 h-4 text-[#185840]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Buat Laporan Baru</span>
                    </a>

                    <a href="{{ route('warga.pengaduan.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold rounded-full border border-white/20 backdrop-blur-xs transition-all duration-200">
                        <span>Laporan Saya</span>
                    </a>

                    <a href="{{ route('warga.aspirasi.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold rounded-full border border-white/20 transition-all">
                        <span>Aspirasi Warga</span>
                    </a>
                </div>

                {{-- Baris Rekapitulasi Statistik Status --}}
                <div class="mt-8 pt-6 border-t border-white/15 grid grid-cols-3 sm:grid-cols-6 gap-4 sm:gap-6">
                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $totalLaporan }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Total laporan</span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $countDiajukan }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Diajukan</span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $countDitinjau }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Ditinjau</span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $countDiproses }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Diproses</span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $countSelesai }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Selesai</span>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $countDitolak }}</span>
                        <span class="text-[11px] sm:text-xs text-white/70 mt-0.5 font-medium">Ditolak</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== 2. MENU LAYANAN ==================== --}}
        <div>
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">Menu Layanan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Semua yang Anda butuhkan ada di sini</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mt-4">
                {{-- Kartu: Buat Laporan Baru --}}
                <a href="{{ route('warga.pengaduan.create') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-xs hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 group-hover:text-[#1F6B4F] transition-colors">Buat Laporan Baru</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Sampaikan pengaduan atau aspirasi Anda kepada desa</p>
                    </div>
                    <div class="mt-4 pt-2">
                        <span class="text-xs font-semibold text-[#1F6B4F] inline-flex items-center gap-1 group-hover:gap-1.5 transition-all">
                            <span>Mulai ajukan</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- Kartu: Laporan Saya --}}
                <a href="{{ route('warga.pengaduan.index') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-xs hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col justify-between relative">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <span class="w-6 h-6 rounded-full bg-[#185840] text-white text-[11px] font-bold flex items-center justify-center shadow-xs">
                                {{ $totalLaporan }}
                            </span>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 group-hover:text-[#1F6B4F] transition-colors">Laporan Saya</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Pantau status dan riwayat semua laporan Anda</p>
                    </div>
                    <div class="mt-4 pt-2">
                        <span class="text-xs font-semibold text-[#1F6B4F] inline-flex items-center gap-1 group-hover:gap-1.5 transition-all">
                            <span>Lihat semua riwayat</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- Kartu: Aspirasi Warga (PBI #39) --}}
                <a href="{{ route('warga.aspirasi.index') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-xs hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-1.414-.586M15 4H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l4-4h4a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 group-hover:text-[#1F6B4F] transition-colors">Aspirasi Warga</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Baca, dukung, dan diskusikan usulan warga lain</p>
                    </div>
                    <div class="mt-4 pt-2">
                        <span class="text-xs font-semibold text-[#1F6B4F] inline-flex items-center gap-1 group-hover:gap-1.5 transition-all">
                            <span>Lihat aspirasi</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- Kartu: Status Sanksi Akun (PBI #48) --}}
                <a href="{{ route('warga.sanksi.index') }}" class="group bg-white rounded-2xl p-5 border shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between {{ $jumlahSanksiBerlaku > 0 ? 'border-rose-200 hover:border-rose-300' : 'border-gray-100 hover:border-emerald-200' }}">
                    <div>
                        <div class="flex items-center justify-between">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $jumlahSanksiBerlaku > 0 ? 'bg-rose-50 text-rose-600' : 'bg-[#EAF5EE] text-[#1F6B4F]' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            @if ($jumlahSanksiBerlaku > 0)
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold">{{ $jumlahSanksiBerlaku }} berlaku</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">Aman</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 transition-colors {{ $jumlahSanksiBerlaku > 0 ? 'group-hover:text-rose-600' : 'group-hover:text-[#1F6B4F]' }}">Status Sanksi Akun</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Lihat sanksi yang berlaku, peringatan, dan riwayat pelanggaran akun Anda</p>
                    </div>
                    <div class="mt-4 pt-2">
                        <span class="text-xs font-semibold inline-flex items-center gap-1 group-hover:gap-1.5 transition-all {{ $jumlahSanksiBerlaku > 0 ? 'text-rose-600' : 'text-[#1F6B4F]' }}">
                            <span>Lihat status sanksi</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- Kartu: Profil Saya --}}
                <a href="{{ route('profile.edit') }}" class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-xs hover:shadow-md hover:border-emerald-200 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 group-hover:text-[#1F6B4F] transition-colors">Profil Saya</h3>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Perbarui kontak dan ubah kata sandi</p>
                    </div>
                    <div class="mt-4 pt-2">
                        <span class="text-xs font-semibold text-[#1F6B4F] inline-flex items-center gap-1 group-hover:gap-1.5 transition-all">
                            <span>Kelola akun</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </span>
                    </div>
                </a>

                {{-- Kartu: Keluar (Logout dengan Validasi Popup) --}}
                <form method="POST" action="{{ route('logout') }}" class="flex"
                      data-konfirmasi="Anda perlu masuk kembali untuk memakai layanan SILAFA."
                      data-konfirmasi-judul="Keluar dari akun?"
                      data-konfirmasi-tombol="Keluar"
                      data-konfirmasi-ikon="keluar"
                      data-konfirmasi-jenis="bahaya">
                    @csrf
                    <button type="submit" class="w-full text-left group bg-white rounded-2xl p-5 border border-gray-100 shadow-xs hover:shadow-md hover:border-rose-200 transition-all duration-200 flex flex-col justify-between cursor-pointer">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-gray-50 text-gray-500 group-hover:bg-rose-50 group-hover:text-rose-600 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-sm sm:text-base text-gray-900 mt-4 group-hover:text-rose-600 transition-colors">Keluar</h3>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">Akhiri sesi Anda dengan aman</p>
                        </div>
                        <div class="mt-4 pt-2">
                            <span class="text-xs font-semibold text-gray-600 group-hover:text-rose-600 inline-flex items-center gap-1 transition-colors">
                                <span>Keluar dari akun</span>
                            </span>
                        </div>
                    </button>
                </form>
            </div>
        </div>

        {{-- ==================== 3. LAPORAN TERBARU ==================== --}}
        <div>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">Laporan Terbaru</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Perkembangan terkini dari laporan yang Anda kirim</p>
                </div>
                <a href="{{ route('warga.pengaduan.index') }}" class="text-xs font-semibold text-gray-700 hover:text-[#1F6B4F] inline-flex items-center gap-1 transition-colors">
                    <span>Lihat Semua Riwayat</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs divide-y divide-gray-100 overflow-hidden mt-4">
                @forelse ($laporanTerbaru as $laporan)
                    @php
                        // Gaya pill status ber-titik persis seperti referensi visual
                        [$pillBg, $dotBg] = match ($laporan->status) {
                            \App\Enums\StatusLaporan::Diajukan => ['bg-gray-100 text-gray-700', 'bg-gray-500'],
                            \App\Enums\StatusLaporan::Ditinjau => ['bg-amber-50 text-amber-700 border border-amber-200/60', 'bg-amber-500'],
                            \App\Enums\StatusLaporan::Diproses => ['bg-blue-50 text-blue-600 border border-blue-200/60', 'bg-blue-600'],
                            \App\Enums\StatusLaporan::Ditampung => ['bg-zinc-100 text-zinc-700 border border-zinc-200/60', 'bg-zinc-500'],
                            \App\Enums\StatusLaporan::DalamPembahasan => ['bg-sky-50 text-sky-700 border border-sky-200/60', 'bg-sky-500'],
                            \App\Enums\StatusLaporan::Selesai,
                            \App\Enums\StatusLaporan::Direalisasikan => ['bg-emerald-50 text-emerald-700 border border-emerald-200/60', 'bg-emerald-500'],
                            \App\Enums\StatusLaporan::Ditolak,
                            \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => ['bg-rose-50 text-rose-700 border border-rose-200/60', 'bg-rose-500'],
                        };
                    @endphp

                    <a href="{{ route('warga.pengaduan.show', $laporan) }}" class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-[#F8FAF9] transition-colors group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-sm text-gray-900 truncate group-hover:text-[#1F6B4F] transition-colors">
                                        {{ $laporan->judul }}
                                    </h3>
                                    @if ($laporan->komentar_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <svg class="w-3 h-3 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            <span>{{ $laporan->komentar_count }} balasan baru</span>
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-1 truncate">
                                    @if ($laporan->isAspirasi())
                                        <span class="font-medium text-emerald-800">Aspirasi</span> &bull;
                                    @endif
                                    {{ $laporan->kategori->nama ?? 'Umum' }} &bull; {{ $laporan->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shadow-xs {{ $pillBg }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotBg }}"></span>
                                <span>{{ $laporan->status->label() }}</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-8 sm:p-12 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Belum Ada Laporan</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Anda belum pernah mengajukan laporan pengaduan atau aspirasi warga.</p>
                        <div class="mt-4">
                            <a href="{{ route('warga.pengaduan.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#185840] hover:bg-[#124230] text-white text-xs font-semibold rounded-xl transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Mulai Buat Laporan</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
