<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Home page route using Volt component
Volt::route('/', 'pages.home')->name('home');

// Property routes
Route::prefix('properties')->group(function () {
    // Main property listing route
    Volt::route('/', 'pages.properties.index')
        ->name('properties.index');
    
    // Property type-specific routes
    Volt::route('/sale', 'pages.properties.index', ['type' => 'sale'])
        ->name('properties.sale');
    
    Volt::route('/rent', 'pages.properties.rental')
        ->name('properties.rent');
    
    Volt::route('/commercial', 'pages.properties.commercial')
        ->name('properties.commercial');

    Volt::route('/airbnb', 'pages.properties.index', ['type' => 'airbnb'])
        ->name('properties.airbnb');
    
    // Property search route
    Volt::route('/search', 'pages.properties.index')
        ->name('properties.search');
    
    // Individual property details
    Volt::route('/{property}', 'pages.properties.show')
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
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        // Property management
        Route::prefix('properties')->name('properties.')->group(function () {
            // Property listing and management
            Volt::route('/', 'pages.admin.properties.index')
                ->name('index');
            
            Volt::route('/manage', 'pages.admin.properties.manage')
                ->name('manage');
            
            // Financial reporting
            Volt::route('/financials', 'pages.admin.properties.financial-report')
                ->name('financials');
                
            // Photos management
            Volt::route('/{property}/photos', 'pages.admin.properties.photos')
                ->name('photos');

            // Commercial properties management
            Volt::route('/commercial', 'pages.admin.properties.commercial')
                ->name('commercial');
        });

        // Booking management
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Volt::route('/', 'pages.admin.bookings.index')
                ->name('index');
        });
    });
});

require __DIR__.'/auth.php';
