{{--
    Popup konfirmasi bertema SILAFA, menggantikan confirm() bawaan browser.
    Dipasang sekali per layout. Formulir cukup diberi atribut:
    - data-konfirmasi="Pesan konfirmasi" (wajib)
    - data-konfirmasi-judul="Judul popup" (opsional)
    - data-konfirmasi-tombol="Teks tombol setuju" (opsional)
    - data-konfirmasi-jenis="bahaya" untuk aksi menghapus/keluar (merah), selain itu hijau
    - data-konfirmasi-ikon="hapus|keluar|setujui|kunci" (opsional, bawaan: hapus untuk bahaya, setujui untuk lainnya)
    Penangkap event submit ada di resources/js/app.js.
--}}
<div x-data="{
        buka: false,
        judul: '',
        pesan: '',
        tombol: '',
        bahaya: false,
        ikon: 'setujui',
        lanjut: null,
        tampilkan(detail) {
            this.judul = detail.judul;
            this.pesan = detail.pesan;
            this.tombol = detail.tombol;
            this.bahaya = detail.bahaya;
            this.ikon = detail.ikon;
            this.lanjut = detail.lanjut;
            this.buka = true;
            this.$nextTick(() => this.$refs.tombolBatal?.focus());
        },
        setuju() {
            const lanjut = this.lanjut;
            this.buka = false;
            this.lanjut = null;
            lanjut?.();
        }
     }"
     x-init="window.dialogKonfirmasiSiap = true"
     @konfirmasi-buka.window="tampilkan($event.detail)"
     @keydown.escape.window="buka = false"
     x-effect="document.body.classList.toggle('overflow-hidden', buka)">
    <div x-show="buka" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4"
         role="alertdialog" aria-modal="true" aria-labelledby="judul-dialog-konfirmasi" aria-describedby="pesan-dialog-konfirmasi">
        <div x-show="buka"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="buka = false"
             class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]"></div>

        <div x-show="buka"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-[22rem] bg-white rounded-3xl shadow-2xl px-7 pt-7 pb-6 text-center">
            {{-- Ikon sesuai tempat --}}
            <div class="flex justify-center" :class="bahaya ? 'text-rose-500' : 'text-[#1F6B4F]'" aria-hidden="true">
                <svg x-show="ikon === 'hapus'" class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path d="M9 3a1 1 0 00-1 1v1H4.5a1 1 0 100 2h15a1 1 0 100-2H16V4a1 1 0 00-1-1H9z"/><path fill-rule="evenodd" d="M5.5 9h13l-.84 10.08A2 2 0 0115.67 21H8.33a2 2 0 01-1.99-1.92L5.5 9zm4 2.5a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75zm5 0a.75.75 0 00-.75.75v5a.75.75 0 001.5 0v-5a.75.75 0 00-.75-.75z" clip-rule="evenodd"/></svg>
                <svg x-show="ikon === 'keluar'" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                <svg x-show="ikon === 'setujui'" class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zm4.28 7.53a.75.75 0 00-1.06-1.06l-4.47 4.47-1.97-1.97a.75.75 0 10-1.06 1.06l2.5 2.5a.75.75 0 001.06 0l5-5z" clip-rule="evenodd"/></svg>
                <svg x-show="ikon === 'kunci'" class="w-8 h-8" viewBox="0 0 24 24" fill="none"><path d="M7.5 10V7.5a4.5 4.5 0 019 0V10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><rect x="4" y="9.5" width="16" height="11.5" rx="3.2" fill="currentColor"/><circle cx="8.5" cy="15.25" r="1.3" fill="#fff"/><circle cx="12" cy="15.25" r="1.3" fill="#fff"/><circle cx="15.5" cy="15.25" r="1.3" fill="#fff"/></svg>
            </div>

            <h2 id="judul-dialog-konfirmasi" class="mt-3 text-lg font-semibold text-gray-800" x-text="judul"></h2>
            <p id="pesan-dialog-konfirmasi" class="mt-2.5 text-xs text-gray-500 leading-relaxed break-words" x-text="pesan"></p>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" x-ref="tombolBatal" @click="buka = false"
                        class="py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="setuju()"
                        class="py-2.5 rounded-full text-white text-sm font-semibold shadow-sm transition-colors cursor-pointer"
                        :class="bahaya ? 'bg-rose-500 hover:bg-rose-600' : 'bg-[#1F6B4F] hover:bg-[#174B39]'"
                        x-text="tombol"></button>
            </div>
        </div>
    </div>
</div>
