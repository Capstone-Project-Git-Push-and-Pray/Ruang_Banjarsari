<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin Desa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Dashboard Admin Desa - placeholder</h3>
                    <p class="text-sm text-gray-600">Selamat datang, <strong>{{ Auth::user()->nama_lengkap }}</strong>. Halaman ini adalah placeholder untuk dashboard administrator desa.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
