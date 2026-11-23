{{-- Rincian satu sanksi untuk warga (PBI #48). Membutuhkan $sanksi (Sanksi). --}}
@php
    $status = $sanksi->status();
    $riwayat = $sanksi->riwayatModerasi;
@endphp
<div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xs border {{ $status === \App\Enums\StatusSanksi::Berlaku ? 'border-red-200' : 'border-[#E2E8E4]' }} space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="font-bold text-gray-900">Fitur {{ $sanksi->fitur->label() }} &bull; Tingkat {{ $sanksi->tingkat }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">Mulai {{ $sanksi->mulai_pada->translatedFormat('d M Y, H:i') }} WIB</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $status->badgeClasses() }}">{{ $status->label() }}</span>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
            <dt class="text-gray-500">Fitur yang dibatasi</dt>
            <dd class="text-gray-900 font-medium mt-0.5">
                {{ $status === \App\Enums\StatusSanksi::Peringatan ? 'Tidak ada. Pelanggaran berikutnya akan membatasi fitur ini.' : $sanksi->fitur->pembatasan() }}
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">Masa berlaku</dt>
            <dd class="text-gray-900 font-medium mt-0.5">
                @if ($sanksi->dicabut_pada)
                    Dicabut {{ $sanksi->dicabut_pada->translatedFormat('d M Y, H:i') }} WIB
                @else
                    {{ $sanksi->keteranganMasaBerlaku() }}
                @endif
            </dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-gray-500">Alasan</dt>
            <dd class="text-gray-900 font-medium mt-0.5">
                {{ $riwayat?->jenis_konten->label() ?? 'Konten' }} Anda dihapus Admin Desa karena: {{ $riwayat?->alasan?->label() ?? 'pelanggaran ketentuan' }}.
                @if ($riwayat?->catatan)
                    <span class="block text-gray-600 font-normal mt-0.5">Catatan: {{ $riwayat->catatan }}</span>
                @endif
            </dd>
        </div>
        @if ($riwayat)
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Konten yang dihapus</dt>
                <dd class="mt-1 bg-gray-50 border border-gray-200 rounded-xl p-3 text-gray-700 whitespace-pre-line break-words line-clamp-6">{{ $riwayat->isi_konten }}</dd>
            </div>
        @endif
        @if ($sanksi->catatan_admin)
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Keterangan Admin Desa</dt>
                <dd class="text-gray-900 mt-0.5">{{ $sanksi->catatan_admin }}</dd>
            </div>
        @endif
    </dl>
</div>
