# SEO Implementation Guide for Pelek Properties

This guide outlines how to use the SEO components and services in the Pelek Properties project.

## Architecture Overview

The SEO implementation follows these key components:

1. **Configuration Files**:
   - `config/seotools.php` - General SEO configuration
   - `config/seo-content.php` - Content-specific SEO data

2. **Service Class**:
   - `app/Services/SEOService.php` - Main service for handling SEO operations

3. **Components**:
   - `resources/views/livewire/components/seo-meta.blade.php` - Livewire Volt component for page-level SEO
   - `resources/views/livewire/components/structured-data.blade.php` - Component for additional structured data

## How to Use

### Basic Page SEO

In any Livewire/Volt page, include the SEO Meta component:

```php
<livewire:components.seo-meta 
    title="Page Title"
    description="Page description for SEO"
    :keywords="['keyword1', 'keyword2']"
    :canonicalUrl="route('current.route')"
    :image="asset('images/featured.jpg')"
    type="WebPage"
/>
```

### Using the SEO Service

In Livewire components, inject the SEO service in the mount method:

```php
use App\Services\SEOService;

public function mount(SEOService $seoService, $property)
{
    // Set property-specific SEO
    $seoService->setPropertyMeta($property);
    
    // Load property data
    $this->property = $property;
}
```

### Predefined Page Types

The SEO service includes methods for common page types:

- `setHomeMeta()` - Homepage
- `setPropertyTypeMeta($type)` - Property listing pages (airbnb, rental, commercial, sale)
- `setServiceMeta($service)` - Service pages
- `setPropertyMeta($property)` - Individual property pages
- `setBlogPostMeta($post)` - Blog post pages
- `setBlogIndexMeta()` - Blog index/category pages
- `setAboutPageMeta()` - About page
- `setContactPageMeta()` - Contact page
- `setLegalPageMeta($page)` - Legal pages (privacy, terms, cookies)
- `setLocationMeta($city, $area, $propertyType)` - Location-specific pages

### Adding Custom Structured Data

For advanced structured data needs, use the structured-data component:

```php
<livewire:components.structured-data
    type="Organization"
    :data="[
        'name' => 'Pelek Properties',
        'url' => url('/'),
        'logo' => asset('images/logo.png'),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+254711614099',
            'contactType' => 'customer service'
        ]
    ]"
/>
```

### Best Practices

1. **Canonical URLs**: Always set canonical URLs to prevent duplicate content
2. **Meaningful Titles**: Use descriptive titles with location and brand
3. **Structured Data**: Use appropriate schema types for each page
4. **Image Optimization**: Provide high-quality images with proper dimensions
5. **Keywords**: Use researched keywords relevant to the Kenyan real estate market

## Schema Types by Page

| Page Type | Schema Type |
|-----------|-------------|
| Home | RealEstateAgent |
| About | RealEstateAgent |
| Contact | ContactPage |
| Blog Index | Blog |
| Blog Post | BlogPosting |
| Property Sale | RealEstateListing |
| Property Rental | Apartment |
| Property Airbnb | LodgingBusiness |
| Property Commercial | CommercialProperty |
| Services | Service |

## Testing SEO Implementation

Use these tools to validate the SEO implementation:

1. [Google Rich Results Test](https://search.google.com/test/rich-results)
2. [Schema.org Validator](https://validator.schema.org/)
3. [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
4. [Twitter Card Validator](https://cards-dev.twitter.com/validator)

## Extending SEO Functionality

To add new SEO features:

1. Add configuration in `config/seo-content.php`
2. Create a new method in `SEOService.php`
3. Use the service in the relevant Livewire component

---

For questions or additional SEO requirements, contact the development team.
