<x-admin-layout>
    @php
        $title = 'Rekapitulasi Laporan Pengaduan & Aspirasi';
        $breadcrumb = 'Rekapitulasi Laporan';
    @endphp

    <div class="space-y-6">

        {{-- ==================== 1. HERO COMMAND BANNER ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    @if ($tanggalMulai || $tanggalSelesai)
                        <div class="flex flex-wrap items-center gap-2.5 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <span>Periode: {{ $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') : 'Awal' }} s/d {{ $tanggalSelesai ? \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') : 'Hari ini' }}</span>
                            </span>
                        </div>
                    @endif

                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Rekapitulasi Laporan Pengaduan & Aspirasi
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Pemantauan beban penanganan, tren topik aduan, serta status tindak lanjut desa secara berkala untuk mendukung pengambilan kebijakan aparatur desa.
                    </p>
                </div>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('admin.pengaduan.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Buka Daftar Laporan</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ==================== 2. FILTER RENTANG TANGGAL (PBI #35) ==================== --}}
        <div class="bg-white p-6 rounded-3xl shadow-xs border border-[#E2E8E4]">
            <form method="GET" action="{{ route('admin.rekapitulasi.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-bold text-gray-700 mb-1.5">Dari Tanggal</label>
                    <div class="relative">
                        <input type="date"
                               id="tanggal_mulai"
                               name="tanggal_mulai"
                               value="{{ request('tanggal_mulai') }}"
                               class="w-full text-xs rounded-xl border border-[#E2E8E4] py-2 px-3 text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] bg-gray-50/40" />
                    </div>
                    <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-1" />
                </div>

                <div>
                    <label for="tanggal_selesai" class="block text-xs font-bold text-gray-700 mb-1.5">Sampai Tanggal</label>
                    <div class="relative">
                        <input type="date"
                               id="tanggal_selesai"
                               name="tanggal_selesai"
                               value="{{ request('tanggal_selesai') }}"
                               class="w-full text-xs rounded-xl border border-[#E2E8E4] py-2 px-3 text-gray-800 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] bg-gray-50/40" />
                    </div>
                    <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-1" />
                </div>

                <div class="flex items-center gap-2 sm:col-span-1 lg:col-span-2">
                    <button type="submit" class="bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold py-2.5 px-4 rounded-xl shadow-xs inline-flex items-center gap-2 transition-colors">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Terapkan Filter</span>
                    </button>

                    @if (request()->hasAny(['tanggal_mulai', 'tanggal_selesai']))
                        <a href="{{ route('admin.rekapitulasi.index') }}" class="px-4 py-2.5 text-xs font-semibold border border-[#E2E8E4] rounded-xl text-gray-700 hover:bg-gray-50 transition inline-flex items-center gap-1.5">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- ==================== 3. RINGKASAN METRIK (KPI) ==================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Total Masuk --}}
            <div class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">TOTAL MASUK</span>
                    <div class="w-9 h-9 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center shrink-0 border border-gray-200">
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">{{ $metrics['total'] }}</span>
                </div>
                <div class="text-[11px] text-gray-500 font-medium">Laporan Periode Ini</div>
            </div>

            {{-- Menunggu Tindakan --}}
            <div class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">MENUNGGU</span>
                    <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-amber-700 tracking-tight block leading-none">{{ $metrics['menunggu'] }}</span>
                </div>
                <div class="text-[11px] text-gray-500 font-medium">Diajukan & Ditinjau</div>
            </div>

            {{-- Sedang Diproses --}}
            <div class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">DIPROSES</span>
                    <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 border border-blue-200">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-blue-700 tracking-tight block leading-none">{{ $metrics['diproses'] }}</span>
                </div>
                <div class="text-[11px] text-gray-500 font-medium">Penanganan Petugas</div>
            </div>

            {{-- Tuntas Selesai --}}
            <div class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">SELESAI</span>
                    <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-emerald-700 tracking-tight block leading-none">{{ $metrics['selesai'] }}</span>
                </div>
                <div class="text-[11px] text-gray-500 font-medium">Tuntas Diselesaikan</div>
            </div>

            {{-- Ditolak --}}
            <div class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">DITOLAK</span>
                    <div class="w-9 h-9 rounded-2xl bg-rose-50 text-rose-700 flex items-center justify-center shrink-0 border border-rose-200">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-rose-700 tracking-tight block leading-none">{{ $metrics['ditolak'] }}</span>
                </div>
                <div class="text-[11px] text-gray-500 font-medium">Tidak Memenuhi Syarat</div>
            </div>
        </div>

        {{-- ==================== 4. GRID REKAPITULASI: STATUS & JENIS ==================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Rekapitulasi per Status: Grafik Batang Visual & Tabel Sinkron (PBI #35) --}}
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#E2E8E4] shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        </div>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900">
                            Distribusi Berdasarkan Status Laporan
                        </h3>
                    </div>
                    <span class="text-[11px] text-gray-400 font-medium">Sinkron Data & Grafik</span>
                </div>

                {{-- Visualisasi Batang Horizontal --}}
                <div class="space-y-3.5">
                    @foreach ($rekapStatus as $item)
                        <div>
                            <div class="flex justify-between items-center text-xs mb-1.5">
                                <span class="font-bold text-gray-700 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $item['status']->value === 'diajukan' ? 'bg-amber-400' : ($item['status']->value === 'ditinjau' ? 'bg-indigo-400' : ($item['status']->value === 'diproses' ? 'bg-blue-500' : ($item['status']->value === 'selesai' ? 'bg-emerald-500' : 'bg-rose-500'))) }}"></span>
                                    {{ $item['status']->label() }}
                                </span>
                                <span class="font-mono text-gray-600 text-xs">
                                    <strong class="text-gray-900">{{ $item['count'] }}</strong> ({{ $item['percentage'] }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $item['status']->value === 'diajukan' ? 'bg-amber-400' : ($item['status']->value === 'ditinjau' ? 'bg-indigo-400' : ($item['status']->value === 'diproses' ? 'bg-blue-500' : ($item['status']->value === 'selesai' ? 'bg-emerald-500' : 'bg-rose-500'))) }}"
                                     style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tabel Sinkron Status --}}
                <div class="overflow-x-auto rounded-2xl border border-[#E2E8E4] mt-4">
                    <table class="min-w-full divide-y divide-[#E2E8E4] text-xs text-left">
                        <thead class="bg-[#F8FAF9] text-gray-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                                <th class="px-4 py-3 text-right">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8E4] bg-white">
                            @foreach ($rekapStatus as $item)
                                <tr class="hover:bg-gray-50/80">
                                    <td class="px-4 py-2.5 font-medium text-gray-800 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold shadow-xs border {{ $item['status']->badgeClasses() }}">
                                            {{ $item['status']->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono font-bold text-gray-900">{{ $item['count'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-600">{{ $item['percentage'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rekapitulasi per Jenis: Grafik & Tabel Sinkron --}}
            <div class="bg-white p-6 sm:p-7 rounded-3xl border border-[#E2E8E4] shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                            <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        </div>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900">
                            Distribusi Berdasarkan Jenis Laporan
                        </h3>
                    </div>
                    <span class="text-[11px] text-gray-400 font-medium">Pengaduan vs Aspirasi</span>
                </div>

                {{-- Visualisasi Batang Jenis --}}
                <div class="space-y-4">
                    @foreach ($rekapJenis as $item)
                        <div>
                            <div class="flex justify-between items-center text-xs mb-1.5">
                                <span class="font-bold text-gray-700 capitalize flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $item['jenis']->value === 'pengaduan' ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                    {{ $item['jenis']->label() }}
                                </span>
                                <span class="font-mono text-gray-600 text-xs">
                                    <strong class="text-gray-900">{{ $item['count'] }}</strong> ({{ $item['percentage'] }}%)
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $item['jenis']->value === 'pengaduan' ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                     style="width: {{ $item['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tabel Sinkron Jenis --}}
                <div class="overflow-x-auto rounded-2xl border border-[#E2E8E4] mt-4">
                    <table class="min-w-full divide-y divide-[#E2E8E4] text-xs text-left">
                        <thead class="bg-[#F8FAF9] text-gray-500 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Jenis Laporan</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                                <th class="px-4 py-3 text-right">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8E4] bg-white">
                            @foreach ($rekapJenis as $item)
                                <tr class="hover:bg-gray-50/80">
                                    <td class="px-4 py-2.5 font-bold text-gray-800 capitalize">{{ $item['jenis']->label() }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono font-bold text-gray-900">{{ $item['count'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-600">{{ $item['percentage'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================== 5. REKAPITULASI TOPIK & KATEGORI MASALAH ==================== --}}
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900">
                            Rekapitulasi Topik & Kategori Masalah
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Membantu aparatur desa mengidentifikasi topik masalah yang paling sering dikeluhkan warga</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#1F6B4F] border border-emerald-100">
                    {{ $rekapKategori->count() }} Kategori
                </span>
            </div>

            {{-- Visualisasi Batang Kategori --}}
            <div class="space-y-4">
                @forelse ($rekapKategori as $item)
                    <div>
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="font-bold text-gray-800 flex items-center gap-2">
                                {{ $item['nama'] }}
                                @if (! $item['aktif'])
                                    <span class="text-[9px] px-2 py-0.5 bg-gray-100 text-gray-500 font-semibold rounded-md">Nonaktif</span>
                                @endif
                            </span>
                            <span class="font-mono text-gray-600 text-xs">
                                <strong class="text-gray-900">{{ $item['count'] }}</strong> Laporan ({{ $item['percentage'] }}%)
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full bg-[#1F6B4F] transition-all duration-500"
                                 style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada kategori yang terdaftar.</p>
                @endforelse
            </div>

            {{-- Tabel Sinkron Kategori --}}
            <div class="overflow-x-auto rounded-2xl border border-[#E2E8E4] mt-4">
                <table class="min-w-full divide-y divide-[#E2E8E4] text-xs text-left">
                    <thead class="bg-[#F8FAF9] text-gray-500 font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Kategori</th>
                            <th class="px-5 py-3">Status Aktif</th>
                            <th class="px-5 py-3 text-right">Jumlah Masuk</th>
                            <th class="px-5 py-3 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8E4] bg-white">
                        @forelse ($rekapKategori as $item)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-5 py-3 font-bold text-gray-900">{{ $item['nama'] }}</td>
                                <td class="px-5 py-3">
                                    @if ($item['aktif'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-gray-900">{{ $item['count'] }}</td>
                                <td class="px-5 py-3 text-right font-mono text-gray-600">{{ $item['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-6 text-center text-gray-400">Tidak ada data kategori.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
