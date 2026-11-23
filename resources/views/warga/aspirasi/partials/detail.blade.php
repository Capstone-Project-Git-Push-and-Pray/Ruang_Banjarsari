{{-- Isi lengkap aspirasi publik (PBI #40). Membutuhkan $pengaduan. --}}
<div class="space-y-6">
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
                        <img src="{{ $foto->url }}" alt="Foto pendukung {{ $loop->iteration }}" loading="lazy" class="w-full h-full object-cover">
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ((int) $pengaduan->user_id === (int) Auth::id())
        <a href="{{ route('warga.pengaduan.show', $pengaduan) }}" class="inline-block text-xs font-semibold text-[#1F6B4F] hover:underline">Ini aspirasi Anda. Lihat perkembangan penanganan &rarr;</a>
    @endif
</div>
