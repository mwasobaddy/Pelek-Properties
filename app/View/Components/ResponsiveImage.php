<?php

namespace App\View\Components;

use App\Models\PropertyImage;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Storage;

class ResponsiveImage extends Component
{
    public $image;
    public $alt;
    public $class;
    public $sizes;

    public function __construct(PropertyImage $image, string $alt = '', string $class = '', string $sizes = '')
    {
        $this->image = $image;
        $this->alt = $alt ?: $image->alt_text;
        $this->class = $class;
        $this->sizes = $sizes ?: '(min-width: 1536px) 1536px, (min-width: 1280px) 1280px, (min-width: 1024px) 1024px, (min-width: 768px) 768px, (min-width: 640px) 640px, 320px';
    }

    public function render()
    {
        return view('components.responsive-image');
    }

    public function getSrcset()
    {
        $metadata = $this->image->metadata;
        if (!isset($metadata['responsive_paths'])) {
            return '';
        }

        $paths = collect($metadata['responsive_paths']);
        
        return $paths->map(function ($pathInfo, $size) {
            $width = match ($size) {
                'xs' => 320,
                'sm' => 640,
                'md' => 768,
                'lg' => 1024,
                'xl' => 1280,
                '2xl' => 1536,
                default => 1024
            };
            
            return Storage::disk('property_images')->url($pathInfo['original']) . " {$width}w";
        })->implode(', ');
    }

    public function getWebpSrcset()
    {
        $metadata = $this->image->metadata;
        if (!isset($metadata['responsive_paths'])) {
            return '';
        }

        $paths = collect($metadata['responsive_paths']);
        
        return $paths->map(function ($pathInfo, $size) {
            $width = match ($size) {
                'xs' => 320,
                'sm' => 640,
                'md' => 768,
                'lg' => 1024,
                'xl' => 1280,
                '2xl' => 1536,
                default => 1024
            };
            
            return Storage::disk('property_images')->url($pathInfo['webp']) . " {$width}w";
        })->implode(', ');
    }
}
