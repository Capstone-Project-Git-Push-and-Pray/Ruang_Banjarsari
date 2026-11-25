<x-app-layout>
    @php
        $kategoriList = $kategoriList ?? \App\Models\Kategori::where('aktif', true)->orderBy('nama')->get();
        $user = Auth::user();
        $sanksiAspirasi = $user->sanksiBerlaku(\App\Enums\FiturSanksi::Aspirasi);
        $nik = $user->nik ?? '';
        $maskedNik = strlen($nik) >= 8
            ? substr($nik, 0, 4) . '************' . substr($nik, -3)
            : ($nik ?: '9999************001');
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-2 sm:py-4">

        {{-- ==================== TOMBOL KEMBALI & HEADER HALAMAN ==================== --}}
        <div>
            <a href="{{ route('dashboard') }}" class="tombol-kembali inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold text-gray-700 bg-white border border-gray-200/80 hover:bg-gray-50 transition shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-3">
                Buat Laporan Baru
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Sampaikan pengaduan atau aspirasi Anda kepada aparatur Desa Banjarsari.
            </p>
        </div>

        {{-- ==================== BANNER IDENTITAS RESMI (MINT TINT) ==================== --}}
        <div class="bg-[#EBF7F0] border border-[#CDEBD8] rounded-2xl p-4 sm:p-4.5 flex items-start gap-3.5 mt-5">
            <div class="w-9 h-9 rounded-xl bg-[#185840] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="text-xs">
                <span class="font-bold text-gray-900 block text-xs sm:text-sm">
                    Laporan atas nama {{ $user->nama_lengkap }} &bull; NIK {{ $maskedNik }}
                </span>
                <span class="text-gray-600 mt-0.5 block leading-relaxed">
                    Laporan tercatat resmi dan tidak dapat dikirim secara anonim, agar desa mudah menindaklanjuti.
                </span>
            </div>
        </div>

        {{-- ==================== FORMULIR DAN SIDEBAR 2-KOLOM ==================== --}}
        <form method="POST" action="{{ route('warga.pengaduan.store') }}" enctype="multipart/form-data" class="mt-6"
              x-data="{
                  jenis: '{{ old('jenis', 'pengaduan') }}',
                  kategoriId: '{{ old('kategori_id', '') }}',
                  judul: '{{ old('judul', '') }}',
                  deskripsi: '{{ old('deskripsi', '') }}',
                  lokasi: '{{ old('lokasi', '') }}',
                  files: [],
                  handleFiles(event) {
                      const newFiles = Array.from(event.target.files || (event.dataTransfer ? event.dataTransfer.files : []));
                      for (let f of newFiles) {
                          if (!['image/jpeg', 'image/png', 'image/jpg'].includes(f.type)) {
                              alert('Format berkas ' + f.name + ' tidak didukung. Harap pilih foto JPG atau PNG.');
                              continue;
                          }
                          if (f.size > 2097152) {
                              alert('Ukuran foto ' + f.name + ' melebihi batas 2 MB.');
                              continue;
                          }
                          if (this.files.length < 3) {
                              this.files.push(f);
                          } else {
                              alert('Maksimal hanya dapat melampirkan 3 foto.');
                              break;
                          }
                      }
                      this.syncDataTransfer();
                  },
                  removeFile(index) {
                      this.files.splice(index, 1);
                      this.syncDataTransfer();
                  },
                  syncDataTransfer() {
                      const dt = new DataTransfer();
                      this.files.forEach(f => dt.items.add(f));
                      this.$refs.fileInput.files = dt.files;
                  },
                  formatSize(bytes) {
                      if (bytes < 1048576) {
                          return (bytes / 1024).toFixed(1).replace('.', ',') + ' KB';
                      }
                      return (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB';
                  },
                  get completedCount() {
                      let count = 0;
                      if (this.jenis) count++;
                      if (this.kategoriId) count++;
                      if (this.judul.trim().length >= 5) count++;
                      if (this.deskripsi.trim().length >= 10) count++;
                      return count;
                  },
                  get progressPercent() {
                      return (this.completedCount / 4) * 100;
                  }
              }">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- ==================== KOLOM KIRI: FORMULIR UTAMA ==================== --}}
                <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-8">

                    {{-- ----------------- LANGKAH 1: APA YANG INGIN DISAMPAIKAN ----------------- --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-6 h-6 rounded-full bg-[#EAF5EE] text-[#185840] font-bold text-xs flex items-center justify-center">1</span>
                            <h2 class="font-bold text-sm sm:text-base text-gray-900">Apa yang ingin Anda sampaikan?</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Opsi: Pengaduan --}}
                            <div @click="jenis = 'pengaduan'"
                                 :class="jenis === 'pengaduan' ? 'border-[#185840] bg-[#F3FAF6] ring-1 ring-[#185840]' : 'border-gray-200 bg-white hover:bg-gray-50/60'"
                                 class="relative p-4 sm:p-5 border rounded-2xl cursor-pointer transition-all duration-150 flex flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div :class="jenis === 'pengaduan' ? 'bg-[#185840]' : 'bg-gray-100'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors">
                                        <img src="{{ asset('images/icon-pengaduan.png') }}"
                                             alt="Ikon Pengaduan"
                                             class="w-5 h-5 object-contain transition-all"
                                             :class="jenis === 'pengaduan' ? 'brightness-0 invert' : 'opacity-70'">
                                    </div>
                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center"
                                         :class="jenis === 'pengaduan' ? 'border-[#185840] bg-[#185840]' : 'border-gray-300'">
                                        <span class="w-2 h-2 rounded-full bg-white" x-show="jenis === 'pengaduan'"></span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h3 class="font-bold text-sm text-gray-900">Pengaduan</h3>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Keluhan fasilitas rusak, gangguan lingkungan, atau masalah pelayanan desa.
                                    </p>
                                </div>
                            </div>

                            {{-- Opsi: Aspirasi --}}
                            <div @click="jenis = 'aspirasi'"
                                 :class="jenis === 'aspirasi' ? 'border-[#185840] bg-[#F3FAF6] ring-1 ring-[#185840]' : 'border-gray-200 bg-white hover:bg-gray-50/60'"
                                 class="relative p-4 sm:p-5 border rounded-2xl cursor-pointer transition-all duration-150 flex flex-col justify-between">
                                <div class="flex items-start justify-between">
                                    <div :class="jenis === 'aspirasi' ? 'bg-[#185840] text-white' : 'bg-gray-100 text-gray-600'"
                                         class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center"
                                         :class="jenis === 'aspirasi' ? 'border-[#185840] bg-[#185840]' : 'border-gray-300'">
                                        <span class="w-2 h-2 rounded-full bg-white" x-show="jenis === 'aspirasi'"></span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h3 class="font-bold text-sm text-gray-900">Aspirasi</h3>
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                        Saran, ide pembangunan, gagasan program, atau masukan untuk kemajuan desa.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden Input untuk Form Submission --}}
                        <input type="hidden" name="jenis" :value="jenis">

                        {{-- Fitur aspirasi dibatasi sanksi (PBI #46, PBI #48) --}}
                        @if ($sanksiAspirasi)
                            <div class="mt-3" x-show="jenis === 'aspirasi'">
                                @include('warga.sanksi.partials.banner', ['sanksi' => $sanksiAspirasi])
                            </div>
                        @endif

                        {{-- Informasi Visibilitas (PBI #38) --}}
                        <p class="mt-3 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5">
                            <span x-show="jenis === 'pengaduan'"><strong>Privat.</strong> Pengaduan hanya dapat dilihat oleh Anda dan pihak desa.</span>
                            <span x-show="jenis === 'aspirasi'" x-cloak><strong>Publik.</strong> Aspirasi dapat dibaca, didukung, dan dikomentari warga lain. Nama Anda ditampilkan sebagai &ldquo;{{ $user->namaPublik() }}&rdquo;; NIK, kontak, dan alamat tidak ditampilkan. Jangan menulis data pribadi di dalam isi aspirasi.</span>
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('jenis')" />
                    </div>

                    {{-- ----------------- LANGKAH 2: ISI LAPORAN ----------------- --}}
                    <div class="space-y-5">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-full bg-[#EAF5EE] text-[#185840] font-bold text-xs flex items-center justify-center">2</span>
                            <h2 class="font-bold text-sm sm:text-base text-gray-900">Isi laporan</h2>
                        </div>

                        {{-- Kategori (Pill Buttons) --}}
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <label class="font-bold text-xs sm:text-sm text-gray-900">Kategori</label>
                                <span class="text-[11px] text-gray-400">Pilih satu yang paling sesuai</span>
                            </div>

                            <input type="hidden" name="kategori_id" :value="kategoriId" required>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($kategoriList as $kat)
                                    <button type="button"
                                            @click="kategoriId = '{{ $kat->id }}'"
                                            :class="kategoriId == '{{ $kat->id }}' ? 'bg-[#185840] text-white border-[#185840] shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                            class="px-4 py-2 rounded-full border text-xs font-medium transition-all duration-150 cursor-pointer">
                                        {{ $kat->nama }}
                                    </button>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('kategori_id')" />
                        </div>

                        {{-- Judul Laporan --}}
                        <div>
                            <label for="judul" class="font-bold text-xs sm:text-sm text-gray-900 block mb-1.5">Judul laporan</label>
                            <input id="judul"
                                   name="judul"
                                   type="text"
                                   maxlength="100"
                                   x-model="judul"
                                   placeholder="Lampu penerangan jalan RT 02 mati total"
                                   class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#185840] focus:ring-[#185840] py-3 px-4 shadow-2xs placeholder-gray-400 transition"
                                   required />
                            <div class="flex items-center justify-between mt-1.5 text-[11px] text-gray-400">
                                <span>Buat judul ringkas dan jelas yang menggambarkan inti masalah atau usulan.</span>
                                <span class="font-mono" x-text="judul.length + '/100'">0/100</span>
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('judul')" />
                        </div>

                        {{-- Deskripsi Lengkap --}}
                        <div>
                            <label for="deskripsi" class="font-bold text-xs sm:text-sm text-gray-900 block mb-1.5">Deskripsi lengkap</label>
                            <textarea id="deskripsi"
                                      name="deskripsi"
                                      rows="5"
                                      maxlength="1000"
                                      x-model="deskripsi"
                                      placeholder="Tulis apa yang terjadi, sejak kapan, dan dampaknya..."
                                      class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#185840] focus:ring-[#185840] p-4 shadow-2xs placeholder-gray-400 transition leading-relaxed"
                                      required></textarea>
                            <div class="flex items-center justify-between mt-1.5 text-[11px] text-gray-400">
                                <span>Tulis apa yang terjadi, sejak kapan, dan dampaknya.</span>
                                <span class="font-mono" x-text="deskripsi.length + '/1000'">0/1000</span>
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('deskripsi')" />
                        </div>
                    </div>

                    {{-- ----------------- LANGKAH 3: LOKASI DAN FOTO ----------------- --}}
                    <div class="space-y-5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#EAF5EE] text-[#185840] font-bold text-xs flex items-center justify-center">3</span>
                                <h2 class="font-bold text-sm sm:text-base text-gray-900">Lokasi dan foto</h2>
                            </div>
                            <span class="text-xs text-gray-400 font-medium">Opsional</span>
                        </div>

                        {{-- Lokasi Kejadian --}}
                        <div>
                            <label for="lokasi" class="font-bold text-xs sm:text-sm text-gray-900 block mb-1.5">Lokasi kejadian</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <input id="lokasi"
                                       name="lokasi"
                                       type="text"
                                       maxlength="255"
                                       x-model="lokasi"
                                       placeholder="Depan rumah Pak RT 02 / RW 01, Dusun Krajan"
                                       class="w-full rounded-2xl border-gray-200 text-sm focus:border-[#185840] focus:ring-[#185840] py-3 pl-11 pr-4 shadow-2xs placeholder-gray-400 transition" />
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1.5">Sertakan patokan lokasi atau nomor RT/RW agar mudah dicek.</p>
                            <x-input-error class="mt-1" :messages="$errors->get('lokasi')" />
                        </div>

                        {{-- Foto Pendukung --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="font-bold text-xs sm:text-sm text-gray-900">Foto pendukung</label>
                                <span class="text-xs text-gray-400 font-mono" x-text="files.length + ' dari 3 foto'">0 dari 3 foto</span>
                            </div>

                            {{-- Area Dropzone --}}
                            <div @click="$refs.fileInput.click()"
                                 @dragover.prevent="$el.classList.add('border-[#185840]', 'bg-emerald-50/20')"
                                 @dragleave.prevent="$el.classList.remove('border-[#185840]', 'bg-emerald-50/20')"
                                 @drop.prevent="$el.classList.remove('border-[#185840]', 'bg-emerald-50/20'); handleFiles($event)"
                                 class="border-2 border-dashed border-gray-200 hover:border-emerald-300 rounded-2xl p-6 text-center cursor-pointer transition bg-[#FBFDFB]">
                                <div class="w-10 h-10 rounded-xl bg-[#EAF5EE] text-[#185840] flex items-center justify-center mx-auto mb-2.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-xs sm:text-sm text-gray-900">Tarik foto ke sini atau pilih dari perangkat</h3>
                                <p class="text-[11px] text-gray-400 mt-1">Format JPG atau PNG, maksimal 2 MB per foto.</p>
                            </div>

                            <input type="file"
                                   x-ref="fileInput"
                                   id="file-input"
                                   name="foto[]"
                                   multiple
                                   accept="image/jpeg,image/png,image/jpg"
                                   @change="handleFiles($event)"
                                   class="hidden">

                            {{-- Daftar Pratinjau Foto --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3" x-show="files.length > 0">
                                <template x-for="(f, idx) in files" :key="idx">
                                    <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200 bg-white shadow-2xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#1F6B4F] flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-xs font-semibold text-gray-900 truncate block max-w-[130px] sm:max-w-[160px]" x-text="f.name"></span>
                                                <span class="text-[11px] text-gray-400 block" x-text="formatSize(f.size)"></span>
                                            </div>
                                        </div>
                                        <button type="button"
                                                @click.stop="removeFile(idx)"
                                                class="w-6 h-6 rounded-full text-gray-400 hover:text-red-600 hover:bg-gray-100 flex items-center justify-center transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <x-input-error class="mt-2" :messages="$errors->get('foto')" />
                            <x-input-error class="mt-2" :messages="$errors->get('foto.*')" />
                        </div>
                    </div>

                    {{-- ----------------- AKSI DAN PERNYATAAN SUBMIT ----------------- --}}
                    <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <p class="text-xs text-gray-400 leading-relaxed max-w-sm">
                            Dengan mengirim, Anda menyatakan isi laporan ini sesuai dengan keadaan sebenarnya.
                        </p>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#185840] hover:bg-[#124230] text-white text-xs font-bold shadow-sm inline-flex items-center gap-2 transition cursor-pointer">
                                <span>Kirim Laporan</span>
                            </button>
                        </div>
                    </div>

                </div>

                {{-- ==================== KOLOM KANAN: SIDEBAR PETUNJUK & PROGRES ==================== --}}
                <div class="lg:col-span-4 space-y-5">

                    {{-- 1. Kartu Kelengkapan Laporan --}}
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-xs space-y-4">
                        <div>
                            <h3 class="font-bold text-sm text-gray-900">Kelengkapan laporan</h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="completedCount + ' dari 4 isian wajib sudah terisi'">
                                0 dari 4 isian wajib sudah terisi
                            </p>
                        </div>

                        {{-- Bar Progres --}}
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#185840] rounded-full transition-all duration-300"
                                 :style="'width: ' + progressPercent + '%'"></div>
                        </div>

                        <span class="text-xs font-bold block"
                              :class="completedCount === 4 ? 'text-[#185840]' : 'text-amber-600'"
                              x-text="completedCount === 4 ? 'Laporan siap dikirim' : 'Lengkapi isian wajib'">
                            Lengkapi isian wajib
                        </span>

                        {{-- Daftar Isian Ceklis --}}
                        <div class="space-y-2.5 pt-1 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                                     :class="jenis ? 'bg-[#185840] text-white' : 'border border-gray-300 text-transparent'">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-gray-700 font-medium">Jenis laporan</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                                     :class="kategoriId ? 'bg-[#185840] text-white' : 'border border-gray-300 text-transparent'">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-gray-700 font-medium">Kategori</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                                     :class="judul.trim().length >= 5 ? 'bg-[#185840] text-white' : 'border border-gray-300 text-transparent'">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-gray-700 font-medium">Judul</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                                     :class="deskripsi.trim().length >= 10 ? 'bg-[#185840] text-white' : 'border border-gray-300 text-transparent'">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-gray-700 font-medium">Deskripsi</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                                         :class="(lokasi.trim().length > 0 || files.length > 0) ? 'bg-[#185840] text-white' : 'border border-gray-300 text-transparent'">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span class="text-gray-700 font-medium">Lokasi dan foto</span>
                                </div>
                                <span class="text-[11px] text-gray-400">Opsional</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Kartu Agar Cepat Ditangani --}}
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-xs space-y-3">
                        <h3 class="font-bold text-sm text-gray-900">Agar cepat ditangani</h3>
                        <ul class="text-xs text-gray-600 space-y-2.5 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-gray-400 shrink-0 select-none">&check;</span>
                                <span>Tulis judul yang spesifik, bukan hanya &ldquo;jalan rusak&rdquo;.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-gray-400 shrink-0 select-none">&check;</span>
                                <span>Sebut RT/RW atau patokan lokasi yang mudah dikenali.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-gray-400 shrink-0 select-none">&check;</span>
                                <span>Lampirkan foto yang jelas dan terang.</span>
                            </li>
                        </ul>
                    </div>

                    {{-- 3. Kartu Setelah Laporan Dikirim --}}
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-xs space-y-3">
                        <div>
                            <h3 class="font-bold text-sm text-gray-900">Setelah laporan dikirim</h3>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                Pantau perkembangannya kapan saja di menu Laporan Saya.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] font-medium">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                <span>Diajukan</span>
                                <span class="text-gray-400 ms-0.5">&rsaquo;</span>
                            </span>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Ditinjau</span>
                                <span class="text-amber-400 ms-0.5">&rsaquo;</span>
                            </span>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                <span>Diproses</span>
                                <span class="text-blue-400 ms-0.5">&rsaquo;</span>
                            </span>

                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Selesai</span>
                            </span>
                        </div>
                    </div>

                </div>

            </div>
        </form>

    </div>
</x-app-layout>
