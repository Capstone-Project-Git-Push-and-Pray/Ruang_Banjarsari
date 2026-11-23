<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        <a href="{{ route('warga.aspirasi.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Aspirasi Warga</span>
        </a>

        {{-- ==================== ISI ASPIRASI (PBI #40) ==================== --}}
        <article class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4] space-y-6">
            @include('warga.aspirasi.partials.detail')

            @include('warga.aspirasi.partials.tombol-dukung', ['aspirasi' => $pengaduan])
        </article>

        {{-- ==================== KOMENTAR PUBLIK (PBI #42) ==================== --}}
        @include('pengaduan.partials.komentar-section')
    </div>
</x-app-layout>
