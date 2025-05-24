# Pelek Properties - Laravel Best Practices

## Tech Stack
- **Laravel 12.14.1** (with built-in Livewire/Volt #, Flux, Tailwind)
- **Spatie Permissions** (v6) for role management
- **SweetAlert2** for notifications
- **MySQL** database

## Architecture Principles

### 1. Service Layer Pattern
// Example service structure
app/
└── Services/
    ├── PropertyService.php
    ├── BookingService.php
    └── UserService.php


## Implementation Guidelines:
- Each service handles business logic for a specific domain
- Services are called from Livewire components
- Methods should be single-responsibility

Use dependency injection

php
class PropertyService
{
    public function getFeaturedListings(string $type): Collection
    {
        return Property::where('type', $type)
            ->where('is_featured', true)
            ->latest()
            ->get();
    }
}


### 2. Livewire/Volt Components
<?php
 
use Livewire\Volt\Component;
 
new class extends Component {
    public $count = 0;
 
    public function increment()
    {
        $this->count++;
    }
} ?>
 
<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>

## Best Practices:
- Keep components small and focused
- Use wire:model.live for instant search
- Delegate complex logic to services
- Use Flux components where possible
- Implement pagination for large datasets
- Use Livewire events for inter-component communication
- Use Volt for reusable components

### 3. Database Migrations
php
// Migration for properties table
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->string('location');
    $table->boolean('is_featured')->default(false);
    $table->timestamps();
});

### 4. Eloquent Models
php
// Property model
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'is_featured'
    ];

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}

### 5. Database Seeders
php
// DatabaseSeeder.php
use Illuminate\Database\Seeder;
use App\Models\Property;
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Property::factory()->count(50)->create();
    }
}

### 6. Factory Classes
php
// PropertyFactory.php
namespace Database\Factories;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 100000, 900000),
            'location' => $this->faker->city(),
            'is_featured' => $this->faker->boolean(),
        ];
    }
}

### 7. Validation Rules
php
// PropertyRequest.php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'is_featured' => 'required|boolean',
        ];
    }
}

### 8. Role Management (Spatie)
## Role and Permission Management
- Use Spatie's package for role and permission management
- Define roles and permissions in database seeder
- Assign roles to users
- Use @can directive in Blade templates

## Example Roles and Permissions
### Roles:
- Admin
- Property Manager
- Content Editor
### Permissions:
- manage_properties
- manage_blog
- manage_users
### Example Role and Permission Assignment
php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
// Create roles
Role::create(['name' => 'admin']);
Role::create(['name' => 'property_manager']);
Role::create(['name' => 'content_editor']);
// Create permissions
Permission::create(['name' => 'manage_properties']);
Permission::create(['name' => 'manage_blog']);
Permission::create(['name' => 'manage_users']);
// Assign permissions to roles
$adminRole = Role::findByName('admin');
$adminRole->givePermissionTo(['manage_properties', 'manage_blog', 'manage_users']);
$propertyManagerRole = Role::findByName('property_manager');
$propertyManagerRole->givePermissionTo(['manage_properties']);
$contentEditorRole = Role::findByName('content_editor');
$contentEditorRole->givePermissionTo(['manage_blog']);
// Assign roles to users
$user = User::find(1);
$user->assignRole('admin');
// Assigning permissions to users
$user->givePermissionTo('manage_properties');
// Checking permissions in Blade
@can('manage_properties')
    <button>Edit Property</button>
@endcan
### Example Blade Template

Implementation:

Define roles in database seeder:
- Admin
- Property Manager
- Content Editor

Create permissions:
- manage_properties
- manage_blog
- manage_users

### 9. UI/UX Implementation
## Flux Components:

html
<flux:button icon="home" wire:click="search">
    Find Properties
</flux:button>

Tailwind Best Practices:
- Use @apply for reusable styles
- Create component classes in resources/css/app.css
- Use Flux components first, customize with Tailwind when needed

### 10. Notification System
## SweetAlert2 Integration:

