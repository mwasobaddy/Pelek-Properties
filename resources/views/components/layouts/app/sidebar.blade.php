<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <!-- Add motion-safe animations -->
        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            .animate-fade-in {
                animation: fadeIn 0.5s ease-out forwards;
            }
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgba(2, 201, 194, 0.3);
                border-radius: 999px;
            }
            .sidebar-section {
                transition: all 0.3s ease;
            }
            .sidebar-section:hover {
                transform: translateX(4px);
            }
            @media (prefers-color-scheme: dark) {
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background-color: rgba(2, 201, 194, 0.5);
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-50 to-white dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-800">
        <!-- Improved Sidebar with Glassmorphism & Gradient Accent -->
        <flux:sidebar sticky stashable class="border-e border-white/10 bg-white/10 dark:bg-zinc-900/60 backdrop-blur-xl shadow-xl overflow-y-auto custom-scrollbar">
            <!-- Better Toggle Button with Animation -->
            <flux:sidebar.toggle class="lg:hidden hover:text-[#02c9c2] transition-all duration-300 hover:rotate-180" icon="x-mark" />

            <!-- Enhanced Logo with Subtle Animation -->
            <a href="{{ route('admin.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse transform transition-all duration-300 hover:scale-105 group" wire:navigate>
                <x-app-logo class="group-hover:animate-pulse" />
            </a>

            <!-- Modern Navigation with Better Spacing -->
            <flux:navlist variant="outline" class="mt-6 px-1 space-y-2">
                <!-- Main Platform Section -->
                <div class="sidebar-section">
                    <flux:navlist.group 
                        :heading="__('Quick Access')" 
                        class="grid gap-1.5 relative"
                    >
                        <!-- Add decorative accent element -->
                        <div class="absolute -left-1 top-0 h-5 w-1 bg-gradient-to-b from-[#02c9c2] to-[#012e2b] rounded-r-full"></div>
                        <flux:navlist.item 
                            icon="home" 
                            :href="route('admin.dashboard')" 
                            :current="request()->routeIs('admin.dashboard')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            <span class="flex items-center gap-2">
                                {{ __('Dashboard') }}
                                <!-- New badge example -->
                                <span class="hidden px-1.5 py-0.5 text-xs bg-[#02c9c2]/20 text-[#02c9c2] rounded-full">New</span>
                            </span>
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="building-office-2" 
                            :href="route('admin.properties.index')" 
                            :current="request()->routeIs('admin.properties.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('All Properties') }}
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="users" 
                            :href="route('admin.tenants.index')" 
                            :current="request()->routeIs('admin.tenants.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Manage Tenants') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="newspaper" 
                            :href="route('admin.blog.index')" 
                            :current="request()->routeIs('admin.blog.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Blog Management') }}
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="shield-check" 
                            :href="route('admin.roles.index')" 
                            :current="request()->routeIs('admin.roles.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Roles & Permissions') }}
                            <span class="hidden px-1.5 py-0.5 text-xs bg-[#02c9c2]/20 text-[#02c9c2] rounded-full">New</span>
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="user" 
                            :href="route('admin.users.index')" 
                            :current="request()->routeIs('admin.users.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('User Management') }}
                            <span class="hidden px-1.5 py-0.5 text-xs bg-[#02c9c2]/20 text-[#02c9c2] rounded-full">New</span>
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>

                <!-- Property Management Section with Enhanced Visual Hierarchy -->
                <div class="sidebar-section">
                    <flux:navlist.group 
                        :heading="__('Property Management')" 
                        class="grid gap-1.5 relative"
                    >
                        <!-- Add decorative accent element -->
                        <div class="absolute -left-1 top-0 h-5 w-1 bg-gradient-to-b from-[#02c9c2] to-[#012e2b] rounded-r-full"></div>
                        <flux:navlist.item 
                            icon="building-office-2" 
                            :href="route('management.contracts')" 
                            :current="request()->routeIs('management.contracts')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Management Contracts') }}
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="wrench-screwdriver" 
                            :href="route('management.maintenance')" 
                            :current="request()->routeIs('management.maintenance')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            <span class="flex items-center justify-between w-full">
                                {{ __('Maintenance Requests') }}
                            </span>
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="chart-bar" 
                            :href="route('management.financials')" 
                            :current="request()->routeIs('management.financials')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Financial Records') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="calculator" 
                            :href="route('admin.management.valuations')" 
                            :current="request()->routeIs('admin.management.valuations')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Property Valuations') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="chart-bar-square" 
                            :href="route('admin.management.market-analysis')" 
                            :current="request()->routeIs('admin.management.market-analysis')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Market Analysis') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>

                <!-- Viewing Management Section with Visual Distinction -->
                <div class="sidebar-section">
                    <flux:navlist.group 
                        :heading="__('Schedule Management')" 
                        class="grid gap-1.5 relative"
                    >
                        <!-- Add decorative accent element -->
                        <div class="absolute -left-1 top-0 h-5 w-1 bg-gradient-to-b from-[#02c9c2] to-[#012e2b] rounded-r-full"></div>
                        <flux:navlist.item 
                            icon="calendar" 
                            :href="route('admin.schedule.index')" 
                            :current="request()->routeIs('admin.schedule.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Viewing Schedule') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="calendar-days" 
                            :href="route('admin.schedule.appointments')" 
                            :current="request()->routeIs('admin.schedule.appointments')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Manage Appointments') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>

                <!-- Tenant Management Section -->
                <div class="sidebar-section">
                    <flux:navlist.group :heading="__('tenant management')" class="grid gap-1.5">
                        <flux:navlist.item 
                            icon="users" 
                            :href="route('admin.tenants.index')" 
                            :current="request()->routeIs('admin.tenants.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Manage Tenants') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>

                <!-- Properties Administration with Enhanced Group Headers -->
                <div class="sidebar-section">
                    <flux:navlist.group 
                        :heading="__('Properties Admin')" 
                        class="grid gap-1.5 relative"
                    >
                        <!-- Add decorative accent element -->
                        <div class="absolute -left-1 top-0 h-5 w-1 bg-gradient-to-b from-[#02c9c2] to-[#012e2b] rounded-r-full"></div>
                        
                        <flux:navlist.item 
                            icon="building-office-2" 
                            :href="route('admin.properties.index')" 
                            :current="request()->routeIs('admin.properties.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('All Properties') }}
                        </flux:navlist.item>
                        
                        {{-- <flux:navlist.item 
                            icon="cog" 
                            :href="route('admin.properties.manage')" 
                            :current="request()->routeIs('admin.properties.manage')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Manage Properties') }}
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="photo" 
                            :href="route('admin.properties.photos', ['property' => 1])" 
                            :current="request()->routeIs('admin.properties.photos')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Property Photos') }}
                        </flux:navlist.item>
                        
                        <flux:navlist.item 
                            icon="building-office" 
                            :href="route('admin.properties.commercial')" 
                            :current="request()->routeIs('admin.properties.commercial')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Commercial Properties') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="currency-dollar" 
                            :href="route('admin.properties.offers')" 
                            :current="request()->routeIs('admin.properties.offers')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Property Offers') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="document-duplicate" 
                            :href="route('admin.documents.index')" 
                            :current="request()->routeIs('admin.documents.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Document Management') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="building-library" 
                            :href="route('admin.properties.developments')" 
                            :current="request()->routeIs('admin.properties.developments')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Development Projects') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="banknotes" 
                            :href="route('admin.properties.sales')" 
                            :current="request()->routeIs('admin.properties.sales')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Property Sales') }}
                        </flux:navlist.item> --}}
                    </flux:navlist.group>
                </div>

                <!-- Analytics Section with Improved Visual Design -->
                <div class="sidebar-section">
                    <flux:navlist.group :heading="__('Analytics')" class="grid gap-1.5 relative">
                        <!-- Add decorative accent element -->
                        <div class="absolute -left-1 top-0 h-5 w-1 bg-gradient-to-b from-[#02c9c2] to-[#012e2b] rounded-r-full"></div>
                        
                        {{-- <flux:navlist.item 
                            icon="chart-pie" 
                            :href="route('admin.analytics.dashboard')" 
                            :current="request()->routeIs('admin.analytics.dashboard')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('analytics dashboard') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="document-chart-bar" 
                            :href="route('admin.reports.index')" 
                            :current="request()->routeIs('admin.reports.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('reports exports') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="chart-bar" 
                            :href="route('admin.analytics.occupancy-reports')" 
                            :current="request()->routeIs('admin.analytics.occupancy-reports')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('occupancy reports') }}
                        </flux:navlist.item>

                        <flux:navlist.item 
                            icon="clock" 
                            :href="route('admin.audit-logs')" 
                            :current="request()->routeIs('admin.audit-logs')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('audit logs') }}
                        </flux:navlist.item> --}}

                        <!-- Placeholder for future system settings items -->
                        <div class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 italic">
                            Analytics coming soon
                        </div>
                    </flux:navlist.group>
                </div>

                <!-- Other Management Section -->
                <div class="sidebar-section"> 
                    <flux:navlist.group :heading="__('Other Management')" class="grid gap-1.5">
                        <flux:navlist.item 
                            icon="newspaper" 
                            :href="route('admin.blog.index')" 
                            :current="request()->routeIs('admin.blog.index')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Blog Management') }}
                        </flux:navlist.item>

                        {{-- <flux:navlist.item 
                            icon="wrench-screwdriver" 
                            :href="route('management.maintenance')" 
                            :current="request()->routeIs('management.maintenance')" 
                            class="rounded-xl transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] hover:scale-[1.02]"
                            wire:navigate
                        >
                            {{ __('Maintenance Requests') }}
                        </flux:navlist.item> --}}
                    </flux:navlist.group>
                </div>

                <!-- System Settings -->
                <div class="sidebar-section">
                    <flux:navlist.group :heading="__('System')" class="grid gap-1.5">
                        <!-- Placeholder for future system settings items -->
                        <div class="px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 italic">
                            System settings coming soon
                        </div>
                    </flux:navlist.group>
                </div>
            </flux:navlist>

            <flux:spacer />

            <!-- Enhanced Desktop User Menu with Modern Profile Card -->
            <div class="p-3">
                <flux:dropdown position="bottom" align="start" class="w-full">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                        class="w-full rounded-lg bg-gradient-to-r from-[#02c9c2]/10 to-[#012e2b]/10 dark:from-[#02c9c2]/20 dark:to-[#012e2b]/20 backdrop-blur-sm ring-1 ring-white/10 hover:ring-[#02c9c2]/30 hover:from-[#02c9c2]/20 hover:to-[#012e2b]/20 transition-all duration-300"
                    />

                    <flux:menu class="w-[250px] bg-white/90 dark:bg-zinc-800/90 backdrop-blur-xl border border-white/10 shadow-xl rounded-xl overflow-hidden">
                        <flux:menu.radio.group>
                            <div class="p-3 text-sm font-normal">
                                <div class="flex items-center gap-3 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-gradient-to-br from-[#02c9c2]/20 to-[#012e2b]/20 text-[#02c9c2] dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start leading-tight">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                                
                                <!-- User status indicator -->
                                <div class="mt-2 px-1.5 py-1 text-xs bg-[#02c9c2]/10 text-[#02c9c2] rounded-lg flex items-center">
                                    <span class="h-2 w-2 rounded-full bg-[#02c9c2] mr-2"></span>
                                    Online
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator class="border-white/10" />

                        <flux:menu.radio.group>
                            <flux:menu.item 
                                :href="route('settings.profile')" 
                                icon="cog" 
                                class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                                wire:navigate
                            >
                                {{ __('Settings') }}
                            </flux:menu.item>
                            
                            <!-- Added help item -->
                            <flux:menu.item 
                                href="#" 
                                icon="question-mark-circle" 
                                class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                            >
                                {{ __('Help & Support') }}
                            </flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator class="border-white/10" />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item 
                                as="button" 
                                type="submit" 
                                icon="arrow-right-start-on-rectangle" 
                                class="w-full hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                            >
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </flux:sidebar>

        <!-- Enhanced Mobile Header with Glassmorphism -->
        <flux:header class="lg:hidden bg-white/10 dark:bg-zinc-900/60 backdrop-blur-xl border-b border-white/10 shadow-lg">
            <flux:sidebar.toggle class="lg:hidden hover:text-[#02c9c2] transition-all duration-300 hover:scale-110" icon="bars-2" inset="left" />

            <span class="text-lg font-semibold bg-gradient-to-r from-[#02c9c2] to-[#012e2b] bg-clip-text text-transparent">Pelek Properties</span>

            <flux:spacer />

            <!-- Notification bell with maintenance request count -->
            <flux:dropdown position="bottom" align="end">
                <button class="relative p-2 mr-2 rounded-full hover:bg-[#02c9c2]/10 transition-all duration-200">
                    <flux:icon name="bell" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <span class="absolute top-1 right-1 h-2 w-2 bg-[#02c9c2] rounded-full"></span>
                </button>

                <!-- Notification Menu -->
                <flux:menu class="w-[300px] bg-white/90 dark:bg-zinc-800/90 backdrop-blur-xl border border-white/10 shadow-xl rounded-xl overflow-hidden">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold mb-2">{{ __('Notifications') }}</h3>
                        <flux:menu.item 
                            :href="route('management.maintenance')" 
                            class="flex items-center justify-between hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            <span>{{ __('New maintenance requests') }}</span>
                        </flux:menu.item>
                    </div>
                </flux:menu>
            </flux:dropdown>

            <flux:dropdown position="bottom" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="rounded-full bg-gradient-to-r from-[#02c9c2]/10 to-[#012e2b]/10 dark:from-[#02c9c2]/20 dark:to-[#012e2b]/20 backdrop-blur-sm ring-1 ring-white/10 hover:ring-[#02c9c2]/30 transition-all duration-300"
                />

                <flux:menu class="w-[300px] bg-white/90 dark:bg-zinc-800/90 backdrop-blur-xl border border-white/10 shadow-xl rounded-xl overflow-hidden">
                    <!-- User Profile Section -->
                    <flux:menu.radio.group>
                        <div class="p-3 text-sm font-normal">
                            <div class="flex items-center gap-3 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-gradient-to-br from-[#02c9c2]/20 to-[#012e2b]/20 text-[#02c9c2] dark:from-[#02c9c2]/30 dark:to-[#012e2b]/30">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start leading-tight">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                            
                            <!-- User status indicator -->
                            <div class="mt-2 px-1.5 py-1 text-xs bg-[#02c9c2]/10 text-[#02c9c2] rounded-lg flex items-center">
                                <span class="h-2 w-2 rounded-full bg-[#02c9c2] mr-2"></span>
                                Online
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-white/10" />

                    <!-- Quick Access Section -->
                    <flux:menu.radio.group>
                        <flux:menu.item 
                            :href="route('admin.dashboard')" 
                            icon="home" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('Dashboard') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('admin.properties.index')" 
                            icon="building-office-2" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('All Properties') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('admin.tenants.index')" 
                            icon="users" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('Manage Tenants') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('admin.blog.index')" 
                            icon="newspaper" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('Blog Management') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('admin.roles.index')" 
                            icon="shield-check" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('Roles & Permissions') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('admin.users.index')" 
                            icon="user" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('User Management') }}
                        </flux:menu.item>

                        <flux:menu.item 
                            :href="route('management.maintenance')" 
                            icon="wrench-screwdriver" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            <span class="flex items-center justify-between w-full">
                                {{ __('Maintenance Requests') }}
                            </span>
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-white/10" />

                    <!-- Settings & Support -->
                    <flux:menu.radio.group>
                        <flux:menu.item 
                            :href="route('settings.profile')" 
                            icon="cog" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                            wire:navigate
                        >
                            {{ __('Settings') }}
                        </flux:menu.item>
                        
                        <flux:menu.item 
                            href="#" 
                            icon="question-mark-circle" 
                            class="hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                        >
                            {{ __('Help & Support') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator class="border-white/10" />

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item 
                            as="button" 
                            type="submit" 
                            icon="arrow-right-start-on-rectangle" 
                            class="w-full hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-colors duration-200"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Content area with decorative elements -->
        {{ $slot }}

        <!-- Decorative elements similar to schedule.blade.php -->
        <div class="fixed top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
        <div class="fixed bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>


        @fluxScripts
    </body>
</html>
