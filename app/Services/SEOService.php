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
}