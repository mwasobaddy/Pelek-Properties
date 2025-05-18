@props([
    'border' => true
])

<div {{ $attributes->merge(['class' => 'px-4 py-5 sm:px-6' . ($border ? ' border-b border-gray-200 dark:border-gray-700' : '')]) }}>
    {{ $slot }}
</div>
