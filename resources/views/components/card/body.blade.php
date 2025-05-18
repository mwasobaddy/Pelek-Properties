@props([
    'padding' => true
])

<div {{ $attributes->merge(['class' => $padding ? 'px-4 py-5 sm:p-6' : '']) }}>
    {{ $slot }}
</div>
