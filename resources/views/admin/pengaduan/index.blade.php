<x-admin-layout>
    @php
        $title = 'Pengelolaan Laporan Warga';
        $breadcrumb = 'Kelola Pengaduan';
    @endphp

    <div class="space-y-6">

        {{-- ==================== 1. HERO COMMAND BANNER ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Pengelolaan Laporan Warga
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Daftar seluruh laporan pengaduan dan aspirasi warga Desa Banjarsari. Pantau respon tindak lanjut, verifikasi bukti lapangan, dan perbarui tahapan penanganan secara transparan.
                    </p>
                </div>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('admin.rekapitulasi.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Lihat Rekapitulasi</span>
                    </a>

                    <a href="{{ route('admin.kategori.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white shadow-xs transition-colors">
                        <i data-lucide="tags" class="w-4 h-4"></i>
                        <span>Kelola Kategori</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ==================== 2. FILTER & PENCARIAN (PBI #27) ==================== --}}
        <div class="bg-white p-6 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Filter & Pencarian Laporan</span>
                </div>
                <span class="text-xs text-gray-500">
                    Total: <strong>{{ $pengaduans->total() }}</strong> laporan
                </span>
            </div>

            <form id="filterPengaduanForm" method="GET" action="{{ route('admin.pengaduan.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                    {{-- Cari Judul, Deskripsi, atau Pelapor --}}
                    <div class="lg:col-span-2">
                        <label for="q" class="block text-xs font-bold text-gray-700 mb-1.5">Kata Kunci Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </div>
                            <input type="text"
                                   id="q"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="Ketik judul, lokasi, nama, atau NIK..."
                                   class="block w-full ps-10 pe-3 py-2 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] bg-gray-50/40">
                        </div>
                    </div>

                    {{-- Filter Status (Custom Dropdown) --}}
                    @php
                        $currentStatusValue = request('status', '');
                        $currentStatusLabel = 'Semua Status';
                        foreach ($statusList as $s) {
                            if ($s->value === $currentStatusValue) {
                                $currentStatusLabel = $s->label();
                                break;
                            }
                        }
                    @endphp
                    <div class="relative" x-data="{
                        open: false,
                        selected: '{{ $currentStatusValue }}',
                        selectedLabel: '{{ $currentStatusLabel }}',
                        select(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            this.open = false;
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Status Laporan</label>
                        <input type="hidden" name="status" :value="selected">

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

                            {{-- Option: Semua Status --}}
                            <button type="button"
                                    @click="select('', 'Semua Status')"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                    :class="selected === ''
                                        ? 'bg-[#185840] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                <span>Semua Status</span>
                                <svg x-show="selected === ''" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            @foreach ($statusList as $statusOption)
                                <button type="button"
                                        @click="select('{{ $statusOption->value }}', '{{ $statusOption->label() }}')"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                        :class="selected === '{{ $statusOption->value }}'
                                            ? 'bg-[#185840] text-white font-semibold'
                                            : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                    <span>{{ $statusOption->label() }}</span>
                                    <svg x-show="selected === '{{ $statusOption->value }}'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Kategori (Custom Dropdown) --}}
                    @php
                        $currentKategoriValue = request('kategori_id', '');
                        $currentKategoriLabel = 'Semua Kategori';
                        foreach ($kategoriList as $k) {
                            if ((string) $k->id === (string) $currentKategoriValue) {
                                $currentKategoriLabel = $k->nama;
                                break;
                            }
                        }
                    @endphp
                    <div class="relative" x-data="{
                        open: false,
                        selected: '{{ $currentKategoriValue }}',
                        selectedLabel: '{{ $currentKategoriLabel }}',
                        select(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            this.open = false;
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Kategori Masalah</label>
                        <input type="hidden" name="kategori_id" :value="selected">

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

                            {{-- Option: Semua Kategori --}}
                            <button type="button"
                                    @click="select('', 'Semua Kategori')"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                    :class="selected === ''
                                        ? 'bg-[#185840] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                <span>Semua Kategori</span>
                                <svg x-show="selected === ''" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            @foreach ($kategoriList as $kategoriOption)
                                <button type="button"
                                        @click="select('{{ $kategoriOption->id }}', '{{ addslashes($kategoriOption->nama) }}')"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                        :class="selected === '{{ $kategoriOption->id }}'
                                            ? 'bg-[#185840] text-white font-semibold'
                                            : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                    <span class="truncate">{{ $kategoriOption->nama }}</span>
                                    <svg x-show="selected === '{{ $kategoriOption->id }}'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Jenis (Custom Dropdown) --}}
                    @php
                        $currentJenisValue = request('jenis', '');
                        $currentJenisLabel = 'Semua Jenis';
                        foreach ($jenisList as $j) {
                            if ($j->value === $currentJenisValue) {
                                $currentJenisLabel = $j->label();
                                break;
                            }
                        }
                    @endphp
                    <div class="relative" x-data="{
                        open: false,
                        selected: '{{ $currentJenisValue }}',
                        selectedLabel: '{{ $currentJenisLabel }}',
                        select(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            this.open = false;
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Jenis Laporan</label>
                        <input type="hidden" name="jenis" :value="selected">

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

                            {{-- Option: Semua Jenis --}}
                            <button type="button"
                                    @click="select('', 'Semua Jenis')"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                    :class="selected === ''
                                        ? 'bg-[#185840] text-white font-semibold'
                                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                <span>Semua Jenis</span>
                                <svg x-show="selected === ''" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>

                            @foreach ($jenisList as $jenisOption)
                                <button type="button"
                                        @click="select('{{ $jenisOption->value }}', '{{ $jenisOption->label() }}')"
                                        class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                                        :class="selected === '{{ $jenisOption->value }}'
                                            ? 'bg-[#185840] text-white font-semibold'
                                            : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                                    <span>{{ $jenisOption->label() }}</span>
                                    <svg x-show="selected === '{{ $jenisOption->value }}'" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                    <span class="text-gray-500">
                        Menampilkan halaman <strong>{{ $pengaduans->currentPage() }}</strong> dari <strong>{{ $pengaduans->lastPage() }}</strong>
                    </span>
                    <div class="flex items-center gap-2">
                        @if (request()->hasAny(['q', 'status', 'kategori_id', 'jenis']))
                            <a href="{{ route('admin.pengaduan.index') }}" class="px-3.5 py-2 rounded-xl border border-[#E2E8E4] text-gray-600 hover:bg-gray-50 font-semibold transition">
                                Reset Filter
                            </a>
                        @endif
                        <button type="submit" class="px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white rounded-xl font-semibold shadow-xs transition-colors inline-flex items-center gap-1.5">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ==================== 3. TABEL DAFTAR LAPORAN ==================== --}}
        <div class="bg-white overflow-hidden shadow-xs rounded-3xl border border-[#E2E8E4]">
            @if ($pengaduans->isEmpty())
                <div class="p-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-4 border border-gray-200">
                        <i data-lucide="inbox" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Tidak Ada Laporan yang Cocok</h3>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                        Coba ubah kata kunci pencarian atau bersihkan filter yang diterapkan untuk melihat daftar laporan lainnya.
                    </p>
                    @if (request()->hasAny(['q', 'status', 'kategori_id', 'jenis']))
                        <div class="mt-4">
                            <a href="{{ route('admin.pengaduan.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-[#1F6B4F] bg-[#EAF5EE] hover:bg-emerald-100 transition">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                <span>Bersihkan Semua Filter</span>
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E2E8E4] text-left text-sm">
                        <thead class="bg-[#F8FAF9] text-gray-500 text-[11px] uppercase tracking-wider font-bold border-b border-[#E2E8E4]">
                            <tr>
                                <th class="px-6 py-4">Pelapor & NIK</th>
                                <th class="px-6 py-4">Judul & Kategori</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4">Waktu Masuk</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8E4]">
                            @foreach ($pengaduans as $laporan)
                                <tr class="hover:bg-[#F8FAF9]/80 transition-colors">
                                    {{-- Pelapor --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-100">
                                                {{ strtoupper(substr($laporan->user->nama_lengkap, 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 block text-sm">{{ $laporan->user->nama_lengkap }}</span>
                                                <span class="text-xs font-mono text-gray-400 block mt-0.5">{{ $laporan->user->nik }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Judul & Kategori --}}
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.pengaduan.show', $laporan) }}" class="font-bold text-gray-900 hover:text-[#1F6B4F] text-sm block leading-snug line-clamp-1">
                                            {{ $laporan->judul }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                <i data-lucide="tag" class="w-3 h-3 text-[#1F6B4F]"></i>
                                                <span>{{ $laporan->kategori->nama ?? 'Umum' }}</span>
                                            </span>
                                            @if ($laporan->lampiran->isNotEmpty())
                                                <span class="inline-flex items-center gap-1 text-[11px] text-gray-500 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-200">
                                                    <i data-lucide="image" class="w-3 h-3 text-gray-400"></i>
                                                    <span>{{ $laporan->lampiran->count() }} foto</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-[11px] font-bold shadow-xs border whitespace-nowrap {{ $laporan->status->badgeClasses() }}">
                                            {{ $laporan->status->label() }}
                                        </span>
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="px-6 py-4 text-xs text-gray-600">
                                        <div class="font-medium text-gray-900">{{ $laporan->created_at->format('d M Y, H:i') }}</div>
                                        <div class="text-gray-400 text-[11px] mt-0.5 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>{{ $laporan->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('admin.pengaduan.show', $laporan) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                                            <span>Tindak Lanjut</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-[#E2E8E4] bg-white">
                    {{ $pengaduans->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
