<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        {!! SEO::generate() !!}
        
        @include('partials.head')
    </head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        @include('components.nav.guest-nav')

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Back to Top Button -->
        <button x-data="{ show: false }"
            @scroll.window="show = window.pageYOffset > 400"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-8 right-8 p-3 rounded-full bg-[#02c9c2] text-white shadow-lg hover:bg-[#02a8a2] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[#02c9c2] focus:ring-offset-2 dark:focus:ring-offset-gray-900 z-[2]">
            <flux:icon name="arrow-up" class="w-6 h-6" />
        </button>

        <!-- Footer -->
        @include('components.footer.guest-footer')
    </div>
</body>
</html>
