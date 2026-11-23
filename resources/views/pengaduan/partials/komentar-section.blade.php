{{--
    Kolom komentar bergaya media sosial (PBI #33, #34, #42, #44, #47).
    Membutuhkan $pengaduan dengan relasi komentar.user. Variabel opsional $dalamPopup menampilkan
    kolom tanpa bingkai kartu dan dengan daftar yang dapat digulir.
--}}
@php
    $user = Auth::user();
    $dalamPopup = $dalamPopup ?? false;
    $canComment = $user->can('komentari', $pengaduan);
    $isFormKomentarIni = (string) old('form_komentar') === (string) $pengaduan->id;
    $sanksiKomentar = ! $canComment && $user->isWarga() && $pengaduan->isAspirasi()
        ? $user->sanksiBerlaku(\App\Enums\FiturSanksi::KomentarPublik)
        : null;
    $inisial = fn (string $nama) => \Illuminate\Support\Str::of($nama)->squish()->explode(' ')
        ->take(2)->map(fn (string $bagian) => mb_strtoupper(mb_substr($bagian, 0, 1)))->implode('');
    $warnaAvatar = fn ($penulis) => match (true) {
        $penulis->isAdminDesa() => 'bg-[#1F6B4F] text-white',
        $penulis->isKepalaDesa() => 'bg-blue-700 text-white',
        default => 'bg-[#EAF5EE] text-[#1F6B4F]',
    };
@endphp

<div @if (! $dalamPopup) id="diskusi" @endif
     class="{{ $dalamPopup ? 'px-6 sm:px-8 pb-6 pt-3' : 'bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4]' }} space-y-5">
    {{-- Judul & jumlah komentar --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-base font-bold text-gray-900">
                {{ $pengaduan->isAspirasi() ? 'Komentar' : 'Diskusi & Tanggapan' }}
                <span class="text-gray-400 font-semibold">{{ $pengaduan->komentar->count() }}</span>
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $pengaduan->isAspirasi() ? 'Komentar dapat dibaca seluruh warga dan aparatur desa' : 'Komunikasi dua arah antara warga pelapor dan aparatur desa' }}</p>
        </div>
    </div>

    {{-- Daftar komentar, terlama di atas seperti percakapan --}}
    <div class="space-y-4 {{ $dalamPopup ? 'max-h-[50vh] overflow-y-auto pr-1 -mr-1 overscroll-contain' : '' }}"
         @if ($dalamPopup) x-data x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)" @endif>
        @forelse ($pengaduan->komentar as $komentar)
            @php
                $penulis = $komentar->user;
                $namaPenulis = $penulis->namaUntuk($user);
            @endphp
            <div class="flex items-start gap-3" x-data="{ editing: false, moderasi: false, isi: @js($komentar->isi) }">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $warnaAvatar($penulis) }}" aria-hidden="true">
                    {{ $inisial($namaPenulis) }}
                </div>

                <div class="min-w-0 flex-1">
                    {{-- Gelembung komentar --}}
                    <div x-show="! editing" class="inline-block max-w-full rounded-2xl rounded-tl-sm px-4 py-2.5 {{ $komentar->isDisembunyikan() ? 'bg-amber-50 border border-amber-200' : 'bg-gray-100' }}">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="font-bold text-xs text-gray-900">{{ $namaPenulis }}</span>

                            {{-- Label Peran (PBI #34: Balasan berlabel Admin Desa) --}}
                            @if ($penulis->isAdminDesa())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-[#1F6B4F] text-white">Admin Desa</span>
                            @elseif ($penulis->isKepalaDesa())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-700 text-white">Kepala Desa</span>
                            @elseif ((int) $komentar->user_id === (int) $pengaduan->user_id)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-sky-100 text-sky-700">{{ $pengaduan->isAspirasi() ? 'Pengusul' : 'Warga Pelapor' }}</span>
                            @else
                                <span class="sr-only">Warga</span>
                            @endif

                            @if ($komentar->isDisembunyikan())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">Disembunyikan dari publik</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line break-words mt-0.5">{{ $komentar->isi }}</p>
                    </div>

                    {{-- Form ubah komentar (hanya penulis) --}}
                    @can('update', $komentar)
                        <form x-show="editing" x-cloak method="POST" action="{{ route('komentar.update', $komentar) }}" data-pertahankan-gulir class="space-y-2">
                            @csrf
                            @method('put')
                            <textarea name="isi" x-model="isi" rows="2" class="block w-full rounded-2xl border-gray-300 text-sm focus:border-[#1F6B4F] focus:ring-[#1F6B4F]" required></textarea>
                            <div class="flex items-center gap-3 text-xs font-semibold">
                                <button type="submit" class="text-[#1F6B4F] hover:underline cursor-pointer">Simpan</button>
                                <button type="button" @click="editing = false; isi = @js($komentar->isi)" class="text-gray-500 hover:underline cursor-pointer">Batal</button>
                            </div>
                        </form>
                    @endcan

                    {{-- Waktu & aksi di bawah gelembung --}}
                    <div x-show="! editing" class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 ms-3 text-[11px] text-gray-500">
                        <time datetime="{{ $komentar->created_at->toIso8601String() }}" title="{{ $komentar->created_at->translatedFormat('d M Y, H:i') }}">
                            {{ $komentar->created_at->gt(now()->subMinute()) ? 'Baru saja' : $komentar->created_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}
                        </time>
                        @if ($komentar->updated_at->gt($komentar->created_at))
                            <span>Diedit</span>
                        @endif

                        {{-- Aksi Edit/Hapus (Kriteria: komentar tidak dapat diubah orang lain) --}}
                        @can('update', $komentar)
                            <button type="button" @click="editing = true" class="font-semibold hover:text-gray-800 cursor-pointer">Edit</button>
                            <form method="POST" action="{{ route('komentar.destroy', $komentar) }}" data-pertahankan-gulir onsubmit="return confirm('Hapus komentar ini?')" class="inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="font-semibold hover:text-rose-600 cursor-pointer">Hapus</button>
                            </form>
                        @endcan

                        {{-- Moderasi Admin Desa (PBI #44, PBI #45) --}}
                        @can('moderasi', $komentar)
                            <button type="button" @click="moderasi = ! moderasi" class="font-semibold text-amber-700 hover:text-amber-900 cursor-pointer">Moderasi</button>
                        @endcan
                    </div>

                    @can('moderasi', $komentar)
                        <div x-show="moderasi || @js(old('form_moderasi') === 'komentar-'.$komentar->id)" x-cloak class="mt-2">
                            @include('admin.moderasi.partials.aksi', ['konten' => $komentar, 'jenis' => 'komentar'])
                        </div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="py-8 text-center">
                <p class="text-sm font-semibold text-gray-700">Belum ada komentar</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $canComment ? 'Jadilah yang pertama memberikan tanggapan.' : 'Belum ada tanggapan pada laporan ini.' }}
                </p>
            </div>
        @endforelse
    </div>

    {{-- Kolom tulis komentar --}}
    @if ($canComment)
        <form method="POST" action="{{ route('komentar.store', $pengaduan) }}" data-pertahankan-gulir
              class="flex items-end gap-3 pt-4 border-t border-gray-100"
              x-data="{ isi: @js($isFormKomentarIni ? (string) old('isi') : '') }">
            @csrf
            <input type="hidden" name="form_komentar" value="{{ $pengaduan->id }}">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $warnaAvatar($user) }}" aria-hidden="true">
                {{ $inisial($user->nama_lengkap) }}
            </div>
            <div class="flex-1 min-w-0">
                <label for="isi_komentar-{{ $pengaduan->id }}" class="sr-only">
                    @if ($user->isAdminDesa())
                        Balas Laporan sebagai Admin Desa
                    @elseif ($user->isKepalaDesa())
                        Catatan Tanggapan Kepala Desa
                    @else
                        {{ $pengaduan->isAspirasi() ? 'Tulis Komentar Publik' : 'Tulis Pertanyaan atau Informasi Tambahan' }}
                    @endif
                </label>
                <div class="flex items-end gap-2 rounded-3xl bg-gray-100 px-4 py-2 focus-within:ring-2 focus-within:ring-[#1F6B4F]/40">
                    <textarea id="isi_komentar-{{ $pengaduan->id }}" name="isi" rows="1" x-model="isi" required maxlength="2000"
                              x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'"
                              x-on:keydown.enter="if (! $event.shiftKey && isi.trim().length >= 2) { $event.preventDefault(); $el.form.requestSubmit(); }"
                              class="flex-1 resize-none bg-transparent border-0 p-0 py-1 text-sm text-gray-800 placeholder-gray-500 focus:ring-0"
                              placeholder="{{ $user->isAdminDesa() ? 'Tulis balasan sebagai Admin Desa...' : ($user->isKepalaDesa() ? 'Tulis tanggapan Kepala Desa...' : 'Tulis komentar...') }}"></textarea>
                    <button type="submit" :disabled="isi.trim().length < 2"
                            class="shrink-0 p-1.5 rounded-full text-[#1F6B4F] hover:bg-white disabled:text-gray-400 disabled:hover:bg-transparent transition-colors cursor-pointer disabled:cursor-default">
                        <span class="sr-only">
                            @if ($user->isAdminDesa())
                                Kirim Balasan Admin Desa
                            @elseif ($user->isKepalaDesa())
                                Kirim Tanggapan
                            @else
                                Kirim Komentar
                            @endif
                        </span>
                        <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 ms-4">Enter untuk mengirim, Shift + Enter untuk baris baru.</p>
                @if ($isFormKomentarIni)
                    <x-input-error class="mt-1 ms-4" :messages="$errors->get('isi')" />
                @endif
            </div>
        </form>
    @elseif ($sanksiKomentar)
        {{-- Komentar publik dibatasi sanksi (PBI #47, PBI #48) --}}
        <div class="pt-4 border-t border-gray-100">
            @include('warga.sanksi.partials.banner', ['sanksi' => $sanksiKomentar])
        </div>
    @else
        <div class="pt-4 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">
                @if ($pengaduan->isDisembunyikan())
                    Diskusi dikunci selama aspirasi ini disembunyikan oleh Admin Desa.
                @else
                    Hanya warga pemilik laporan dan aparatur desa yang berwenang memberikan komentar pada laporan ini.
                @endif
            </p>
        </div>
    @endif
</div>
