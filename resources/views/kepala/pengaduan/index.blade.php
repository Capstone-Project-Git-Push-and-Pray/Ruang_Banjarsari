<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    Monitoring Pengaduan & Aspirasi Desa
                </h2>
                <p class="text-xs text-gray-500 mt-1">Supervisi laporan warga dan respons penanganan aparatur Desa Banjarsari.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
            pengingat: { buka: @js($errors->has('catatan')), aksi: @js(old('aksi_pengingat', '')), judul: @js(old('judul_pengingat', '')), catatan: @js(old('catatan', '')) },
            bukaPengingat(aksi, judul) { this.pengingat = { buka: true, aksi, judul, catatan: '' }; },
         }"
         x-effect="document.body.classList.toggle('overflow-hidden', pengingat.buka)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @php
                $jenisEnum = \App\Enums\JenisLaporan::class;
                $parameterFilter = array_filter(request()->only(['q', 'status', 'kategori', 'dusun', 'tanggal_mulai', 'tanggal_selesai', 'tertunda']));
                $jumlahFilterLanjutan = collect(request()->only(['status', 'kategori', 'dusun', 'tanggal_mulai', 'tanggal_selesai']))->filter()->count();
                $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
                $tabJenis = [
                    ['label' => 'Semua Laporan', 'ikon' => 'M4 6h16M4 12h16M4 18h16', 'jenis' => null, 'jumlah' => array_sum($jumlahPerJenis)],
                    ['label' => 'Pengaduan', 'ikon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z', 'jenis' => $jenisEnum::Pengaduan, 'jumlah' => $jumlahPerJenis[$jenisEnum::Pengaduan->value] ?? 0],
                    ['label' => 'Aspirasi', 'ikon' => 'M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18', 'jenis' => $jenisEnum::Aspirasi, 'jumlah' => $jumlahPerJenis[$jenisEnum::Aspirasi->value] ?? 0],
                ];
                $statusPerJenis = [
                    $jenisEnum::Pengaduan->value => collect(\App\Enums\StatusLaporan::pengaduanCases())->pluck('value')->all(),
                    $jenisEnum::Aspirasi->value => collect(\App\Enums\StatusLaporan::aspirasiCases())->pluck('value')->all(),
                ];
                $tertundaAktif = request('tertunda') === '1';
                $parameterJenis = $jenisAktif ? ['jenis' => $jenisAktif->value] : [];
            @endphp

            {{-- Tab jenis laporan (filter tetap terbawa; status dibuang bila tidak berlaku di tab tujuan) --}}
            <nav class="flex flex-wrap items-center gap-2" aria-label="Jenis laporan">
                @foreach ($tabJenis as $tab)
                    @php
                        $aktif = $jenisAktif === $tab['jenis'];
                        $parameterTab = $parameterFilter;
                        if ($tab['jenis'] && isset($parameterTab['status']) && ! in_array($parameterTab['status'], $statusPerJenis[$tab['jenis']->value], true)) {
                            unset($parameterTab['status']);
                        }
                    @endphp
                    <a href="{{ route('kepala.pengaduan.index', $parameterTab + ($tab['jenis'] ? ['jenis' => $tab['jenis']->value] : [])) }}"
                       @if ($aktif) aria-current="page" @endif
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-colors {{ $aktif ? 'bg-[#1F6B4F] text-white border-[#1F6B4F] shadow-sm' : 'bg-white text-gray-600 border-[#E2E8E4] hover:text-[#1F6B4F] hover:border-[#1F6B4F]/40' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['ikon'] }}"/></svg>
                        {{ $tab['label'] }}
                        <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px] font-bold text-center {{ $aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $tab['jumlah'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Pencarian, laporan tertunda, dan panel filter lanjutan --}}
            <form method="GET" action="{{ route('kepala.pengaduan.index') }}"
                  x-data="{ panel: @js($errors->hasAny(['tanggal_mulai', 'tanggal_selesai'])) }"
                  class="flex flex-wrap items-center gap-2">
                @if ($jenisAktif)
                    <input type="hidden" name="jenis" value="{{ $jenisAktif->value }}">
                @endif
                @if ($tertundaAktif)
                    <input type="hidden" name="tertunda" value="1">
                @endif

                <div class="relative w-full sm:w-72">
                    <label for="q" class="sr-only">Cari laporan</label>
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    <input id="q" name="q" type="text" maxlength="100" value="{{ request('q') }}" placeholder="Cari judul, nama pelapor, atau NIK..."
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
                         class="absolute left-0 z-40 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-[#E2E8E4] shadow-xl p-4 space-y-3.5">
                        <x-filter-dropdown name="status" label="Status"
                                           :selected="request('status', '')"
                                           :options="['' => 'Semua Status'] + collect($pilihanStatus)->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all()" />
                        <x-filter-dropdown name="kategori" label="Kategori"
                                           :selected="(string) request('kategori', '')"
                                           :options="['' => 'Semua Kategori'] + $daftarKategori->mapWithKeys(fn ($nama, $id) => [(string) $id => $nama])->all()" />
                        <x-filter-dropdown name="dusun" label="Dusun Pelapor"
                                           :selected="request('dusun', '')"
                                           :options="['' => 'Semua Dusun'] + $daftarDusun->mapWithKeys(fn ($dusun) => [$dusun => $dusun])->all()" />
                        <div>
                            <p class="block text-xs font-bold text-gray-700 mb-1.5">Rentang Tanggal Laporan</p>
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

                {{-- Tombol cepat "Perlu Tindak Lanjut": laporan Diajukan yang belum ditanggapi melewati batas hari --}}
                @php
                    $parameterTertunda = $tertundaAktif
                        ? collect($parameterFilter)->except('tertunda')->all()
                        : collect($parameterFilter)->except('status')->all() + ['tertunda' => '1'];
                @endphp
                <a href="{{ route('kepala.pengaduan.index', $parameterTertunda + $parameterJenis) }}"
                   @if ($tertundaAktif) aria-current="true" @endif
                   title="Laporan berstatus Diajukan lebih dari {{ $batasTertundaHari }} hari tanpa tanggapan petugas"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border text-xs font-semibold transition-colors {{ $tertundaAktif ? 'bg-orange-500 border-orange-500 text-white shadow-sm hover:bg-orange-600' : 'bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100 hover:border-orange-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Perlu Tindak Lanjut
                    <span class="min-w-5 px-1.5 py-0.5 rounded-full text-[10px] font-bold text-center {{ $tertundaAktif ? 'bg-white text-orange-600' : 'bg-orange-500 text-white' }}">{{ $jumlahTertunda }}</span>
                </a>

                @if ($adaFilter)
                    <a href="{{ route('kepala.pengaduan.index', $parameterJenis) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-600 text-xs font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Hapus Filter</span>
                    </a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-xs sm:rounded-2xl border border-[#E2E8E4]">
                @if ($pengaduans->isEmpty() && $adaFilter)
                    <div class="p-12 flex flex-col items-center gap-2 text-center">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                        <p class="text-sm font-bold text-gray-700">Data Tidak Ditemukan</p>
                        <p class="text-xs text-gray-500">Tidak ada laporan yang sesuai dengan filter. Coba ubah atau hapus filter.</p>
                    </div>
                @elseif ($pengaduans->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">{{ $jenisAktif ? 'Belum Ada '.$jenisAktif->label().' Masuk' : 'Belum Ada Laporan Masuk' }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Belum ada data {{ $jenisAktif ? strtolower($jenisAktif->label()) : 'pengaduan atau aspirasi' }} yang diajukan oleh masyarakat desa.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Pelapor & NIK</th>
                                    <th class="px-6 py-3.5">Judul & Kategori</th>
                                    <th class="px-6 py-3.5">Jenis</th>
                                    <th class="px-6 py-3.5">Status</th>
                                    <th class="px-6 py-3.5">Tanggal</th>
                                    <th class="px-6 py-3.5 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($pengaduans as $laporan)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-bold text-gray-900 block">{{ $laporan->user->nama_lengkap }}</span>
                                            <span class="text-xs font-mono text-gray-400">{{ $laporan->user->nik }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('kepala.pengaduan.show', $laporan) }}" class="font-bold text-gray-900 hover:text-[#1F6B4F] block">
                                                {{ $laporan->judul }}
                                            </a>
                                            <span class="text-xs text-gray-500">{{ $laporan->kategori->nama ?? 'Umum' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs font-medium text-gray-700 capitalize">{{ $laporan->jenis->label() ?? $laporan->jenis->value }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                {{ $laporan->status->label() ?? $laporan->status->value }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            {{ $laporan->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="ml-auto flex w-36 flex-col items-stretch gap-1.5">
                                                <a href="{{ route('kepala.pengaduan.show', $laporan) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-lg shadow-xs transition-colors whitespace-nowrap">
                                                    Supervisi &rarr;
                                                </a>

                                                {{-- Pengingat tindak lanjut ke Admin Desa (PBI #50) --}}
                                                @if ($laporan->perluTindakLanjut())
                                                    <button type="button"
                                                            @click="bukaPengingat(@js(route('kepala.pengaduan.pengingat', $laporan)), @js($laporan->judul))"
                                                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-orange-200 bg-orange-50 hover:bg-orange-100 hover:border-orange-300 text-orange-700 text-xs font-semibold transition-colors whitespace-nowrap cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                                        Ingatkan Admin
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $pengaduans->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Popup kirim pengingat dengan catatan opsional (PBI #50) --}}
        <template x-teleport="body">
            <div x-show="pengingat.buka" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                 role="dialog" aria-modal="true" aria-labelledby="judul-pengingat"
                 @keydown.escape.window="pengingat.buka = false">
                <div x-show="pengingat.buka" x-transition.opacity class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]" @click="pengingat.buka = false"></div>

                <form method="POST" :action="pengingat.aksi"
                      x-show="pengingat.buka"
                      x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                      class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl px-7 pt-7 pb-6">
                    @csrf
                    <input type="hidden" name="aksi_pengingat" :value="pengingat.aksi">
                    <input type="hidden" name="judul_pengingat" :value="pengingat.judul">

                    <div class="flex justify-center text-orange-500" aria-hidden="true">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h2 id="judul-pengingat" class="mt-3 text-lg font-semibold text-gray-800 text-center">Ingatkan Admin Desa</h2>
                    <p class="mt-2 text-xs text-gray-500 leading-relaxed text-center">
                        Pengingat untuk laporan <span class="font-semibold text-gray-700" x-text="'&quot;' + pengingat.judul + '&quot;'"></span> akan dikirim ke seluruh Admin Desa.
                    </p>

                    <div class="mt-5">
                        <label for="catatan-pengingat" class="flex items-center justify-between text-xs font-bold text-gray-700 mb-1.5">
                            <span>Catatan <span class="font-normal text-gray-400">(opsional)</span></span>
                            <span class="font-normal text-gray-400" x-text="pengingat.catatan.length + '/255'"></span>
                        </label>
                        <textarea id="catatan-pengingat" name="catatan" rows="3" maxlength="255" x-model="pengingat.catatan"
                                  placeholder="Contoh: Mohon segera ditinjau, warga sudah menanyakan perkembangannya."
                                  class="block w-full px-3 py-2.5 text-sm rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] resize-none"></textarea>
                        @error('catatan')
                            <p role="alert" class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="button" @click="pengingat.buka = false"
                                class="py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="py-2.5 rounded-full bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold shadow-sm transition-colors cursor-pointer">
                            Kirim Pengingat
                        </button>
                    </div>
                </form>
            </div>
        </template>
    </div>
</x-app-layout>
