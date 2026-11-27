{{--
    Kotak "Pengingat dari Kepala Desa (N)" di atas halaman Kelola Pengaduan dan Kelola Aspirasi (PBI #52).
    Hanya tampil bila ada pengingat aktif. Satu baris per laporan, memakai pengingat terbarunya.
    Prop `pengingat`: Collection<PengingatTindakLanjut> (sudah unik per laporan, terbaru dulu).
    Prop `jumlahPerLaporan`: array<pengaduan_id, int> jumlah pengingat aktif tiap laporan.
--}}
@props(['pengingat', 'jumlahPerLaporan' => []])

@if ($pengingat->isNotEmpty())
    <section class="bg-orange-50/70 border border-orange-200 rounded-3xl p-5 sm:p-6" aria-labelledby="judul-kotak-pengingat">
        <div class="flex items-center gap-3 mb-4">
            <span class="w-9 h-9 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-orange-500/30">
                <i data-lucide="bell-ring" class="w-4 h-4"></i>
            </span>
            <div>
                <h2 id="judul-kotak-pengingat" class="font-bold text-sm sm:text-base text-gray-900">
                    Pengingat dari Kepala Desa ({{ $pengingat->count() }})
                </h2>
                <p class="text-xs text-gray-600">Laporan berikut lebih dari {{ \App\Models\Pengaduan::BATAS_TERTUNDA_HARI }} hari belum ditanggapi. Mohon didahulukan.</p>
            </div>
        </div>

        <ul class="space-y-2.5">
            @foreach ($pengingat as $item)
                @php
                    $laporan = $item->pengaduan;
                    $jumlah = $jumlahPerLaporan[$item->pengaduan_id] ?? 1;
                    $ruteTinjau = $laporan->isAspirasi() ? 'admin.aspirasi.show' : 'admin.pengaduan.show';
                @endphp
                <li class="bg-white rounded-2xl border border-orange-100 px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">{{ $laporan->judul }}</p>
                        @if ($item->catatan)
                            <p class="mt-0.5 text-xs text-gray-600 break-words">&ldquo;{{ $item->catatan }}&rdquo;</p>
                        @endif
                        <p class="mt-1 text-[11px] text-gray-400">
                            {{ $item->pengirim->nama_lengkap ?? 'Kepala Desa' }} &middot; {{ $item->created_at->locale('id')->diffForHumans() }}
                            @if ($jumlah > 1)
                                &middot; <span class="font-semibold text-orange-600">diingatkan {{ $jumlah }} kali</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route($ruteTinjau, $laporan) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition-colors shrink-0">
                        Tinjau
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
