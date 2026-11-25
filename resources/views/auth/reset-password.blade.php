<x-auth-shell title="Buat Kata Sandi Baru">
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl overflow-hidden"
         x-data="{
             password: '',
             konfirmasi: '',
             tampilkanSandi: false,
             tampilkanKonfirmasi: false,
             get kekuatan() {
                 let skor = 0;
                 if (this.password.length >= 8) skor++;
                 if (/[a-z]/.test(this.password) && /[A-Z]/.test(this.password)) skor++;
                 if (/[0-9]/.test(this.password)) skor++;
                 if (/[^A-Za-z0-9]/.test(this.password) || this.password.length >= 12) skor++;
                 return this.password.length === 0 ? 0 : Math.max(1, skor);
             },
             get labelKekuatan() {
                 return ['', 'Lemah', 'Cukup', 'Baik', 'Kuat'][this.kekuatan];
             },
             get cocok() {
                 return this.konfirmasi.length > 0 && this.konfirmasi === this.password;
             }
         }">
        {{-- Strip aksen tema --}}
        <div class="h-1.5 bg-gradient-to-r from-[#1A5B42] via-[#1F6B4F] to-emerald-400"></div>

        <div class="p-7 sm:p-9">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-[#1F6B4F] to-[#174B39] shadow-lg shadow-[#1F6B4F]/25 ring-4 ring-[#EAF5EE] flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 10V7.5a4.5 4.5 0 019 0V10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><rect x="4" y="9.5" width="16" height="11.5" rx="3.2" fill="currentColor"/><circle cx="8.5" cy="15.25" r="1.3" fill="#1F6B4F"/><circle cx="12" cy="15.25" r="1.3" fill="#1F6B4F"/><circle cx="15.5" cy="15.25" r="1.3" fill="#1F6B4F"/></svg>
                </div>
                <h1 class="mt-5 font-display font-extrabold text-2xl text-gray-900 tracking-tight">Buat Kata Sandi Baru</h1>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                    Gunakan kata sandi yang kuat dan belum pernah Anda pakai sebelumnya.
                </p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold text-gray-800">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $request->email) }}"
                               required
                               autocomplete="username"
                               class="w-full pl-10 pr-4 py-3 border {{ $errors->has('email') ? 'bg-rose-50/60 border-rose-400' : 'bg-gray-50 border-[#D5DDD6]' }} rounded-xl text-sm text-gray-700 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                    </div>
                    @error('email')
                        <p role="alert" class="text-xs text-rose-600">
                            {{ $message }}
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="font-semibold underline hover:text-rose-700">Minta tautan baru</a>
                            @endif
                        </p>
                    @enderror
                </div>

                {{-- Kata Sandi Baru --}}
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-bold text-gray-800">Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input id="password"
                               :type="tampilkanSandi ? 'text' : 'password'"
                               name="password"
                               x-model="password"
                               required
                               autofocus
                               autocomplete="new-password"
                               placeholder="Minimal 8 karakter"
                               class="w-full pl-10 pr-11 py-3 border {{ $errors->has('password') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }} rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                        <button type="button" @click="tampilkanSandi = !tampilkanSandi" tabindex="-1" aria-label="Tampilkan kata sandi"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg x-show="!tampilkanSandi" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="tampilkanSandi" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>

                    {{-- Indikator kekuatan --}}
                    <div x-show="password.length > 0" style="display: none;" class="pt-1">
                        <div class="flex gap-1">
                            <template x-for="i in 4" :key="i">
                                <span class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                                      :class="i <= kekuatan ? ['', 'bg-rose-500', 'bg-amber-400', 'bg-emerald-400', 'bg-[#1F6B4F]'][kekuatan] : 'bg-gray-200'"></span>
                            </template>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500">Kekuatan: <span class="font-semibold text-gray-700" x-text="labelKekuatan"></span></p>
                    </div>

                    @error('password')
                        <p role="alert" class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Kata Sandi --}}
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-bold text-gray-800">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input id="password_confirmation"
                               :type="tampilkanKonfirmasi ? 'text' : 'password'"
                               name="password_confirmation"
                               x-model="konfirmasi"
                               required
                               autocomplete="new-password"
                               placeholder="Ulangi kata sandi baru"
                               class="w-full pl-10 pr-11 py-3 bg-white border border-[#D5DDD6] rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all"
                               :class="konfirmasi.length > 0 && ! cocok ? '!border-rose-400' : (cocok ? '!border-emerald-500' : '')">
                        <button type="button" @click="tampilkanKonfirmasi = !tampilkanKonfirmasi" tabindex="-1" aria-label="Tampilkan konfirmasi kata sandi"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg x-show="!tampilkanKonfirmasi" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="tampilkanKonfirmasi" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    <p x-show="konfirmasi.length > 0 && ! cocok" style="display: none;" class="text-xs text-rose-600">Kata sandi belum sama.</p>
                    <p x-show="cocok" style="display: none;" class="text-xs text-[#1F6B4F]">Kata sandi sudah sama.</p>
                    @error('password_confirmation')
                        <p role="alert" class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer group">
                    <span>Simpan Kata Sandi Baru</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center text-xs">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 font-semibold text-gray-600 hover:text-[#1F6B4F] transition-colors group">
                    <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Login
                </a>
            </div>
        </div>
    </div>
</x-auth-shell>
