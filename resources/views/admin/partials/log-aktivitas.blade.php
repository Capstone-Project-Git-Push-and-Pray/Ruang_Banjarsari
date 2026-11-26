{{--
    Daftar log aktivitas petugas (data asli dari AktivitasPetugasService).
    Dipakai saat dashboard dimuat dan saat pembaruan otomatis berkala.
--}}
@forelse ($logAktivitas as $aktivitas)
    <div class="flex items-start gap-3">
        <span class="w-2.5 h-2.5 rounded-full {{ $aktivitas['warna'] }} shrink-0 mt-1.5 ring-4 {{ $aktivitas['cincin'] }}"></span>
        <div class="text-xs space-y-0.5 min-w-0">
            <p class="font-bold text-gray-800">{{ $aktivitas['judul'] }}</p>
            <p class="text-gray-500 leading-relaxed break-words">{{ $aktivitas['deskripsi'] }}</p>
            <time datetime="{{ $aktivitas['waktu']->toIso8601String() }}"
                  title="{{ $aktivitas['waktu']->locale('id')->translatedFormat('d F Y, H:i') }} WIB"
                  class="text-[10px] text-gray-400 font-mono">
                @if ($aktivitas['waktu']->gt(now()->subDay()))
                    {{ $aktivitas['waktu']->locale('id')->diffForHumans() }} &middot; {{ $aktivitas['waktu']->format('H:i') }} WIB
                @else
                    {{ $aktivitas['waktu']->locale('id')->translatedFormat('d M Y') }} &middot; {{ $aktivitas['waktu']->format('H:i') }} WIB
                @endif
            </time>
        </div>
    </div>
@empty
    <div class="py-8 text-center">
        <i data-lucide="history" class="w-8 h-8 mx-auto text-gray-300"></i>
        <p class="mt-2 text-xs font-semibold text-gray-500">Belum ada aktivitas petugas</p>
        <p class="mt-0.5 text-[11px] text-gray-400">Tindakan petugas akan tercatat di sini secara otomatis.</p>
    </div>
@endforelse
