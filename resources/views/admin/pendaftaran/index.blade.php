<x-admin-layout>
    @php
        $title = 'Peninjauan Pendaftaran Warga';
        $breadcrumb = 'Tinjau Pendaftaran';
    @endphp

    <div class="space-y-6">

        {{-- ==================== 1. HERO COMMAND BANNER ==================== --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E2E8E4] shadow-xs space-y-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                {{-- Title & Info --}}
                <div class="space-y-2 max-w-3xl">
                    <h1 class="font-extrabold text-2xl sm:text-3xl text-gray-900 tracking-tight leading-snug">
                        Peninjauan Pendaftaran Warga
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                        Daftar akun warga yang menunggu peninjauan dan verifikasi manual identitas kependudukan dengan Data Kependudukan Desa Banjarsari sebelum akun diaktifkan.
                    </p>
                </div>

                {{-- Action Shortcuts --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('admin.kependudukan.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2E8E4] hover:border-gray-300 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-2xs">
                        <i data-lucide="contact-round" class="w-4 h-4 text-[#1F6B4F]"></i>
                        <span>Data Kependudukan</span>
                    </a>
                    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>{{ $pendaftarList->total() }} Menunggu Tinjauan</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- FLASH ALERT --}}
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="font-bold text-emerald-950 text-sm">Berhasil</div>
                    <div class="text-xs text-emerald-800 mt-0.5">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        {{-- ==================== 2. TABEL ANTREAN PENDAFTAR ==================== --}}
        <div class="bg-white rounded-3xl border border-[#E2E8E4] shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-[#E2E8E4] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base sm:text-lg text-gray-900 leading-tight">
                            Antrean Verifikasi Pendaftar
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Diurutkan dari pendaftaran terlama agar tidak ada permohonan warga yang terlewat.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Total antrean: <strong>{{ $pendaftarList->total() }}</strong> warga</span>
                </div>
            </div>

            @if ($pendaftarList->isEmpty())
                <div class="py-16 px-4 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center mx-auto mb-4 border border-emerald-100">
                        <i data-lucide="check-check" class="w-8 h-8"></i>
                    </div>
                    <h4 class="font-extrabold text-gray-900 text-base">Tidak Ada Antrean Pendaftaran</h4>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Seluruh pendaftaran warga saat ini sudah ditinjau atau terverifikasi otomatis.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.kependudukan.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-[#1F6B4F] bg-[#EAF5EE] hover:bg-emerald-100 transition">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Buka Data Kependudukan</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-[#F8FAF9] text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-[#E2E8E4]">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">No</th>
                                <th scope="col" class="py-4 px-6">Identitas Warga</th>
                                <th scope="col" class="py-4 px-6">NIK</th>
                                <th scope="col" class="py-4 px-6">Kontak</th>
                                <th scope="col" class="py-4 px-6">Waktu Mendaftar</th>
                                <th scope="col" class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8E4]">
                            @foreach ($pendaftarList as $index => $warga)
                                <tr class="hover:bg-[#F8FAF9]/80 transition-colors">
                                    <td class="py-4 px-6 text-xs text-gray-400 font-semibold text-center">
                                        {{ $pendaftarList->firstItem() + $index }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] font-bold text-xs flex items-center justify-center shrink-0 border border-emerald-100 shadow-2xs">
                                                {{ strtoupper(substr($warga->nama_lengkap, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-sm">{{ $warga->nama_lengkap }}</div>
                                                <div class="text-xs text-gray-400 flex items-center gap-1.5 mt-0.5">
                                                    <i data-lucide="map-pin" class="w-3 h-3 text-gray-400"></i>
                                                    <span>{{ $warga->dusun ?? 'Dusun tidak diisi' }} (RT {{ $warga->rt ?? '-' }}/RW {{ $warga->rw ?? '-' }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-mono text-xs font-bold bg-gray-50 px-2.5 py-1.5 rounded-lg text-gray-800 border border-[#E2E8E4]">
                                            {{ $warga->nik }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs space-y-0.5">
                                        <div class="text-gray-800 font-medium flex items-center gap-1.5">
                                            <i data-lucide="phone" class="w-3 h-3 text-gray-400"></i>
                                            <span>{{ $warga->telepon ?: '-' }}</span>
                                        </div>
                                        <div class="text-gray-400 truncate max-w-[180px] flex items-center gap-1.5">
                                            <i data-lucide="mail" class="w-3 h-3 text-gray-400"></i>
                                            <span>{{ $warga->email ?: '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-xs">
                                        <div class="font-semibold text-gray-800">
                                            {{ $warga->created_at->format('d M Y, H:i') }}
                                        </div>
                                        <div class="text-gray-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>{{ $warga->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('admin.pendaftaran.show', $warga) }}"
                                           class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-[#1F6B4F] hover:bg-[#174B39] rounded-xl shadow-xs transition-colors">
                                            <span>Bandingkan & Tinjau</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($pendaftarList->hasPages())
                    <div class="p-5 border-t border-[#E2E8E4] bg-white">
                        {{ $pendaftarList->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</x-admin-layout>
