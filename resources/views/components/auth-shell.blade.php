@props(['title'])

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — SILAFA Banjarsari</title>

    <link rel="icon" type="image/png" href="{{ asset('images/Logo_DesaBanjarsari.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#F8FAF9] text-gray-800 font-sans min-h-screen flex flex-col relative selection:bg-[#1F6B4F] selection:text-white">

    {{-- Ambient Background --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[550px] bg-gradient-to-b from-[#1F6B4F]/10 via-[#1F6B4F]/[0.02] to-transparent blur-3xl opacity-80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#1F6B4F_1px,transparent_1px)] [background-size:24px_24px] opacity-[0.035]"></div>
    </div>

    {{-- Navbar --}}
    <header class="bg-white/95 backdrop-blur-md border-b border-[#E2E8E4] sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/Logo_DesaBanjarsari.png') }}" alt="Logo Desa Banjarsari" class="w-10 h-10 object-contain">
                    <div>
                        <span class="font-display font-bold text-gray-900 text-sm sm:text-base leading-tight block group-hover:text-[#1F6B4F] transition-colors">SILAFA Banjarsari</span>
                        <span class="text-[11px] text-gray-500 hidden sm:block">Pemerintah Desa Banjarsari</span>
                    </div>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 sm:px-5 sm:py-2 text-xs sm:text-sm font-semibold text-white bg-[#1F6B4F] hover:bg-[#174B39] rounded-lg shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main class="flex-1 flex items-center justify-center px-4 py-10 sm:py-16">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer ringkas --}}
    <footer class="border-t border-[#E2E8E4] bg-white/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-gray-400">
            <p>&copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Seluruh Hak Cipta Dilindungi.</p>
            <p>Amanah • Transparan • Akuntabel</p>
        </div>
    </footer>
</body>
</html>
