{{--
    Tombol Dukung / Batalkan Dukungan (PBI #41) diikuti jumlah dukungan di sebelah kanannya.
    Bekerja seperti tombol suka di media sosial: langsung berubah tanpa memuat ulang halaman.
    Membutuhkan $aspirasi dengan atribut didukung & dukungan_count.
--}}
@php
    $ikonDukung = 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5';
@endphp
<div class="inline-flex items-center gap-2"
     x-data="{
         didukung: @js((bool) $aspirasi->didukung),
         jumlah: @js((int) $aspirasi->dukungan_count),
         memproses: false,
         animasi: false,
         async ubah() {
             if (this.memproses) return;
             this.memproses = true;
             const sebelumnya = { didukung: this.didukung, jumlah: this.jumlah };
             this.didukung = ! this.didukung;
             this.jumlah += this.didukung ? 1 : -1;
             if (this.didukung) {
                 this.animasi = true;
                 setTimeout(() => this.animasi = false, 250);
             }
             try {
                 const respons = await fetch(@js(route('warga.aspirasi.dukungan.store', $aspirasi)), {
                     method: this.didukung ? 'POST' : 'DELETE',
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                     },
                 });
                 if (respons.status === 419) {
                     window.location.reload();
                     return;
                 }
                 if (! respons.ok) throw new Error(respons.status);
                 const data = await respons.json();
                 this.didukung = data.didukung;
                 this.jumlah = data.jumlah;
             } catch (galat) {
                 this.didukung = sebelumnya.didukung;
                 this.jumlah = sebelumnya.jumlah;
             } finally {
                 this.memproses = false;
             }
         },
     }">
    @can('dukung', $aspirasi)
        <form method="POST" action="{{ $aspirasi->didukung ? route('warga.aspirasi.dukungan.destroy', $aspirasi) : route('warga.aspirasi.dukungan.store', $aspirasi) }}"
              data-pertahankan-gulir @submit.prevent="ubah()">
            @csrf
            @if ($aspirasi->didukung)
                @method('delete')
            @endif
            <button type="submit"
                    title="{{ $aspirasi->didukung ? 'Batalkan dukungan' : 'Dukung aspirasi ini' }}"
                    :title="didukung ? 'Batalkan dukungan' : 'Dukung aspirasi ini'"
                    :aria-pressed="didukung"
                    {{-- Kelas warna hanya lewat :class; jika ikut ditulis di atribut class, Alpine tidak dapat melepasnya saat dukungan dibatalkan --}}
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border transition-colors cursor-pointer"
                    :class="didukung ? 'bg-[#1F6B4F] hover:bg-[#174B39] text-white border-[#174B39]' : 'bg-white hover:bg-[#EAF5EE] text-[#1F6B4F] border-[#1F6B4F]'">
                <svg class="w-4 h-4 transition-transform duration-200 ease-out" :class="animasi ? 'scale-150' : 'scale-100'"
                     :fill="didukung ? 'currentColor' : 'none'"
                     stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonDukung }}"/></svg>
                <span x-text="didukung ? 'Didukung' : 'Dukung'">{{ $aspirasi->didukung ? 'Didukung' : 'Dukung' }}</span>
            </button>
        </form>
    @else
        <span title="{{ (int) $aspirasi->user_id === (int) Auth::id() ? 'Anda tidak dapat mendukung aspirasi sendiri' : 'Dukungan tidak tersedia' }}"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-50 text-gray-400 border border-gray-200 cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ikonDukung }}"/></svg>
            <span>Dukung</span>
        </span>
    @endcan
    <span class="text-sm font-bold text-gray-800 tabular-nums"
          title="{{ $aspirasi->dukungan_count }} warga mendukung" :title="jumlah + ' warga mendukung'"
          x-text="jumlah">{{ $aspirasi->dukungan_count }}</span>
</div>
