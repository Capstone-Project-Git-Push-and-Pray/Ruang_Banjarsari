{{--
    Tombol moderasi konten publik (PBI #44, PBI #45).
    Membutuhkan $konten (Pengaduan|Komentar) dan $jenis ('aspirasi'|'komentar').
--}}
@php
    $idForm = $jenis.'-'.$konten->getKey();
    $isFormAktif = old('form_moderasi') === $idForm;
@endphp
<div x-data="{ hapus: @js($isFormAktif), alasan: @js($isFormAktif ? old('alasan', '') : '') }" class="space-y-3" id="moderasi-{{ $idForm }}">
    <div class="flex flex-wrap items-center gap-2">
        <form method="POST" action="{{ route("admin.moderasi.{$jenis}.".($konten->isDisembunyikan() ? 'tampilkan' : 'sembunyikan'), $konten) }}" data-pertahankan-gulir>
            @csrf
            @method('patch')
            <button type="submit" class="px-3 py-1.5 rounded-lg border text-[11px] font-semibold transition-colors cursor-pointer {{ $konten->isDisembunyikan() ? 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' : 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                {{ $konten->isDisembunyikan() ? 'Tampilkan Kembali' : 'Sembunyikan' }}
            </button>
        </form>
        <button type="button" @click="hapus = ! hapus" class="px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 text-[11px] font-semibold transition-colors cursor-pointer">
            Hapus dengan Alasan
        </button>
    </div>

    <form x-show="hapus" x-cloak method="POST" action="{{ route("admin.moderasi.{$jenis}.hapus", $konten) }}" data-pertahankan-gulir
          onsubmit="return confirm('Hapus {{ $jenis }} ini? Konten tidak akan terlihat lagi oleh siapa pun di halaman warga maupun desa.')"
          class="p-4 rounded-2xl border border-red-200 bg-red-50/40 space-y-3">
        @csrf
        @method('delete')
        <input type="hidden" name="form_moderasi" value="{{ $idForm }}">
        <x-filter-dropdown name="alasan" label="Alasan Penghapusan"
                           x-on:dropdown-changed="alasan = $event.detail.value"
                           :selected="$isFormAktif ? old('alasan', '') : ''"
                           placeholder="Pilih alasan..."
                           :options="collect(\App\Enums\AlasanModerasi::cases())->mapWithKeys(fn ($alasanModerasi) => [$alasanModerasi->value => $alasanModerasi->label()])->all()" />
        <div>
            <label for="catatan-{{ $idForm }}" class="block text-xs font-bold text-gray-700 mb-1">
                Catatan <span x-text="alasan === 'lainnya' ? '(wajib)' : '(opsional)'"></span>
            </label>
            <textarea id="catatan-{{ $idForm }}" name="catatan_moderasi" rows="2" maxlength="1000" :required="alasan === 'lainnya'" class="block w-full rounded-xl border border-[#E2E8E4] text-xs focus:border-[#1F6B4F] focus:ring-[#1F6B4F]">{{ $isFormAktif ? old('catatan_moderasi') : '' }}</textarea>
        </div>
        @if ($isFormAktif)
            <x-input-error :messages="array_merge($errors->get('alasan'), $errors->get('catatan_moderasi'))" />
        @endif
        <div class="flex justify-end gap-2">
            <button type="button" @click="hapus = false" class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg text-gray-600 hover:bg-white cursor-pointer">Batal</button>
            <button type="submit" class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg cursor-pointer">Hapus Konten</button>
        </div>
    </form>
</div>
