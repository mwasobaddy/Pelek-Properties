<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Property;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;
use Illuminate\Support\Facades\Config;

class SEOService
{
    protected array $seoConfig;
    
    public function __construct()
    {
        $this->seoConfig = Config::get('seo-content');
    }

    /**
     * Set SEO meta tags for the homepage
     */
    public function setHomeMeta()
    {
        $config = $this->seoConfig['homepage'];

        SEOMeta::setTitle($config['title']);
        SEOMeta::setDescription($config['description']);
        SEOMeta::addKeyword($config['keywords']);
        
        OpenGraph::setTitle($config['title']);
        OpenGraph::setDescription($config['description']);
        OpenGraph::setUrl(url('/'));
        OpenGraph::addProperty('locale', 'en_KE');
        OpenGraph::addImage(asset('images/logo.png'));
        
        TwitterCard::setTitle($config['title']);
        TwitterCard::setDescription($config['description']);
        TwitterCard::setImage(asset('images/logo.png'));
        
        // Set up LocalBusiness schema for homepage
        JsonLd::setType($config['schema_type']);
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($config['title']);
        JsonLd::setDescription($config['description']);
        JsonLd::addValue('address', [
            '@type' => 'PostalAddress',
            'addressCountry' => 'KE',
            'availableAreas' => collect($this->seoConfig['locations']['cities'])
                ->pluck('name')
                ->join(', ')
        ]);
    }

    /**
     * Set SEO meta tags for property listing pages
     */
    public function setPropertyTypeMeta(string $type)
    {
        $config = $this->seoConfig['property_types'][$type] ?? null;
        if (!$config) return;

        SEOMeta::setTitle($config['title']);
        SEOMeta::setDescription($config['description']);
        SEOMeta::addKeyword($config['keywords']);
        SEOMeta::setCanonical(route('properties.' . $type));
        
        OpenGraph::setTitle($config['title']);
        OpenGraph::setDescription($config['description']);
        OpenGraph::setUrl(route('properties.' . $type));
        OpenGraph::setType('website');
        
        TwitterCard::setTitle($config['title']);
        TwitterCard::setDescription($config['description']);
        
        JsonLd::setType($config['schema_type']);
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($config['title']);
        JsonLd::setDescription($config['description']);
    }

    /**
     * Set SEO meta tags for service pages
     */
    public function setServiceMeta(string $service)
    {
        $config = $this->seoConfig['services'][$service] ?? null;
        if (!$config) return;

        SEOMeta::setTitle($config['title']);
        SEOMeta::setDescription($config['description']);
        SEOMeta::addKeyword($config['keywords']);
        SEOMeta::setCanonical(route('services.' . $service));
        
        OpenGraph::setTitle($config['title']);
        OpenGraph::setDescription($config['description']);
        OpenGraph::setUrl(route('services.' . $service));
        OpenGraph::setType('website');
        
        TwitterCard::setTitle($config['title']);
        TwitterCard::setDescription($config['description']);
        
        JsonLd::setType($config['schema_type']);
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($config['title']);
        JsonLd::setDescription($config['description']);
    }

    /**
     * Set SEO meta tags for individual property pages
     */
    public function setPropertyMeta(Property $property)
    {
        $title = "{$property->title} | {$property->location} | Pelek Properties";
        $description = strip_tags(substr($property->description, 0, 160));

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta('property:price', $property->price, 'property');
        SEOMeta::addMeta('property:location', $property->location, 'property');
        SEOMeta::addMeta('property:type', $property->type, 'property');
        SEOMeta::setCanonical(route('properties.show', $property));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('properties.show', $property));
        OpenGraph::setType('realestate.property');
        OpenGraph::addProperty('price', $property->price);
        OpenGraph::addProperty('currency', 'KES');
        OpenGraph::addProperty('location', $property->location);
        
        if ($property->images->isNotEmpty()) {
            foreach($property->images as $image) {
                OpenGraph::addImage(url($image->url));
            }
        }

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        if ($property->images->isNotEmpty()) {
            TwitterCard::setImage(url($property->images->first()->url));
        }

