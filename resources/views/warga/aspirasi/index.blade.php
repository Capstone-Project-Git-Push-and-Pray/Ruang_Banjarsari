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
            $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
            $filterAktif = request()->anyFilled(['q', 'kategori_id', 'status']);
        @endphp
        <form method="GET" action="{{ route('warga.aspirasi.index') }}" class="bg-white p-5 rounded-3xl border border-[#E2E8E4] shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 items-end">
            <div class="sm:col-span-2">
                <label for="q" class="block text-xs font-bold text-gray-700 mb-1.5">Kata Kunci</label>
                <input type="text" id="q" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Judul, isi, atau lokasi..." class="{{ $kelasInput }}">
            </div>
            <div>
                <label for="kategori_id" class="block text-xs font-bold text-gray-700 mb-1.5">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="{{ $kelasInput }}">
                    <option value="">Semua Kategori</option>
                    @foreach ($kategoriList as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $kategori->id === request('kategori_id'))>{{ $kategori->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-bold text-gray-700 mb-1.5">Status</label>
                <select id="status" name="status" class="{{ $kelasInput }}">
                    <option value="">Semua Status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status->value }}" @selected($status->value === request('status'))>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="urut" class="block text-xs font-bold text-gray-700 mb-1.5">Urutkan</label>
                <select id="urut" name="urut" class="{{ $kelasInput }}">
                    <option value="terbaru">Terbaru</option>
                    <option value="dukungan" @selected(request('urut') === 'dukungan')>Dukungan terbanyak</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">
                Terapkan
            </button>
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

                            <a href="{{ route('warga.aspirasi.show', $aspirasi) }}" class="font-bold text-gray-900 hover:text-[#1F6B4F] text-[15px] sm:text-base leading-snug line-clamp-2 transition-colors block mt-4">
                                {{ $aspirasi->judul }}
                            </a>
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

                        <div class="mt-5 pt-3.5 border-t border-gray-100 flex items-center justify-between gap-3">
                            @include('warga.aspirasi.partials.tombol-dukung', ['aspirasi' => $aspirasi])
                            <a href="{{ route('warga.aspirasi.show', $aspirasi) }}" class="text-xs font-semibold text-gray-600 hover:text-[#1F6B4F] transition-colors whitespace-nowrap">
                                {{ $aspirasi->komentar_count }} komentar &rarr;
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($aspirasis->hasPages())
                <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $aspirasis->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
