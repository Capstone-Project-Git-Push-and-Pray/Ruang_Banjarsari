<x-guest-layout>
    <div class="mb-5 text-center">
        <h2 class="font-display font-bold text-xl text-gray-900">Atur Ulang Kata Sandi</h2>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
            Lupa kata sandi akun Anda? Masukkan alamat email terdaftar dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi baru.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <x-input-label for="email" value="Alamat Email Terdaftar" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2 flex items-center justify-between">
            <a href="{{ route('login') }}" class="text-xs text-gray-600 hover:text-[#1F6B4F] font-medium transition-colors">
                &larr; Kembali ke Login
            </a>
            <x-primary-button class="bg-[#1F6B4F] hover:bg-[#174B39]">
                Kirim Tautan Reset
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
