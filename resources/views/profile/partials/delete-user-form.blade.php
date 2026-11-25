<section x-data="{ buka: @js($errors->userDeletion->isNotEmpty()), kataSandi: '' }" @keydown.escape.window="buka = false">
    <header class="flex items-start gap-3">
        <div class="w-10 h-10 shrink-0 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3a1 1 0 00-1 1v1H4.5a1 1 0 100 2h15a1 1 0 100-2H16V4a1 1 0 00-1-1H9z"/><path fill-rule="evenodd" d="M5.5 9h13l-.84 10.08A2 2 0 0115.67 21H8.33a2 2 0 01-1.99-1.92L5.5 9zm4 2.5a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75zm5 0a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75z" clip-rule="evenodd"/></svg>
        </div>
        <div>
            <h2 class="text-base font-bold text-gray-900">Hapus Akun</h2>
            <p class="mt-0.5 text-xs text-gray-500 leading-relaxed">Akun dan seluruh datanya akan dihapus permanen dan tidak dapat dipulihkan.</p>
        </div>
    </header>

    <button type="button" @click="buka = true"
            class="mt-5 w-full px-5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-600 text-sm font-bold transition-colors cursor-pointer">
        Hapus Akun Saya
    </button>

    <template x-teleport="body">
        <div x-show="buka" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             role="alertdialog" aria-modal="true" aria-labelledby="judul-hapus-akun"
             x-effect="document.body.classList.toggle('overflow-hidden', buka)">
            <div x-show="buka" x-transition.opacity @click="buka = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]"></div>

            <form method="post" action="{{ route('profile.destroy') }}"
                  x-show="buka"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                  x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                  class="relative w-full max-w-[22rem] bg-white rounded-3xl shadow-2xl px-7 pt-7 pb-6 text-center">
                @csrf
                @method('delete')

                <div class="flex justify-center text-rose-500" aria-hidden="true">
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3a1 1 0 00-1 1v1H4.5a1 1 0 100 2h15a1 1 0 100-2H16V4a1 1 0 00-1-1H9z"/><path fill-rule="evenodd" d="M5.5 9h13l-.84 10.08A2 2 0 0115.67 21H8.33a2 2 0 01-1.99-1.92L5.5 9zm4 2.5a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75zm5 0a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75z" clip-rule="evenodd"/></svg>
                </div>
                <h2 id="judul-hapus-akun" class="mt-3 text-lg font-semibold text-gray-800">Hapus akun Anda?</h2>
                <p class="mt-2.5 text-xs text-gray-500 leading-relaxed">Akun dan seluruh data Anda akan dihapus permanen. Masukkan kata sandi akun Anda untuk mengaktifkan tombol Hapus.</p>

                <div class="mt-4 text-left">
                    <label for="password_hapus_akun" class="sr-only">Kata sandi</label>
                    <input id="password_hapus_akun" name="password" type="password" placeholder="Kata sandi Anda" autocomplete="current-password" required x-model="kataSandi"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-rose-400 focus:ring-2 focus:ring-rose-200 {{ $errors->userDeletion->has('password') ? 'bg-rose-50/60 border-rose-400' : 'bg-white border-[#D5DDD6]' }}">
                    @if ($errors->userDeletion->has('password'))
                        <p class="mt-1.5 text-xs text-rose-600">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button type="button" @click="buka = false"
                            class="py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors cursor-pointer">Batal</button>
                    <button type="submit" :disabled="kataSandi.trim() === ''"
                            class="py-2.5 rounded-full bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold shadow-sm transition-colors cursor-pointer disabled:bg-rose-200 disabled:cursor-not-allowed disabled:shadow-none">Hapus</button>
                </div>
            </form>
        </div>
    </template>
</section>
