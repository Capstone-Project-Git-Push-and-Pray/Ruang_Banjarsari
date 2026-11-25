<x-admin-layout>
    @php
        $title = 'Riwayat Moderasi';
        $breadcrumb = 'Riwayat Moderasi';
    @endphp

    <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs">
            <h1 class="font-extrabold text-2xl text-gray-900 tracking-tight">Riwayat Moderasi Konten</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-2">Catatan setiap keputusan menyembunyikan, menampilkan kembali, dan menghapus aspirasi atau komentar warga.</p>
        </div>

        {{-- Tab pemisah jenis konten --}}
        @php
            $tabJenis = [
                ['label' => 'Semua', 'jenis' => null, 'jumlah' => array_sum($jumlahPerJenis)],
                ['label' => 'Aspirasi', 'jenis' => \App\Enums\JenisKonten::Aspirasi, 'jumlah' => $jumlahPerJenis[\App\Enums\JenisKonten::Aspirasi->value] ?? 0],
                ['label' => 'Komentar', 'jenis' => \App\Enums\JenisKonten::Komentar, 'jumlah' => $jumlahPerJenis[\App\Enums\JenisKonten::Komentar->value] ?? 0],
            ];
        @endphp
        @php
            $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
            $parameterFilter = request()->only(['q', 'aksi', 'sanksi', 'tanggal_mulai', 'tanggal_selesai']);
        @endphp
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            {{-- Tab jenis konten (mempertahankan filter yang sedang dipakai) --}}
            <nav class="flex flex-wrap items-center gap-2 shrink-0" aria-label="Jenis konten">
                @foreach ($tabJenis as $tab)
                    @php($aktif = $jenisAktif === $tab['jenis'])
                    <a href="{{ route('admin.moderasi.index', array_filter($parameterFilter + ['jenis' => $tab['jenis']?->value])) }}"
                       @if ($aktif) aria-current="page" @endif
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-colors {{ $aktif ? 'bg-[#1F6B4F] text-white border-[#1F6B4F] shadow-sm' : 'bg-white text-gray-600 border-[#E2E8E4] hover:text-[#1F6B4F] hover:border-[#1F6B4F]/40' }}">
                        {{ $tab['label'] }}
                        <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px] font-bold text-center {{ $aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $tab['jumlah'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Filter riwayat: pencarian langsung + panel filter lanjutan --}}
            @php($jumlahFilterLanjutan = collect(request()->only(['aksi', 'sanksi', 'tanggal_mulai', 'tanggal_selesai']))->filter()->count())
            <form method="GET" action="{{ route('admin.moderasi.index') }}"
                  x-data="{ panel: @js($errors->hasAny(['tanggal_mulai', 'tanggal_selesai'])) }"
                  class="flex flex-wrap lg:justify-end items-center gap-2">
                @if ($jenisAktif)
                    <input type="hidden" name="jenis" value="{{ $jenisAktif->value }}">
                @endif

                <div class="relative w-full sm:w-64">
                    <label for="q" class="sr-only">Cari warga</label>
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input id="q" name="q" type="text" maxlength="100" value="{{ request('q') }}" placeholder="Cari nama atau NIK warga..."
                           class="block w-full py-2.5 pl-9 pr-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-white focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]">
                </div>

                <div class="relative" @click.outside="panel = false" @keydown.escape.window="panel = false">
                    <button type="button" @click="panel = !panel" :aria-expanded="panel"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-colors cursor-pointer {{ $jumlahFilterLanjutan > 0 ? 'border-[#1F6B4F] text-[#1F6B4F] bg-[#F4F9F6]' : 'border-[#E2E8E4] text-gray-700 bg-white hover:border-[#1F6B4F]/40' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18l-7 8.5V19l-4 2v-8.5L3 4z"/></svg>
                        Filter
                        @if ($jumlahFilterLanjutan > 0)
                            <span class="min-w-5 px-1.5 py-0.5 rounded-full bg-[#1F6B4F] text-white text-[10px] font-bold text-center">{{ $jumlahFilterLanjutan }}</span>
                        @endif
                    </button>

                    <div x-show="panel" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 z-40 mt-2 w-80 bg-white rounded-2xl border border-[#E2E8E4] shadow-xl p-4 space-y-3.5">
                        <x-filter-dropdown name="aksi" label="Keputusan"
                                           :selected="request('aksi', '')"
                                           :options="['' => 'Semua Keputusan'] + collect(\App\Enums\AksiModerasi::cases())->mapWithKeys(fn ($aksi) => [$aksi->value => $aksi->label()])->all()" />
                        <x-filter-dropdown name="sanksi" label="Sanksi"
                                           :selected="request('sanksi', '')"
                                           :options="['' => 'Semua', 'ya' => 'Memicu Sanksi', 'tidak' => 'Tanpa Sanksi']" />
                        <div>
                            <p class="block text-xs font-bold text-gray-700 mb-1.5">Rentang Tanggal</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="tanggal_mulai" class="block text-[11px] text-gray-500 mb-1">Dari</label>
                                    <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ request('tanggal_mulai') }}" max="{{ now()->toDateString() }}" class="{{ $kelasInput }}">
                                </div>
                                <div>
                                    <label for="tanggal_selesai" class="block text-[11px] text-gray-500 mb-1">Sampai</label>
                                    <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ request('tanggal_selesai') }}" max="{{ now()->toDateString() }}" class="{{ $kelasInput }}">
                                </div>
                            </div>
                            @if ($errors->hasAny(['tanggal_mulai', 'tanggal_selesai']))
                                <p role="alert" class="mt-1.5 text-xs text-rose-600">{{ $errors->first('tanggal_selesai') ?: $errors->first('tanggal_mulai') }}</p>
                            @endif
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">Terapkan Filter</button>
                    </div>
                </div>

                <button type="submit" class="px-4 py-2.5 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs rounded-xl font-semibold shadow-xs transition-colors cursor-pointer">Cari</button>

                @if ($adaFilter)
                    <a href="{{ route('admin.moderasi.index', $jenisAktif ? ['jenis' => $jenisAktif->value] : []) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-600 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Hapus Filter</span>
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#F8FAF9] text-gray-500 uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Waktu</th>
                        <th class="px-5 py-3.5 font-bold">Keputusan</th>
                        <th class="px-5 py-3.5 font-bold">Konten</th>
                        <th class="px-5 py-3.5 font-bold">Alasan</th>
                        <th class="px-5 py-3.5 font-bold">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($riwayatModerasi as $riwayat)
                        <tr class="align-top">
                            <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ $riwayat->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold border whitespace-nowrap {{ $riwayat->aksi->badgeClasses() }}">{{ $riwayat->aksi->label() }}</span>
                            </td>
                            <td class="px-5 py-4 min-w-64 max-w-md">
                                <span class="font-semibold text-gray-900">{{ $riwayat->jenis_konten->label() }}</span>
                                <span class="text-gray-500">oleh {{ $riwayat->pemilik->nama_lengkap ?? 'akun terhapus' }}</span>
                                <p class="text-gray-600 mt-1 whitespace-pre-line break-words line-clamp-4">{{ $riwayat->isi_konten }}</p>
                                @if ($riwayat->pengaduan && ! $riwayat->pengaduan->trashed())
                                    <a href="{{ route('admin.aspirasi.show', $riwayat->pengaduan) }}" class="inline-block mt-1 text-[#1F6B4F] font-semibold hover:underline">Buka aspirasi</a>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-700 max-w-xs">
                                {{ $riwayat->alasan?->label() ?? '-' }}
                                @if ($riwayat->catatan)
                                    <p class="text-gray-500 mt-1 break-words">{{ $riwayat->catatan }}</p>
                                @endif
                                @if ($riwayat->sanksi)
                                    <a href="{{ route('admin.sanksi.index', ['status' => 'semua', 'q' => $riwayat->pemilik?->nik]) }}" class="inline-block mt-1 text-red-700 font-semibold hover:underline">
                                        Sanksi tingkat {{ $riwayat->sanksi->tingkat }} &bull; {{ $riwayat->sanksi->fitur->label() }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $riwayat->dilakukanOleh->nama_lengkap ?? 'akun terhapus' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                @if ($adaFilter)
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                                        <p class="text-sm font-bold text-gray-700">Data Tidak Ditemukan</p>
                                        <p class="text-xs text-gray-500">Tidak ada riwayat moderasi yang sesuai dengan filter. Coba ubah atau hapus filter.</p>
                                    </div>
                                @else
                                    {{ $jenisAktif ? 'Belum ada keputusan moderasi untuk '.strtolower($jenisAktif->label()).'.' : 'Belum ada keputusan moderasi yang tercatat.' }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayatModerasi->hasPages())
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $riwayatModerasi->links() }}</div>
        @endif
    </div>
</x-admin-layout>
