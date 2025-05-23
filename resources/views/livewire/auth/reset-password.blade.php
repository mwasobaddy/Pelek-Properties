<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;

        $this->email = request()->string('email');
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status != Password::PasswordReset) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
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

    <div class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <!-- Left Section -->
        <div class="hidden lg:block">
            <h1 class="text-4xl font-bold mb-3 text-gray-900 dark:text-white">Reset Your Password</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Create a new secure password for your PelekProperties account to regain access to your property management dashboard</p>
            
            <div class="relative h-80 bg-gradient-to-br from-[#02c9c2]/10 to-[#012e2b]/5 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-grid-pattern opacity-10">
                    <img src="{{ asset('images/placeholder.webp') }}" alt="Reset Password" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105" />
                </div>
            </div>
        </div>

        <!-- Right Section - Reset Password Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-300">Please enter your new password below</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 text-sm text-[#02c9c2] text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <form wire:submit="resetPassword" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="email">
                            Email address
                        </label>
                        <input 
                            wire:model="email"
                            type="email"
                            id="email"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                            autocomplete="email"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="password">
                            New Password
                        </label>
                        <input 
                            wire:model="password"
                            type="password"
                            id="password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                            autocomplete="new-password"
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
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent transition-all"
                            required
                            autocomplete="new-password"
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
                            Reset Password
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
