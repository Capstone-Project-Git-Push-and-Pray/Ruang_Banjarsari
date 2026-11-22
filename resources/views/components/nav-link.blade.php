@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold leading-5 text-white bg-[#1F6B4F] shadow-xs transition duration-150 ease-in-out'
            : 'inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium leading-5 text-gray-600 hover:text-[#1F6B4F] hover:bg-[#EAF5EE] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>