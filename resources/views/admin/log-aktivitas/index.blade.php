<x-admin-layout>
    @php
        $title = 'Log Aktivitas Petugas';
        $breadcrumb = 'Log Aktivitas';
        $kelasInput = 'block w-full py-2 px-3 text-xs rounded-xl border border-[#E2E8E4] text-gray-800 bg-gray-50/40 focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F]';
        $parameterFilter = request()->only(['petugas', 'tanggal_mulai', 'tanggal_selesai']);
        $jumlahFilterLanjutan = collect($parameterFilter)->filter()->count();
        $tabJenis = ['' => 'Semua'] + $daftarJenis;
    @endphp

    <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs">
            <h1 class="font-extrabold text-2xl text-gray-900 tracking-tight">Log Aktivitas Petugas</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-2">Catatan lengkap tindakan Admin Desa dan Kepala Desa: perubahan status laporan, peninjauan pendaftaran warga, moderasi konten, pencabutan sanksi, dan pengingat tindak lanjut.</p>
        </div>

        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
            {{-- Tab jenis aktivitas (mempertahankan filter yang sedang dipakai) --}}
            <nav class="flex flex-wrap items-center gap-2" aria-label="Jenis aktivitas">
                @foreach ($tabJenis as $jenis => $label)
                    @php
                        $aktif = ($jenisAktif ?? '') === $jenis;
                        $jumlah = $jenis === '' ? array_sum($jumlahPerJenis) : ($jumlahPerJenis[$jenis] ?? 0);
                    @endphp
                    <a href="{{ route('admin.log-aktivitas.index', array_filter($parameterFilter + ['jenis' => $jenis])) }}"
                       @if ($aktif) aria-current="page" @endif
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-colors {{ $aktif ? 'bg-[#1F6B4F] text-white border-[#1F6B4F] shadow-sm' : 'bg-white text-gray-600 border-[#E2E8E4] hover:text-[#1F6B4F] hover:border-[#1F6B4F]/40' }}">
                        {{ $label }}
                        <span class="min-w-6 px-1.5 py-0.5 rounded-full text-[11px] font-bold text-center {{ $aktif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $jumlah }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Filter lanjutan: petugas dan rentang tanggal --}}
            <form method="GET" action="{{ route('admin.log-aktivitas.index') }}"
                  x-data="{ panel: @js($errors->hasAny(['tanggal_mulai', 'tanggal_selesai'])) }"
                  class="flex flex-wrap xl:justify-end items-center gap-2 shrink-0">
                @if ($jenisAktif)
                    <input type="hidden" name="jenis" value="{{ $jenisAktif }}">
                @endif

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
                         class="absolute left-0 xl:left-auto xl:right-0 z-40 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-2xl border border-[#E2E8E4] shadow-xl p-4 space-y-3.5">
                        <x-filter-dropdown name="petugas" label="Petugas"
                                           :selected="(string) request('petugas', '')"
                                           :options="['' => 'Semua Petugas'] + $daftarPetugas->mapWithKeys(fn ($nama, $id) => [(string) $id => $nama])->all()" />
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

                @if ($adaFilter)
                    <a href="{{ route('admin.log-aktivitas.index', $jenisAktif ? ['jenis' => $jenisAktif] : []) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-600 text-xs font-semibold transition-colors">
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
                        <th class="px-5 py-3.5 font-bold">Aktivitas</th>
                        <th class="px-5 py-3.5 font-bold">Rincian</th>
                        <th class="px-5 py-3.5 font-bold">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logAktivitas as $aktivitas)
                        <tr class="align-top">
                            <td class="px-5 py-4 text-gray-600 whitespace-nowrap">
                                {{ $aktivitas['waktu']->locale('id')->translatedFormat('d M Y, H:i') }}
                                <span class="block text-[11px] text-gray-400 mt-0.5">{{ $aktivitas['waktu']->locale('id')->diffForHumans() }}</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-2 font-semibold text-gray-900">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $aktivitas['warna'] }} ring-4 {{ $aktivitas['cincin'] }}" aria-hidden="true"></span>
                                    {{ $aktivitas['judul'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600 min-w-64 max-w-lg break-words">{{ $aktivitas['deskripsi'] }}</td>
                            <td class="px-5 py-4 text-gray-700 whitespace-nowrap">{{ $aktivitas['petugas'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                @if ($adaFilter)
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                                        <p class="text-sm font-bold text-gray-700">Data Tidak Ditemukan</p>
                                        <p class="text-xs text-gray-500">Tidak ada aktivitas yang sesuai dengan filter. Coba ubah atau hapus filter.</p>
                                    </div>
                                @else
                                    {{ $jenisAktif ? 'Belum ada aktivitas '.strtolower($daftarJenis[$jenisAktif]).' yang tercatat.' : 'Belum ada aktivitas petugas yang tercatat.' }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logAktivitas->hasPages())
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#E2E8E4] shadow-xs">{{ $logAktivitas->links() }}</div>
        @endif
    </div>
</x-admin-layout>