        // Structured data for property listing
        JsonLd::setType('RealEstateListing');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('url', route('properties.show', $property));
        JsonLd::addValue('price', $property->price);
        JsonLd::addValue('priceCurrency', 'KES');
        JsonLd::addValue('address', [
            '@type' => 'PostalAddress',
            'addressLocality' => $property->location,
            'addressCountry' => 'KE'
        ]);
        if ($property->images->isNotEmpty()) {
            JsonLd::addImage($property->images->map(fn($img) => url($img->url))->toArray());
        }
    }

    /**
     * Set SEO meta tags for blog posts
     */
    public function setBlogPostMeta(BlogPost $post)
    {
        $title = "{$post->title} | Pelek Properties Blog";
        $description = strip_tags(substr($post->content, 0, 160));

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addMeta('article:published_time', $post->published_at->toW3CString(), 'property');
        SEOMeta::addMeta('article:author', $post->author?->name ?? 'Pelek Properties', 'property');
        SEOMeta::setCanonical(route('blog.show', $post));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setType('article');
        OpenGraph::setUrl(route('blog.show', $post));
        if ($post->featured_image) {
            OpenGraph::addImage(url($post->featured_image));
        }

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        if ($post->featured_image) {
            TwitterCard::setImage(url($post->featured_image));
        }

        JsonLd::setType('BlogPosting');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('datePublished', $post->published_at->toW3CString());
        JsonLd::addValue('author', [
            '@type' => 'Person',
            'name' => $post->author?->name ?? 'Pelek Properties'
        ]);
        if ($post->featured_image) {
            JsonLd::addImage(url($post->featured_image));
        }
    }

    /**
     * Set SEO meta tags for the blog index page
     */
    public function setBlogIndexMeta()
    {
        $title = 'Real Estate Blog & Property Insights | Pelek Properties';
        $description = 'Stay informed about Kenya\'s real estate market. Expert property insights, market trends, and investment tips from Pelek Properties.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addKeyword([
            'real estate blog kenya',
            'property insights kenya',
            'real estate market trends',
            'property investment tips',
            'kenya real estate news'
        ]);
        SEOMeta::setCanonical(route('blog.index'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('blog.index'));
        OpenGraph::setType('blog');

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);

        JsonLd::setType('Blog');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('publisher', [
            '@type' => 'Organization',
            'name' => 'Pelek Properties',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/logo.png')
            ]
        ]);
    }

    /**
     * Set SEO meta tags for the about page
     */
    public function setAboutPageMeta()
    {
        $title = 'About Pelek Properties | Leading Real Estate Agency in Kenya';
        $description = 'Founded in 2024, Pelek Properties is transforming the Kenyan real estate landscape through innovation, integrity, and exceptional service. Learn about our mission, vision, and commitment to excellence.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addKeyword([
            'real estate agency kenya',
            'property management kenya',
            'about pelek properties',
            'kenyan real estate company',
            'property services nairobi'
        ]);
        SEOMeta::setCanonical(route('about'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('about'));
        OpenGraph::setType('website');
        OpenGraph::addImage(asset('images/logo.png'));

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        TwitterCard::setImage(asset('images/logo.png'));

        JsonLd::setType('RealEstateAgent');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('foundingDate', '2024');
        JsonLd::addValue('address', [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Nairobi',
            'addressCountry' => 'KE'
        ]);
    }

    /**
     * Set SEO meta tags for the contact page
     */
    public function setContactPageMeta()
    {
        $title = 'Contact Pelek Properties | Get in Touch';
        $description = 'Contact Pelek Properties for all your real estate needs in Kenya. Our team of experts is ready to assist with property sales, rentals, management, and valuations.';

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addKeyword([
            'contact real estate agent',
            'property inquiry kenya',
            'real estate consultation',
            'property viewing appointment',
            'real estate agent nairobi'
        ]);
        SEOMeta::setCanonical(route('contact'));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('contact'));
        OpenGraph::setType('website');

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);

        JsonLd::setType('ContactPage');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('contactPoint', [
            '@type' => 'ContactPoint',
            'telephone' => '+254711614099',
            'contactType' => 'customer service',
            'email' => 'admin@pelekproperties.co.ke',
            'areaServed' => 'KE'
        ]);
    }

    /**
     * Set SEO meta tags for legal pages
     */
    public function setLegalPageMeta(string $page)
    {
        $config = $this->seoConfig['legal'][$page] ?? null;
        if (!$config) return;

        SEOMeta::setTitle($config['title']);
        SEOMeta::setDescription($config['description']);
        SEOMeta::addKeyword($config['keywords']);
        SEOMeta::setCanonical(route($page));

        OpenGraph::setTitle($config['title']);
        OpenGraph::setDescription($config['description']);
        OpenGraph::setUrl(route($page));
        OpenGraph::setType('website');

        TwitterCard::setTitle($config['title']);
        TwitterCard::setDescription($config['description']);

        JsonLd::setType($config['schema_type']);
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($config['title']);
        JsonLd::setDescription($config['description']);
        JsonLd::addValue('publisher', [
            '@type' => 'Organization',
            'name' => 'Pelek Properties',
            'url' => url('/')
        ]);
    }

    /**
     * Set SEO meta tags for property booking pages
     */
    public function setPropertyBookingMeta(Property $property)
    {
        $title = "Book Viewing: {$property->title} | Pelek Properties";
        $description = "Schedule a viewing for {$property->title} located in {$property->location}. Easy online booking process with Pelek Properties.";

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('properties.book', $property));
        
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('properties.book', $property));
        OpenGraph::setType('website');
        
        if ($property->images->isNotEmpty()) {
            OpenGraph::addImage(url($property->images->first()->url));
        }

        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        if ($property->images->isNotEmpty()) {
            TwitterCard::setImage(url($property->images->first()->url));
        }

        JsonLd::setType('RealEstateAgent');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        JsonLd::addValue('makesOffer', [
            '@type' => 'Offer',
            'itemOffered' => [
                '@type' => 'Service',
                'name' => 'Property Viewing',
                'description' => "Schedule a viewing for {$property->title}"
            ]
        ]);
    }

    /**
     * Get recommended schema type for a specific page or content type
     * 
     * @param string $pageType The type of page (e.g., 'property', 'blog', 'contact', etc.)
     * @param string|null $subType Optional subtype (e.g., 'sale', 'rental', 'airbnb' for property type)
     * @return string The recommended schema.org type
     */
    /**
     * Set SEO meta tags for location-specific pages
     * 
     * @param string $city The city name (e.g., 'nairobi', 'mombasa')
     * @param string|null $area The specific area within the city (optional)
     * @param string|null $propertyType The type of property listing (optional)
     */
    public function setLocationMeta(string $city, ?string $area = null, ?string $propertyType = null)
    {
        // Get city data from config
        $cityData = $this->seoConfig['locations']['cities'][$city] ?? null;
        if (!$cityData) return;
        
        $cityName = $cityData['name'];
        
        // Build title and description based on parameters
        $title = "{$cityName} ";
        $description = "";
        
        if ($propertyType) {
            $propertyTypeConfig = $this->seoConfig['property_types'][$propertyType] ?? null;
            if ($propertyTypeConfig) {
                // For specific property type in location
                $title .= match($propertyType) {
                    'sale' => 'Properties for Sale',
                    'rental' => 'Properties for Rent',
                    'airbnb' => 'Airbnb Accommodations',
                    'commercial' => 'Commercial Properties',
                    default => 'Real Estate'
                };
                
                $description = "Discover " . match($propertyType) {
                    'sale' => "premium properties for sale",
                    'rental' => "quality rental properties",
                    'airbnb' => "luxury furnished Airbnb stays",
                    'commercial' => "prime commercial spaces",
                    default => "exceptional real estate"
                } . " in {$cityName}";
                
                if ($area) {
                    $title .= " in {$area}, {$cityName}";
                    $description .= ", specifically in the {$area} area";
                } else {
                    $title .= " in {$cityName}, Kenya";
                }
                
                $description .= ". Exclusive listings by Pelek Properties.";
                
                $schemaType = $propertyTypeConfig['schema_type'];
                $keywords = array_merge(
                    $propertyTypeConfig['keywords'],
                    [strtolower($cityName) . " " . $propertyType]
                );
            } else {
                // Generic property type
                $title .= "Real Estate | Pelek Properties";
                $description = "Explore real estate options in {$cityName}, Kenya. Quality properties offered by Pelek Properties.";
                $schemaType = 'RealEstateListing';
                $keywords = ['real estate', 'properties', strtolower($cityName)];
            }
        } else {
            // General location page
            $title .= "Real Estate | Properties in {$cityName} | Pelek Properties";
            $description = "Explore our selection of properties in {$cityName}, Kenya. Find your ideal home, rental, or commercial space with Pelek Properties.";
            $schemaType = 'RealEstateAgent';
            $keywords = ['real estate ' . strtolower($cityName), 'properties in ' . strtolower($cityName)];
        }
        
        if ($area) {
            $keywords[] = strtolower($area) . ' ' . strtolower($cityName);
        }
        
        // Set meta tags
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::addKeyword($keywords);
        
        $canonicalUrl = $area 
            ? route('properties.location', ['city' => $city, 'area' => $area]) 
            : route('properties.city', ['city' => $city]);
        
        if ($propertyType) {
            $canonicalUrl .= "?type={$propertyType}";
        }
        
        SEOMeta::setCanonical($canonicalUrl);
        
        // Set Open Graph
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl($canonicalUrl);
        OpenGraph::setType('website');
        OpenGraph::addProperty('locale', 'en_KE');
        OpenGraph::addProperty('location:locality', $cityName);
        OpenGraph::addProperty('location:region', $cityName);
        OpenGraph::addProperty('location:country', 'Kenya');
        
        // Set Twitter Card
        TwitterCard::setTitle($title);
        TwitterCard::setDescription($description);
        
        // Set JSON-LD
        JsonLd::setType($schemaType);
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle($title);
        JsonLd::setDescription($description);
        
        // Add location data to JSON-LD
        JsonLd::addValue('areaServed', [
            '@type' => 'City',
            'name' => $cityName,
            'containedInPlace' => [
                '@type' => 'Country',
                'name' => 'Kenya'
            ]
        ]);
        
        if ($area) {
            JsonLd::addValue('areaServed', [
                '@type' => 'Place',
                'name' => $area,
                'containedInPlace' => [
                    '@type' => 'City',
                    'name' => $cityName
                ]
            ]);
        }
    }
    
    public function getSchemaType(string $pageType, ?string $subType = null): string
    {
        $types = [
            'home' => 'RealEstateAgent',
            'about' => 'RealEstateAgent',
            'contact' => 'ContactPage',
            'blog' => [
                'index' => 'Blog',
                'post' => 'BlogPosting',
            ],
            'property' => [
                'default' => 'RealEstateListing',
                'sale' => 'RealEstateListing',
                'rental' => 'Apartment',
                'airbnb' => 'LodgingBusiness',
                'commercial' => 'CommercialProperty',
                'booking' => 'Service'
            ],
            'service' => [
                'default' => 'Service',
                'property_management' => 'PropertyManagementCompany',
                'valuation' => 'PropertyValue',
            ],
            'legal' => [
                'default' => 'WebPage',
                'privacy' => 'PrivacyPolicy',
                'terms' => 'TermsOfService',
                'cookies' => 'WebPage'
            ]
        ];

        if (is_array($types[$pageType])) {
            return $types[$pageType][$subType] ?? $types[$pageType]['default'] ?? 'WebPage';
        }

        return $types[$pageType] ?? 'WebPage';
    }
}