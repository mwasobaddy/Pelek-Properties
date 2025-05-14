<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Home page route using Volt component
Volt::route('/', 'pages.home')->name('home');

// Property routes
Route::prefix('properties')->group(function () {
    Volt::route('/', 'pages.propertylist')
        ->name('properties.index');
    
    Volt::route('/search', 'pages.property-search')
        ->name('properties.search');
        
    Volt::route('/{property}', 'pages.property-details')
        ->name('properties.show');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Property management
        Route::prefix('properties')->name('properties.')->group(function () {
            Volt::route('{property}/airbnb-photos', 'airbnb-image-upload-volt')
                ->name('airbnb-photos');
        });
    });
});

require __DIR__.'/auth.php';
