@props([
    'border' => true
])

<div {{ $attributes->merge(['class' => 'px-4 py-4 sm:px-6' . ($border ? ' border-t border-gray-200 dark:border-gray-700' : '')]) }}>
    {{ $slot }}
</div>
