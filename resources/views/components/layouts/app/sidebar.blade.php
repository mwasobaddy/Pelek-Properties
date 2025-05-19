<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
        <flux:sidebar sticky stashable class="border-e border-white/10 bg-white/8 dark:bg-zinc-900/50 backdrop-blur-xl shadow-xl">
            <flux:sidebar.toggle class="lg:hidden hover:text-[#02c9c2] transition-colors duration-200" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse transform transition-all duration-300 hover:scale-105" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline" class="mt-6">
                    <flux:navlist.group :heading="__('platform')" class="grid gap-1">
                        <flux:navlist.item 
                            icon="home" 
                            :href="route('dashboard')" 
                            :current="request()->routeIs('dashboard')" 
                            class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                            wire:navigate
                        >
                            {{ __('dashboard') }}
                        </flux:navlist.item>

                        <!-- Property Management Section -->
                        <flux:navlist.group :heading="__('property_management')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="building-office-2" 
                                :href="route('management.contracts')" 
                                :current="request()->routeIs('management.contracts')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('management_contracts') }}
                            </flux:navlist.item>
                            <flux:navlist.item 
                                icon="wrench-screwdriver" 
                                :href="route('management.maintenance')" 
                                :current="request()->routeIs('management.maintenance')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('maintenance_requests') }}
                            </flux:navlist.item>
                            <flux:navlist.item 
                                icon="chart-bar" 
                                :href="route('management.financials')" 
                                :current="request()->routeIs('management.financials')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('financial_records') }}
                            </flux:navlist.item>

                            <!-- Adding Valuation Section -->
                            <flux:navlist.item 
                                icon="calculator" 
                                :href="route('admin.management.valuations')" 
                                :current="request()->routeIs('admin.management.valuations')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('property_valuations') }}
                            </flux:navlist.item>

                            <!-- Adding Market Analysis -->
                            <flux:navlist.item 
                                icon="chart-bar-square" 
                                :href="route('admin.management.market-analysis')" 
                                :current="request()->routeIs('admin.management.market-analysis')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('market_analysis') }}
                            </flux:navlist.item>
                        </flux:navlist.group>

                        <!-- Viewing Management Section -->
                        <flux:navlist.group :heading="__('viewing_management')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="calendar" 
                                :href="route('admin.schedule.index')" 
                                :current="request()->routeIs('admin.schedule.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('viewing_schedule') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="calendar-days" 
                                :href="route('admin.schedule.appointments')" 
                                :current="request()->routeIs('admin.schedule.appointments')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('manage_appointments') }}
                            </flux:navlist.item>
                        </flux:navlist.group>

                        <!-- Tenant Management Section -->
                        <flux:navlist.group :heading="__('tenant_management')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="users" 
                                :href="route('admin.tenants.index')" 
                                :current="request()->routeIs('admin.tenants.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('manage_tenants') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="document-check" 
                                :href="route('admin.tenants.contracts')" 
                                :current="request()->routeIs('admin.tenants.contracts')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('tenant_contracts') }}
                            </flux:navlist.item>
                        </flux:navlist.group>

                        <!-- Properties Administration -->
                        <flux:navlist.group :heading="__('properties_admin')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="building-office-2" 
                                :href="route('admin.properties.index')" 
                                :current="request()->routeIs('admin.properties.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('all_properties') }}
                            </flux:navlist.item>
                            <flux:navlist.item 
                                icon="cog" 
                                :href="route('admin.properties.manage')" 
                                :current="request()->routeIs('admin.properties.manage')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('manage_properties') }}
                            </flux:navlist.item>
                            <flux:navlist.item 
                                icon="photo" 
                                :href="route('admin.properties.photos', ['property' => 1])" 
                                :current="request()->routeIs('admin.properties.photos')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('property_photos') }}
                            </flux:navlist.item>
                            <flux:navlist.item 
                                icon="building-office" 
                                :href="route('admin.properties.commercial')" 
                                :current="request()->routeIs('admin.properties.commercial')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('commercial_properties') }}
                            </flux:navlist.item>

                            <!-- Property Offers -->
                            <flux:navlist.item 
                                icon="currency-dollar" 
                                :href="route('admin.properties.offers')" 
                                :current="request()->routeIs('admin.properties.offers')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('property_offers') }}
                            </flux:navlist.item>

                            <!-- Documents -->
                            <flux:navlist.item 
                                icon="document-duplicate" 
                                :href="route('admin.documents.index')" 
                                :current="request()->routeIs('admin.documents.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('document_management') }}
                            </flux:navlist.item>

                            <!-- Development Projects -->
                            <flux:navlist.item 
                                icon="building-library" 
                                :href="route('admin.properties.developments')" 
                                :current="request()->routeIs('admin.properties.developments')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('development_projects') }}
                            </flux:navlist.item>

                            <!-- Property Sales -->
                            <flux:navlist.item 
                                icon="banknotes" 
                                :href="route('admin.properties.sales')" 
                                :current="request()->routeIs('admin.properties.sales')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('property_sales') }}
                            </flux:navlist.item>
                        </flux:navlist.group>

                        <!-- Analytics Section -->
                        <flux:navlist.group :heading="__('analytics')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="chart-pie" 
                                :href="route('admin.analytics.dashboard')" 
                                :current="request()->routeIs('admin.analytics.dashboard')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('analytics_dashboard') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="document-chart-bar" 
                                :href="route('admin.reports.index')" 
                                :current="request()->routeIs('admin.reports.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('reports_exports') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="chart-bar" 
                                :href="route('admin.analytics.occupancy-reports')" 
                                :current="request()->routeIs('admin.analytics.occupancy-reports')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('occupancy_reports') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="clock" 
                                :href="route('admin.audit-logs')" 
                                :current="request()->routeIs('admin.audit-logs')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('audit_logs') }}
                            </flux:navlist.item>
                        </flux:navlist.group>

                        <!-- Bookings & Content -->
                        <flux:navlist.group :heading="__('other_management')" class="grid gap-1 mt-4">
                            <flux:navlist.item 
                                icon="newspaper" 
                                :href="route('blog.index')" 
                                :current="request()->routeIs('blog.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('blog_management') }}
                            </flux:navlist.item>

                            <flux:navlist.item 
                                icon="wrench-screwdriver" 
                                :href="route('management.maintenance')" 
                                :current="request()->routeIs('management.maintenance')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('maintenance_requests') }}
                            </flux:navlist.item>

                            {{-- Temporarily disabled until service providers feature is implemented --}}
                            {{-- <flux:navlist.item 
                                icon="users" 
                                :href="route('service-providers.index')" 
                                :current="request()->routeIs('service-providers.index')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('service_providers') }}
                            </flux:navlist.item> --}}
                        </flux:navlist.group>

                        <!-- System Settings -->
                        <flux:navlist.group :heading="__('system')" class="grid gap-1 mt-4">
                            {{-- Temporarily disabled until settings feature is implemented --}}
                            {{-- <flux:navlist.item 
                                icon="cog" 
                                :href="route('admin.settings')" 
                                :current="request()->routeIs('admin.settings')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('system_settings') }}
                            </flux:navlist.item> --}}

                            {{-- <flux:navlist.item 
                                icon="users" 
                                :href="route('admin.users')" 
                                :current="request()->routeIs('admin.users')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('user_management') }}
                            </flux:navlist.item> --}}

                            {{-- <flux:navlist.item 
                                icon="key" 
                                :href="route('admin.roles')" 
                                :current="request()->routeIs('admin.roles')" 
                                class="transition-all duration-200 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2]"
                                wire:navigate
                            >
                                {{ __('roles_permissions') }}
                            </flux:navlist.item> --}}
                        </flux:navlist.group>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                    class="rounded-lg bg-white/5 dark:bg-zinc-800/30 backdrop-blur-sm ring-1 ring-white/10 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-all duration-200"
                />

                <flux:menu class="w-[220px] bg-white/80 dark:bg-zinc-800/90 backdrop-blur-xl border border-white/10 shadow-xl">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-[#02c9c2]/10 text-[#02c9c2] dark:bg-[#02c9c2]/20">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</span>
                                </div>
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
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden bg-white/8 dark:bg-zinc-900/50 backdrop-blur-xl border-b border-white/10 shadow-lg">
            <flux:sidebar.toggle class="lg:hidden hover:text-[#02c9c2] transition-colors duration-200" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="rounded-lg bg-white/5 dark:bg-zinc-800/30 backdrop-blur-sm ring-1 ring-white/10 hover:bg-[#02c9c2]/10 hover:text-[#02c9c2] transition-all duration-200"
                />

                <flux:menu class="bg-white/80 dark:bg-zinc-800/90 backdrop-blur-xl border border-white/10 shadow-xl">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-[#02c9c2]/10 text-[#02c9c2] dark:bg-[#02c9c2]/20">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</span>
                                </div>
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
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
