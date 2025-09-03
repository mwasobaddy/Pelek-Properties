<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="min-h-screen w-full flex items-center justify-center animate-fade-in px-4 sm:px-6 lg:px-8 flex-col gap-6">
    
    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
        <span class="flex mb-1 items-center justify-center rounded-md">
            <x-app-logo-icon2 class="size-9 fill-current text-black dark:text-white" />
        </span>
        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
    </a>

    <!-- Left Section -->
    <div class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <div class="hidden lg:block">
            <h1 class="text-4xl font-bold mb-3 text-gray-900 dark:text-white">PelekProperties</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Experience luxury living with our curated collection of premium properties, expertly managed for your comfort and investment success</p>
            
            <!-- 3D Element Placeholder -->
            <div class="relative h-80 bg-gradient-to-br from-[#02c9c2]/10 to-[#012e2b]/5 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-grid-pattern opacity-10">
                    <img src="{{ asset('images/placeholder.webp') }}" alt="3D Element" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105" />
                </div>
            </div>
        </div>

        <!-- Right Section - Login Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome to PelekProperties</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">Log in to your account to continue</p>
                </div>

                <form wire:submit="login" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="email">
                            Email address
                        </label>
                        <input 
                            wire:model="email"
                            type="email"
                            id="email"
                            placeholder="hello@hytypo.studio"
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

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input wire:model="remember" type="checkbox" id="remember" class="rounded border-gray-300 text-[#02c9c2] focus:ring-[#02c9c2]">
                            <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-300">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-[#02c9c2] hover:text-[#028e89]">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-[#02c9c2] text-white py-3 rounded-xl font-medium hover:bg-[#028e89] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75"
                    >
                        <span wire:loading.remove>
                            Sign in
                        </span>
                        <span wire:loading class="inline-flex items-center">
                            <flux:icon name="arrow-path" class="w-5 h-5 animate-spin mr-2" />
                            Processing...
                        </span>
                    </button>

                    <!-- Social Login -->
                    {{-- <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500">or</span>
                        </div>
                    </div>

                    <button type="button" class="w-full flex items-center justify-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all">
                        <flux:icon name="globe-alt" class="w-5 h-5" />
                        Sign up with Google
                    </button>

                    <!-- Sign Up Link -->
                    @if (Route::has('register'))
                        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
                            Don't have an account? 
                            <a href="{{ route('register') }}" wire:navigate class="text-[#02c9c2] hover:text-[#028e89] font-medium">
                                Sign up
                            </a>
                        </p>
                    @endif --}}
                </div>
            </div>

            <!-- Join Stats -->
            <div class="mt-8 flex items-center gap-2 justify-center">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">With 500+ Properties Managed!</span>
            </div>
        </div>
    </div>
</div>
