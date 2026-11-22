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

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs sm:rounded-2xl border border-[#E2E8E4]">
                @if ($pengaduans->isEmpty())
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-50 text-gray-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Belum Ada Laporan Masuk</h3>
                        <p class="text-sm text-gray-500 mt-1">Belum ada data pengaduan yang diajukan oleh masyarakat desa.</p>
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
                                    <th class="px-6 py-3.5 text-right">Aksi</th>
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
                                            <a href="{{ route('kepala.pengaduan.show', $laporan) }}" class="inline-flex items-center px-3 py-1.5 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold rounded-lg shadow-xs transition-colors">
                                                Supervisi &rarr;
                                            </a>
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
    </div>
</x-app-layout>

