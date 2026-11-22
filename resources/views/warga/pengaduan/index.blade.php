<x-app-layout>
    <div class="py-6 sm:py-8 min-h-screen bg-[#F9FAF9]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Tombol Navigasi Kembali ke Beranda --}}
            <div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            {{-- Notifikasi Sukses Pembuatan Laporan --}}
            @if (session('status') === 'laporan-berhasil-diajukan')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <span class="font-bold text-sm text-emerald-950 block">Laporan Berhasil Diajukan!</span>
                            <span class="text-xs text-emerald-700">Laporan Anda telah tercatat dengan status Diajukan dan segera ditinjau oleh aparatur desa.</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Header Halaman: Judul, Deskripsi & Tombol Buat Laporan Baru --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        Laporan Saya
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Daftar pengaduan dan aspirasi yang telah Anda sampaikan ke pemerintah desa.
                    </p>
                </div>
                <div>
                    <a href="{{ route('warga.pengaduan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#185840] hover:bg-[#124230] text-white text-xs sm:text-sm font-semibold rounded-full shadow-sm hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Laporan Baru</span>
                    </a>
                </div>
            </div>

            {{-- Alpine Container untuk Interaktivitas Pencarian, Tab Status & Jenis --}}
            <div x-data="{
                searchQuery: '',
                activeStatusFilter: 'all',
                activeTypeFilter: 'all',
                sortOrder: 'terbaru',
                reports: {{ Js::from($pengaduans->map(fn($l) => [
                    'id' => $l->id,
                    'judul' => strtolower($l->judul),
                    'jenis' => $l->jenis->value,
                    'statusGroup' => match($l->status) {
                        \App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::Direalisasikan => 'selesai',
                        \App\Enums\StatusLaporan::Ditolak, \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'tidak_dilanjutkan',
                        default => 'sedang_berjalan',
                    },
                    'created_at' => $l->created_at->timestamp,
                ])) }},
                get filteredReports() {
                    return this.reports.filter(r => {
                        const matchesSearch = this.searchQuery.trim() === '' || r.judul.includes(this.searchQuery.toLowerCase().trim());
                        const matchesStatus = this.activeStatusFilter === 'all' || r.statusGroup === this.activeStatusFilter;
                        const matchesType = this.activeTypeFilter === 'all' || r.jenis === this.activeTypeFilter;
                        return matchesSearch && matchesStatus && matchesType;
                    });
                },
                isShown(id) {
                    return this.filteredReports.some(r => r.id === id);
                }
            }" class="space-y-6">

                {{-- 4 Kartu Metrik Ringkasan (Dapat Diklik Sebagai Filter) --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    {{-- Card 1: Semua laporan --}}
                    <button type="button" @click="activeStatusFilter = 'all'"
                        :class="activeStatusFilter === 'all' ? 'border-[#185840] ring-2 ring-[#185840]/20 bg-[#F4F9F6]' : 'border-gray-200 bg-white hover:border-gray-300'"
                        class="p-4 sm:p-5 rounded-2xl border text-left transition-all duration-200 cursor-pointer flex items-center gap-3.5 sm:gap-4 shadow-2xs">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 text-[#185840] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xl sm:text-2xl font-bold text-gray-900 leading-none">{{ $totalCount }}</span>
                            <span class="block text-xs sm:text-sm text-gray-500 mt-1 font-medium">Semua laporan</span>
                        </div>
                    </button>

                    {{-- Card 2: Sedang berjalan --}}
                    <button type="button" @click="activeStatusFilter = 'sedang_berjalan'"
                        :class="activeStatusFilter === 'sedang_berjalan' ? 'border-blue-600 ring-2 ring-blue-500/20 bg-blue-50/40' : 'border-gray-200 bg-white hover:border-gray-300'"
                        class="p-4 sm:p-5 rounded-2xl border text-left transition-all duration-200 cursor-pointer flex items-center gap-3.5 sm:gap-4 shadow-2xs">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xl sm:text-2xl font-bold text-gray-900 leading-none">{{ $sedangBerjalanCount }}</span>
                            <span class="block text-xs sm:text-sm text-gray-500 mt-1 font-medium">Sedang berjalan</span>
                        </div>
                    </button>

                    {{-- Card 3: Selesai --}}
                    <button type="button" @click="activeStatusFilter = 'selesai'"
                        :class="activeStatusFilter === 'selesai' ? 'border-[#185840] ring-2 ring-[#185840]/20 bg-[#F4F9F6]' : 'border-gray-200 bg-white hover:border-gray-300'"
                        class="p-4 sm:p-5 rounded-2xl border text-left transition-all duration-200 cursor-pointer flex items-center gap-3.5 sm:gap-4 shadow-2xs">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xl sm:text-2xl font-bold text-gray-900 leading-none">{{ $selesaiCount }}</span>
                            <span class="block text-xs sm:text-sm text-gray-500 mt-1 font-medium">Selesai</span>
                        </div>
                    </button>

                    {{-- Card 4: Tidak dilanjutkan --}}
                    <button type="button" @click="activeStatusFilter = 'tidak_dilanjutkan'"
                        :class="activeStatusFilter === 'tidak_dilanjutkan' ? 'border-rose-600 ring-2 ring-rose-500/20 bg-rose-50/40' : 'border-gray-200 bg-white hover:border-gray-300'"
                        class="p-4 sm:p-5 rounded-2xl border text-left transition-all duration-200 cursor-pointer flex items-center gap-3.5 sm:gap-4 shadow-2xs">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-xl sm:text-2xl font-bold text-gray-900 leading-none">{{ $tidakDilanjutkanCount }}</span>
                            <span class="block text-xs sm:text-sm text-gray-500 mt-1 font-medium">Tidak dilanjutkan</span>
                        </div>
                    </button>
                </div>

                {{-- Bar Pencarian, Filter Kategori Jenis & Pengurutan --}}
                <div class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        {{-- Input Pencarian Judul --}}
                        <div class="relative flex-1 sm:max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input x-model="searchQuery" type="text" placeholder="Cari judul laporan"
                                class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-full text-xs sm:text-sm placeholder-gray-400 focus:outline-none focus:border-[#185840] focus:ring-1 focus:ring-[#185840] shadow-2xs transition-colors">
                        </div>

                        {{-- Filter Pill: Semua, Pengaduan, Aspirasi & Sortir --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" @click="activeTypeFilter = 'all'"
                                :class="activeTypeFilter === 'all' ? 'bg-[#185840] text-white shadow-2xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors cursor-pointer">
                                Semua
                            </button>
                            <button type="button" @click="activeTypeFilter = 'pengaduan'"
                                :class="activeTypeFilter === 'pengaduan' ? 'bg-[#185840] text-white shadow-2xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors cursor-pointer">
                                Pengaduan
                            </button>
                            <button type="button" @click="activeTypeFilter = 'aspirasi'"
                                :class="activeTypeFilter === 'aspirasi' ? 'bg-[#185840] text-white shadow-2xs' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200'"
                                class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors cursor-pointer">
                                Aspirasi
                            </button>

                            {{-- Sortir Dropdown --}}
                            <div class="relative" x-data="{ openSort: false }">
                                <button type="button" @click="openSort = !openSort"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white border border-gray-200 rounded-full text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-2xs cursor-pointer"
                                    :class="openSort ? 'border-[#185840] text-[#185840]' : ''">
                                    <span x-text="sortOrder === 'terbaru' ? 'Terbaru' : 'Terlama'">Terbaru</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
                                         :class="openSort ? 'rotate-180 text-[#185840]' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openSort"
                                     @click.outside="openSort = false"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute right-0 mt-1.5 w-36 bg-white rounded-2xl shadow-xl border border-gray-100 z-30 text-xs focus:outline-none overscroll-contain">
                                    <button type="button"
                                            @click="sortOrder = 'terbaru'; openSort = false"
                                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                            :class="sortOrder === 'terbaru'
                                                ? 'bg-[#185840] text-white font-semibold'
                                                : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                        <span>Terbaru</span>
                                        <svg x-show="sortOrder === 'terbaru'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button type="button"
                                            @click="sortOrder = 'terlama'; openSort = false"
                                            class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                            :class="sortOrder === 'terlama'
                                                ? 'bg-[#185840] text-white font-semibold'
                                                : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                        <span>Terlama</span>
                                        <svg x-show="sortOrder === 'terlama'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Counter Text --}}
                    <div class="text-xs text-gray-500 font-medium">
                        Menampilkan <span x-text="filteredReports.length" class="font-bold text-gray-800"></span> dari {{ $totalCount }} laporan
                    </div>
                </div>

                {{-- Daftar Laporan Card-Based --}}
                @if ($pengaduans->isEmpty())
                    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-2xs">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-[#185840] flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Belum Ada Laporan</h3>
                        <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">Anda belum pernah mengajukan laporan pengaduan atau aspirasi warga.</p>
                        <div class="mt-6">
                            <a href="{{ route('warga.pengaduan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#185840] hover:bg-[#124230] text-white text-xs sm:text-sm font-semibold rounded-full shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Ajukan Pengaduan Sekarang</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col gap-4" :class="sortOrder === 'terlama' ? 'flex-col-reverse' : 'flex-col'">
                        @foreach ($pengaduans as $laporan)
                            @php
                                $isAspirasi = $laporan->isAspirasi();
                                $isPengaduan = $laporan->isPengaduan();

                                // Warna Titik & Pill Status
                                $statusDot = match ($laporan->status) {
                                    \App\Enums\StatusLaporan::Diajukan => 'bg-amber-500',
                                    \App\Enums\StatusLaporan::Ditinjau => 'bg-blue-600',
                                    \App\Enums\StatusLaporan::Diproses => 'bg-indigo-600',
                                    \App\Enums\StatusLaporan::Ditampung => 'bg-zinc-700',
                                    \App\Enums\StatusLaporan::DalamPembahasan => 'bg-sky-600',
                                    \App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::Direalisasikan => 'bg-[#185840]',
                                    \App\Enums\StatusLaporan::Ditolak, \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'bg-rose-600',
                                };

                                $statusPill = match ($laporan->status) {
                                    \App\Enums\StatusLaporan::Diajukan => 'bg-amber-50 text-amber-800 border-amber-200',
                                    \App\Enums\StatusLaporan::Ditinjau => 'bg-blue-50 text-blue-700 border-blue-200',
                                    \App\Enums\StatusLaporan::Diproses => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    \App\Enums\StatusLaporan::Ditampung => 'bg-zinc-100 text-zinc-700 border-zinc-300',
                                    \App\Enums\StatusLaporan::DalamPembahasan => 'bg-sky-50 text-sky-700 border-sky-200',
                                    \App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::Direalisasikan => 'bg-emerald-50 text-[#185840] border-emerald-200',
                                    \App\Enums\StatusLaporan::Ditolak, \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'bg-rose-50 text-rose-700 border-rose-200',
                                };

                                if ($isAspirasi) {
                                    $isAspirasiRejected = $laporan->status === \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti;
                                    $currentStep = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::Diajukan => 1,
                                        \App\Enums\StatusLaporan::Ditinjau => 2,
                                        \App\Enums\StatusLaporan::Ditampung => 3,
                                        \App\Enums\StatusLaporan::DalamPembahasan, \App\Enums\StatusLaporan::Diproses => 4,
                                        \App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 5,
                                        default => 1,
                                    };
                                    $barWidth = match ($currentStep) {
                                        1 => 20,
                                        2 => 40,
                                        3 => 60,
                                        4 => 80,
                                        5 => 100,
                                        default => 20,
                                    };
                                    $barColor = $isAspirasiRejected ? 'bg-rose-600' : 'bg-[#185840]';
                                    $tindakLanjutLabel = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'Tidak Ditindaklanjuti',
                                        \App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai => 'Direalisasikan',
                                        default => 'Keputusan Akhir',
                                    };
                                    $mobileStepLabel = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'Tidak Ditindaklanjuti',
                                        \App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai => 'Direalisasikan',
                                        \App\Enums\StatusLaporan::DalamPembahasan => 'Dalam Pembahasan',
                                        \App\Enums\StatusLaporan::Ditampung => 'Ditampung',
                                        \App\Enums\StatusLaporan::Ditinjau => 'Ditinjau',
                                        default => 'Diajukan',
                                    };
                                    $mobileNextLabel = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::Diajukan => 'Lanjut: Ditinjau',
                                        \App\Enums\StatusLaporan::Ditinjau => 'Lanjut: Ditampung',
                                        \App\Enums\StatusLaporan::Ditampung => 'Lanjut: Pembahasan',
                                        \App\Enums\StatusLaporan::DalamPembahasan => 'Lanjut: Keputusan',
                                        \App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai => 'Selesai Direalisasikan',
                                        \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'Keputusan Final',
                                        default => '',
                                    };
                                } else {
                                    $isPengaduanRejected = $laporan->status === \App\Enums\StatusLaporan::Ditolak;
                                    $currentStep = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::Diajukan => 1,
                                        \App\Enums\StatusLaporan::Ditinjau => 2,
                                        \App\Enums\StatusLaporan::Diproses => 3,
                                        \App\Enums\StatusLaporan::Selesai, \App\Enums\StatusLaporan::Ditolak => 4,
                                        default => 1,
                                    };
                                    $barWidth = match ($currentStep) {
                                        1 => 25,
                                        2 => 50,
                                        3 => 75,
                                        4 => 100,
                                        default => 25,
                                    };
                                    $barColor = $isPengaduanRejected ? 'bg-rose-600' : 'bg-[#185840]';
                                    $pengaduanFinalLabel = $isPengaduanRejected ? 'Ditolak' : 'Selesai';
                                    $mobileStepLabel = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::Ditolak => 'Ditolak',
                                        \App\Enums\StatusLaporan::Selesai => 'Selesai',
                                        \App\Enums\StatusLaporan::Diproses => 'Diproses',
                                        \App\Enums\StatusLaporan::Ditinjau => 'Ditinjau',
                                        default => 'Diajukan',
                                    };
                                    $mobileNextLabel = match ($laporan->status) {
                                        \App\Enums\StatusLaporan::Diajukan => 'Lanjut: Ditinjau',
                                        \App\Enums\StatusLaporan::Ditinjau => 'Lanjut: Diproses',
                                        \App\Enums\StatusLaporan::Diproses => 'Lanjut: Selesai',
                                        \App\Enums\StatusLaporan::Selesai => 'Laporan Selesai',
                                        \App\Enums\StatusLaporan::Ditolak => 'Laporan Ditolak',
                                        default => '',
                                    };
                                }
                            @endphp

                            <a href="{{ route('warga.pengaduan.show', $laporan) }}"
                                x-show="isShown({{ $laporan->id }})"
                                class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs hover:shadow-md hover:border-gray-300 transition-all duration-200 p-4 sm:p-6 block group">

                                {{-- Bagian Atas: Icon Jenis, Judul & Metadata, Status Pill --}}
                                <div class="flex items-start justify-between gap-3 sm:gap-4">
                                    <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
                                        {{-- Icon Jenis --}}
                                        @if ($isAspirasi)
                                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 border border-sky-100/80 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100/80 flex items-center justify-center shrink-0 mt-0.5">
                                                <img src="{{ asset('images/icon-pengaduan.png') }}"
                                                     alt="Ikon Pengaduan"
                                                     class="w-5 h-5 object-contain [filter:invert(24%)_sepia(85%)_saturate(3078%)_hue-rotate(335deg)_brightness(91%)_contrast(93%)]">
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            {{-- Judul Laporan & Balasan Baru --}}
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-[#185840] transition-colors leading-snug">
                                                    {{ $laporan->judul }}
                                                </h3>
                                                @if ($laporan->komentar->isNotEmpty())
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-[#185840] border border-emerald-200/80">
                                                        <svg class="w-3 h-3 text-[#185840]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                        <span>{{ $laporan->komentar->count() }} balasan baru</span>
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Metadata (Badge Jenis, Kategori, Tanggal, Foto) --}}
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1.5 text-xs">
                                                @if ($isAspirasi)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-sky-100 text-sky-700">Aspirasi</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-700">Pengaduan</span>
                                                @endif
                                                <span class="text-gray-600 font-medium">{{ $laporan->kategori->nama ?? 'Umum' }}</span>
                                                <span class="text-gray-400">&bull;&nbsp;{{ $laporan->created_at->format('j M Y, H.i') }} WIB</span>
                                                @if ($laporan->lampiran->isNotEmpty())
                                                    <span class="text-gray-400 inline-flex items-center gap-1">
                                                        &bull;&nbsp;<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        {{ $laporan->lampiran->count() }} foto
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Status Pill dengan Dot Berwarna & Chevron --}}
                                    <div class="shrink-0">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border shadow-2xs {{ $statusPill }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                            <span>{{ $laporan->status->label() }}</span>
                                            <svg class="w-3.5 h-3.5 opacity-60 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bagian Bawah: Stepper Track Alur Proses --}}
                                <div class="mt-4 sm:mt-5 pt-3">
                                    {{-- Bar Garis Progres --}}
                                    <div class="w-full h-1.5 sm:h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $barColor }}" style="width: {{ $barWidth }}%"></div>
                                    </div>

                                    {{-- Label Tahapan Proses: Responsive untuk Desktop & Mobile --}}
                                    @if ($isAspirasi)
                                        {{-- Tampilan Desktop (sm ke atas): 5 Tahapan Horisontal Lengkap --}}
                                        <div class="hidden sm:flex items-center justify-between text-xs mt-2 font-medium">
                                            <span class="text-left {{ $currentStep >= 1 ? ($isAspirasiRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Diajukan</span>
                                            <span class="text-center {{ $currentStep >= 2 ? ($isAspirasiRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Ditinjau</span>
                                            <span class="text-center {{ $currentStep >= 3 ? ($isAspirasiRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Ditampung</span>
                                            <span class="text-center {{ $currentStep >= 4 ? ($isAspirasiRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Dalam Pembahasan</span>
                                            <span class="text-right {{ $isAspirasiRejected ? 'text-rose-600 font-bold' : ($currentStep >= 5 ? 'text-[#185840] font-bold' : 'text-gray-400') }}">
                                                {{ $tindakLanjutLabel }}
                                            </span>
                                        </div>
                                    @else
                                        {{-- Tampilan Desktop (sm ke atas): 4 Tahapan Horisontal Lengkap --}}
                                        <div class="hidden sm:flex items-center justify-between text-xs mt-2 font-medium">
                                            <span class="text-left {{ $currentStep >= 1 ? ($isPengaduanRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Diajukan</span>
                                            <span class="text-center {{ $currentStep >= 2 ? ($isPengaduanRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Ditinjau</span>
                                            <span class="text-center {{ $currentStep >= 3 ? ($isPengaduanRejected ? 'text-gray-700 font-semibold' : 'text-[#185840] font-semibold') : 'text-gray-400' }}">Diproses</span>
                                            <span class="text-right {{ $isPengaduanRejected ? 'text-rose-600 font-bold' : ($currentStep >= 4 ? 'text-[#185840] font-bold' : 'text-gray-400') }}">
                                                {{ $pengaduanFinalLabel }}
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Tampilan Mobile (< sm): Indikator Ringkas, Terbaca Jelas & Tidak Bertumpuk --}}
                                    <div class="flex sm:hidden items-center justify-between text-xs mt-2 font-medium">
                                        <div class="flex items-center gap-1.5 font-semibold {{ ($isAspirasi && $isAspirasiRejected) || (!$isAspirasi && $isPengaduanRejected) ? 'text-rose-600' : 'text-[#185840]' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ ($isAspirasi && $isAspirasiRejected) || (!$isAspirasi && $isPengaduanRejected) ? 'bg-rose-600' : 'bg-[#185840]' }}"></span>
                                            <span>Tahap {{ $currentStep }}/{{ $isAspirasi ? 5 : 4 }}: {{ $mobileStepLabel }}</span>
                                        </div>
                                        <span class="text-[11px] text-gray-400 font-medium truncate ml-2">
                                            {{ $mobileNextLabel }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- State Kosong Jika Filter Pencarian Tidak Menemukan Hasil --}}
                    <div x-show="filteredReports.length === 0" x-cloak class="bg-white rounded-2xl border border-gray-200 p-10 text-center shadow-2xs">
                        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-800">Tidak Ada Laporan yang Cocok</h4>
                        <p class="text-xs text-gray-500 mt-1">Coba gunakan kata kunci pencarian lain atau ubah filter status.</p>
                        <button type="button" @click="searchQuery = ''; activeStatusFilter = 'all'; activeTypeFilter = 'all'" class="mt-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-xs font-semibold transition-colors cursor-pointer">
                            Reset Semua Filter
                        </button>
                    </div>

                    {{-- Paginasi (Jika Laporan Melebihi Batas Per Halaman) --}}
                    @if ($pengaduans->hasPages())
                        <div class="pt-4">
                            {{ $pengaduans->links() }}
                        </div>
                    @endif

                    {{-- Indikator Semua Laporan Ditampilkan --}}
                    <div class="text-center py-6">
                        <p class="text-xs text-gray-400 font-medium">Anda sudah melihat semua laporan.</p>
                    </div>
                @endif
            </div>

            {{-- Footer Bawah Halaman --}}
            <div class="text-center pb-8 border-t border-gray-200/60 pt-6 mt-6">
                <p class="text-xs text-gray-400">
                    &copy; 2026 SILAFA Banjarsari &mdash; Sistem Informasi Layanan Pengaduan dan Aspirasi Warga Desa Banjarsari.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>