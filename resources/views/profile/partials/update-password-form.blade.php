@php
    $kolomKataSandi = [
        ['id' => 'update_password_current_password', 'name' => 'current_password', 'label' => 'Kata Sandi Saat Ini', 'autocomplete' => 'current-password', 'placeholder' => 'Masukkan kata sandi saat ini'],
        ['id' => 'update_password_password', 'name' => 'password', 'label' => 'Kata Sandi Baru', 'autocomplete' => 'new-password', 'placeholder' => 'Minimal 8 karakter'],
        ['id' => 'update_password_password_confirmation', 'name' => 'password_confirmation', 'label' => 'Konfirmasi Kata Sandi Baru', 'autocomplete' => 'new-password', 'placeholder' => 'Ulangi kata sandi baru'],
    ];
@endphp

<section>
    <header class="flex items-start gap-3">
        <div class="w-10 h-10 shrink-0 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 10V7.5a4.5 4.5 0 019 0V10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><rect x="4" y="9.5" width="16" height="11.5" rx="3.2" fill="currentColor"/><circle cx="8.5" cy="15.25" r="1.3" fill="#EAF5EE"/><circle cx="12" cy="15.25" r="1.3" fill="#EAF5EE"/><circle cx="15.5" cy="15.25" r="1.3" fill="#EAF5EE"/></svg>
        </div>
        <div>
            <h2 class="text-base font-bold text-gray-900">Ubah Kata Sandi</h2>
            <p class="mt-0.5 text-xs text-gray-500">Gunakan kata sandi yang panjang dan sulit ditebak.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        @foreach ($kolomKataSandi as $kolom)
            <div class="space-y-1.5" x-data="{ tampil: false }">
                <label for="{{ $kolom['id'] }}" class="text-xs font-bold text-gray-800">{{ $kolom['label'] }}</label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <input id="{{ $kolom['id'] }}" name="{{ $kolom['name'] }}" :type="tampil ? 'text' : 'password'" type="password"
                           autocomplete="{{ $kolom['autocomplete'] }}" placeholder="{{ $kolom['placeholder'] }}"
                           class="w-full pl-10 pr-11 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all {{ $errors->updatePassword->has($kolom['name']) ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
                    <button type="button" @click="tampil = ! tampil" tabindex="-1" aria-label="Tampilkan kata sandi"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg x-show="! tampil" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="tampil" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                    </button>
                </div>
                @if ($errors->updatePassword->has($kolom['name']))
                    <p class="text-xs text-rose-600">{{ $errors->updatePassword->first($kolom['name']) }}</p>
                @endif
            </div>
        @endforeach

        <div class="pt-2 space-y-3">
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1F6B4F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Kata sandi berhasil diperbarui.
                </p>
            @endif
            <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-bold shadow-xs transition-colors cursor-pointer">
                Perbarui Kata Sandi
            </button>
        </div>
    </form>
</section>
