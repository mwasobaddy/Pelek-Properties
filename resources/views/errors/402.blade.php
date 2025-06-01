<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        {!! SEO::generate() !!}
        
        @include('partials.head')
    </head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col relative overflow-hidden bg-gradient-to-b from-gray-50 via-gray-100 to-white dark:from-gray-900 dark:via-gray-850 dark:to-gray-800">
        <!-- Animated decorative elements -->
        <div aria-hidden="true" class="absolute inset-0 overflow-hidden -z-10">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-gradient-to-br from-[#02c9c2]/20 to-transparent rounded-full blur-3xl animate-pulse" style="animation-duration: 8s;"></div>
            <div class="absolute bottom-1/3 right-1/4 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/15 to-transparent rounded-full blur-3xl animate-pulse" style="animation-duration: 10s; animation-delay: 1s;"></div>
            <!-- Abstract pattern -->
            <div class="absolute inset-0 opacity-[0.015] dark:opacity-[0.03]">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid-pattern" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M0 40L40 0M0 0L40 40" stroke="currentColor" stroke-width="0.5" fill="none" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-pattern)" />
                </svg>
            </div>
        </div>
        
        <!-- Top accent bar with animated gradient -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#02c9c2] via-[#01a5a0] to-[#012e2b] relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        </div>
        
        <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 py-16">
            <div class="w-full max-w-md mx-auto text-center">
                <!-- 3D-style error badge -->
                <div class="inline-flex items-center justify-center mb-6 relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] rounded-xl blur opacity-50 group-hover:opacity-75 transition duration-1000 group-hover:duration-200 animate-tilt"></div>
                    <div class="relative flex items-center gap-3 bg-white dark:bg-gray-800 rounded-lg px-6 py-3 shadow-lg">
                        <flux:icon name="credit-card" class="w-8 h-8 sm:w-10 sm:h-10 text-[#02c9c2]" />
                        <span class="text-5xl sm:text-6xl font-extrabold bg-clip-text bg-gradient-to-br from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 text-red-500">402</span>
                    </div>
                </div>
                
                <!-- Error content with improved typography -->
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3">Payment Required</h1>
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-sm mx-auto">
                    Access to this resource requires payment or subscription. Please complete the payment process to continue.
                </p>
                
                <!-- Action buttons with hover effects -->
                <div class="flex flex-row gap-4 justify-center">
                    <a href="
                        @auth
                            {{ route('admin.dashboard') }}
                        @else
                            {{ route('home') }}
                        @endauth
                    " 
                        class="group relative px-6 py-3 bg-gradient-to-r from-[#02c9c2] to-[#01706c] text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden"
                        wire:navigate
                    >
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -translate-x-full group-hover:translate-x-full transition-transform duration-700"></span>
                        <span class="relative flex items-center">
                            <flux:icon name="arrow-left" class="w-4 h-4 mr-2 transition-transform group-hover:-translate-x-1" />
                            Return Home
                        </span>
                    </a>
                    
                    <a href="javascript:history.back()" 
                        class="group px-6 py-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-medium rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-all duration-300"
                        wire:navigate
                    >
                        <span class="flex items-center">
                            <flux:icon name="arrow-path" class="w-4 h-4 mr-2 transition-transform group-hover:rotate-45" style="transition: transform 0.5s ease;" />
                            Go Back
                        </span>
                    </a>
                </div>
            </div>
            
            <!-- Illustrated element -->
            <div class="mt-12 sm:mt-16 relative max-w-lg w-full">
                <svg class="w-full h-auto text-gray-300 dark:text-gray-700" viewBox="0 0 400 150" xmlns="http://www.w3.org/2000/svg">
                    <!-- Simplified landscape illustration -->
                    <path d="M0,100 L50,90 L100,110 L150,95 L200,105 L250,85 L300,95 L350,90 L400,100 L400,150 L0,150 Z" fill="currentColor" opacity="0.3" />
                    <path d="M0,110 L50,100 L100,120 L150,105 L200,115 L250,95 L300,105 L350,100 L400,110 L400,150 L0,150 Z" fill="currentColor" opacity="0.5" />
                    <path d="M0,120 L50,110 L100,130 L150,115 L200,125 L250,105 L300,115 L350,110 L400,120 L400,150 L0,150 Z" fill="currentColor" opacity="0.7" />
                    
                    <!-- Payment animation -->
                    <g class="animate-float" style="animation-duration: 4s;">
                        <rect x="170" y="50" width="60" height="40" rx="4" fill="currentColor" opacity="0.9" />
                        <rect x="180" y="65" width="40" height="5" rx="1" fill="white" opacity="0.6" />
                        <rect x="180" y="75" width="20" height="5" rx="1" fill="white" opacity="0.6" />
                    </g>
                </svg>
            </div>
        </main>
        
        <footer class="py-4 px-6 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="javascript:window.location.reload()" class="hover:text-[#02c9c2] transition-colors duration-300 underline underline-offset-2" wire:navigate>
                    Refresh the page
                </a>
                or contact support if you think this is an error
            </p>
        </footer>
    </div>
</body>
</html>
