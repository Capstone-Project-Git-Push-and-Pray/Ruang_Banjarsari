<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight">
                    Dashboard Kepala Desa
                </h2>
                <p class="text-xs text-gray-500 mt-1">Dashboard Pemantauan Kepala Desa — Supervisi eksekutif layanan SILAFA Banjarsari (PBI #36)</p>
            </div>
            <a href="{{ route('kepala.pengaduan.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Lihat Seluruh Laporan</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Kartu Sambutan Supervisi --}}
            <div class="bg-gradient-to-r from-emerald-800 to-[#1F6B4F] text-white p-6 sm:p-8 rounded-2xl shadow-sm">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-xs text-emerald-100 mb-3 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Hak Akses Supervisi Eksekutif (Read-Only)
                </span>
                <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                    Selamat Datang, Bapak/Ibu Kepala Desa {{ Auth::user()->nama_lengkap ?? Auth::user()->name }}!
                </h3>
                <p class="mt-2 text-sm text-emerald-100 leading-relaxed max-w-2xl">
                    Pantau kinerja penanganan aduan warga desa secara transparan dan terukur. Semua data pelapor dapat Anda tinjau untuk memastikan pelayanan masyarakat berjalan akuntabel.
                </p>
            </div>

            {{-- Ringkasan Metrik Rekapitulasi (PBI #36) --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Pengaduan & Aspirasi</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-1 block">{{ $totalLaporan }}</span>
                    <span class="text-[11px] text-gray-500 mt-1 block">Laporan Masyarakat Terdata</span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs">
                    <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Menunggu / Ditinjau</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-amber-700 mt-1 block">{{ $countDiajukan + $countDitinjau }}</span>
                    <span class="text-[11px] text-gray-500 mt-1 block">Belum Ditangani Petugas</span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs">
                    <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block">Sedang Ditangani</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-blue-700 mt-1 block">{{ $countDiproses }}</span>
                    <span class="text-[11px] text-gray-500 mt-1 block">Proses Pengerjaan Lapangan</span>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs">
                    <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Selesai Dituntaskan</span>
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-1 block">{{ $countSelesai }}</span>
                    <span class="text-[11px] text-gray-500 mt-1 block">Tingkat Tuntas: <strong>{{ $tingkatPenyelesaian }}%</strong></span>
                </div>
            </div>

            {{-- Grid Distribusi Status & Kategori --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Status Overview Bar --}}
                <div class="bg-white p-6 rounded-2xl border border-[#E2E8E4] shadow-xs lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Status Progres Penanganan Aduan Desa
                        </h3>
                        <span class="text-xs text-gray-400 font-mono">{{ $totalLaporan }} Total</span>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-700">1. Baru Diajukan Warga</span>
                                <span class="font-mono text-gray-600">{{ $countDiajukan }} Laporan</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-amber-400 h-2.5 rounded-full" style="width: {{ $totalLaporan > 0 ? ($countDiajukan / $totalLaporan) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-700">2. Telah Ditinjau Admin</span>
                                <span class="font-mono text-gray-600">{{ $countDitinjau }} Laporan</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-indigo-400 h-2.5 rounded-full" style="width: {{ $totalLaporan > 0 ? ($countDitinjau / $totalLaporan) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-700">3. Sedang Ditindaklanjuti</span>
                                <span class="font-mono text-gray-600">{{ $countDiproses }} Laporan</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $totalLaporan > 0 ? ($countDiproses / $totalLaporan) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-700">4. Berhasil Diselesaikan</span>
                                <span class="font-mono text-gray-600">{{ $countSelesai }} Laporan</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $totalLaporan > 0 ? ($countSelesai / $totalLaporan) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center text-xs mb-1">
                                <span class="font-semibold text-gray-700">5. Ditolak (Tidak Valid)</span>
                                <span class="font-mono text-gray-600">{{ $countDitolak }} Laporan</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-rose-500 h-2.5 rounded-full" style="width: {{ $totalLaporan > 0 ? ($countDitolak / $totalLaporan) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kategori Terbanyak --}}
                <div class="bg-white p-6 rounded-2xl border border-[#E2E8E4] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M10 7h10M10 11h10M10 15h10M4 3h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                            Top Kategori Aduan
                        </h3>
                    </div>

                    <div class="space-y-3">
                        @forelse ($kategoriBreakdown as $kat)
                            <div class="flex items-center justify-between text-xs py-1 border-b border-gray-50 last:border-none">
                                <span class="text-gray-700 font-medium truncate max-w-[170px]">{{ $kat->nama }}</span>
                                <span class="px-2 py-0.5 rounded-md font-mono font-bold bg-emerald-50 text-emerald-700">
                                    {{ $kat->pengaduan_count }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">Belum ada data kategori.</p>
                        @endforelse
                    </div>

                    <div class="pt-2 text-center border-t border-gray-100">
                        <span class="text-[11px] text-gray-400">Total Warga Terdaftar: <strong>{{ $totalWarga }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- 5 Laporan Terbaru untuk Pemantauan Pimpinan (Read-Only) --}}
            <div class="bg-white p-6 rounded-2xl border border-[#E2E8E4] shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Laporan Terbaru Masuk (Pemantauan Tindak Lanjut)
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar laporan masyarakat terbaru untuk supervisi pimpinan desa</p>
                    </div>
                    <a href="{{ route('kepala.pengaduan.index') }}" class="text-xs text-[#1F6B4F] font-semibold hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 font-semibold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Pelapor</th>
                                <th class="px-4 py-3">Judul & Kategori</th>
                                <th class="px-4 py-3">Status Saat Ini</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($laporanTerbaru as $lap)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-gray-500 whitespace-nowrap">
                                        {{ $lap->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-bold text-gray-900 block">{{ $lap->user->nama_lengkap ?? $lap->user->name }}</span>
                                        <span class="font-mono text-[10px] text-gray-400 block">{{ $lap->user->nik }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-gray-900 block truncate max-w-xs">{{ $lap->judul }}</span>
                                        <span class="text-[11px] text-emerald-700 block">{{ $lap->kategori->nama ?? 'Umum' }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow-xs border {{ $lap->status->badgeClasses() }}">
                                            {{ $lap->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('kepala.pengaduan.show', $lap) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Buka Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-400">
                                        Belum ada laporan yang diajukan oleh warga desa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>