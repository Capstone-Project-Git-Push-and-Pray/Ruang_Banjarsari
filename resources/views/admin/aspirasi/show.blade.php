<x-admin-layout>
    @php
        $title = 'Tinjau Aspirasi: ' . $pengaduan->judul;
        $breadcrumb = 'Tinjau Aspirasi';
    @endphp

    <div class="space-y-6 max-w-5xl mx-auto">

        {{-- ==================== 1. TOP HEADER & BREADCRUMB ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <a href="{{ route('admin.aspirasi.index') }}" class="tombol-kembali hover:text-[#1F6B4F] transition-colors flex items-center gap-1 font-semibold text-[#1F6B4F]">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Kembali ke Daftar Aspirasi</span>
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-700">Rincian Aspirasi #{{ $pengaduan->id }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
                <div class="space-y-1">
                    <h1 class="font-extrabold text-xl sm:text-2xl text-gray-900 leading-tight">
                        Tinjau & Tindak Lanjut Aspirasi Warga
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500">
                        Telaah gagasan warga, berikan respon catatan perkembangan, dan kelola tahapan penanganan secara transparan.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Terkini:</span>
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                        {{ $pengaduan->status->label() }}
                    </span>
                    @if ($pengaduan->status->badgeSubLabel())
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider shadow-xs {{ in_array($pengaduan->status, [\App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai]) ? 'bg-[#1F6B4F] text-white border border-[#174B39]' : 'bg-red-600 text-white border border-red-700' }}">
                            {{ $pengaduan->status->badgeSubLabel() }}
                        </span>
                    @endif
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
                    <span class="font-extrabold text-sm text-emerald-950 block">Status Aspirasi Berhasil Diperbarui!</span>
                    <span class="text-xs text-emerald-700 mt-0.5 block">Perubahan status dan catatan telaah telah dicatat ke riwayat resmi dan diteruskan ke warga pengusul.</span>
                </div>
            </div>
        @endif

        {{-- ==================== WORKFLOW TRACKER INDICATOR ==================== --}}
        @php
            $currentStepIndex = match ($pengaduan->status) {
                \App\Enums\StatusLaporan::Diajukan => 1,
                \App\Enums\StatusLaporan::Ditinjau => 2,
                \App\Enums\StatusLaporan::Ditampung => 3,
                \App\Enums\StatusLaporan::DalamPembahasan => 4,
                \App\Enums\StatusLaporan::Direalisasikan,
                \App\Enums\StatusLaporan::Selesai,
                \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 5,
                default => 1,
            };
        @endphp
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#E2E8E4] shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="git-commit" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900">
                            Alur Pemrosesan Aspirasi
                        </h3>
                        <p class="text-[11px] text-gray-400">Tahapan penanganan resmi usulan masyarakat</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 hidden sm:inline">Status Terkini:</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                        {{ $pengaduan->status->label() }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-1">
                {{-- 1. Diajukan --}}
                <div class="p-3.5 rounded-2xl border text-center transition-all {{ $currentStepIndex === 1 ? 'bg-[#EAF5EE] border-2 border-[#1F6B4F] shadow-xs' : ($currentStepIndex > 1 ? 'bg-white border-emerald-200' : 'bg-[#F8FAF9] border-[#E2E8E4]') }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        @if ($currentStepIndex > 1)
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        @elseif ($currentStepIndex === 1)
                            <span class="w-2 h-2 rounded-full bg-[#1F6B4F] animate-pulse"></span>
                        @endif
                        <span class="text-xs font-bold {{ $currentStepIndex === 1 ? 'text-[#1F6B4F]' : ($currentStepIndex > 1 ? 'text-gray-900' : 'text-gray-400') }}">
                            Diajukan
                        </span>
                    </div>
                    <span class="block text-[11px] {{ $currentStepIndex === 1 ? 'text-emerald-800 font-medium' : ($currentStepIndex > 1 ? 'text-gray-500' : 'text-gray-400') }}">
                        Warga Mengirim
                    </span>
                </div>

                {{-- 2. Ditinjau --}}
                <div class="p-3.5 rounded-2xl border text-center transition-all {{ $currentStepIndex === 2 ? 'bg-[#EAF5EE] border-2 border-[#1F6B4F] shadow-xs' : ($currentStepIndex > 2 ? 'bg-white border-emerald-200' : 'bg-[#F8FAF9] border-[#E2E8E4]') }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        @if ($currentStepIndex > 2)
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        @elseif ($currentStepIndex === 2)
                            <span class="w-2 h-2 rounded-full bg-[#1F6B4F] animate-pulse"></span>
                        @endif
                        <span class="text-xs font-bold {{ $currentStepIndex === 2 ? 'text-[#1F6B4F]' : ($currentStepIndex > 2 ? 'text-gray-900' : 'text-gray-400') }}">
                            Ditinjau
                        </span>
                    </div>
                    <span class="block text-[11px] {{ $currentStepIndex === 2 ? 'text-emerald-800 font-medium' : ($currentStepIndex > 2 ? 'text-gray-500' : 'text-gray-400') }}">
                        Pemeriksaan Admin
                    </span>
                </div>

                {{-- 3. Ditampung --}}
                <div class="p-3.5 rounded-2xl border text-center transition-all {{ $currentStepIndex === 3 ? 'bg-[#EAF5EE] border-2 border-[#1F6B4F] shadow-xs' : ($currentStepIndex > 3 ? 'bg-white border-emerald-200' : 'bg-[#F8FAF9] border-[#E2E8E4]') }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        @if ($currentStepIndex > 3)
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        @elseif ($currentStepIndex === 3)
                            <span class="w-2 h-2 rounded-full bg-[#1F6B4F] animate-pulse"></span>
                        @endif
                        <span class="text-xs font-bold {{ $currentStepIndex === 3 ? 'text-[#1F6B4F]' : ($currentStepIndex > 3 ? 'text-gray-900' : 'text-gray-400') }}">
                            Ditampung
                        </span>
                    </div>
                    <span class="block text-[11px] {{ $currentStepIndex === 3 ? 'text-emerald-800 font-medium' : ($currentStepIndex > 3 ? 'text-gray-500' : 'text-gray-400') }}">
                        Masukan Diterima
                    </span>
                </div>

                {{-- 4. Dalam Pembahasan --}}
                <div class="p-3.5 rounded-2xl border text-center transition-all {{ $currentStepIndex === 4 ? 'bg-[#EAF5EE] border-2 border-[#1F6B4F] shadow-xs' : ($currentStepIndex > 4 ? 'bg-white border-emerald-200' : 'bg-[#F8FAF9] border-[#E2E8E4]') }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        @if ($currentStepIndex > 4)
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                        @elseif ($currentStepIndex === 4)
                            <span class="w-2 h-2 rounded-full bg-[#1F6B4F] animate-pulse"></span>
                        @endif
                        <span class="text-xs font-bold {{ $currentStepIndex === 4 ? 'text-[#1F6B4F]' : ($currentStepIndex > 4 ? 'text-gray-900' : 'text-gray-400') }}">
                            Dalam Pembahasan
                        </span>
                    </div>
                    <span class="block text-[11px] {{ $currentStepIndex === 4 ? 'text-emerald-800 font-medium' : ($currentStepIndex > 4 ? 'text-gray-500' : 'text-gray-400') }}">
                        Musyawarah Desa
                    </span>
                </div>

                {{-- 5. Keputusan Akhir --}}
                @php
                    $isFinalNegative = $pengaduan->status === \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti;
                    $isFinalPositive = in_array($pengaduan->status, [\App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai]);
                @endphp
                <div class="p-3.5 rounded-2xl border text-center transition-all col-span-2 sm:col-span-1 {{ $currentStepIndex === 5 ? ($isFinalNegative ? 'bg-red-50 border-2 border-red-500 shadow-xs' : 'bg-[#EAF5EE] border-2 border-[#1F6B4F] shadow-xs') : 'bg-[#F8FAF9] border-[#E2E8E4]' }}">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        @if ($currentStepIndex === 5)
                            @if ($isFinalNegative)
                                <i data-lucide="x-circle" class="w-3.5 h-3.5 text-red-600"></i>
                            @else
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#1F6B4F]"></i>
                            @endif
                        @endif
                        <span class="text-xs font-bold {{ $currentStepIndex === 5 ? ($isFinalNegative ? 'text-red-700' : 'text-[#1F6B4F]') : 'text-gray-400' }}">
                            @if ($isFinalNegative)
                                Tidak Ditindaklanjuti
                            @elseif ($isFinalPositive)
                                Direalisasikan
                            @else
                                Keputusan Akhir
                            @endif
                        </span>
                    </div>
                    <span class="block text-[11px] {{ $currentStepIndex === 5 ? ($isFinalNegative ? 'text-red-600 font-medium' : 'text-emerald-800 font-medium') : 'text-gray-400' }}">
                        @if ($isFinalNegative)
                            Keputusan Final
                        @elseif ($isFinalPositive)
                            Selesai Diwujudkan
                        @else
                            Tindak Lanjut Akhir
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ==================== 2. IDENTITAS & KONTAK PENGUSUL (PBI #28) ==================== --}}
        @can('viewDataPelapor', $pengaduan)
            <div class="bg-white p-6 sm:p-7 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900">
                            Identitas & Kontak Warga Pengusul
                        </h3>
                        <p class="text-[11px] text-gray-500">Data identitas pengusul aspirasi yang terverifikasi di sistem desa</p>
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

        {{-- ==================== 3. ISI ASPIRASI ==================== --}}
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                        <span>Aspirasi & Usulan</span>
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
                        <strong class="text-gray-900 block font-bold text-sm">Lokasi Sasaran / Target Usulan:</strong>
                        <span class="mt-0.5 block leading-relaxed">{{ $pengaduan->lokasi }}</span>
                    </div>
                </div>
            @endif

            {{-- Foto Lampiran Pendukung (PBI #28) --}}
            @if ($pengaduan->lampiran->isNotEmpty())
                <div class="border-t border-[#E2E8E4] pt-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Berkas / Foto Pendukung ({{ $pengaduan->lampiran->count() }} Lampiran)</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($pengaduan->lampiran as $foto)
                            <a href="{{ $foto->url }}" target="_blank" class="group relative block rounded-2xl overflow-hidden border border-[#E2E8E4] bg-gray-50 aspect-4/3 hover:opacity-95 transition-all shadow-2xs hover:shadow-md">
                                <img src="{{ $foto->url }}" alt="{{ $foto->nama_asli }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-xs font-medium p-3 text-center">
                                    <i data-lucide="maximize-2" class="w-6 h-6 mb-1"></i>
                                    <span class="font-bold">Buka Berkas Penuh</span>
                                    <span class="text-[10px] text-gray-300 truncate max-w-full mt-1">{{ $foto->nama_asli }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ==================== MODERASI KONTEN (PBI #44, PBI #45) ==================== --}}
        @can('moderasi', $pengaduan)
            <div class="bg-white p-6 sm:p-7 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">Moderasi Aspirasi</h3>
                        <p class="text-xs text-gray-500">
                            @if ($pengaduan->isDisembunyikan())
                                Disembunyikan {{ $pengaduan->disembunyikan_pada->format('d M Y, H:i') }} oleh {{ $pengaduan->disembunyikanOleh->nama_lengkap ?? 'akun terhapus' }}.
                            @else
                                Aspirasi ini sedang terlihat oleh seluruh warga.
                            @endif
                            {{ $pengaduan->dukungan_count }} warga mendukung.
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $pengaduan->isDisembunyikan() ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200' }}">
                        {{ $pengaduan->isDisembunyikan() ? 'Disembunyikan' : 'Tampil Publik' }}
                    </span>
                </div>
                @include('admin.moderasi.partials.aksi', ['konten' => $pengaduan, 'jenis' => 'aspirasi'])
            </div>
        @endcan

        {{-- ==================== 4. PEMBARUAN STATUS & TINDAK LANJUT ==================== --}}
        @if ($pengaduan->allowedNextStatuses())
            <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-100">
                        <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-900">
                            Pembaruan Status & Tindak Lanjut Aspirasi
                        </h3>
                        <p class="text-xs text-gray-500">Pilih status berikutnya dan cantumkan catatan perkembangan penanganan atau tanggapan atas aspirasi warga.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.pengaduan.status.update', $pengaduan) }}" class="space-y-5">
                    @csrf
                    @method('patch')

                    {{-- Pilihan Status Tujuan yang Sah --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700">Pilih Tahap Tindak Lanjut Berikutnya:</label>
                        <div class="grid grid-cols-1 {{ count($pengaduan->allowedNextStatuses()) > 1 ? 'sm:grid-cols-2' : '' }} gap-3.5">
                            @foreach ($pengaduan->allowedNextStatuses() as $nextStatus)
                                @php
                                    $isReject = in_array($nextStatus, [\App\Enums\StatusLaporan::TidakDapatDitindaklanjuti, \App\Enums\StatusLaporan::Ditolak]);
                                    $isSuccess = in_array($nextStatus, [\App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai]);
                                @endphp
                                <label class="relative flex items-start p-4 border rounded-2xl cursor-pointer hover:bg-gray-50 transition-all {{ old('status') === $nextStatus->value ? ($isReject ? 'border-red-500 bg-red-50/40 ring-2 ring-red-500/20' : 'border-[#1F6B4F] bg-emerald-50/40 ring-2 ring-[#1F6B4F]/20') : 'border-[#E2E8E4]' }}">
                                    <input type="radio" name="status" value="{{ $nextStatus->value }}" class="{{ $isReject ? 'text-red-600 focus:ring-red-600' : 'text-[#1F6B4F] focus:ring-[#1F6B4F]' }} mt-1 shrink-0" {{ old('status') === $nextStatus->value ? 'checked' : '' }} required>
                                    <div class="ms-3.5 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="block text-xs font-bold {{ $isReject ? 'text-red-700' : 'text-gray-900' }}">
                                                @if ($isReject)
                                                    Ditolak (Tidak Ditindaklanjuti)
                                                @else
                                                    {{ $nextStatus->label() }}
                                                @endif
                                            </span>
                                            @if ($isReject)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-xs bg-red-600 text-white border border-red-700">
                                                    Ditolak
                                                </span>
                                            @elseif ($isSuccess)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-xs bg-[#1F6B4F] text-white border border-[#174B39]">
                                                    Selesai
                                                </span>
                                            @elseif ($nextStatus->badgeSubLabel())
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shadow-xs bg-gray-600 text-white border border-gray-700">
                                                    {{ $nextStatus->badgeSubLabel() }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="block text-[11px] text-gray-500 mt-1 leading-relaxed">
                                            @if ($nextStatus === \App\Enums\StatusLaporan::Ditinjau)
                                                Admin memeriksa kelayakan dan isi aspirasi warga.
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::Ditampung)
                                                Aspirasi diterima sebagai masukan resmi pemerintah desa.
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::DalamPembahasan)
                                                Sedang dibahas dan dipertimbangkan lebih lanjut (forum musyawarah / rapat desa).
                                            @elseif ($nextStatus === \App\Enums\StatusLaporan::Direalisasikan)
                                                Aspirasi diwujudkan karena dapat dilaksanakan oleh pemerintah desa.
                                            @elseif ($isReject)
                                                Tolak aspirasi dan tidak dapat dilaksanakan oleh pemerintah desa (catatan alasan penolakan wajib diisi).
                                            @else
                                                {{ $nextStatus->aspirasiDescription() }}
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
                            Catatan Tindak Lanjut / Tanggapan Resmi Desa
                        </label>
                        <textarea id="catatan"
                                  name="catatan"
                                  rows="3"
                                  class="w-full rounded-2xl border border-[#E2E8E4] text-xs p-3.5 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 shadow-2xs placeholder-gray-400"
                                  placeholder="Tuliskan catatan tindak lanjut, ringkasan pertimbangan pembahasan, atau alasan jika aspirasi tidak dapat ditindaklanjuti...">{{ old('catatan') }}</textarea>
                        <p class="text-[11px] text-gray-400">
                            Catatan ini akan dicatat ke riwayat status dan dapat dibaca langsung oleh warga pengusul. <strong>Wajib diisi jika aspirasi tidak dapat ditindaklanjuti.</strong>
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
                <span class="font-bold text-sm text-gray-900 block">Status Aspirasi: {{ $pengaduan->status->label() }}</span>
                <span class="text-xs text-gray-500">Aspirasi ini telah mencapai status akhir (final) dan proses telaah telah selesai.</span>
            </div>
        @endif

        {{-- ==================== 5. RIWAYAT STATUS LENGKAP ==================== --}}
        <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-xs border border-[#E2E8E4] space-y-6">
            <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                <div class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-600 flex items-center justify-center shrink-0 border border-gray-200">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900">
                        Riwayat Perubahan Status ({{ $pengaduan->riwayatStatus->count() }} Perubahan)
                    </h3>
                    <p class="text-xs text-gray-500">Jejak audit alur penanganan aspirasi dari awal diajukan hingga saat ini</p>
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

        {{-- ==================== 6. KOLOM DISKUSI & BALASAN KOMENTAR ==================== --}}
        @include('pengaduan.partials.komentar-section')

    </div>
</x-admin-layout>

