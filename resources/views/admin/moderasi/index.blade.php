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
                            </td>
                            <td class="px-5 py-4 text-gray-700">{{ $riwayat->dilakukanOleh->nama_lengkap ?? 'akun terhapus' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500">Belum ada keputusan moderasi yang tercatat.</td>
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
