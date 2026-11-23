<div id="diskusi" class="bg-white p-6 sm:p-8 rounded-2xl shadow-xs border border-[#E2E8E4] space-y-6">
    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
        <div>
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#1F6B4F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                {{ $pengaduan->isAspirasi() ? 'Diskusi Publik Aspirasi' : 'Diskusi & Tanggapan Laporan' }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $pengaduan->isAspirasi() ? 'Komentar dapat dibaca seluruh warga dan aparatur desa' : 'Komunikasi dua arah antara warga pelapor dan aparatur desa' }}</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
            {{ $pengaduan->komentar->count() }} Komentar
        </span>
    </div>

    {{-- Notifikasi Sukses Komentar --}}
    @if (session('status') === 'komentar-berhasil-ditambahkan')
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Komentar Anda berhasil dikirim dan ditambahkan ke percakapan.</span>
        </div>
    @elseif (session('status') === 'komentar-berhasil-diperbarui')
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Komentar berhasil diperbarui.</span>
        </div>
    @elseif (session('status') === 'komentar-berhasil-dihapus')
        <div class="p-3 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-xs flex items-center gap-2">
            <span>Komentar telah dihapus.</span>
        </div>
    @endif

    {{-- Daftar Komentar Berurutan Kronologis (PBI #33, PBI #34) --}}
    <div class="space-y-4">
        @forelse ($pengaduan->komentar as $komentar)
            <div class="p-4 rounded-xl border {{ $komentar->user->isAdminDesa() ? 'bg-emerald-50/40 border-emerald-200/80' : ($komentar->user->isKepalaDesa() ? 'bg-blue-50/40 border-blue-200/80' : 'bg-gray-50 border-gray-200/80') }}"
                 x-data="{ editing: false, isi: @js($komentar->isi) }">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-xs text-gray-900">
                            {{ $komentar->user->namaUntuk(Auth::user()) }}
                        </span>

                        {{-- Label Peran (PBI #34: Balasan berlabel Admin Desa) --}}
                        @if ($komentar->user->isAdminDesa())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#1F6B4F] text-white tracking-wide">
                                Admin Desa
                            </span>
                        @elseif ($komentar->user->isKepalaDesa())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-700 text-white tracking-wide">
                                Kepala Desa
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-200 text-gray-700">
                                {{ (int) $komentar->user_id === (int) $pengaduan->user_id ? ($pengaduan->isAspirasi() ? 'Pengusul' : 'Warga Pelapor') : 'Warga' }}
                            </span>
                        @endif

                        @if ($komentar->isDisembunyikan())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Disembunyikan dari publik
                            </span>
                        @endif

                        <span class="text-gray-300">&bull;</span>
                        <span class="text-[11px] text-gray-400 font-mono">
                            {{ $komentar->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>

                    {{-- Aksi Edit/Hapus (Kriteria: komentar tidak dapat diubah orang lain) --}}
                    @can('update', $komentar)
                        <div class="flex items-center gap-1">
                            <button type="button" @click="editing = !editing" class="text-[11px] text-gray-500 hover:text-gray-800 font-medium">
                                Edit
                            </button>
                            <span class="text-gray-300">&bull;</span>
                            <form method="POST" action="{{ route('komentar.destroy', $komentar) }}" onsubmit="return confirm('Hapus komentar ini?')" class="inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="text-[11px] text-rose-500 hover:text-rose-700 font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                {{-- Tampilan Normal Isi Komentar --}}
                <div x-show="!editing">
                    <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-line break-words">{{ $komentar->isi }}</p>
                </div>

                {{-- Form Edit Komentar (Hanya untuk Penulis Komentar) --}}
                <div x-show="editing" x-cloak class="mt-2">
                    <form method="POST" action="{{ route('komentar.update', $komentar) }}" class="space-y-2">
                        @csrf
                        @method('put')
                        <textarea name="isi" x-model="isi" rows="2" class="block w-full rounded-lg border-gray-300 text-xs focus:border-[#1F6B4F] focus:ring-[#1F6B4F]" required></textarea>
                        <div class="flex items-center gap-2 justify-end">
                            <button type="button" @click="editing = false" class="px-2.5 py-1 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-white">
                                Batal
                            </button>
                            <button type="submit" class="px-3 py-1 text-xs bg-[#1F6B4F] text-white font-semibold rounded-lg hover:bg-[#174B39]">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Moderasi Admin Desa (PBI #44, PBI #45) --}}
                @can('moderasi', $komentar)
                    <div class="mt-3 pt-3 border-t border-gray-200/70">
                        @include('admin.moderasi.partials.aksi', ['konten' => $komentar, 'jenis' => 'komentar'])
                    </div>
                @endcan
            </div>
        @empty
            <div class="p-6 text-center text-xs text-gray-500 bg-gray-50 rounded-xl border border-gray-100">
                Belum ada tanggapan atau komentar pada laporan ini.
            </div>
        @endforelse
    </div>

    {{-- Formulir Tambah Komentar / Balasan --}}
    @php
        $user = Auth::user();
        $canComment = $user->can('komentari', $pengaduan);
        $sanksiKomentar = ! $canComment && $user->isWarga() && $pengaduan->isAspirasi()
            ? $user->sanksiBerlaku(\App\Enums\FiturSanksi::KomentarPublik)
            : null;
    @endphp

    @if ($canComment)
        <div class="border-t border-gray-100 pt-5">
            <form method="POST" action="{{ route('komentar.store', $pengaduan) }}" class="space-y-3">
                @csrf
                <label for="isi_komentar" class="block text-xs font-semibold text-gray-700">
                    @if ($user->isAdminDesa())
                        Balas Laporan sebagai Admin Desa:
                    @elseif ($user->isKepalaDesa())
                        Catatan Tanggapan Kepala Desa:
                    @else
                        {{ $pengaduan->isAspirasi() ? 'Tulis Komentar Publik:' : 'Tulis Pertanyaan atau Informasi Tambahan:' }}
                    @endif
                </label>
                <textarea id="isi_komentar" name="isi" rows="3" class="block w-full rounded-xl border-gray-300 text-xs focus:border-[#1F6B4F] focus:ring-[#1F6B4F] shadow-xs" placeholder="Tuliskan pesan atau tanggapan Anda di sini..." required>{{ old('isi') }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('isi')" />

                <div class="flex items-center justify-end">
                    <x-primary-button class="bg-[#1F6B4F] hover:bg-[#174B39] text-xs py-2 px-5">
                        @if ($user->isAdminDesa())
                            Kirim Balasan Admin Desa
                        @elseif ($user->isKepalaDesa())
                            Kirim Tanggapan
                        @else
                            Kirim Komentar
                        @endif
                    </x-primary-button>
                </div>
            </form>
        </div>
    @elseif ($sanksiKomentar)
        {{-- Komentar publik dibatasi sanksi (PBI #47, PBI #48) --}}
        <div class="border-t border-gray-100 pt-4">
            @include('warga.sanksi.partials.banner', ['sanksi' => $sanksiKomentar])
        </div>
    @else
        <div class="border-t border-gray-100 pt-4 text-center">
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

