<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-6">
        {{-- ==================== 1. HEADER ==================== --}}
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Aspirasi Warga</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Baca, dukung, dan diskusikan usulan warga untuk Desa Banjarsari.</p>
            </div>
            <a href="{{ route('warga.pengaduan.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#185840] hover:bg-[#124230] text-white text-xs sm:text-sm font-semibold rounded-full shadow-sm transition-colors w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Ajukan Aspirasi</span>
            </a>
        </div>

        {{-- ==================== 2. FILTER & PENCARIAN ==================== --}}
        @php
            // Popup komentar yang dibuka kembali setelah warga mengirim, mengubah, atau menghapus komentar
            $bukaKomentar = (int) (old('form_komentar') ?: session('buka_komentar'));
            $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
            $filterAktif = request()->anyFilled(['q', 'kategori_id', 'status']);
            // Tombol Hapus Filter tampil hanya jika ada filter atau urutan yang sedang diterapkan
            $filterDigunakan = $filterAktif || request()->filled('urut');
        @endphp
        <form method="GET" action="{{ route('warga.aspirasi.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 items-start">
            <div class="sm:col-span-2">
                <label for="q" class="block text-xs font-bold text-gray-700 mb-1.5">Kata Kunci</label>
                <input type="text" id="q" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Judul, isi, atau lokasi..." class="{{ $kelasInput }}">
            </div>
            <x-filter-dropdown name="kategori_id" label="Kategori"
                               :selected="request('kategori_id', '')"
                               :options="['' => 'Semua Kategori'] + $kategoriList->pluck('nama', 'id')->all()" />
            <x-filter-dropdown name="status" label="Status"
                               :selected="request('status', '')"
                               :options="['' => 'Semua Status'] + collect($statusList)->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all()" />
            <x-filter-dropdown name="urut" label="Urutkan"
                               :selected="request('urut', '')"
                               :options="['' => 'Terbaru', 'dukungan' => 'Dukungan Terbanyak']" />
            <div class="flex flex-col gap-2">
                <span class="hidden lg:block text-xs mb-[-2px] invisible" aria-hidden="true">Aksi</span>
                <button type="submit" class="w-full px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">
                    Terapkan
                </button>
                @if ($filterDigunakan)
                    <a href="{{ route('warga.aspirasi.index') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-600 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Hapus Filter</span>
                    </a>
                @endif
            </div>
        </form>

        {{-- ==================== 3. DAFTAR ASPIRASI ==================== --}}
        @if ($aspirasis->isEmpty())
            <div class="bg-white rounded-3xl p-12 border border-[#E2E8E4] text-center shadow-xs space-y-2">
                <h3 class="text-base font-extrabold text-gray-900">{{ $filterAktif ? 'Aspirasi Tidak Ditemukan' : 'Belum Ada Aspirasi' }}</h3>
                <p class="text-xs text-gray-500">
                    {{ $filterAktif ? 'Tidak ada aspirasi yang cocok dengan filter atau kata kunci Anda.' : 'Jadilah warga pertama yang menyampaikan usulan untuk desa.' }}
                </p>
                @if ($filterAktif)
                    <a href="{{ route('warga.aspirasi.index') }}" class="inline-flex px-4 py-2 mt-2 rounded-xl border border-[#E2E8E4] text-xs text-gray-600 hover:bg-gray-50 font-semibold transition">Reset Filter</a>
                @endif
            </div>
        @else
            <p class="text-xs text-gray-500 px-1">Menampilkan <strong>{{ $aspirasis->count() }}</strong> dari <strong>{{ $aspirasis->total() }}</strong> aspirasi</p>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($aspirasis as $aspirasi)
                    <article class="bg-white rounded-3xl p-5 sm:p-6 border border-[#E2E8E4] shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="font-bold text-gray-900 block text-sm truncate">{{ $aspirasi->user->namaUntuk(Auth::user()) }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $aspirasi->created_at->diffForHumans() }}</span>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border whitespace-nowrap {{ $aspirasi->status->badgeClasses() }}">
                                    {{ $aspirasi->status->label() }}
                                </span>
                            </div>

                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'detail-aspirasi-{{ $aspirasi->id }}')"
                                    class="font-bold text-gray-900 hover:text-[#1F6B4F] text-[15px] sm:text-base leading-snug line-clamp-2 transition-colors block mt-4 text-left cursor-pointer">
                                {{ $aspirasi->judul }}
                            </button>
                            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mt-1.5">{{ $aspirasi->deskripsi }}</p>

                            <div class="flex flex-wrap items-center gap-1.5 mt-3.5">
                                <span class="text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100/80">{{ $aspirasi->kategori->nama ?? 'Umum' }}</span>
                                @if ($aspirasi->lampiran->isNotEmpty())
                                    <span class="text-[11px] font-medium text-gray-600 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200">{{ $aspirasi->lampiran->count() }} foto</span>
                                @endif
                                @if ((int) $aspirasi->user_id === (int) Auth::id())
                                    <span class="text-[11px] font-semibold text-sky-700 bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-200">Aspirasi Anda</span>
                                @endif
                            </div>
                        </div>

                        {{-- Aksi: Dukung + jumlah, Komentar + jumlah, lalu Lihat --}}
                        <div class="mt-5 pt-3.5 border-t border-gray-100 flex items-center justify-between gap-3" x-data>
                            <div class="flex items-center gap-3">
                                @include('warga.aspirasi.partials.tombol-dukung', ['aspirasi' => $aspirasi])

                                <button type="button"
                                        x-on:click="$dispatch('open-modal', 'komentar-aspirasi-{{ $aspirasi->id }}')"
                                        title="Lihat dan tulis komentar"
                                        aria-label="Komentar ({{ $aspirasi->komentar_count }})"
                                        class="inline-flex items-center gap-1.5 p-2 rounded-xl text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE] transition-colors cursor-pointer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span class="text-sm font-bold text-gray-800 tabular-nums">{{ $aspirasi->komentar_count }}</span>
                                </button>
                            </div>

                            <button type="button"
                                    x-on:click="$dispatch('open-modal', 'detail-aspirasi-{{ $aspirasi->id }}')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-[#185840] hover:bg-[#124230] text-white shadow-xs transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat</span>
                            </button>
                        </div>
                    </article>

                    {{-- Popup isi lengkap aspirasi (PBI #40) --}}
                    <x-modal name="detail-aspirasi-{{ $aspirasi->id }}" maxWidth="2xl" focusable>
                        <div class="p-6 sm:p-8 space-y-6">
                            @include('warga.aspirasi.partials.detail', ['pengaduan' => $aspirasi])
                            <div class="flex justify-end pt-2 border-t border-gray-100">
                                <button type="button" x-on:click="$dispatch('close-modal', 'detail-aspirasi-{{ $aspirasi->id }}')"
                                        class="px-4 py-2 rounded-xl border border-[#E2E8E4] text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </x-modal>

                    {{-- Popup komentar publik (PBI #42): lembar bawah di ponsel/tablet, popup di desktop --}}
                    <x-lembar-bawah name="komentar-aspirasi-{{ $aspirasi->id }}" :show="$bukaKomentar === $aspirasi->id">
                        <div class="relative">
                            <button type="button" x-on:click="$dispatch('close-modal', 'komentar-aspirasi-{{ $aspirasi->id }}')"
                                    class="absolute top-0 lg:top-4 right-4 p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 cursor-pointer" aria-label="Tutup">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <p class="px-6 sm:px-8 pt-1.5 lg:pt-6 pr-14 text-xs font-semibold text-gray-500 line-clamp-1">{{ $aspirasi->judul }}</p>
                            @include('pengaduan.partials.komentar-section', ['pengaduan' => $aspirasi, 'dalamPopup' => true])
                        </div>
                    </x-lembar-bawah>
                @endforeach
            </div>

            @if ($aspirasis->hasPages())
                <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $aspirasis->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
