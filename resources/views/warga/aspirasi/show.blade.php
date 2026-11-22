<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('warga.aspirasi.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Aspirasi Warga</span>
        </a>

        {{-- ==================== ISI ASPIRASI (PBI #40) ==================== --}}
        <article class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4] space-y-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2 text-xs">
                    <span class="px-2.5 py-0.5 rounded-md font-semibold border {{ $pengaduan->jenis->badgeClasses() }}">{{ $pengaduan->jenis->label() }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-bold border {{ $pengaduan->status->badgeClasses() }}">{{ $pengaduan->status->label() }}</span>
                    <span class="text-emerald-700 font-medium">{{ $pengaduan->kategori->nama ?? 'Umum' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 break-words">{{ $pengaduan->judul }}</h1>
                <p class="text-xs text-gray-500 mt-1.5">
                    Diusulkan oleh <strong class="text-gray-800">{{ $pengaduan->user->namaUntuk(Auth::user()) }}</strong>
                    &bull; {{ $pengaduan->created_at->format('d M Y, H:i') }}
                </p>
            </div>

            <p class="whitespace-pre-line break-words text-gray-700 text-sm leading-relaxed border-t border-b border-gray-100 py-4">{{ $pengaduan->deskripsi }}</p>

            @if ($pengaduan->lokasi)
                <div class="text-xs text-gray-600 bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                    <strong class="text-gray-900 block font-semibold">Lokasi Usulan:</strong>
                    <span>{{ $pengaduan->lokasi }}</span>
                </div>
            @endif

            @if ($pengaduan->lampiran->isNotEmpty())
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Foto Pendukung ({{ $pengaduan->lampiran->count() }} Foto)</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($pengaduan->lampiran as $foto)
                            <a href="{{ $foto->url }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-gray-200 bg-gray-50 aspect-4/3 hover:opacity-90 transition-opacity">
                                <img src="{{ $foto->url }}" alt="Foto pendukung {{ $loop->iteration }}" class="w-full h-full object-cover">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                @include('warga.aspirasi.partials.tombol-dukung', ['aspirasi' => $pengaduan])
                @if ((int) $pengaduan->user_id === (int) Auth::id())
                    <a href="{{ route('warga.pengaduan.show', $pengaduan) }}" class="text-xs font-semibold text-[#1F6B4F] hover:underline">Ini aspirasi Anda. Lihat perkembangan penanganan &rarr;</a>
                @endif
            </div>
        </article>

        {{-- ==================== KOMENTAR PUBLIK (PBI #42) ==================== --}}
        @include('pengaduan.partials.komentar-section')
    </div>
</x-app-layout>
