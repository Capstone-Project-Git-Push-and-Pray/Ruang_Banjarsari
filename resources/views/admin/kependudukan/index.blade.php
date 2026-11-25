<x-admin-layout>
    {{-- Title and Breadcrumb --}}
    @php
        $title = 'Data Kependudukan';
        $breadcrumb = 'Data Kependudukan';
    @endphp

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        activePenduduk: null,
        editForm: {
            id: '',
            nik: '',
            nama_lengkap: '',
            tanggal_lahir: '',
            alamat: '',
            rt: '',
            rw: '',
            dusun: ''
        },
        openDetail(item) {
            this.activePenduduk = item;
            this.showDetailModal = true;
        },
        openEdit(item) {
            this.editForm = {
                id: item.id,
                nik: item.nik,
                nama_lengkap: item.nama_lengkap,
                tanggal_lahir: item.tanggal_lahir ? item.tanggal_lahir.substring(0, 10) : '',
                alamat: item.alamat,
                rt: item.rt,
                rw: item.rw,
                dusun: item.dusun ?? ''
            };
            this.showEditModal = true;
        }
    }" class="space-y-6">

        {{-- ==================== 1. HEADER & TOMBOL AKSI ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Data Kependudukan
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Data kependudukan warga resmi Desa Banjarsari sebagai referensi utama pencocokan identitas (crosscheck NIK) saat meninjau dan memvalidasi permohonan akun warga di aplikasi SILAFA.
                    </p>
                </div>

                {{-- Quick Actions --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    {{-- Tinjau Pendaftaran Shortcut --}}
                    <a href="{{ route('admin.pendaftaran.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="user-check" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Tinjau Pendaftaran</span>
                        @if ($totalMenungguTinjauan > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800">
                                {{ $totalMenungguTinjauan }}
                            </span>
                        @endif
                    </a>

                    {{-- Tombol Tambah Data Penduduk --}}
                    <button type="button"
                            @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white shadow-xs transition cursor-pointer">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Tambah Data Penduduk</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== 2. KARTU METRIK STATISTIK ==================== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Penduduk --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        TOTAL PENDUDUK
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ number_format($totalPenduduk, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Tercatat di Buku Induk</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Master Data
                    </span>
                </div>
            </div>

            {{-- Akun Aktif SILAFA --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        AKUN AKTIF SILAFA
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-[#1F6B4F] tracking-tight block leading-none">
                        {{ number_format($totalAkunAktif, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Warga Terverifikasi</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                        Aktif
                    </span>
                </div>
            </div>

            {{-- Menunggu Tinjauan --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        MENUNGGU TINJAUAN
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-100">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-amber-700 tracking-tight block leading-none">
                        {{ number_format($totalMenungguTinjauan, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Perlu Verifikasi Manual</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-100 text-amber-800">
                        Antrean
                    </span>
                </div>
            </div>

            {{-- Wilayah Dusun --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        WILAYAH DUSUN
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-100">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $dusunList->count() }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Dusun Terdata</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-700">
                        Wilayah
                    </span>
                </div>
            </div>
        </div>

        {{-- ==================== 3. BAR PENCARIAN & FILTER ==================== --}}
        <div class="bg-white rounded-3xl p-6 border border-[#E2E8E4] shadow-xs space-y-4">
            <form id="filterKependudukanForm" method="GET" action="{{ route('admin.kependudukan.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
                    {{-- Input Pencarian Utama (Nama / NIK / Alamat) --}}
                    <div class="md:col-span-6">
                        <label for="search" class="block text-xs font-bold text-gray-700 mb-1.5">Kata Kunci Pencarian</label>
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari berdasarkan Nama Lengkap, NIK 16 Digit, atau Alamat..."
                                   class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 placeholder:text-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition bg-gray-50/40" />
                        </div>
                    </div>

                    {{-- Filter Berdasarkan Dusun (Custom Dropdown) --}}
                    @php
                        $currentDusunValue = request('dusun', '');
                        $currentDusunLabel = $currentDusunValue ?: 'Semua Dusun';
                    @endphp
                    <div class="md:col-span-3 relative" x-data="{
                        open: false,
                        selected: '{{ addslashes($currentDusunValue) }}',
                        selectedLabel: '{{ addslashes($currentDusunLabel) }}',
                        select(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            this.open = false;
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Wilayah Dusun</label>
                        <input type="hidden" name="dusun" :value="selected">

                        <button type="button"
                                @click="open = !open"
                                class="w-full flex items-center justify-between text-xs rounded-xl border border-[#E2E8E4] py-2 px-3 text-gray-800 bg-gray-50/40 hover:bg-white focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition-all cursor-pointer shadow-2xs"
                                :class="selected !== '' ? 'border-[#185840] text-[#185840] font-semibold bg-[#F4F9F6]' : ''">
                            <span class="truncate font-medium" x-text="selectedLabel"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 ml-1.5"
                                 :class="open ? 'rotate-180 text-[#1F6B4F]' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute z-30 w-full mt-1.5 bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto text-xs focus:outline-none overscroll-contain">

                            {{-- Option: Semua Dusun --}}
                            <button type="button"
                                    @click="select('', 'Semua Dusun')"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                    :class="selected === ''
                                        ? 'bg-[#185840] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                <span>Semua Dusun</span>
                                <svg x-show="selected === ''" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            @foreach ($dusunList as $dusunName)
                                <button type="button"
                                        @click="select('{{ addslashes($dusunName) }}', '{{ addslashes($dusunName) }}')"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                        :class="selected === '{{ addslashes($dusunName) }}'
                                            ? 'bg-[#185840] text-white font-semibold'
                                            : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                    <span>{{ $dusunName }}</span>
                                    <svg x-show="selected === '{{ addslashes($dusunName) }}'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Berdasarkan Status Akun SILAFA (Custom Dropdown) --}}
                    @php
                        $statusAkunOptions = [
                            'aktif' => 'Akun Aktif (Terdaftar)',
                            'menunggu' => 'Menunggu Tinjauan',
                            'ditolak' => 'Pendaftaran Ditolak',
                            'belum' => 'Belum Memiliki Akun',
                        ];
                        $currentStatusAkunValue = request('status_akun', '');
                        $currentStatusAkunLabel = $statusAkunOptions[$currentStatusAkunValue] ?? 'Semua Status Akun';
                    @endphp
                    <div class="md:col-span-3 relative" x-data="{
                        open: false,
                        selected: '{{ $currentStatusAkunValue }}',
                        selectedLabel: '{{ $currentStatusAkunLabel }}',
                        select(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            this.open = false;
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Status Akun SILAFA</label>
                        <input type="hidden" name="status_akun" :value="selected">

                        <button type="button"
                                @click="open = !open"
                                class="w-full flex items-center justify-between text-xs rounded-xl border border-[#E2E8E4] py-2 px-3 text-gray-800 bg-gray-50/40 hover:bg-white focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition-all cursor-pointer shadow-2xs"
                                :class="selected !== '' ? 'border-[#185840] text-[#185840] font-semibold bg-[#F4F9F6]' : ''">
                            <span class="truncate font-medium" x-text="selectedLabel"></span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 ml-1.5"
                                 :class="open ? 'rotate-180 text-[#1F6B4F]' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             @click.outside="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             class="absolute z-30 w-full mt-1.5 bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto text-xs focus:outline-none overscroll-contain">

                            {{-- Option: Semua Status Akun --}}
                            <button type="button"
                                    @click="select('', 'Semua Status Akun')"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                    :class="selected === ''
                                        ? 'bg-[#185840] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                <span>Semua Status Akun</span>
                                <svg x-show="selected === ''" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            @foreach ($statusAkunOptions as $val => $lbl)
                                <button type="button"
                                        @click="select('{{ $val }}', '{{ $lbl }}')"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                        :class="selected === '{{ $val }}'
                                            ? 'bg-[#185840] text-white font-semibold'
                                            : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                    <span>{{ $lbl }}</span>
                                    <svg x-show="selected === '{{ $val }}'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Action Buttons Filter --}}
                <div class="flex items-center justify-between pt-1">
                    <div class="text-xs text-gray-500">
                        @if (request()->filled('search') || request()->filled('dusun') || request()->filled('status_akun'))
                            <span>Menemukan <strong>{{ $pendudukList->total() }}</strong> hasil pencarian</span>
                        @else
                            <span>Total terdata: <strong>{{ $pendudukList->total() }}</strong> warga</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if (request()->filled('search') || request()->filled('dusun') || request()->filled('status_akun'))
                            <a href="{{ route('admin.kependudukan.index') }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-gray-400"></i>
                                <span>Reset Filter</span>
                            </a>
                        @endif

                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white transition shadow-2xs cursor-pointer">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ==================== 4. TABEL MASTER DATA KEPENDUDUKAN ==================== --}}
        <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-gray-100">
                    <thead class="bg-gray-50/75 text-gray-400 text-[10px] uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4">NIK & IDENTITAS</th>
                            <th class="py-3.5 px-4">NAMA LENGKAP</th>
                            <th class="py-3.5 px-4">TANGGAL LAHIR & USIA</th>
                            <th class="py-3.5 px-4">ALAMAT DOMISILI</th>
                            <th class="py-3.5 px-4">STATUS AKUN SILAFA</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pendudukList as $penduduk)
                            <tr class="hover:bg-gray-50/70 transition">
                                {{-- NIK --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-gray-900 text-xs tracking-wider">
                                            {{ $penduduk->nik }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">
                                        Terdaftar {{ $penduduk->created_at?->translatedFormat('d M Y') ?? 'Buku Induk' }}
                                    </span>
                                </td>

                                {{-- Nama Lengkap --}}
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#1F6B4F] font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-100">
                                            {{ strtoupper(substr($penduduk->nama_lengkap, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-gray-900 block truncate">
                                                {{ $penduduk->nama_lengkap }}
                                            </span>
                                            <span class="text-[11px] text-gray-400 block">
                                                Warga Tetap Desa Banjarsari
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Tanggal Lahir & Usia --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-gray-800 block">
                                        {{ $penduduk->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}
                                    </span>
                                    @if ($penduduk->tanggal_lahir)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-gray-500 mt-0.5">
                                            <span>{{ $penduduk->tanggal_lahir->age }} Tahun</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Alamat & Dusun --}}
                                <td class="py-4 px-4 max-w-xs">
                                    <span class="text-gray-800 block truncate" title="{{ $penduduk->alamat }}">
                                        {{ $penduduk->alamat }}
                                    </span>
                                    <div class="flex items-center gap-1.5 mt-1 text-[10px] text-gray-500">
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono">
                                            RT {{ $penduduk->rt }} / RW {{ $penduduk->rw }}
                                        </span>
                                        @if ($penduduk->dusun)
                                            <span class="text-gray-400">•</span>
                                            <span class="font-medium text-[#1F6B4F]">
                                                {{ $penduduk->dusun }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status Akun di SILAFA (Untuk Crosscheck) --}}
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if ($penduduk->user)
                                        @if ($penduduk->user->isAktif())
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i>
                                                    Akun Aktif
                                                </span>
                                                <span class="text-[10px] text-gray-400 block font-mono truncate max-w-[140px]" title="{{ $penduduk->user->email }}">
                                                    {{ $penduduk->user->email }}
                                                </span>
                                            </div>
                                        @elseif ($penduduk->user->isMenungguTinjauan())
                                            <div class="space-y-1.5">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Menunggu Tinjauan
                                                </span>
                                                <a href="{{ route('admin.pendaftaran.show', $penduduk->user) }}"
                                                   class="inline-flex items-center gap-1 text-[11px] text-[#1F6B4F] font-semibold hover:underline block">
                                                    <span>Tinjau Akun Sekarang</span>
                                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                </a>
                                            </div>
                                        @elseif ($penduduk->user->isDitolak())
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-800 border border-red-200">
                                                    <i data-lucide="x-circle" class="w-3 h-3 text-red-600"></i>
                                                    Pendaftaran Ditolak
                                                </span>
                                                <span class="text-[10px] text-gray-400 block truncate max-w-[140px]">
                                                    Ditinjau
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                                            <i data-lucide="circle-dashed" class="w-3 h-3 text-gray-400"></i>
                                            Belum Mendaftar
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Tombol Detail --}}
                                        <button type="button"
                                                @click='openDetail(@json($penduduk))'
                                                title="Lihat Detail Lengkap"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-[#1F6B4F] hover:bg-emerald-50 transition cursor-pointer">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>

                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                @click='openEdit(@json($penduduk))'
                                                title="Edit Data Penduduk"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition cursor-pointer">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form method="POST"
                                              action="{{ route('admin.kependudukan.destroy', $penduduk) }}"
                                              data-konfirmasi="Data {{ $penduduk->nama_lengkap }} (NIK {{ $penduduk->nik }}) akan dihapus dari master data kependudukan desa."
                                              data-konfirmasi-judul="Hapus data kependudukan?"
                                              data-konfirmasi-tombol="Hapus"
                                              data-konfirmasi-jenis="bahaya"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Hapus Data Penduduk"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="max-w-xs mx-auto text-center space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                                            <i data-lucide="search-x" class="w-6 h-6"></i>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="font-bold text-sm text-gray-800">Data Penduduk Tidak Ditemukan</h3>
                                            <p class="text-xs text-gray-500 leading-relaxed">
                                                Tidak ada data warga yang cocok dengan kata kunci atau filter pencarian yang Anda tentukan.
                                            </p>
                                        </div>
                                        @if (request()->filled('search') || request()->filled('dusun') || request()->filled('status_akun'))
                                            <a href="{{ route('admin.kependudukan.index') }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#1F6B4F] text-white text-xs font-semibold hover:bg-[#174B39] transition">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span>Bersihkan Filter</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($pendudukList->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $pendudukList->links() }}
                </div>
            @endif
        </div>

        {{-- ==================== 5. MODAL DETAIL PENDUDUK ==================== --}}
        <div x-show="showDetailModal"
             x-transition:enter="transition-opacity ease-linear duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
             style="display: none;"
             @keydown.escape.window="showDetailModal = false">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-xl border border-[#E2E8E4]"
                 @click.outside="showDetailModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center">
                            <i data-lucide="contact-round" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Rincian Data Kependudukan</h3>
                            <p class="text-[11px] text-gray-400">Master Data Induk Penduduk Desa Banjarsari</p>
                        </div>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <template x-if="activePenduduk">
                    <div class="space-y-4 text-xs">
                        {{-- Kartu Profil & NIK --}}
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400 uppercase tracking-wider font-bold text-[10px]">Nomor Induk Kependudukan (NIK)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Master Data Sah</span>
                            </div>
                            <span class="font-mono text-base sm:text-lg font-bold text-gray-900 tracking-wider block" x-text="activePenduduk.nik"></span>
                        </div>

                        {{-- Data Rinci --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-white rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">Nama Lengkap</span>
                                <span class="font-bold text-gray-900 block mt-0.5" x-text="activePenduduk.nama_lengkap"></span>
                            </div>

                            <div class="p-3 bg-white rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">Tanggal Lahir</span>
                                <span class="font-bold text-gray-900 block mt-0.5" x-text="activePenduduk.tanggal_lahir ? activePenduduk.tanggal_lahir.substring(0, 10) : '-'"></span>
                            </div>

                            <div class="p-3 bg-white rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">RT / RW</span>
                                <span class="font-bold text-gray-900 block mt-0.5" x-text="'RT ' + activePenduduk.rt + ' / RW ' + activePenduduk.rw"></span>
                            </div>

                            <div class="p-3 bg-white rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] font-semibold uppercase">Wilayah Dusun</span>
                                <span class="font-bold text-[#1F6B4F] block mt-0.5" x-text="activePenduduk.dusun ?? '-'"></span>
                            </div>
                        </div>

                        {{-- Alamat Lengkap --}}
                        <div class="p-3 bg-white rounded-xl border border-gray-100">
                            <span class="text-gray-400 block text-[10px] font-semibold uppercase">Alamat Domisili KTP</span>
                            <span class="text-gray-800 block mt-0.5 leading-relaxed" x-text="activePenduduk.alamat"></span>
                        </div>

                        {{-- Hubungan ke Akun SILAFA --}}
                        <div class="p-4 rounded-2xl border border-emerald-200/80 bg-emerald-50/40 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#1F6B4F] text-[11px] flex items-center gap-1.5">
                                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                    Crosscheck Status Akun SILAFA
                                </span>
                            </div>
                            <template x-if="activePenduduk.user">
                                <div class="text-xs text-gray-700 space-y-1">
                                    <p>Telah mendaftar dengan email: <strong x-text="activePenduduk.user.email"></strong></p>
                                    <p>Status akun: <strong class="capitalize" x-text="activePenduduk.user.status_akun"></strong></p>
                                </div>
                            </template>
                            <template x-if="!activePenduduk.user">
                                <p class="text-xs text-gray-500 italic">
                                    Warga dengan NIK ini belum pernah mendaftar akun di aplikasi SILAFA Banjarsari.
                                </p>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                    <button type="button"
                            @click="showDetailModal = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- ==================== 6. MODAL TAMBAH DATA PENDUDUK ==================== --}}
        <div x-show="showCreateModal"
             x-transition:enter="transition-opacity ease-linear duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
             style="display: none;"
             @keydown.escape.window="showCreateModal = false">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-xl border border-[#E2E8E4]"
                 @click.outside="showCreateModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Tambah Data Penduduk Baru</h3>
                            <p class="text-[11px] text-gray-400">Pendaftaran warga ke master data kependudukan desa</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.kependudukan.store') }}" class="space-y-4 text-xs">
                    @csrf

                    {{-- NIK --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Nomor Induk Kependudukan (NIK 16 Digit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="nik"
                               maxlength="16"
                               required
                               placeholder="Contoh: 3201xxxxxxxxxxxx"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] font-mono text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="nama_lengkap"
                               required
                               placeholder="Contoh: AHMAD HIDAYAT"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition uppercase" />
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="tanggal_lahir"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                    </div>

                    {{-- Alamat Domisili --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Alamat Jalan / Blok <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat"
                                  rows="2"
                                  required
                                  placeholder="Contoh: Jl. Merpati No. 12"
                                  class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition"></textarea>
                    </div>

                    {{-- RT, RW, Dusun --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">RT <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="rt"
                                   maxlength="3"
                                   required
                                   placeholder="001"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs font-mono text-center text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">RW <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="rw"
                                   maxlength="3"
                                   required
                                   placeholder="001"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs font-mono text-center text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="dusun"
                                   required
                                   placeholder="Dusun Krajan"
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button"
                                @click="showCreateModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white transition shadow-xs cursor-pointer">
                            Simpan Data Penduduk
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== 7. MODAL EDIT DATA PENDUDUK ==================== --}}
        <div x-show="showEditModal"
             x-transition:enter="transition-opacity ease-linear duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
             style="display: none;"
             @keydown.escape.window="showEditModal = false">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-xl border border-[#E2E8E4]"
                 @click.outside="showEditModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900">Perbarui Data Penduduk</h3>
                            <p class="text-[11px] text-gray-400">Koreksi administrasi data induk kependudukan</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/admin/kependudukan') }}/' + editForm.id" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    {{-- NIK --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Nomor Induk Kependudukan (NIK 16 Digit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="nik"
                               x-model="editForm.nik"
                               maxlength="16"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] font-mono text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="nama_lengkap"
                               x-model="editForm.nama_lengkap"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition uppercase" />
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="tanggal_lahir"
                               x-model="editForm.tanggal_lahir"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                    </div>

                    {{-- Alamat Domisili --}}
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            Alamat Jalan / Blok <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat"
                                  x-model="editForm.alamat"
                                  rows="2"
                                  required
                                  class="w-full px-3.5 py-2 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition"></textarea>
                    </div>

                    {{-- RT, RW, Dusun --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">RT <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="rt"
                                   x-model="editForm.rt"
                                   maxlength="3"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs font-mono text-center text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">RW <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="rw"
                                   x-model="editForm.rw"
                                   maxlength="3"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs font-mono text-center text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Dusun <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   name="dusun"
                                   x-model="editForm.dusun"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-[#E2E8E4] text-xs text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition" />
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button"
                                @click="showEditModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white transition shadow-xs cursor-pointer">
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

