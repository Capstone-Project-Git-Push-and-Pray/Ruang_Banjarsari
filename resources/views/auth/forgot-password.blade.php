<x-auth-shell title="Atur Ulang Kata Sandi">
    <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl overflow-hidden">
        {{-- Strip aksen tema --}}
        <div class="h-1.5 bg-gradient-to-r from-[#1A5B42] via-[#1F6B4F] to-emerald-400"></div>

        <div class="p-7 sm:p-9">
            @if (session('status'))
                {{-- ==================== STATE: TAUTAN TERKIRIM ==================== --}}
                <div class="text-center">
                    <div class="relative mx-auto w-16 h-16">
                        <span class="absolute inset-0 rounded-2xl bg-emerald-200/60 animate-ping"></span>
                        <div class="relative w-16 h-16 rounded-2xl bg-[#EAF5EE] border border-[#1F6B4F]/15 flex items-center justify-center">
                            <svg class="w-8 h-8 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <h1 class="mt-5 font-display font-extrabold text-2xl text-gray-900 tracking-tight">Periksa Email Anda</h1>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Jika
                        @if (old('email'))
                            <strong class="text-gray-800 break-all">{{ old('email') }}</strong>
                        @else
                            alamat email Anda
                        @endif
                        terdaftar, kami telah mengirimkan tautan untuk mengatur ulang kata sandi.
                    </p>
                </div>

                <ul class="mt-6 space-y-2.5 rounded-2xl bg-[#F8FAF9] border border-[#E2E8E4] p-4 text-xs text-gray-600">
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-[#1F6B4F] text-white text-[10px] font-bold flex items-center justify-center">1</span>
                        <span>Buka kotak masuk email Anda. Periksa juga folder <strong>Spam</strong> atau <strong>Promosi</strong>.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-[#1F6B4F] text-white text-[10px] font-bold flex items-center justify-center">2</span>
                        <span>Klik tombol <strong>Buat Kata Sandi Baru</strong> di dalam email tersebut.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full bg-[#1F6B4F] text-white text-[10px] font-bold flex items-center justify-center">3</span>
                        <span>Buat kata sandi baru, lalu masuk kembali ke SILAFA.</span>
                    </li>
                </ul>

                <a href="{{ route('login') }}"
                   class="mt-6 w-full py-3 px-4 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-200 flex items-center justify-center gap-2 group">
                    <span>Kembali ke Halaman Masuk</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>

                @if (old('email'))
                    <form method="POST" action="{{ route('password.email') }}" class="mt-4 text-center text-xs text-gray-500">
                        @csrf
                        <input type="hidden" name="email" value="{{ old('email') }}">
                        Belum menerima email?
                        <button type="submit" class="font-bold text-[#1F6B4F] hover:underline cursor-pointer">Kirim ulang tautan</button>
                    </form>
                @endif
            @else
                {{-- ==================== STATE: FORM PERMINTAAN ==================== --}}
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-br from-[#1F6B4F] to-[#174B39] shadow-lg shadow-[#1F6B4F]/25 ring-4 ring-[#EAF5EE] flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 10V7.5a4.5 4.5 0 019 0V10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><rect x="4" y="9.5" width="16" height="11.5" rx="3.2" fill="currentColor"/><circle cx="8.5" cy="15.25" r="1.3" fill="#1F6B4F"/><circle cx="12" cy="15.25" r="1.3" fill="#1F6B4F"/><circle cx="15.5" cy="15.25" r="1.3" fill="#1F6B4F"/></svg>
                    </div>
                    <h1 class="mt-5 font-display font-extrabold text-2xl text-gray-900 tracking-tight">Lupa Kata Sandi?</h1>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Tenang, masukkan email yang terdaftar pada akun Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="text-xs font-bold text-gray-800">Alamat Email Terdaftar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="email"
                                   placeholder="nama@email.com"
                                   class="w-full pl-10 pr-4 py-3 border {{ $errors->has('email') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }} rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#1F6B4F] focus:ring-2 focus:ring-[#1F6B4F]/20 transition-all">
                        </div>
                        @error('email')
                            <p role="alert" class="text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer group">
                        <span>Kirim Tautan Reset</span>
                        <svg class="w-4 h-4 rotate-90 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-between text-xs">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 font-semibold text-gray-600 hover:text-[#1F6B4F] transition-colors group">
                        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Login
                    </a>
                    <a href="https://wa.me/6289662893128" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-semibold text-gray-600 hover:text-[#1F6B4F] transition-colors">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.668-.699c.974.536 1.764.814 2.792.814 3.18 0 5.766-2.587 5.766-5.766 0-3.18-2.586-5.766-5.766-5.766zm9.969 5.766c0 5.485-4.453 9.938-9.969 9.938-1.745 0-3.385-.45-4.819-1.237l-5.212 1.361 1.39-5.078c-.887-1.488-1.359-3.21-1.359-4.984 0-5.485 4.453-9.938 9.969-9.938 5.516 0 10 4.453 10 9.938z"/></svg>
                        Tidak punya akses email?
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-auth-shell>
