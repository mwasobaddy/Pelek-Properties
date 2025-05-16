<nav x-data="{ open: false, darkMode: localStorage.getItem('darkMode') === 'true', properties: false }" 
    x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); document.documentElement.classList.toggle('dark', val) }); 
    document.documentElement.classList.toggle('dark', darkMode)"
    class="sticky top-0 z-50 bg-white dark:bg-gray-900 backdrop-blur-lg bg-opacity-80 dark:bg-opacity-80 border-b border-gray-100 dark:border-gray-800 transition-all duration-300 ease-in-out">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/" wire:navigate class="flex items-center space-x-2 group">
                    <div class="w-8 h-8 bg-gradient-to-br from-[#012e2b] to-[#02c9c2] rounded-lg transition-transform duration-300 group-hover:rotate-6"></div>
                    <span class="text-gray-900 dark:text-white font-extrabold text-xl tracking-tight">Pelek<span class="text-[#02c9c2] dark:text-[#3fe8e2]">Properties</span></span>
                </a>
            </div>
            
            <!-- Desktop Navigation - Hidden on mobile -->
            <div class="hidden md:flex items-center space-x-8">
                <!-- Properties Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            @click.away="open = false"
                            class="text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2] transition duration-300 flex items-center"
                            :aria-expanded="open">
                        <span>Properties</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 transition-transform duration-300" 
                            :class="{'rotate-180': open}" 
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-cloak x-show="open" 
                        x-transition:enter="transition ease-out duration-200" 
                        x-transition:enter-start="opacity-0 translate-y-1" 
                        x-transition:enter-end="opacity-100 translate-y-0" 
                        x-transition:leave="transition ease-in duration-150" 
                        x-transition:leave-start="opacity-100 translate-y-0" 
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 z-50 overflow-hidden">
                        <a href="{{ route('properties.index') }}" wire:navigate class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">All Properties</a>
                        <a href="{{ route('properties.sale') }}" wire:navigate class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">For Sale</a>
                        <a href="{{ route('properties.rent') }}" wire:navigate class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">For Rent</a>
                        <a href="{{ route('properties.airbnb') }}" wire:navigate class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">Airbnb</a>
                        <a href="{{ route('properties.commercial') }}" wire:navigate class="block px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200">Commercial</a>
                    </div>
                </div>

                <a href="{{ route('about') }}" wire:navigate class="text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2] transition duration-300 relative after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 hover:after:w-full after:bg-[#02c9c2] dark:after:bg-[#3fe8e2] after:transition-all after:duration-300">About Us</a>
                <a href="{{ route('contact') }}" wire:navigate class="text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2] transition duration-300 relative after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 hover:after:w-full after:bg-[#02c9c2] dark:after:bg-[#3fe8e2] after:transition-all after:duration-300">Contact</a>
            </div>
            
            <!-- Auth Links & Dark Mode - Hidden on mobile -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Dark mode toggle -->
                <div x-data="{ theme: localStorage.getItem('theme') || 'system', isOpen: false }" 
                     x-init="
                        $watch('theme', value => {
                            if (value === 'system') {
                                localStorage.removeItem('theme');
                                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                                    document.documentElement.classList.add('dark');
                                    darkMode = true;
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    darkMode = false;
                                }
                            } else {
                                localStorage.setItem('theme', value);
                                darkMode = value === 'dark';
                                document.documentElement.classList.toggle('dark', darkMode);
                            }
                        });
                        
                        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                            if (theme === 'system') {
                                darkMode = e.matches;
                                document.documentElement.classList.toggle('dark', e.matches);
                            }
                        });
                     "
                     class="relative">
                    <button @click="isOpen = !isOpen" 
                            class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-300" 
                            aria-label="Toggle theme"
                            title="Change theme">
                        <!-- System icon -->
                        <svg x-show="theme === 'system'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <!-- Dark icon -->
                        <svg x-show="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                        <!-- Light icon -->
                        <svg x-show="theme === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="isOpen" @click.away="isOpen = false" 
                         class="absolute right-0 mt-2 w-36 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700">
                        <button @click="theme = 'system'; isOpen = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-between items-center" title="Use system theme">
                            System
                            <svg x-show="theme === 'system'" class="h-4 w-4 text-[#02c9c2] dark:text-[#3fe8e2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button @click="theme = 'dark'; isOpen = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-between items-center" title="Use dark theme">
                            Dark
                            <svg x-show="theme === 'dark'" class="h-4 w-4 text-[#02c9c2] dark:text-[#3fe8e2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button @click="theme = 'light'; isOpen = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-between items-center" title="Use light theme">
                            Light
                            <svg x-show="theme === 'light'" class="h-4 w-4 text-[#02c9c2] dark:text-[#3fe8e2]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>

                <a href="{{ route('blog.index') }}" wire:navigate class="bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] hover:from-[#012e2b] hover:to-[#014e4a] dark:hover:from-[#02c9c2] dark:hover:to-[#02a8a2] text-white font-medium px-4 py-2 rounded-md shadow-md hover:shadow-lg transition-all duration-300">Blog</a>
            </div>
            
            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center space-x-2">
                <!-- Dark mode mobile -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-300" aria-label="Toggle dark mode">
                    <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                    <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    </svg>
                </button>
                
                <button @click="open = !open" class="outline-none p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300" aria-label="Toggle menu">
                    <svg x-show="!open" class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="open" class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-cloak x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4"
            class="md:hidden pb-6">
            
            <!-- Properties accordion -->
            <div class="space-y-1">
                <button @click="properties = !properties" class="flex justify-between items-center w-full py-2 text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">
                    Properties
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" :class="{'rotate-180': properties}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="properties" x-cloak class="pl-4 border-l-2 border-[#02c9c2] dark:border-[#3fe8e2] space-y-1">
                    <a href="{{ route('properties.index') }}" wire:navigate class="block py-2 text-gray-700 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">All Properties</a>
                    <a href="{{ route('properties.sale') }}" wire:navigate class="block py-2 text-gray-700 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">For Sale</a>
                    <a href="{{ route('properties.rent') }}" wire:navigate class="block py-2 text-gray-700 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">For Rent</a>
                    <a href="{{ route('properties.airbnb') }}" wire:navigate class="block py-2 text-gray-700 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">Airbnb</a>
                </div>
            </div>
            
            <a href="{{ route('about') }}" wire:navigate class="flex items-center py-2 text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">About Us</a>
            <a href="{{ route('contact') }}" wire:navigate class="flex items-center py-2 text-gray-700 dark:text-gray-200 hover:text-[#02c9c2] dark:hover:text-[#3fe8e2]">Contact</a>
            
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 mt-4 space-y-2">
                <a href="{{ route('blog.index') }}" wire:navigate class="block py-2 text-[#02c9c2] dark:text-[#3fe8e2] hover:text-[#018a85] dark:hover:text-[#7df3ee] font-medium">
                    <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2" />
                    </svg>
                    Blog
                    </span>
                </a>
            </div>
        </div>
    </div>
</nav>
