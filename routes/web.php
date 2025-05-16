<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\PropertyBookingForm;

// Home page route using Volt component
Volt::route('/', 'pages.home')->name('home');
Volt::route('/about', 'pages.about')->name('about');
Volt::route('/contact', 'pages.contact')->name('contact');
Volt::route('/cookie-policy', 'pages.legal.cookie-policy')->name('cookies');
Volt::route('/privacy-policy', 'pages.legal.privacy-policy')->name('privacy');
Volt::route('/terms-of-service', 'pages.legal.terms-of-service')->name('terms');
Volt::route('/blog', 'pages.blog.index')->name('blog.index');
Volt::route('/blog/{post:slug}', 'pages.blog.show')->name('blog.show');

// Property routes
Route::prefix('properties')->group(function () {
    // Main property listing route
    Volt::route('/', 'pages.properties.index')
        ->name('properties.index');
    
    // Property type-specific routes
    Volt::route('?propertyListingType=sale', 'pages.properties.index')
        ->name('properties.sale');

    Volt::route('?propertyListingType=rent', 'pages.properties.index')
        ->name('properties.rent');

    Volt::route('?propertyListingType=commercial', 'pages.properties.index')
        ->name('properties.commercial');

    Volt::route('?propertyListingType=airbnb', 'pages.properties.index')
        ->name('properties.airbnb');
    
    // Property search route
    Volt::route('/search', 'pages.properties.index')
        ->name('properties.search');
    
    // Individual property details
    Volt::route('/{property}', 'pages.properties.show')
        ->name('properties.show');

    // Booking form route
    Route::get('/{property}/book', PropertyBookingForm::class)
        ->name('properties.book');
});

// Valuation Services routes
Route::prefix('services')->name('services.')->group(function () {
    Volt::route('/valuation', 'pages.services.valuation.index')
        ->name('valuation');
    Volt::route('/property-management', 'pages.services.property-management.index')
        ->name('management');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    

    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        // Blog management
        Volt::route('/blog', 'pages.admin.blog.index')
            ->name('blog.index')
            ->middleware(['role:admin']);

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
