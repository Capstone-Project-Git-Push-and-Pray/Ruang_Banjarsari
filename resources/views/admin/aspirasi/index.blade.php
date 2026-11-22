<x-admin-layout>
    @php
        $title = 'Kelola Aspirasi Warga';
        $breadcrumb = 'Kelola Aspirasi';
    @endphp

    <div class="space-y-6">

        {{-- ==================== 1. HERO COMMAND BANNER ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Kelola Aspirasi Warga
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Daftar usulan ide, gagasan inovasi, dan aspirasi pembangunan dari masyarakat Desa Banjarsari. Berikan telaah dan catatan tindak lanjut agar pembangunan desa lebih partisipatif.
                    </p>
                </div>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('admin.pengaduan.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="headset" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Kelola Pengaduan</span>
                    </a>

                    <a href="{{ route('admin.rekapitulasi.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white shadow-xs transition-colors">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                        <span>Rekapitulasi & Analitik</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ==================== 2. FILTER & PENCARIAN ==================== --}}
        <div class="bg-white p-6 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 uppercase tracking-wider">Filter & Pencarian Aspirasi</span>
                </div>
                <span class="text-xs text-gray-500">
                    Total: <strong>{{ $aspirasis->total() }}</strong> aspirasi tercatat
                </span>
            </div>

            <form id="filterAspirasiForm" method="GET" action="{{ route('admin.aspirasi.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
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
                                   placeholder="Ketik judul aspirasi, lokasi, nama pengusul, atau NIK..."
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
                            this.$nextTick(() => {
                                document.getElementById('filterAspirasiForm').submit();
                            });
                        }
                    }">
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Status Aspirasi</label>
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
                            this.$nextTick(() => {
                                document.getElementById('filterAspirasiForm').submit();
                            });
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
                </div>

                {{-- Filter Visibilitas & Urutan (PBI #41, PBI #44) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="visibilitas" class="block text-xs font-bold text-gray-700 mb-1.5">Visibilitas</label>
                        <select id="visibilitas" name="visibilitas" onchange="this.form.submit()" class="block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]">
                            <option value="">Semua</option>
                            <option value="tampil" @selected(request('visibilitas') === 'tampil')>Tampil publik</option>
                            <option value="disembunyikan" @selected(request('visibilitas') === 'disembunyikan')>Disembunyikan</option>
                        </select>
                    </div>
                    <div>
                        <label for="urut" class="block text-xs font-bold text-gray-700 mb-1.5">Urutkan</label>
                        <select id="urut" name="urut" onchange="this.form.submit()" class="block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]">
                            <option value="">Terbaru</option>
                            <option value="dukungan" @selected(request('urut') === 'dukungan')>Dukungan terbanyak</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                    <span class="text-gray-500">
                        Menampilkan halaman <strong>{{ $aspirasis->currentPage() }}</strong> dari <strong>{{ $aspirasis->lastPage() }}</strong>
                    </span>
                    <div class="flex items-center gap-2">
                        @if (request()->anyFilled(['q', 'status', 'kategori_id', 'visibilitas', 'urut']))
                            <a href="{{ route('admin.aspirasi.index') }}" class="px-3.5 py-2 rounded-xl border border-[#E2E8E4] text-gray-600 hover:bg-gray-50 font-semibold transition">
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

        {{-- ==================== 3. DAFTAR ASPIRASI (KARTU KOTAK-KOTAK) ==================== --}}
        @if ($aspirasis->isEmpty())
            <div class="bg-white rounded-3xl p-12 sm:p-16 border border-[#E2E8E4] text-center shadow-xs space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto border border-amber-200/80 shadow-2xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="space-y-1.5 max-w-md mx-auto">
                    <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">
                        Data Tidak Ditemukan
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                        @if (request()->anyFilled(['q', 'status', 'kategori_id', 'visibilitas', 'urut']))
                            Tidak ada data aspirasi yang sesuai dengan kriteria filter atau kata kunci pencarian yang Anda pilih.
                        @else
                            Belum ada data aspirasi warga yang tercatat dalam sistem.
                        @endif
                    </p>
                </div>

                @if (request()->anyFilled(['q', 'status', 'kategori_id', 'visibilitas', 'urut']))
                    <div class="pt-2 flex flex-wrap items-center justify-center gap-2.5">
                        <a href="{{ route('admin.aspirasi.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#185840] hover:bg-[#124230] shadow-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reset Semua Filter</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            {{-- Header Ringkasan Urutan Aspirasi --}}
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#1F6B4F]"></div>
                    <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                        Daftar Usulan Aspirasi
                    </h2>
                </div>
                <span class="text-xs text-gray-500 font-medium">
                    Menampilkan <strong>{{ $aspirasis->count() }}</strong> dari <strong>{{ $aspirasis->total() }}</strong> usulan
                </span>
            </div>

            {{-- Grid Kartu Kotak-Kotak Aspirasi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($aspirasis as $aspirasi)
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E2E8E4] shadow-xs hover:shadow-md hover:border-[#1F6B4F]/40 transition-all duration-200 flex flex-col justify-between group">
                        {{-- Bagian Atas: Warga Pengusul & Status Badge --}}
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                {{-- Warga Pengusul --}}
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-[#EAF5EE] text-[#1F6B4F] font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-100">
                                        {{ strtoupper(substr($aspirasi->user->nama_lengkap, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 block text-sm truncate" title="{{ $aspirasi->user->nama_lengkap }}">
                                            {{ $aspirasi->user->nama_lengkap }}
                                        </span>
                                        <span class="text-[11px] font-mono text-gray-400 block truncate">
                                            {{ $aspirasi->user->nik }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Status Badge --}}
                                <div class="shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold shadow-2xs border whitespace-nowrap {{ $aspirasi->status->badgeClasses() }}">
                                        {{ $aspirasi->status->label() }}
                                    </span>
                                </div>
                            </div>

                            {{-- Judul & Deskripsi Singkat Aspirasi --}}
                            <div class="mt-4 space-y-1.5">
                                <a href="{{ route('admin.aspirasi.show', $aspirasi) }}"
                                   class="font-bold text-gray-900 group-hover:text-[#1F6B4F] text-[15px] sm:text-base leading-snug line-clamp-2 transition-colors block">
                                    {{ $aspirasi->judul }}
                                </a>

                                @if ($aspirasi->deskripsi)
                                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ $aspirasi->deskripsi }}
                                    </p>
                                @endif
                            </div>

                            {{-- Metadata Badges: Kategori, Berkas/Foto, Lokasi --}}
                            <div class="flex flex-wrap items-center gap-1.5 mt-3.5 pt-1">
                                {{-- Kategori --}}
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100/80">
                                    <i data-lucide="tag" class="w-3 h-3 text-[#1F6B4F]"></i>
                                    <span>{{ $aspirasi->kategori->nama ?? 'Umum' }}</span>
                                </span>

                                {{-- Dukungan & komentar warga (PBI #41, PBI #42) --}}
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded-lg border border-gray-200">
                                    <i data-lucide="thumbs-up" class="w-3 h-3 text-gray-400"></i>
                                    <span>{{ $aspirasi->dukungan_count }} dukungan</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded-lg border border-gray-200">
                                    <i data-lucide="message-circle" class="w-3 h-3 text-gray-400"></i>
                                    <span>{{ $aspirasi->komentar_count }} komentar</span>
                                </span>

                                @if ($aspirasi->isDisembunyikan())
                                    <span class="inline-flex items-center text-[11px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200">Disembunyikan</span>
                                @endif

                                {{-- Lampiran foto jika ada --}}
                                @if ($aspirasi->lampiran->isNotEmpty())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded-lg border border-gray-200">
                                        <i data-lucide="image" class="w-3 h-3 text-gray-400"></i>
                                        <span>{{ $aspirasi->lampiran->count() }} foto</span>
                                    </span>
                                @endif

                                {{-- Lokasi jika ada --}}
                                @if ($aspirasi->lokasi)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-gray-50 px-2 py-0.5 rounded-lg border border-gray-200 truncate max-w-[170px]" title="{{ $aspirasi->lokasi }}">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-gray-400 shrink-0"></i>
                                        <span class="truncate">{{ $aspirasi->lokasi }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Bagian Bawah: Waktu Diajukan & Tombol Tinjau Aspirasi --}}
                        <div class="mt-5 pt-3.5 border-t border-gray-100 flex items-center justify-between gap-3">
                            <div class="text-xs text-gray-600 min-w-0">
                                <div class="font-medium text-gray-900 text-xs">{{ $aspirasi->created_at->format('d M Y, H:i') }}</div>
                                <div class="text-gray-400 text-[11px] mt-0.5 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>{{ $aspirasi->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <a href="{{ route('admin.aspirasi.show', $aspirasi) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-xl shadow-xs transition-colors shrink-0">
                                <span>Tinjau Aspirasi</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paginasi --}}
            @if ($aspirasis->hasPages())
                <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">
                    {{ $aspirasis->links() }}
                </div>
            @endif
        @endif

    </div>
</x-admin-layout>

