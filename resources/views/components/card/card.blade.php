@props([
    'variant' => 'default' // default, shadowed, outlined
])

@php
    $variantClasses = [
        'default' => 'bg-white dark:bg-gray-800 rounded-lg',
        'shadowed' => 'bg-white dark:bg-gray-800 rounded-lg',
        'outlined' => 'bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'
    ][$variant];
@endphp

<div {{ $attributes->merge(['class' => $variantClasses . ' overflow-hidden']) }}>
    {{ $slot }}
</div>
