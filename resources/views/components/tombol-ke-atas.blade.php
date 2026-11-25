{{--
    Tombol bulat "kembali ke atas" di pojok kanan bawah.
    Muncul setelah halaman digulir, dengan cincin yang menunjukkan seberapa jauh halaman sudah digulir.
    Dipasang sekali per layout.
--}}
<div x-data="{
        tampil: false,
        progres: 0,
        perbarui() {
            const tinggiGulir = document.documentElement.scrollHeight - window.innerHeight;
            this.tampil = window.scrollY > 400;
            this.progres = tinggiGulir > 0 ? Math.min(1, window.scrollY / tinggiGulir) : 0;
        },
        keAtas() {
            const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: kurangiGerak ? 'auto' : 'smooth' });
        }
     }"
     x-init="perbarui()"
     @scroll.window.passive="perbarui()"
     @resize.window.passive="perbarui()"
     class="fixed right-4 sm:right-6 bottom-[calc(1.25rem+env(safe-area-inset-bottom))] sm:bottom-6 z-40 print:hidden">
    <button type="button"
            x-show="tampil" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 scale-90"
            @click="keAtas()"
            aria-label="Kembali ke atas halaman"
            title="Kembali ke atas"
            class="group relative w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-[#1F6B4F] to-[#174B39] text-white shadow-lg shadow-[#1F6B4F]/30 hover:shadow-xl hover:shadow-[#1F6B4F]/40 hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-4 focus-visible:ring-[#1F6B4F]/30">
        {{-- Cincin progres gulir --}}
        <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 56 56" aria-hidden="true">
            <circle cx="28" cy="28" r="25" fill="none" stroke="currentColor" stroke-opacity="0.18" stroke-width="2.5"/>
            <circle cx="28" cy="28" r="25" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-dasharray="157.08" :stroke-dashoffset="157.08 * (1 - progres)"/>
        </svg>
        <svg class="relative w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19V5m0 0l-6 6m6-6l6 6"/>
        </svg>
    </button>
</div>
