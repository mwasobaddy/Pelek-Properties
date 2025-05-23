<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="min-h-screen w-full flex items-center justify-center animate-fade-in px-4 sm:px-6 lg:px-8 flex-col gap-6">
    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
        <span class="flex mb-1 items-center justify-center rounded-md">
            <x-app-logo-icon2 class="size-9 fill-current text-black dark:text-white" />
        </span>
        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
    </a>

    <div class="max-w-6xl w-full mx-auto grid lg:grid-cols-2 gap-8 items-center">
        <!-- Left Section -->
        <div class="hidden lg:block">
            <h1 class="text-4xl font-bold mb-3 text-gray-900 dark:text-white">Email Verification</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-8">Secure your account and access all features by verifying your email address</p>
            
            <!-- 3D Element Placeholder -->
            <div class="relative h-80 bg-gradient-to-br from-[#02c9c2]/10 to-[#012e2b]/5 rounded-2xl overflow-hidden">
                <div class="absolute inset-0 bg-grid-pattern opacity-10">
                    <img src="{{ asset('images/placeholder.webp') }}" alt="3D Element" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105" />
                </div>
            </div>
        </div>

        <!-- Right Section - Verification Form -->
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl lg:shadow-xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Your Email</h2>
                    <p class="mt-4 text-gray-600 dark:text-gray-300">
                        {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 rounded-xl">
                        <p class="text-sm text-green-600 dark:text-green-400 text-center">
                            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                        </p>
                    </div>
                @endif

                <div class="flex flex-col gap-4">
                    <button 
                        wire:click="sendVerification"
                        class="w-full bg-[#02c9c2] text-white py-3 rounded-xl font-medium hover:bg-[#028e89] transition-all duration-200"
                    >
                        {{ __('Resend verification email') }}
                    </button>

                    <button 
                        wire:click="logout"
                        class="text-sm text-[#02c9c2] hover:text-[#028e89] text-center"
                    >
                        {{ __('Log out') }}
                    </button>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="mt-8 flex items-center gap-2 justify-center">
                <div class="flex -space-x-2">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="w-8 h-8 rounded-full bg-gray-400 dark:bg-gray-500"></div>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Join our verified users!</span>
            </div>
        </div>
    </div>
</div>
