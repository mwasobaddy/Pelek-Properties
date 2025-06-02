<?php
use Artesaos\SEOTools\Facades\JsonLd;
use function Livewire\Volt\{state, mount};

state([
    'data' => [],
    'type' => 'WebPage',
]);

mount(function ($data = [], $type = 'WebPage') {
    $this->data = $data;
    $this->type = $type;
    
    JsonLd::setType($this->type);
    JsonLd::addValue('@context', 'https://schema.org');
    
    // Add all structured data properties
    foreach ($this->data as $key => $value) {
        JsonLd::addValue($key, $value);
    }
});

// No need for a template as this component only handles structured data
?>
