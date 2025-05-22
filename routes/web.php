<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\PropertyBookingForm;

// Public routes
// -------------------------------------

// Home page and basic public routes
Volt::route('/', 'pages.home')->name('home');
Volt::route('/about', 'pages.about')->name('about');
Volt::route('/contact', 'pages.contact')->name('contact');

// Legal pages
Volt::route('/cookie-policy', 'pages.legal.cookie-policy')->name('cookies');
Volt::route('/privacy-policy', 'pages.legal.privacy-policy')->name('privacy');
Volt::route('/terms-of-service', 'pages.legal.terms-of-service')->name('terms');

// Blog routes
Volt::route('/blog', 'pages.blog.index')->name('blog.index');
Volt::route('/blog/{post:slug}', 'pages.blog.show')->name('blog.show');

// Property routes
Route::prefix('properties')->name('properties.')->group(function () {
    // Main property listing route
    Volt::route('/', 'pages.properties.index')->name('index');
    
    // Property type-specific routes
    Volt::route('?propertyListingType=sale', 'pages.properties.index')->name('sale');
    Volt::route('?propertyListingType=rent', 'pages.properties.index')->name('rent');
    Volt::route('?propertyListingType=commercial', 'pages.properties.index')->name('commercial');
    Volt::route('?propertyListingType=airbnb', 'pages.properties.index')->name('airbnb');
    
    // Property search route
    Volt::route('/search', 'pages.properties.index')->name('search');
    
    // Individual property details
    Volt::route('/{property}', 'pages.properties.show')->name('show');

    // Booking form route
    Route::get('/{property}/book', PropertyBookingForm::class)->name('book');
});

// Valuation Services routes
Route::prefix('services')->name('services.')->group(function () {
    Volt::route('/valuation', 'pages.services.valuation.index')->name('valuation');
    Volt::route('/property-management', 'pages.services.property-management.index')->name('management');
});

// Authenticated routes
// -------------------------------------
Route::middleware(['auth', 'verified'])->group(function () {

    // User settings
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Volt::route('/dashboard', 'pages.admin.dashboard')->name('dashboard');
    });
    
    // User settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', 'settings/profile');
        Volt::route('/profile', 'settings.profile')->name('profile');
        Volt::route('/password', 'settings.password')->name('password');
        Volt::route('/appearance', 'settings.appearance')->name('appearance');
    });

    // Management Routes for authenticated users
    Route::prefix('management')->name('management.')->group(function () {
        Volt::route('/contracts', 'pages.admin.management.contracts')->name('contracts');
        Volt::route('/maintenance', 'pages.admin.management.maintenance')->name('maintenance');
        Volt::route('/financials', 'pages.admin.management.financials')->name('financials');
    });

    // Admin routes
    // -------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        // Blog management (admin role required)
        Volt::route('/blog', 'pages.admin.blog.index')
            ->name('blog.index');
            
        // Analytics routes
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Volt::route('/', 'pages.admin.analytics.dashboard')->name('dashboard');
            Volt::route('/occupancy-reports', 'pages.admin.analytics.occupancy-reports')->name('occupancy-reports');
        });
        
        // Audit logs
        Volt::route('/audit-logs', 'pages.admin.audit-logs')->name('audit-logs');
        
        // Reports routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Volt::route('/', 'pages.admin.reports.index')->name('index');
        });
        
        // Property management
        Route::prefix('properties')->name('properties.')->group(function () {
            Volt::route('/', 'pages.admin.properties.index')->name('index');
            Volt::route('/manage', 'pages.admin.properties.manage')->name('manage');
            Volt::route('/financials', 'pages.admin.properties.financial-report')->name('financials');
            Volt::route('/{property}/photos', 'pages.admin.properties.photos')->name('photos');
            Volt::route('/commercial', 'pages.admin.properties.commercial')->name('commercial');
            Volt::route('/sales', 'pages.admin.properties.sales')->name('sales');
            Volt::route('/offers', 'pages.admin.properties.offers')->name('offers');
            Volt::route('/developments', 'pages.admin.properties.developments')->name('developments');
        });
        
        // Management section
        Route::prefix('management')->name('management.')->group(function () {
            Volt::route('/contracts', 'pages.admin.management.contracts')->name('contracts');
            Volt::route('/valuations', 'pages.admin.management.valuations')->name('valuations');
            Volt::route('/valuations/create', 'pages.admin.management.valuation-create')->name('valuations.create');
            Volt::route('/valuations/{valuation}', 'pages.admin.management.valuation-show')->name('valuations.show');
            Volt::route('/market-analysis', 'pages.admin.management.market-analysis-coming-soon')->name('market-analysis');
        });
        
        // Booking management
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Volt::route('/', 'pages.admin.bookings.index')->name('index');
        });
        
        // Schedule management
        Route::prefix('schedule')->name('schedule.')->group(function () {
            Volt::route('/', 'pages.admin.schedule.schedule')->name('index');
            Volt::route('/appointments', 'pages.admin.schedule.appointments')->name('appointments');
        });
        
        // Tenant management
        Route::prefix('tenants')->name('tenants.')->group(function () {
            Volt::route('/', 'pages.admin.tenants.index')->name('index');
            Volt::route('/contracts', 'pages.admin.tenants.contracts')->name('contracts');
        });
        
        // Document management
        Route::prefix('documents')->name('documents.')->group(function () {
            Volt::route('/', 'pages.admin.documents.index')->name('index');
        });
    });
});

require __DIR__.'/auth.php';