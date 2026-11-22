@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-4 py-2.5 rounded-lg text-start text-sm font-semibold text-white bg-[#1F6B4F] shadow-2xs transition duration-150 ease-in-out'
            : 'block w-full px-4 py-2.5 rounded-lg text-start text-sm font-medium text-gray-700 hover:text-[#1F6B4F] hover:bg-[#EAF5EE] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>