javascript
// In app.js
window.showSuccess = (message) => {
    Swal.fire({
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
};
Calling from Livewire:

php
$this->dispatch('notify', 
    type: 'success',
    message: 'Property saved successfully'
);

### 11. Testing Strategy
## Feature Tests:
- Feature Tests for services
- Livewire Tests for components
- Pest.php for unit tests

php
test('property search returns correct results', function () {
    $service = new PropertyService();
    $results = $service->searchProperties('Nairobi');
    expect($results)->toHaveCount(5);
});

### 12. Folder structure
## Follow PSR-4 standards
## Example structure as provided by Laravel 12x insatllation:
app/
├── Services/
├── Models/
├── View/
│   └── Components/
resources/
├── views/
│   ├── components/ # Reusable Livewire components
│   ├── flux/ # customized Flux components
│   ├── livewire/ # Livewire pages and components
│   ├── partials/ # Reusable blade partials
│   ├── dashboard.blade.php/ # Authenticated dashboard
│   ├── welcome.blade.php/ # Guest user welcome page
│   └── layouts/
├── css/
└── js/


### 13. Performance Optimizations
- Use Laravel caching for property listings
- Implement lazy loading for images
- Use pagination for large datasets
- Optimize Livewire renders with wire:key

### 14. Security Practices
- Validate all Livewire inputs
- Use Spatie's permission checks
- Sanitize search inputs


## 15. Error Handling Strategy**
    - Custom exception handling
    - Standardized error responses
    - Logging practices

## 16. Environment & Configuration
    - Environment-specific settings
    - Configuration management best practices

## 17. Asset Management
    - Media/file upload handling
    - Storage configuration (S3/cloud vs local)

## 18. Advanced State Management
    - Complex Livewire component interactions
    - Loading states
    - Event-driven architecture details

## 19. Database Optimization
    - Query optimization guidelines
    - Indexing strategies
    - Eloquent relationship best practices

## 20. Queue Management
    - Background job processing
    - Failed job handling

## 21. Authentication Enhancements
- Laravel provides two optional packages to assist you in managing API tokens
and authenticating requests made with API tokens: Passport and Sanctum. 
Please note that these libraries and Laravel's built-in cookie based authentication 
libraries are not mutually exclusive. These libraries primarily focus on API token 
authentication while the built-in authentication services focus on cookie based browser 
authentication. Many applications will use both Laravel's built-in cookie based 
authentication services and one of Laravel's API authentication packages.

## 22. Development Workflow
    - Git branching strategy
    - Code review process
    - Documentation standards

## 23. Things to note
- All blade must support both light and dark mode
- Laravel Support these layouts. I would love to use the unless otherwise #fetch https://laravel.com/docs/12.x/starter-kits#livewire-available-layouts (No need to update the app.blade since it supports dark mode and uses flux). By Dark and light mode I meant that the components that you will create, must have the class for light and dark mode
- Also note that volt components must be created inside the file "resources/views/livewire"

I see what's happening now. In Laravel 12.14.1 with Livewire 3, there have been significant changes to how computed properties work in Volt components. Let me explain the fix:
The Issue
The error "Undefined variable $priceLabel" occurs because in newer versions of Livewire Volt, computed properties need to be defined differently. The previous computed() function approach is no longer working.
The Solution
I've updated the component to define computed properties directly inside the state() function as arrow functions:
phpstate([
    'property' => null,
    'debug' => '',
    // Add computed properties directly into state as arrow functions
    'formattedPrice' => fn() => number_format(...),
    'priceLabel' => fn() => match(...),
]);
This matches the latest Livewire Volt syntax where computed values are defined directly in the state array.

## Code Examples and Best Practices

### Image Handling
When working with property images, follow these patterns:

```php
// Accessing featured image URL in Livewire components
public function getImageUrl()
{
    $image = $this->property->featuredImage;
    return $image 
        ? Storage::disk('public')->url($image->image_path)
        : asset('images/placeholder.webp');
}

// Handling property images collection
public function getPropertyImages()
{
    return $this->property->listing_type === 'airbnb' 
        ? $this->property->images()->orderBy('display_order')->get()
        : collect([$this->property->featuredImage])->filter();
}
```

### Best Practices
1. Always use Storage::disk('public')->url() for generating image URLs
2. Provide fallback placeholder images
3. Filter null values from image collections
4. Use proper image relationships based on property type
5. Handle image ordering for Airbnb listings