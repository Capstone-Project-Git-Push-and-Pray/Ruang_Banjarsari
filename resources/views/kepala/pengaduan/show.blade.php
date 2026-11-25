<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-start gap-3 lg:flex-row lg:items-center">
                <a href="{{ route('kepala.pengaduan.index') }}" class="tombol-kembali p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="lg:hidden">Kembali</span>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        Supervisi Pengaduan Warga
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Peninjauan eksekutif laporan oleh Kepala Desa</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Terkini:</span>
                <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                    {{ $pengaduan->status->label() }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Data Pelapor (PBI #22: Terlihat oleh Kepala Desa) --}}
            @can('viewDataPelapor', $pengaduan)
                <div class="bg-white p-6 rounded-2xl shadow-xs border border-[#E2E8E4]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Identitas Pelapor
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nama Pelapor</span>
                            <span class="font-bold text-gray-900 uppercase block mt-0.5">{{ $pengaduan->user->nama_lengkap }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">NIK</span>
                            <span class="font-mono font-semibold text-gray-900 block mt-0.5">{{ $pengaduan->user->nik }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Kontak</span>
                            <span class="font-semibold text-gray-900 block mt-0.5">{{ $pengaduan->user->telepon ?? $pengaduan->user->whatsapp ?? $pengaduan->user->email }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Domisili</span>
                            <span class="text-gray-900 block mt-0.5">{{ $pengaduan->user->alamat }} (RT {{ $pengaduan->user->rt }}/RW {{ $pengaduan->user->rw }}, {{ $pengaduan->user->dusun }})</span>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- Isi Laporan --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4] space-y-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 capitalize">
                            {{ $pengaduan->jenis->label() ?? $pengaduan->jenis->value }}
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-emerald-700 font-medium">
                            Kategori: {{ $pengaduan->kategori->nama ?? 'Umum' }}
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-gray-400">
                            {{ $pengaduan->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                        {{ $pengaduan->judul }}
                    </h1>
                </div>

                <div class="prose max-w-none text-gray-700 text-sm leading-relaxed border-t border-b border-gray-100 py-4">
                    <p class="whitespace-pre-line">{{ $pengaduan->deskripsi }}</p>
                </div>

                @if ($pengaduan->lokasi)
                    <div class="flex items-start gap-2 text-xs text-gray-600 bg-gray-50 p-3.5 rounded-xl border border-gray-100">
                        <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <div>
                            <strong class="text-gray-900 block font-semibold">Lokasi Terkait:</strong>
                            <span>{{ $pengaduan->lokasi }}</span>
                        </div>
                    </div>
                @endif

                {{-- Foto Lampiran Pendukung (PBI #25) --}}
                @if ($pengaduan->lampiran->isNotEmpty())
                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Pendukung Lapangan ({{ $pengaduan->lampiran->count() }} Foto)
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            @foreach ($pengaduan->lampiran as $foto)
                                <a href="{{ $foto->url }}" target="_blank" class="group relative block rounded-xl overflow-hidden border border-gray-200 bg-gray-50 aspect-4/3 hover:opacity-95 transition-opacity">
                                    <img src="{{ $foto->url }}" alt="{{ $foto->nama_asli }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-medium p-2 text-center">
                                        <svg class="w-5 h-5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Buka Foto
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Riwayat Status Lengkap (PBI #36: Read-only untuk Kepala Desa) --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4]">
                <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Perubahan Status ({{ $pengaduan->riwayatStatus->count() }} Perubahan)
                </h3>

                <div class="relative border-s border-gray-200 ms-3 space-y-6">
                    @forelse ($pengaduan->riwayatStatus as $riwayat)
                        <div class="ms-6 relative">
                            <span class="absolute -start-9 flex items-center justify-center w-6 h-6 rounded-full bg-white border-2 border-[#1F6B4F] text-[#1F6B4F] ring-4 ring-white">
                                <span class="w-2 h-2 rounded-full bg-[#1F6B4F]"></span>
                            </span>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold shadow-xs border {{ $riwayat->status->badgeClasses() }}">
                                    {{ $riwayat->status->label() }}
                                </span>
                                <span class="text-xs text-gray-400">&bull;</span>
                                <span class="text-xs text-gray-500 font-mono">
                                    {{ $riwayat->created_at->format('d M Y, H:i') }}
                                </span>
                                <span class="text-xs text-gray-400">&bull;</span>
                                <span class="text-xs text-gray-600">
                                    Oleh: <strong>{{ $riwayat->diubahOleh->nama_lengkap ?? $riwayat->diubahOleh->name ?? 'Sistem' }}</strong>
                                </span>
                            </div>
                            @if ($riwayat->catatan)
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-xs text-gray-700 mt-2">
                                    <span class="font-semibold text-gray-500 block text-[11px] mb-0.5">Catatan Petugas:</span>
                                    <p class="whitespace-pre-line">{{ $riwayat->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="ms-6 text-xs text-gray-400">Belum ada riwayat perubahan status.</div>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Diskusi & Balasan Komentar (PBI #33, PBI #34, PBI #36) --}}
            @include('pengaduan.partials.komentar-section')
        </div>
    </div>
</x-app-layout>

