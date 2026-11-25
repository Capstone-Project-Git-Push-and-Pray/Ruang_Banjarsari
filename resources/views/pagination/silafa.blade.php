{{--
    Navigasi halaman bertema SILAFA (bawaan seluruh ->links() di aplikasi).
    Ponsel: tombol Sebelumnya/Berikutnya dengan keterangan halaman.
    Tablet & desktop: keterangan jumlah data dan deretan nomor halaman.
--}}
@if ($paginator->hasPages())
    @php
        $kelasTombol = 'inline-flex items-center justify-center min-w-9 h-9 px-2.5 rounded-xl text-xs font-semibold transition-all duration-150';
        $kelasAktif = 'bg-[#1F6B4F] text-white shadow-sm shadow-[#1F6B4F]/25';
        $kelasBiasa = 'bg-white text-gray-600 border border-[#E2E8E4] hover:border-[#1F6B4F]/40 hover:text-[#1F6B4F] hover:bg-[#F4F9F6]';
        $kelasNonaktif = 'bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed';
        // Tombol ke halaman pertama muncul setelah pengguna maju 2 halaman atau lebih dari awal
        $tampilkanKeAwal = $paginator->currentPage() >= 3;
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman" class="w-full">
        {{-- ==================== PONSEL ==================== --}}
        <div class="flex items-center justify-between gap-2 sm:hidden">
            <div class="flex items-center gap-2">
            @if ($tampilkanKeAwal)
                <a href="{{ $paginator->url(1) }}" class="{{ $kelasTombol }} {{ $kelasBiasa }}" aria-label="Ke halaman pertama" title="Ke halaman pertama">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </a>
            @endif
            @if ($paginator->onFirstPage())
                <span class="{{ $kelasTombol }} {{ $kelasNonaktif }} gap-1.5 {{ $tampilkanKeAwal ? '' : 'px-3.5' }}" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="{{ $tampilkanKeAwal ? 'sr-only' : '' }}">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $kelasTombol }} {{ $kelasBiasa }} gap-1.5 {{ $tampilkanKeAwal ? '' : 'px-3.5' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="{{ $tampilkanKeAwal ? 'sr-only' : '' }}">Sebelumnya</span>
                </a>
            @endif
            </div>

            <span class="text-xs text-gray-500 whitespace-nowrap">
                <span class="{{ $tampilkanKeAwal ? '' : 'sr-only' }}">Halaman</span> <span class="font-bold text-gray-800">{{ $paginator->currentPage() }}</span> {{ $tampilkanKeAwal ? 'dari' : '/' }} {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $kelasTombol }} {{ $kelasBiasa }} gap-1.5 {{ $tampilkanKeAwal ? '' : 'px-3.5' }}">
                    <span class="{{ $tampilkanKeAwal ? 'sr-only' : '' }}">Berikutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="{{ $kelasTombol }} {{ $kelasNonaktif }} gap-1.5 {{ $tampilkanKeAwal ? '' : 'px-3.5' }}" aria-disabled="true">
                    <span class="{{ $tampilkanKeAwal ? 'sr-only' : '' }}">Berikutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- ==================== TABLET & DESKTOP ==================== --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-gray-500">
                Menampilkan
                <span class="font-bold text-gray-800">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-bold text-gray-800">{{ $paginator->total() }}</span>
                data
            </p>

            <div class="flex items-center gap-1.5">
                {{-- Ke halaman pertama --}}
                @if ($tampilkanKeAwal)
                    <a href="{{ $paginator->url(1) }}" class="{{ $kelasTombol }} {{ $kelasBiasa }} gap-1.5 px-3" aria-label="Ke halaman pertama">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                        <span>Halaman Pertama</span>
                    </a>
                    <span class="w-px h-5 bg-[#E2E8E4] mx-0.5" aria-hidden="true"></span>
                @endif

                {{-- Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <span class="{{ $kelasTombol }} {{ $kelasNonaktif }}" aria-disabled="true" aria-label="Halaman sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $kelasTombol }} {{ $kelasBiasa }}" aria-label="Halaman sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Nomor halaman --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $kelasTombol }} text-gray-400" aria-disabled="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $kelasTombol }} {{ $kelasAktif }}" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $kelasTombol }} {{ $kelasBiasa }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Berikutnya --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $kelasTombol }} {{ $kelasBiasa }}" aria-label="Halaman berikutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="{{ $kelasTombol }} {{ $kelasNonaktif }}" aria-disabled="true" aria-label="Halaman berikutnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
