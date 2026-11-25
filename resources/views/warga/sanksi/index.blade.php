<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('dashboard') }}" class="tombol-kembali inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda</span>
        </a>

        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Status Sanksi Akun</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Fitur yang dibatasi, alasan pembatasan, dan kapan pembatasan berakhir.</p>
        </div>

        {{-- ==================== SANKSI YANG SEDANG BERLAKU ==================== --}}
        @if ($sanksiBerlaku->isEmpty())
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-5 rounded-2xl text-sm shadow-xs">
                <strong class="font-bold">Tidak ada pembatasan.</strong> Semua fitur akun Anda dapat digunakan.
            </div>
        @endif

        @foreach ($sanksiBerlaku as $sanksi)
            @include('warga.sanksi.partials.kartu', ['sanksi' => $sanksi])
        @endforeach

        {{-- ==================== RIWAYAT ==================== --}}
        @if ($riwayatSanksi->isNotEmpty())
            <div class="pt-2">
                <h2 class="text-base font-bold text-gray-900">Riwayat Sanksi dan Peringatan</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Pelanggaran dalam {{ \App\Models\Sanksi::MASA_HITUNG_HARI }} hari terakhir menentukan tingkat sanksi berikutnya. Sanksi yang dibatalkan Admin Desa tidak dihitung.
                </p>
            </div>
            @foreach ($riwayatSanksi as $sanksi)
                @include('warga.sanksi.partials.kartu', ['sanksi' => $sanksi])
            @endforeach
        @endif
    </div>
</x-app-layout>
