<?php
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;
use function Livewire\Volt\{state, mount};

state([
    'title' => '',
    'description' => '',
    'keywords' => [],
    'canonicalUrl' => '',
    'image' => '',
]);

mount(function ($title = null, $description = null, $keywords = [], $canonicalUrl = null, $image = null) {
    $this->title = $title;
    $this->description = $description;
    $this->keywords = $keywords;
    $this->canonicalUrl = $canonicalUrl;
    $this->image = $image;

    // Set Meta tags
    if ($this->title) {
        SEOMeta::setTitle($this->title);
    }
    if ($this->description) {
        SEOMeta::setDescription($this->description);
    }
    if (!empty($this->keywords)) {
        SEOMeta::setKeywords($this->keywords);
    }
    if ($this->canonicalUrl) {
        SEOMeta::setCanonical($this->canonicalUrl);
    }

    // Set OpenGraph
    if ($this->title) {
        OpenGraph::setTitle($this->title);
    }
    if ($this->description) {
        OpenGraph::setDescription($this->description);
    }
    if ($this->image) {
        OpenGraph::addImage(url($this->image));
    }

    // Set Twitter Card
    if ($this->title) {
        TwitterCard::setTitle($this->title);
    }
    if ($this->description) {
        TwitterCard::setDescription($this->description);
    }
    if ($this->image) {
        TwitterCard::setImage(url($this->image));
    }

    // Set JSON-LD
    JsonLd::setTitle($this->title ?? config('seotools.json-ld.defaults.title'));
    JsonLd::setDescription($this->description ?? config('seotools.json-ld.defaults.description'));
    JsonLd::setType('RealEstateAgent');
    if ($this->image) {
        JsonLd::addImage(url($this->image));
    }
});

// No need for a template as this component only handles meta tags
?>
