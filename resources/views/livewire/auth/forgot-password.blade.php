<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
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

    <!-- Main Content Grid -->
    <div class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <!-- Left Section -->
        <div class="hidden lg:block">
            <h1 class="text-4xl font-bold mb-3 text-gray-900 dark:text-white">Reset Password</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Don't worry! It happens. Please enter the email address associated with your account.</p>
            
            <!-- 3D Element Placeholder -->
            <div class="relative h-80 bg-gradient-to-br from-[#02c9c2]/10 to-[#012e2b]/5 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-grid-pattern opacity-10">
                    <img src="{{ asset('images/placeholder.webp') }}" alt="3D Element" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105" />
                </div>
            </div>
        </div>

        <!-- Right Section - Reset Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Forgot Password</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">Enter your email to receive a reset link</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded-xl">
                        {{ session('status') }}
                    </div>
                @endif

                <form wire:submit="sendPasswordResetLink" class="space-y-6">
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
                            autofocus
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
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
                            Send Reset Link
                        </span>
                    </button>

                    <!-- Back to Login -->
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                        Remember your password? 
                        <a href="{{ route('login') }}" wire:navigate class="text-[#02c9c2] hover:text-[#028e89] font-medium">
                            Back to login
                        </a>
                    </p>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="mt-8 flex items-center gap-2 justify-center">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Trusted by 500+ Property Owners</span>
            </div>
        </div>
    </div>
</div>
