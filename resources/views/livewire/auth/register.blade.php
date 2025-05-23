<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen w-full flex items-center justify-center animate-fade-in px-4 sm:px-6 lg:px-8 flex-col gap-6"
    x-data="{ processing: false }"
    x-on:submit="processing = true"
    x-on:error.window="processing = false">
    
    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
        <span class="flex mb-1 items-center justify-center rounded-md">
            <x-app-logo-icon2 class="size-9 fill-current text-black dark:text-white" />
        </span>
        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
    </a>

    <!-- Main Grid Layout -->
    <div class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <!-- Left Section -->
        <div class="hidden lg:block">
            <h1 class="text-4xl font-bold mb-3 text-gray-900 dark:text-white">Join PelekProperties</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Create your account to start exploring our premium properties and unlock exclusive investment opportunities.</p>
            
            <!-- 3D Element Placeholder -->
            <div class="relative h-80 bg-gradient-to-br from-[#02c9c2]/10 to-[#012e2b]/5 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-grid-pattern opacity-10">
                    <img src="{{ asset('images/placeholder.webp') }}" alt="3D Element" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105" />
                </div>
            </div>
        </div>

        <!-- Right Section - Registration Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create your account</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">Fill in your details to get started</p>
                </div>

                <form wire:submit="register" class="space-y-6">
                    <!-- Name Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="name">
                            Full Name
                        </label>
                        <input 
                            wire:model="name"
                            type="text"
                            id="name"
                            placeholder="John Doe"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="email">
                            Email address
                        </label>
                        <input 
                            wire:model="email"
                            type="email"
                            id="email"
                            placeholder="hello@example.com"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="password">
                            Password
                        </label>
                        <input 
                            wire:model="password"
                            type="password"
                            id="password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="password_confirmation">
                            Confirm Password
                        </label>
                        <input 
                            wire:model="password_confirmation"
                            type="password"
                            id="password_confirmation"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                        >
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-[#02c9c2] text-white py-3 rounded-xl font-medium hover:bg-[#028e89] transition-all duration-200"
                        :disabled="processing"
                    >
                        <span x-show="processing" class="inline-flex items-center">
                            <flux:icon name="arrow-path" class="w-5 h-5 animate-spin mr-2" />
                            Processing...
                        </span>
                        <span x-show="!processing">
                            Create Account
                        </span>
                    </button>

                    <!-- Login Link -->
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                        Already have an account? 
                        <a href="{{ route('login') }}" wire:navigate class="text-[#02c9c2] hover:text-[#028e89] font-medium">
                            Sign in
                        </a>
                    </p>
                </form>
            </div>

            <!-- Join Stats -->
            <div class="mt-8 flex items-center gap-2 justify-center">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Join our growing community!</span>
            </div>
        </div>
    </div>
</div>
