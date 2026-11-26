{{--
    Popup peringatan sesi akan berakhir karena tidak ada aktivitas.
    Muncul beberapa detik sebelum batas (config/sesi.php), dengan tombol Tetap Masuk dan Keluar.
    Waktu aktivitas terakhir dibagi antartab lewat localStorage agar tab lain tidak salah memperingatkan.
    Tidak dipasang untuk warga yang memilih "Ingat saya" karena sesinya tidak dibatasi.
--}}
@auth
    @php
        $pengguna = auth()->user();
        $diingat = $pengguna->bolehIngatSaya() && request()->cookies->has(auth()->guard('web')->getRecallerName());
    @endphp

    @unless ($diingat)
        <div x-data="{
                batasDetik: {{ $pengguna->batasTidakAktifMenit() * 60 }},
                peringatanDetik: {{ (int) config('sesi.peringatan_detik') }},
                sisaDetik: {{ $pengguna->batasTidakAktifMenit() * 60 }},
                buka: false,
                memproses: false,
                kunci: 'silafa:aktivitas-terakhir:{{ $pengguna->getKey() }}',
                init() {
                    this.catatAktivitas();
                    setInterval(() => this.hitung(), 1000);
                    window.addEventListener('storage', (event) => { if (event.key === this.kunci) { this.hitung(); } });
                },
                catatAktivitas() {
                    localStorage.setItem(this.kunci, String(Date.now()));
                    this.hitung();
                },
                hitung() {
                    const terakhir = Number(localStorage.getItem(this.kunci) || Date.now());
                    this.sisaDetik = Math.max(0, this.batasDetik - Math.floor((Date.now() - terakhir) / 1000));
                    this.buka = this.sisaDetik <= this.peringatanDetik;
                    if (this.sisaDetik === 0) { window.location.reload(); }
                },
                get waktuMundur() {
                    return String(Math.floor(this.sisaDetik / 60)).padStart(2, '0') + ':' + String(this.sisaDetik % 60).padStart(2, '0');
                },
                async tetapMasuk() {
                    this.memproses = true;
                    try {
                        const respons = await fetch(@js(route('sesi.perpanjang')), {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (! respons.ok) { window.location.reload(); return; }
                        this.catatAktivitas();
                    } catch (galat) {
                        window.location.reload();
                    } finally {
                        this.memproses = false;
                    }
                }
             }"
             x-effect="document.body.classList.toggle('overflow-hidden', buka)">
            <div x-show="buka" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4"
                 role="alertdialog" aria-modal="true" aria-labelledby="judul-peringatan-sesi" aria-describedby="pesan-peringatan-sesi">
                <div x-show="buka" x-transition.opacity class="absolute inset-0 bg-gray-900/40 backdrop-blur-[2px]"></div>

                <div x-show="buka"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-[22rem] bg-white rounded-3xl shadow-2xl px-7 pt-7 pb-6 text-center">
                    <div class="flex justify-center text-amber-500" aria-hidden="true">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 id="judul-peringatan-sesi" class="mt-3 text-lg font-semibold text-gray-800">Sesi akan berakhir</h2>
                    <p id="pesan-peringatan-sesi" class="mt-2.5 text-xs text-gray-500 leading-relaxed">
                        Anda tidak melakukan aktivitas cukup lama. Demi keamanan data, Anda akan keluar otomatis dalam
                    </p>
                    <p class="mt-2 font-mono text-3xl font-extrabold tabular-nums text-amber-600" x-text="waktuMundur" aria-live="polite"></p>

                    <form id="form-keluar-sesi" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="submit" form="form-keluar-sesi"
                                class="py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition-colors cursor-pointer">
                            Keluar
                        </button>
                        <button type="button" @click="tetapMasuk()" :disabled="memproses"
                                class="py-2.5 rounded-full bg-[#1F6B4F] hover:bg-[#174B39] text-white text-sm font-semibold shadow-sm transition-colors cursor-pointer disabled:opacity-60">
                            <span x-show="! memproses">Tetap Masuk</span>
                            <span x-show="memproses" x-cloak>Memproses…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endunless
@endauth
