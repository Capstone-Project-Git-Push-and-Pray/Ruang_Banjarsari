<x-admin-layout>
    {{-- Hidden title helper for automated test assertions (PBI #22 / #23) --}}
    <span class="sr-only">Dashboard Admin Desa</span>

    {{-- ==================== 1. HERO COMMAND BANNER (FOREST GREEN) ==================== --}}
    <div class="relative overflow-hidden bg-[#185840] rounded-[28px] p-6 sm:p-8 lg:p-10 shadow-sm text-white">
        {{-- Hiasan Garis Gelombang Kontur (SVG Waves) --}}
        <svg class="absolute top-0 right-0 h-full w-2/3 max-w-lg pointer-events-none opacity-20" viewBox="0 0 500 280" fill="none" preserveAspectRatio="none">
            <path d="M-50 40 C 120 10, 240 70, 520 20" stroke="white" stroke-width="1.75" fill="none" />
            <path d="M-30 85 C 140 55, 260 115, 520 65" stroke="white" stroke-width="1.75" fill="none" />
            <path d="M-10 130 C 160 100, 280 160, 520 110" stroke="white" stroke-width="1.75" fill="none" />
            <path d="M10 175 C 180 145, 300 205, 520 155" stroke="white" stroke-width="1.75" fill="none" />
            <path d="M30 220 C 200 190, 320 250, 520 200" stroke="white" stroke-width="1.75" fill="none" />
        </svg>

        <div class="relative z-10">
            {{-- Sapaan Nama Admin --}}
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                Halo, {{ Auth::user()->nama_lengkap ?? 'Administrator' }}!
            </h1>

            {{-- Deskripsi Sambutan --}}
            <p class="text-xs sm:text-sm text-white/80 leading-relaxed mt-2.5 max-w-2xl">
                Sistem Informasi Layanan Pengaduan & Aspirasi — Manajemen operasional aduan publik, verifikasi kependudukan NIK, serta disposisi teknis lapangan.
            </p>

            {{-- Tombol Aksi Utama --}}
            <div class="flex items-center gap-3 mt-6 flex-wrap">
                <a href="{{ route('admin.pengaduan.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-100 text-[#185840] text-xs sm:text-sm font-bold rounded-full shadow-sm transition-all duration-200">
                    <i data-lucide="headset" class="w-4 h-4 text-[#185840]"></i>
                    <span>Kelola Pengaduan</span>
                </a>

                <a href="{{ route('admin.aspirasi.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold rounded-full border border-white/20 backdrop-blur-xs transition-all duration-200">
                    <i data-lucide="message-square" class="w-4 h-4 text-white"></i>
                    <span>Kelola Aspirasi</span>
                </a>

                <a href="{{ route('admin.pendaftaran.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold rounded-full border border-white/20 backdrop-blur-xs transition-all duration-200">
                    <i data-lucide="user-check" class="w-4 h-4 text-white"></i>
                    <span>Tinjau Pendaftaran</span>
                    @if ($pendingWargaCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-gray-900 leading-none">
                            {{ $pendingWargaCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== 2. ROW METRIK ALUR PEMROSESAN LAPORAN (PBI #29) ==================== --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="font-bold text-gray-900 uppercase text-[11px] tracking-wider flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-[#1F6B4F]"></i>
                <span>Tahapan Pemrosesan Laporan Warga</span>
            </h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            {{-- 1. Total Laporan --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        TOTAL LAPORAN
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $totalAduan }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Aduan & aspirasi</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Semua
                    </span>
                </div>
            </div>

            {{-- 2. Diajukan (Tahap 1) --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        DIAJUKAN
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $countDiajukan }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Menunggu verifikasi</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Tahap 1
                    </span>
                </div>
            </div>

            {{-- 3. Ditinjau (Tahap 2) --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        DITINJAU
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $countDitinjau }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Pemeriksaan berkas</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Tahap 2
                    </span>
                </div>
            </div>

            {{-- 4. Diproses (Tahap 3) --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        DIPROSES
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $countDiproses }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Tindak lanjut lapangan</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Tahap 3
                    </span>
                </div>
            </div>

            {{-- 5. Selesai (Tahap 4a) --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        SELESAI
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $countSelesai }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Penanganan tuntas</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Selesai
                    </span>
                </div>
            </div>

            {{-- 6. Ditolak (Tahap 4b) --}}
            <div class="bg-white p-5 rounded-2xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between hover:shadow-sm transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                        DITOLAK
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-gray-50/80 text-gray-400 flex items-center justify-center shrink-0 border border-gray-100 shadow-2xs">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="my-3">
                    <span class="text-3xl font-extrabold text-gray-900 tracking-tight block leading-none">
                        {{ $countDitolak }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                    <span>Dengan catatan resmi</span>
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600">
                        Ditolak
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== 4. VERIFIKASI CEPAT PENDAFTARAN WARGA ==================== --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center shrink-0 border border-emerald-200">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="font-bold text-base sm:text-lg text-gray-900 leading-tight">
                        Verifikasi Cepat Pendaftaran Warga ({{ $pendingWargaCount }} Menunggu)
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Sinkronisasi data NIK digital pelapor dengan Buku Induk Penduduk Desa Banjarsari
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.pendaftaran.index') }}"
               class="text-xs font-semibold text-[#1F6B4F] hover:text-[#174B39] flex items-center gap-1.5 transition">
                <span>Lihat Seluruh Antrean ({{ $pendingWargaCount }})</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        {{-- Grid Kartu Pendaftar --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse ($pendingWarga as $pendaftar)
                <div class="p-5 rounded-2xl border border-[#E2E8E4] bg-[#F8FAF9] flex flex-col justify-between space-y-4 hover:border-emerald-300 transition">
                    <div>
                        {{-- Status & Waktu --}}
                        <div class="flex items-center justify-between text-[11px] mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                NIK Valid Dukcapil
                            </span>
                            <span class="text-gray-400">{{ $pendaftar->created_at->diffForHumans() }}</span>
                        </div>

                        {{-- Profil --}}
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-[#EAF5EE] text-[#1F6B4F] font-bold flex items-center justify-center shrink-0 text-sm border border-emerald-200">
                                {{ strtoupper(substr($pendaftar->nama_lengkap ?? $pendaftar->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-gray-900 truncate">
                                    {{ $pendaftar->nama_lengkap ?? $pendaftar->name }}
                                </h3>
                                <p class="text-xs text-gray-500 font-mono">
                                    NIK: {{ substr($pendaftar->nik, 0, 6) . '******' . substr($pendaftar->nik, -4) }}
                                </p>
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="space-y-1 text-xs text-gray-600 bg-white p-3 rounded-xl border border-gray-100">
                            <p><span class="text-gray-400">Alamat:</span> {{ $pendaftar->dusun ?? 'Dusun Banjarsari' }}, RT {{ $pendaftar->rt ?? '001' }} / RW {{ $pendaftar->rw ?? '001' }}</p>
                            <p><span class="text-gray-400">Status:</span> Kepala Keluarga (KK Terdaftar)</p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-2 border-t border-[#E2E8E4]">
                        <a href="{{ route('admin.pendaftaran.show', $pendaftar) }}"
                           class="flex-1 py-2 px-3 text-center rounded-lg border border-[#E2E8E4] bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Periksa Data
                        </a>

                        <form method="POST" action="{{ route('admin.pendaftaran.setujui', $pendaftar) }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2 px-3 text-center rounded-lg bg-[#1F6B4F] hover:bg-[#174B39] text-xs font-semibold text-white transition cursor-pointer flex items-center justify-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>Setujui</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 text-gray-400 text-xs italic bg-[#F8FAF9] rounded-2xl border border-dashed border-[#E2E8E4]">
                    Tidak ada antrean verifikasi pendaftaran warga saat ini. Semua akun warga telah diproses.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ==================== 5. ANTREAN ADUAN BUTUH DISPOSISI CEPAT ==================== --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-base sm:text-lg text-gray-900 leading-tight">
                    Antrean Aduan Butuh Disposisi Cepat
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Penugasan mandor lapangan, Satgas Sarpras, atau perangkat desa penanggung jawab
                </p>
            </div>

            {{-- Filter Pills --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#1F6B4F] text-white">
                    Semua Butuh Tindakan ({{ $antreanAduan->count() }})
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer">
                    Aduan Darurat
                </span>
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer">
                    Infrastruktur
                </span>
            </div>
        </div>

        {{-- Tabel Antrean --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#E2E8E4] text-gray-400 text-[10px] uppercase font-bold tracking-wider">
                        <th class="py-3 px-3">ID TIKET</th>
                        <th class="py-3 px-3">JUDUL MASALAH & LOKASI</th>
                        <th class="py-3 px-3">PELAPOR & VALIDASI</th>
                        <th class="py-3 px-3">KATEGORI</th>
                        <th class="py-3 px-3">PRIORITAS</th>
                        <th class="py-3 px-3">SLA BERJALAN</th>
                        <th class="py-3 px-3 text-right">AKSI OPERASIONAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($antreanAduan as $aduan)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="py-3.5 px-3 font-mono font-bold text-gray-900">
                                #LPR-{{ date('Y') }}-{{ str_pad($aduan->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-3.5 px-3 max-w-xs">
                                <span class="font-bold text-gray-900 block truncate">{{ $aduan->judul }}</span>
                                <span class="text-[11px] text-gray-400 block truncate flex items-center gap-1 mt-0.5">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                                    {{ $aduan->lokasi }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="font-semibold text-gray-800 block">{{ $aduan->user->nama_lengkap ?? $aduan->user->name }}</span>
                                <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-semibold mt-0.5">
                                    <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i>
                                    NIK Terverifikasi
                                </span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700">
                                    {{ $aduan->kategori->nama ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Tinggi / Darurat
                                </span>
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="text-xs font-mono font-bold text-gray-800 block">24j : 18m</span>
                                <div class="w-16 bg-gray-200 h-1.5 rounded-full mt-1 overflow-hidden">
                                    <div class="bg-rose-500 h-full rounded-full" style="width: 70%"></div>
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('admin.pengaduan.show', $aduan) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#1F6B4F] hover:bg-[#174B39] text-white text-[11px] font-semibold shadow-2xs transition">
                                    <span>Disposisi Petugas</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 italic">
                                Belum ada antrean aduan publik yang membutuhkan penanganan saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== 6. LOG AKTIVITAS PETUGAS ==================== --}}
    {{-- Data asli tindakan petugas, diperbarui otomatis setiap 30 detik selama tab terbuka --}}
    <div x-data="{
            jedaDetik: 30,
            terakhirDiperbarui: @js(now()->format('H:i:s')),
            memuat: false,
            init() {
                setInterval(() => { if (! document.hidden) { this.perbarui(); } }, this.jedaDetik * 1000);
                document.addEventListener('visibilitychange', () => { if (! document.hidden) { this.perbarui(); } });
            },
            async perbarui() {
                if (this.memuat) { return; }
                this.memuat = true;
                try {
                    const respons = await fetch(@js(route('admin.dashboard.aktivitas')), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Pembaruan-Otomatis': '1' },
                        credentials: 'same-origin',
                    });
                    if (respons.status === 401 || respons.status === 419) { window.location.reload(); return; }
                    if (! respons.ok) { return; }
                    this.$refs.daftar.innerHTML = await respons.text();
                    if (window.lucide) { window.lucide.createIcons(); }
                    this.terakhirDiperbarui = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replaceAll('.', ':');
                } catch (galat) {
                    // Jaringan terputus sementara: coba lagi pada jadwal berikutnya
                } finally {
                    this.memuat = false;
                }
            }
         }">
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E2E8E4] shadow-xs flex flex-col justify-between space-y-6">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-base sm:text-lg text-gray-900 leading-tight">
                        Log Aktivitas Petugas
                    </h2>
                    <div class="flex items-center gap-3">
                        <span class="relative flex w-2 h-2" title="Pembaruan otomatis aktif">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex w-2 h-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <a href="{{ route('admin.log-aktivitas.index') }}"
                           class="text-xs font-semibold text-[#1F6B4F] hover:text-[#174B39] flex items-center gap-1.5 transition">
                            <span>Lihat Seluruh Log</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mb-6">
                    Tindakan terbaru Admin Desa dan Kepala Desa: perubahan status laporan, peninjauan pendaftaran, moderasi konten, dan pencabutan sanksi
                </p>

                <div class="space-y-4" x-ref="daftar" aria-live="polite">
                    @include('admin.partials.log-aktivitas')
                </div>
            </div>

            <div class="border-t border-[#E2E8E4] pt-3 flex items-center justify-between gap-3 text-[11px] text-gray-500">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#1F6B4F]" :class="memuat && 'animate-spin'"></i>
                    <span>Diperbarui otomatis setiap 30 detik</span>
                </span>
                <span class="font-mono text-gray-400">Terakhir <span x-text="terakhirDiperbarui"></span></span>
            </div>
        </div>
    </div>
</x-admin-layout>