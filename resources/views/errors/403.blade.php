<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak | {{ config('app.name', 'Pengaduan Desa Banjarsari') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased font-sans">
    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center space-y-6 bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <div>
                <span class="text-sm font-semibold tracking-wider text-red-600 uppercase">Kesalahan 403</span>
                <h1 class="mt-2 text-2xl font-bold text-gray-900 tracking-tight sm:text-3xl">Akses Tidak Diizinkan</h1>
                <p class="mt-3 text-sm text-gray-600">
                    {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin atau hak akses untuk membuka halaman ini.' }}
                </p>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Kembali ke Beranda
                </a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                            Keluar Akun
                        </button>
                    </form>
                @endauth
            </div>
        </div>
        <p class="mt-8 text-xs text-gray-400 text-center">
            &copy; {{ date('Y') }} Pemerintah Desa Banjarsari. Seluruh hak cipta dilindungi.
        </p>
    </div>
</body>
</html>
