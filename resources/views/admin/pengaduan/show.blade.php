<x-admin-layout>
    @php
        $title = 'Tinjau Laporan: ' . $pengaduan->judul;
        $breadcrumb = 'Tinjau Laporan';
    @endphp

    <div class="space-y-6 max-w-5xl mx-auto">

        {{-- ==================== 1. TOP HEADER & BREADCRUMB ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('admin.pengaduan.index') }}" class="tombol-kembali hover:text-[#1F6B4F] transition-colors flex items-center gap-1 font-semibold text-[#1F6B4F]">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Daftar Laporan</span>
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700">Rincian Laporan #{{ $pengaduan->id }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
                <div class="space-y-1">
                    <h1 class="font-extrabold text-xl sm:text-2xl text-gray-900 leading-tight">
                        Tinjau & Tindak Lanjut Laporan Warga
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500">
                        Kelola penanganan, verifikasi data pendukung, dan lakukan pembaruan status laporan masyarakat secara transparan.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Terkini:</span>
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                        {{ $pengaduan->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Notifikasi Sukses --}}
        @if (session('status') === 'status-laporan-diperbarui')
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-5 rounded-3xl flex items-center gap-4 shadow-xs">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-700 border border-emerald-200">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="font-extrabold text-sm text-emerald-950 block">Status Laporan Berhasil Diperbarui!</span>
                    <span class="text-xs text-emerald-700 mt-0.5 block">Perubahan status dan catatan tindak lanjut telah dicatat ke riwayat resmi dan diteruskan ke warga pelapor.</span>
                </div>
            </div>
        @endif

        {{-- ==================== 2. IDENTITAS & KONTAK PELAPOR (PBI #28) ==================== --}}
        @can('viewDataPelapor', $pengaduan)
            <div class="bg-white p-6 sm:p-7 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900">
                            Identitas & Kontak Pelapor
                        </h3>
                        <p class="text-[11px] text-gray-500">Data identitas pelapor yang tersinkronisasi dengan akun warga</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-[#F8FAF9] p-5 rounded-2xl border border-[#E2E8E4] text-xs">
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Nama Lengkap</span>
                        <span class="font-bold text-gray-900 uppercase block mt-1 text-sm">{{ $pengaduan->user->nama_lengkap }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Nomor Induk Kependudukan (NIK)</span>
                        <span class="font-mono font-bold text-gray-900 block mt-1 text-sm bg-white px-2 py-0.5 rounded-lg border border-gray-200 w-fit">{{ $pengaduan->user->nik }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Kontak (Telepon / WA / Email)</span>
                        <span class="font-semibold text-gray-900 block mt-1">{{ $pengaduan->user->telepon ?? $pengaduan->user->whatsapp ?? $pengaduan->user->email ?? '-' }}</span>
                        @if ($pengaduan->user->whatsapp && $pengaduan->user->telepon && $pengaduan->user->whatsapp !== $pengaduan->user->telepon)
                            <span class="text-gray-500 text-[11px] block mt-0.5">WA: {{ $pengaduan->user->whatsapp }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px] font-medium">Domisili Warga</span>
                        <span class="text-gray-900 font-medium block mt-1">{{ $pengaduan->user->alamat }} (RT {{ $pengaduan->user->rt }}/RW {{ $pengaduan->user->rw }}, {{ $pengaduan->user->dusun }})</span>
                    </div>
                </div>
            </div>
        @endcan

        {{-- ==================== 3. ISI LAPORAN ==================== --}}
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $pengaduan->jenis->badgeClasses() }}">
                        {{ $pengaduan->jenis->label() }}
                    </span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        <span>Kategori: {{ $pengaduan->kategori->nama ?? 'Umum' }}</span>
                    </span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                        <span>Waktu Masuk: {{ $pengaduan->created_at->format('d M Y, H:i') }} WIB ({{ $pengaduan->created_at->diffForHumans() }})</span>
                    </span>
                </div>

                <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug">
                    {{ $pengaduan->judul }}
                </h2>
            </div>

            <div class="prose max-w-none text-gray-700 text-sm leading-relaxed border-t border-b border-[#E2E8E4] py-5">
                <p class="whitespace-pre-line">{{ $pengaduan->deskripsi }}</p>
            </div>

            @if ($pengaduan->lokasi)
                <div class="flex items-start gap-3 text-xs text-gray-700 bg-gray-50 p-4 rounded-2xl border border-[#E2E8E4]">
                    <div class="w-8 h-8 rounded-xl bg-white border border-[#E2E8E4] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <strong class="text-gray-900 block font-bold text-sm">Lokasi Terkait / Alamat Kejadian:</strong>
                        <span class="mt-0.5 block leading-relaxed">{{ $pengaduan->lokasi }}</span>
                    </div>
                </div>
            @endif

            {{-- Foto Lampiran Pendukung (PBI #28) --}}
            @if ($pengaduan->lampiran->isNotEmpty())
                <div class="border-t border-[#E2E8E4] pt-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Foto Pendukung Lapangan ({{ $pengaduan->lampiran->count() }} Foto)</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($pengaduan->lampiran as $foto)
                            <a href="{{ $foto->url }}" target="_blank" class="group relative block rounded-2xl overflow-hidden border border-[#E2E8E4] bg-gray-50 aspect-4/3 hover:opacity-95 transition-all shadow-2xs hover:shadow-md">
                                <img src="{{ $foto->url }}" alt="{{ $foto->nama_asli }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-xs font-medium p-3 text-center">
                                    <i data-lucide="maximize-2" class="w-6 h-6 mb-1"></i>
                                    <span class="font-bold">Buka Foto Penuh</span>
                                    <span class="text-[10px] text-gray-300 truncate max-w-full mt-1">{{ $foto->nama_asli }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ==================== 4. PEMBARUAN STATUS & TINDAK LANJUT (PBI #29) ==================== --}}
        @if ($pengaduan->allowedNextStatuses())
            <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">
                            Pembaruan Status & Tindak Lanjut Laporan
                        </h3>
                        <p class="text-xs text-gray-500">Pilih status berikutnya yang sesuai dan cantumkan catatan perkembangan untuk warga pelapor.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.pengaduan.status.update', $pengaduan) }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    {{-- Pilihan Status Tujuan yang Sah (PBI #29) --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">Ubah Status Menjadi:</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach ($pengaduan->allowedNextStatuses() as $nextStatus)
                                <label class="relative flex items-start p-4 border rounded-2xl cursor-pointer hover:bg-gray-50 transition-all {{ old('status') === $nextStatus->value ? 'border-[#1F6B4F] bg-emerald-50/40 ring-2 ring-[#1F6B4F]/20' : 'border-[#E2E8E4]' }}">
                                    <input type="radio" name="status" value="{{ $nextStatus->value }}" class="text-[#1F6B4F] focus:ring-[#1F6B4F] mt-1 shrink-0" {{ old('status') === $nextStatus->value ? 'checked' : '' }} required>
                                    <div class="ms-3.5">
                                        <span class="block text-xs font-bold text-gray-900">
                                            {{ $nextStatus->label() }}
                                        </span>
                                        <span class="block text-[11px] text-gray-500 mt-0.5 leading-relaxed">
                                            @if ($nextStatus === \App\Enums\StatusLaporan::Ditinjau)
                                                Telah diperiksa aparatur desa dan siap ditindaklanjuti.
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::Diproses)
                                                Sedang dikerjakan atau ditangani oleh perangkat/satgas terkait.
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::Selesai)
                                                Masalah telah selesai ditangani secara tuntas di lapangan.
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::Ditolak)
                                                Laporan tidak memenuhi kriteria / ditolak (alasan wajib diisi).
                                            @endif
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    {{-- Catatan Tindak Lanjut / Alasan Penolakan --}}
                    <div class="space-y-1.5">
                        <label for="catatan" class="block text-xs font-bold text-gray-700">
                            Catatan Tindak Lanjut / Alasan Penolakan
                        </label>
                        <textarea id="catatan"
                                  name="catatan"
                                  rows="3"
                                  class="w-full rounded-2xl border border-[#E2E8E4] text-xs p-3.5 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 shadow-2xs placeholder-gray-400"
                                  placeholder="Tuliskan catatan progres penanganan di lapangan atau alasan jika laporan ditolak...">{{ old('catatan') }}</textarea>
                        <p class="text-[11px] text-gray-400">
                            Catatan ini akan dicatat ke riwayat status dan dapat dibaca oleh warga pelapor. <strong>Wajib diisi jika menolak laporan.</strong>
                        </p>
                        <x-input-error class="mt-1" :messages="$errors->get('catatan')" />
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-white text-xs font-semibold shadow-xs transition-colors inline-flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Perubahan Status</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-white p-6 rounded-3xl border border-[#E2E8E4] shadow-xs text-center space-y-1">
                <span class="font-bold text-sm text-gray-900 block">Status Laporan: {{ $pengaduan->status->label() }}</span>
                <span class="text-xs text-gray-500">Laporan ini telah mencapai status akhir (final) dan proses penanganan telah selesai.</span>
            </div>
        @endif

        {{-- ==================== 5. RIWAYAT STATUS LENGKAP (PBI #28 & PBI #29) ==================== --}}
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-6">
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <div class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center shrink-0 border border-gray-200">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900">
                        Riwayat Perubahan Status ({{ $pengaduan->riwayatStatus->count() }} Perubahan)
                    </h3>
                    <p class="text-xs text-gray-500">Jejak audit alur penanganan laporan dari awal diajukan hingga saat ini</p>
                </div>
            </div>

            <div class="relative border-s-2 border-emerald-100 ms-4 space-y-6 py-2">
                @foreach ($pengaduan->riwayatStatus as $riwayat)
                    <div class="ms-6 relative">
                        <span class="absolute -start-[31px] top-1 flex items-center justify-center w-6 h-6 rounded-full bg-white border-2 border-[#1F6B4F] text-[#1F6B4F] ring-4 ring-white shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#1F6B4F]"></span>
                        </span>
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold shadow-xs border {{ $riwayat->status->badgeClasses() }}">
                                {{ $riwayat->status->label() }}
                            </span>
                            <span class="text-gray-300">&bull;</span>
                            <span class="text-xs text-gray-500 font-mono">
                                {{ $riwayat->created_at->format('d M Y, H:i') }} WIB
                            </span>
                            <span class="text-gray-300">&bull;</span>
                            <span class="text-xs text-gray-600">
                                Oleh: <strong class="text-gray-900">{{ $riwayat->diubahOleh->nama_lengkap ?? $riwayat->diubahOleh->name ?? 'Sistem' }}</strong>
                            </span>
                        </div>
                        @if ($riwayat->catatan)
                            <div class="bg-[#F8FAF9] p-4 rounded-2xl border border-[#E2E8E4] text-xs text-gray-700 mt-2">
                                <span class="font-bold text-gray-600 block text-[11px] mb-1 flex items-center gap-1.5">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-gray-400"></i>
                                    <span>Catatan Petugas:</span>
                                </span>
                                <p class="whitespace-pre-line leading-relaxed text-gray-800">{{ $riwayat->catatan }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ==================== 6. KOLOM DISKUSI & BALASAN KOMENTAR (PBI #33, PBI #34) ==================== --}}
        @include('pengaduan.partials.komentar-section')

    </div>
</x-admin-layout>
