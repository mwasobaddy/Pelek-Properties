<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Home page route using Volt component
Volt::route('/', 'pages.home')->name('home');

// Property routes
Route::prefix('properties')->group(function () {
    // Main property listing route
    Volt::route('/', 'pages.propertylist')
        ->name('properties.index');
    
    // Property type-specific routes
    Volt::route('/sale', 'pages.propertylist', ['type' => 'sale'])
        ->name('properties.sale');
    
    Volt::route('/rent', 'pages.rental-properties')
        ->name('properties.rent');
    
    Volt::route('/commercial', 'pages.commercial-properties')
        ->name('properties.commercial');

    Volt::route('/airbnb', 'pages.propertylist', ['type' => 'airbnb'])
        ->name('properties.airbnb');
    
    // Property search route
    Volt::route('/search', 'pages.property-search')
        ->name('properties.search');
    
    // Individual property details
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
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        // Property management
        Route::prefix('properties')->name('properties.')->group(function () {
            // Property listing and management
            Volt::route('/', 'admin.property-list')
                ->name('index');
            
            Volt::route('/manage', 'admin.manage-rental-properties')
                ->name('manage');
            
            // Financial reporting
            Volt::route('/financials', 'admin.property-financial-report')
                ->name('financials');
                
            // Photos management
            Volt::route('/{property}/photos', 'admin.property-photos')
                ->name('photos');
        });

        // Booking management
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Volt::route('/', 'admin.manage-bookings')
                ->name('index');
        });
    });
});

require __DIR__.'/auth.php';
