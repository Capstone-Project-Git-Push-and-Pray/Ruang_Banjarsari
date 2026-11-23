{{-- Pemberitahuan fitur yang dibatasi sanksi (PBI #48). Membutuhkan $sanksi (Sanksi). --}}
<div class="bg-red-50 border border-red-200 text-red-900 p-4 rounded-2xl text-xs shadow-xs flex flex-wrap items-start justify-between gap-3">
    <div class="space-y-0.5">
        <strong class="font-bold block text-sm">Fitur {{ $sanksi->fitur->label() }} Anda sedang dibatasi</strong>
        <span class="block">{{ $sanksi->fitur->pembatasan() }}</span>
        <span class="block font-semibold">{{ $sanksi->keteranganMasaBerlaku() }}.</span>
    </div>
    <a href="{{ route('warga.sanksi.index') }}" class="shrink-0 font-semibold text-red-800 hover:underline">Lihat alasan &rarr;</a>
</div>
