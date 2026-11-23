<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('warga.pengaduan.index') }}" class="p-2 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" title="Kembali ke Laporan Saya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">
                        Detail Laporan
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Informasi lengkap pengaduan / aspirasi warga</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('warga.pengaduan.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#E2E8E4] bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan Saya</span>
                </a>
                <div class="flex items-center gap-2 sm:ms-2">
                    <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Terkini:</span>
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                        {{ $pengaduan->status->label() }}
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Notifikasi Flash --}}
            @if (session('status') === 'laporan-berhasil-diajukan')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <span class="font-bold text-sm text-emerald-950 block">Laporan Berhasil Diajukan!</span>
                            <span class="text-xs text-emerald-700">Laporan Anda telah tercatat dengan status Diajukan dan segera ditinjau oleh aparatur desa.</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Informasi Visibilitas Laporan (PBI #38, PBI #44) --}}
            @if ($pengaduan->isDisembunyikan())
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-2xl text-xs shadow-xs">
                    <strong class="font-bold block text-sm">Aspirasi Sedang Disembunyikan</strong>
                    <span>Admin Desa menyembunyikan aspirasi ini dari publik selama proses peninjauan. Saat ini hanya Anda dan pihak desa yang dapat melihatnya.</span>
                </div>
            @elseif ($pengaduan->isAspirasi())
                <div class="bg-sky-50 border border-sky-200 text-sky-900 p-4 rounded-2xl text-xs shadow-xs space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span><strong class="font-bold">Publik.</strong> Aspirasi ini dapat dibaca, didukung, dan dikomentari warga lain.</span>
                        <a href="{{ route('warga.aspirasi.show', $pengaduan) }}" class="font-semibold text-sky-800 hover:underline">Lihat halaman publik &rarr;</a>
                    </div>

                    {{-- Respons warga lain (PBI #43) --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-xl border border-sky-100 p-3">
                            <span class="block text-xl font-extrabold text-gray-900">{{ $pengaduan->dukungan_count }}</span>
                            <span class="text-gray-600">warga mendukung</span>
                        </div>
                        <a href="#diskusi" class="bg-white rounded-xl border border-sky-100 p-3 hover:border-sky-300 transition-colors">
                            <span class="block text-xl font-extrabold text-gray-900">{{ $pengaduan->komentar_masuk_count }}</span>
                            <span class="text-gray-600">komentar dari warga lain dan aparatur</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 text-gray-700 p-4 rounded-2xl text-xs shadow-xs">
                    <span><strong class="font-bold">Privat.</strong> Pengaduan ini hanya dapat dilihat oleh Anda dan pihak desa.</span>
                </div>
            @endif

            {{-- Kartu Detail Laporan --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4] space-y-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $pengaduan->jenis->badgeClasses() }}">
                            {{ $pengaduan->jenis->label() }}
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-emerald-700 font-medium">
                            Kategori: {{ $pengaduan->kategori->nama ?? 'Umum' }}
                        </span>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs text-gray-400">
                            Diajukan: {{ $pengaduan->created_at->format('d M Y, H:i') }}
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
                            <strong class="text-gray-900 block font-semibold">Lokasi Kejadian:</strong>
                            <span>{{ $pengaduan->lokasi }}</span>
                        </div>
                    </div>
                @endif

                {{-- Foto Lampiran Pendukung (PBI #25) --}}
                @if ($pengaduan->lampiran->isNotEmpty())
                    <div class="border-t border-gray-100 pt-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Pendukung ({{ $pengaduan->lampiran->count() }} Foto)
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

                {{-- Data Pelapor (PBI #22: Hanya terlihat oleh Admin Desa, Kepala Desa, dan Warga Pemilik) --}}
                @can('viewDataPelapor', $pengaduan)
                    <div class="mt-6 pt-5 border-t border-gray-200" id="data-pelapor-section">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Data Pribadi Pelapor (Terlindungi)
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-emerald-50/50 p-4 rounded-xl border border-emerald-100/60 text-xs text-gray-700">
                            <div>
                                <span class="text-gray-500 block text-[11px]">Nama Lengkap Sesuai KTP</span>
                                <span class="font-bold text-gray-900 uppercase">{{ $pengaduan->user->nama_lengkap }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Nomor Induk Kependudukan (NIK)</span>
                                <span class="font-mono font-semibold text-gray-900">{{ $pengaduan->user->nik }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Kontak (Telepon / WhatsApp)</span>
                                <span class="font-semibold text-gray-900">{{ $pengaduan->user->telepon ?? $pengaduan->user->whatsapp ?? $pengaduan->user->email }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block text-[11px]">Domisili</span>
                                <span class="text-gray-900">{{ $pengaduan->user->alamat }} (RT {{ $pengaduan->user->rt }} / RW {{ $pengaduan->user->rw }}, {{ $pengaduan->user->dusun }})</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-6 pt-5 border-t border-gray-200">
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Data pribadi pelapor dilindungi dan hanya dapat dilihat oleh pelapor serta perangkat desa yang berwenang.</span>
                        </div>
                    </div>
                @endcan
            </div>

            {{-- Timeline Riwayat Status Penanganan Laporan (PBI #31) --}}
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4]">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Timeline Penanganan Laporan
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Perjalanan tindak lanjut laporan Anda secara transparan dan kronologis.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm text-gray-500 font-medium">Status Saat Ini:</span>
                        <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-bold shadow-xs border {{ $pengaduan->status->badgeClasses() }}">
                            {{ $pengaduan->status->label() }}
                        </span>
                    </div>
                </div>

                {{-- Visual Alur Status Langkah --}}
                @if ($pengaduan->isAspirasi())
                    @php
                        $isFinalDitolak = $pengaduan->status === \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti;
                        $tindakLanjutLabel = match ($pengaduan->status) {
                            \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti => 'Tidak Ditindaklanjuti',
                            \App\Enums\StatusLaporan::Direalisasikan, \App\Enums\StatusLaporan::Selesai => 'Direalisasikan',
                            default => 'Keputusan Akhir',
                        };

                        // 5 Langkah Resmi Alur Aspirasi Warga:
                        // 1. Diajukan -> 2. Ditinjau -> 3. Ditampung -> 4. Dalam Pembahasan -> 5. Keputusan Akhir
                        $aspirasiSteps = [
                            ['num' => 1, 'label' => 'Diajukan'],
                            ['num' => 2, 'label' => 'Ditinjau'],
                            ['num' => 3, 'label' => 'Ditampung'],
                            ['num' => 4, 'label' => 'Dalam Pembahasan'],
                            ['num' => 5, 'label' => $tindakLanjutLabel],
                        ];

                        $currentStep = match ($pengaduan->status) {
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

                    @if ($pengaduan->status === \App\Enums\StatusLaporan::TidakDapatDitindaklanjuti)
                        <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div class="text-xs text-rose-900">
                                <strong class="font-bold block text-sm">Aspirasi Tidak Dapat Ditindaklanjuti</strong>
                                <span>Aspirasi ini telah selesai dipertimbangkan oleh pemerintah desa namun belum dapat dilaksanakan. Silakan baca catatan penjelasan resmi aparatur desa pada riwayat di bawah.</span>
                            </div>
                        </div>
                    @elseif ($pengaduan->status === \App\Enums\StatusLaporan::Direalisasikan || $pengaduan->status === \App\Enums\StatusLaporan::Selesai)
                        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="text-xs text-emerald-900">
                                <strong class="font-bold block text-sm">Aspirasi Selesai Direalisasikan</strong>
                                <span>Aspirasi ini telah berhasil diwujudkan oleh pemerintah desa untuk kepentingan masyarakat.</span>
                            </div>
                        </div>
                    @endif

                    <div class="hidden sm:block mb-8 px-4">
                        <div class="flex items-center justify-between relative">
                            <div class="absolute top-1/2 start-0 end-0 -translate-y-1/2 h-1 bg-gray-200 z-0"></div>
                            @foreach ($aspirasiSteps as $stepItem)
                                @php
                                    $stepNum = $stepItem['num'];
                                    $isPassed = $stepNum <= $currentStep;
                                    $isCurrent = $stepNum === $currentStep;
                                    $isFinalNegative = $isCurrent && $isFinalDitolak;
                                @endphp
                                <div class="relative z-10 flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors {{ $isFinalNegative ? 'bg-rose-600 text-white ring-4 ring-rose-100' : ($isPassed ? 'bg-[#1F6B4F] text-white' : 'bg-gray-200 text-gray-500') }} {{ $isCurrent && ! $isFinalNegative ? 'ring-4 ring-emerald-100' : '' }}">
                                        @if ($isFinalNegative)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @elseif ($isPassed && ! $isCurrent)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {{ $stepNum }}
                                        @endif
                                    </div>
                                    <span class="text-xs mt-1.5 font-semibold {{ $isFinalNegative ? 'text-rose-600' : ($isPassed ? 'text-[#1F6B4F]' : 'text-gray-400') }}">
                                        {{ $stepItem['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Alur Standar Pengaduan --}}
                    @if ($pengaduan->status !== \App\Enums\StatusLaporan::Ditolak)
                        @php
                            $steps = [
                                \App\Enums\StatusLaporan::Diajukan,
                                \App\Enums\StatusLaporan::Ditinjau,
                                \App\Enums\StatusLaporan::Diproses,
                                \App\Enums\StatusLaporan::Selesai,
                            ];
                            $statusOrder = [
                                \App\Enums\StatusLaporan::Diajukan->value => 1,
                                \App\Enums\StatusLaporan::Ditinjau->value => 2,
                                \App\Enums\StatusLaporan::Diproses->value => 3,
                                \App\Enums\StatusLaporan::Selesai->value => 4,
                            ];
                            $currentStep = $statusOrder[$pengaduan->status->value] ?? 1;
                        @endphp
                        <div class="hidden sm:block mb-8 px-4">
                            <div class="flex items-center justify-between relative">
                                <div class="absolute top-1/2 start-0 end-0 -translate-y-1/2 h-1 bg-gray-200 z-0"></div>
                                @foreach ($steps as $idx => $step)
                                    @php
                                        $stepNum = $idx + 1;
                                        $isPassed = $stepNum <= $currentStep;
                                        $isCurrent = $stepNum === $currentStep;
                                    @endphp
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors {{ $isPassed ? 'bg-[#1F6B4F] text-white' : 'bg-gray-200 text-gray-500' }} {{ $isCurrent ? 'ring-4 ring-emerald-100' : '' }}">
                                            @if ($isPassed && ! $isCurrent)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                {{ $stepNum }}
                                            @endif
                                        </div>
                                        <span class="text-xs mt-1.5 font-semibold {{ $isPassed ? 'text-[#1F6B4F]' : 'text-gray-400' }}">
                                            {{ $step->label() }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 p-4 rounded-xl mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div class="text-xs text-red-900">
                                <strong class="font-bold block text-sm">Laporan Ditolak</strong>
                                <span>Laporan ini tidak dapat diproses lebih lanjut oleh aparatur desa. Silakan baca catatan penolakan pada riwayat di bawah.</span>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Timeline Kronologis (PBI #31) --}}
                <div class="relative border-s border-gray-200 ms-3 space-y-6 pt-2">
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
                                    Diproses oleh: <strong>{{ $riwayat->diubahOleh->role === \App\Enums\Role::Warga ? 'Warga Pelapor' : 'Aparatur Desa Banjarsari' }}</strong>
                                </span>
                            </div>

                            @if ($riwayat->catatan)
                                <div class="bg-emerald-50/60 p-3.5 rounded-xl border border-emerald-100/80 text-xs text-gray-800 mt-2">
                                    <span class="font-semibold text-emerald-900 block text-[11px] mb-0.5">Catatan Perkembangan:</span>
                                    <p class="whitespace-pre-line text-gray-900 font-medium">{{ $riwayat->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="ms-6 text-xs text-gray-500">
                            Belum ada riwayat pembaruan status.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Diskusi Komentar (PBI #33, PBI #34) --}}
            @include('pengaduan.partials.komentar-section')
        </div>
    </div>
</x-app-layout>
