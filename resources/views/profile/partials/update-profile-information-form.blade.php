@php
    $kelasInput = 'w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all';
    $kelasTerkunci = 'w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#E2E8E4] bg-gray-50 text-sm text-gray-500 cursor-not-allowed';
@endphp

<section>
    <header class="flex items-start gap-3">
        <div class="w-10 h-10 shrink-0 rounded-xl bg-[#EAF5EE] text-[#1F6B4F] flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
            <h2 class="text-base font-bold text-gray-900">Informasi Profil & Kontak</h2>
            <p class="mt-0.5 text-xs text-gray-500">Pastikan email dan nomor telepon aktif agar pemberitahuan dan tautan reset kata sandi dapat diterima.</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        @if ($user->isWarga())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- NIK (terkunci untuk warga) --}}
                <div class="space-y-1.5">
                    <label for="nik" class="text-xs font-bold text-gray-800">Nomor Induk Kependudukan (NIK)</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <input id="nik" type="text" value="{{ $user->nik }}" disabled class="{{ $kelasTerkunci }} font-mono">
                        <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>

                {{-- Nama lengkap (terkunci untuk warga) --}}
                <div class="space-y-1.5">
                    <label for="nama_lengkap" class="text-xs font-bold text-gray-800">Nama Lengkap Sesuai KTP</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input id="nama_lengkap" type="text" value="{{ $user->nama_lengkap }}" disabled class="{{ $kelasTerkunci }} uppercase">
                        <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                </div>
            </div>
            <p class="-mt-2 text-[11px] text-gray-400">NIK dan nama lengkap mengikuti data kependudukan desa dan tidak dapat diubah sendiri. Hubungi Balai Desa jika ada kesalahan.</p>
        @else
            <div class="space-y-1.5">
                <label for="name" class="text-xs font-bold text-gray-800">Nama Lengkap <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->nama_lengkap) }}" required autocomplete="name"
                           class="{{ $kelasInput }} {{ $errors->has('name') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
                </div>
                @error('name')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Email --}}
        <div class="space-y-1.5">
            <label for="email" class="text-xs font-bold text-gray-800">Alamat Email <span class="text-rose-500">*</span></label>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" placeholder="nama@email.com"
                       class="{{ $kelasInput }} {{ $errors->has('email') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
            </div>
            @error('email')
                <p class="text-xs text-rose-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="text-xs text-amber-700">
                    Alamat email Anda belum terverifikasi.
                    <button form="send-verification" class="font-semibold underline hover:text-amber-900 cursor-pointer">Kirim ulang email verifikasi</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="text-xs font-semibold text-[#1F6B4F]">Tautan verifikasi baru telah dikirim ke email Anda.</p>
                @endif
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {{-- Telepon --}}
            <div class="space-y-1.5">
                <label for="telepon" class="text-xs font-bold text-gray-800">
                    Nomor Telepon / HP
                    @if ($user->isWarga())
                        <span class="text-rose-500">*</span>
                    @endif
                </label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11.04 11.04 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/></svg>
                    <input id="telepon" name="telepon" type="tel" value="{{ old('telepon', $user->telepon) }}" placeholder="Contoh: 081234567890" @required($user->isWarga())
                           class="{{ $kelasInput }} {{ $errors->has('telepon') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
                </div>
                @error('telepon')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- WhatsApp --}}
            <div class="space-y-1.5">
                <label for="whatsapp" class="text-xs font-bold text-gray-800">Nomor WhatsApp <span class="font-normal text-gray-400">(opsional)</span></label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.668-.699c.974.536 1.764.814 2.792.814 3.18 0 5.766-2.587 5.766-5.766 0-3.18-2.586-5.766-5.766-5.766zm9.969 5.766c0 5.485-4.453 9.938-9.969 9.938-1.745 0-3.385-.45-4.819-1.237l-5.212 1.361 1.39-5.078c-.887-1.488-1.359-3.21-1.359-4.984 0-5.485 4.453-9.938 9.969-9.938 5.516 0 10 4.453 10 9.938z"/></svg>
                    <input id="whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="Contoh: 081234567890"
                           class="{{ $kelasInput }} {{ $errors->has('whatsapp') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
                </div>
                @error('whatsapp')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                   class="me-auto inline-flex items-center gap-1.5 text-xs font-semibold text-[#1F6B4F]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Data profil berhasil diperbarui.
                </p>
            @endif
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-bold shadow-xs transition-colors cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
