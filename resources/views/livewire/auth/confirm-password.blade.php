<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
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

    <div class="w-full max-w-md mx-auto">
        <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Confirm password') }}</h2>
                <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600 dark:text-green-400 text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="confirmPassword" class="space-y-6">
                <!-- Password Input -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="password">
                        {{ __('Password') }}
                    </label>
                    <input 
                        wire:model="password"
                        type="password"
                        id="password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                        required
                        autocomplete="new-password"
                    >
                    @error('password')
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
                        {{ __('Confirm') }}
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
