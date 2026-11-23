{{--
    Dropdown bergaya kustom (Alpine.js) mengikuti tampilan dropdown Status dan Kategori pada halaman kelola aspirasi.
    - Dengan prop `form`: formulir dengan id tersebut langsung dikirim saat opsi dipilih (filter).
    - Tanpa prop `form`: berperan sebagai isian formulir biasa.
    - Prop `placeholder`: teks tombol saat belum ada pilihan, tanpa ikut tampil sebagai opsi.
    Setiap pilihan memancarkan event `dropdown-changed` berisi { name, value } agar komponen induk dapat ikut bereaksi.
--}}
@props([
    'name',
    'label',
    'form' => null,
    'options' => [],
    'selected' => '',
    'placeholder' => null,
])

@php
    $selected = (string) $selected;
    $selectedLabel = $options[$selected] ?? $placeholder ?? reset($options);
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{
    open: false,
    selected: @js($selected),
    selectedLabel: @js($selectedLabel),
    select(value, label) {
        this.selected = value;
        this.selectedLabel = label;
        this.open = false;
        this.$dispatch('dropdown-changed', { name: @js($name), value });
        @if ($form)
            this.$nextTick(() => document.getElementById(@js($form)).submit());
        @endif
    }
}">
    <label class="block text-xs font-bold text-gray-700 mb-1.5">{{ $label }}</label>
    <input type="hidden" name="{{ $name }}" :value="selected">

    <button type="button"
            aria-haspopup="listbox"
            :aria-expanded="open"
            @keydown.escape="open = false"
            @click="open = !open"
            class="w-full flex items-center justify-between text-xs rounded-xl border border-[#E2E8E4] py-2 px-3 text-gray-800 bg-gray-50/40 hover:bg-white focus:outline-none focus:border-[#1F6B4F] focus:ring-1 focus:ring-[#1F6B4F] transition-all cursor-pointer shadow-2xs"
            :class="selected !== '' ? 'border-[#185840] text-[#185840] font-semibold bg-[#F4F9F6]' : ''">
        <span class="truncate font-medium" :class="selected === '' && @js($placeholder !== null) ? 'text-gray-400' : ''" x-text="selectedLabel"></span>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0 ml-1.5"
             :class="open ? 'rotate-180 text-[#1F6B4F]' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.outside="open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute z-30 w-full mt-1.5 bg-white rounded-2xl shadow-xl border border-gray-100 max-h-64 overflow-y-auto text-xs focus:outline-none overscroll-contain">
        @foreach ($options as $value => $optionLabel)
            <button type="button"
                    @click="select(@js((string) $value), @js($optionLabel))"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 text-left transition-colors cursor-pointer first:rounded-t-2xl last:rounded-b-2xl"
                    :class="selected === @js((string) $value)
                        ? 'bg-[#185840] text-white font-semibold'
                        : 'text-gray-700 hover:bg-emerald-50 hover:text-[#185840]'">
                <span class="truncate">{{ $optionLabel }}</span>
                <svg x-show="selected === @js((string) $value)" class="w-4 h-4 text-white shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        @endforeach
    </div>
</div>
