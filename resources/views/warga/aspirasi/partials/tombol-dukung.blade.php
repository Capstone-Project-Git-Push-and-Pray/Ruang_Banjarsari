{{-- Tombol Dukung / Batalkan Dukungan (PBI #41). Membutuhkan $aspirasi dengan atribut didukung & dukungan_count. --}}
@can('dukung', $aspirasi)
    <form method="POST" action="{{ $aspirasi->didukung ? route('warga.aspirasi.dukungan.destroy', $aspirasi) : route('warga.aspirasi.dukungan.store', $aspirasi) }}">
        @csrf
        @if ($aspirasi->didukung)
            @method('delete')
        @endif
        <button type="submit"
                title="{{ $aspirasi->didukung ? 'Batalkan dukungan' : 'Dukung aspirasi ini' }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border transition-colors cursor-pointer {{ $aspirasi->didukung ? 'bg-[#1F6B4F] hover:bg-[#174B39] text-white border-[#174B39]' : 'bg-white hover:bg-[#EAF5EE] text-[#1F6B4F] border-[#1F6B4F]' }}">
            <svg class="w-4 h-4" fill="{{ $aspirasi->didukung ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
            <span>{{ $aspirasi->didukung ? 'Didukung' : 'Dukung' }} ({{ $aspirasi->dukungan_count }})</span>
        </button>
    </form>
@else
    <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
        {{ $aspirasi->dukungan_count }} dukungan
    </span>
@endcan
