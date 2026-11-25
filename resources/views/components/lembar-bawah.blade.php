{{--
    Lembar bawah (bottom sheet) bergaya media sosial.
    Ponsel dan tablet: muncul meluncur dari bawah, menempel pada tepi bawah layar,
    dan dapat ditutup dengan menggeser pegangan ke bawah.
    Desktop (lg ke atas): tampil sebagai popup di tengah layar.
    Dibuka/ditutup dengan event yang sama seperti <x-modal>: open-modal dan close-modal.
    Elemen bertanda data-gulir-ke-bawah otomatis digulir ke bagian paling bawah setiap kali dibuka.
--}}
@props([
    'name',
    'show' => false,
])

<div x-data="{
        show: @js($show),
        geser: 0,
        mulaiY: null,
        mulaiGeser(event) { this.mulaiY = event.touches[0].clientY; },
        saatGeser(event) {
            if (this.mulaiY === null) { return; }
            this.geser = Math.max(0, event.touches[0].clientY - this.mulaiY);
        },
        selesaiGeser() {
            if (this.geser > 110) { this.show = false; }
            this.geser = 0;
            this.mulaiY = null;
        }
     }"
     x-init="
        const gulirKeKomentarTerbaru = () => $nextTick(() => $el.querySelectorAll('[data-gulir-ke-bawah]').forEach((daftar) => daftar.scrollTop = daftar.scrollHeight));
        $watch('show', (terbuka) => {
            document.body.classList.toggle('overflow-y-hidden', terbuka);
            if (terbuka) { gulirKeKomentarTerbaru(); }
        });
        if (show) { document.body.classList.add('overflow-y-hidden'); gulirKeKomentarTerbaru(); }
     "
     x-on:open-modal.window="$event.detail == @js($name) ? show = true : null"
     x-on:close-modal.window="$event.detail == @js($name) ? show = false : null"
     x-on:keydown.escape.window="show = false"
     x-show="show"
     class="fixed inset-0 z-50 flex items-end lg:items-center justify-center lg:p-6"
     style="display: {{ $show ? 'flex' : 'none' }};"
     role="dialog" aria-modal="true">

    {{-- Latar gelap --}}
    <div x-show="show"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         x-on:click="show = false"
         class="absolute inset-0 bg-gray-900/50"></div>

    {{-- Panel --}}
    <div x-show="show"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full lg:translate-y-4 lg:opacity-0 lg:scale-95"
         x-transition:enter-end="translate-y-0 lg:opacity-100 lg:scale-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 lg:opacity-100 lg:scale-100"
         x-transition:leave-end="translate-y-full lg:translate-y-4 lg:opacity-0 lg:scale-95"
         :style="geser > 0 ? `transform: translateY(${geser}px); transition: none;` : ''"
         class="relative w-full lg:max-w-2xl max-h-[88vh] lg:max-h-[85vh] flex flex-col bg-white rounded-t-3xl lg:rounded-3xl shadow-2xl overflow-hidden pb-[env(safe-area-inset-bottom)]">

        {{-- Pegangan geser (ponsel & tablet) --}}
        <div class="lg:hidden shrink-0 pt-3 pb-1 flex justify-center cursor-grab touch-none"
             x-on:touchstart.passive="mulaiGeser($event)"
             x-on:touchmove.passive="saatGeser($event)"
             x-on:touchend="selesaiGeser()"
             aria-hidden="true">
            <span class="w-10 h-1.5 rounded-full bg-gray-300"></span>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain">
            {{ $slot }}
        </div>
    </div>
</div>
