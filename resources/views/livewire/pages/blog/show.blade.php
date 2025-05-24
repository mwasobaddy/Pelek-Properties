<?php

use App\Models\BlogPost;
use App\Services\SEOService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

new #[Layout('components.layouts.guest')] class extends Component {
    public BlogPost $post;

    public function mount(BlogPost $post)
    {
        if (!$post->is_published && !auth()->user()?->hasRole('admin')) {
            abort(404);
        }
        $this->post = $post;
        
        // SEO Meta Tags
        SEOMeta::setTitle("{$post->title} | Real Estate Blog | Pelek Properties");
        SEOMeta::setDescription(substr(strip_tags($post->content), 0, 160));
        SEOMeta::setCanonical(route('blog.show', $post->slug));
        
        $keywords = [
            'kenya real estate',
            'property investment',
            'real estate tips',
            strtolower($post->title),
            'pelek properties blog',
            'kenyan property market'
        ];
        SEOMeta::addKeyword($keywords);
        
        // Open Graph
        OpenGraph::setTitle("{$post->title} | Pelek Properties Blog");
        OpenGraph::setDescription(substr(strip_tags($post->content), 0, 160));
        OpenGraph::setUrl(route('blog.show', $post->slug));
        OpenGraph::setType('article');
        OpenGraph::setArticle([
            'published_time' => $post->published_at->toIso8601String(),
            'modified_time' => $post->updated_at->toIso8601String(),
            'author' => $post->author->name,
            'section' => 'Real Estate',
            'tag' => $keywords
        ]);
        
        if ($post->featured_image) {
            OpenGraph::addImage(asset("storage/{$post->featured_image}"), [
                'height' => 630,
                'width' => 1200
            ]);
        }
        
        // Twitter Card
        TwitterCard::setType('summary_large_image');
        TwitterCard::setTitle($post->title);
        TwitterCard::setDescription(substr(strip_tags($post->content), 0, 160));
        if ($post->featured_image) {
            TwitterCard::setImage(asset("storage/{$post->featured_image}"));
        }
        
        // JSON-LD Structured Data
        JsonLd::setType('Article');
        JsonLd::setTitle($post->title);
        JsonLd::setDescription(substr(strip_tags($post->content), 0, 160));
        JsonLd::addValue('author', [
            '@type' => 'Person',
            'name' => $post->author->name
        ]);
        JsonLd::addValue('publisher', [
            '@type' => 'Organization',
            'name' => 'Pelek Properties',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('favicon.svg')
            ]
        ]);
        JsonLd::addValue('datePublished', $post->published_at->toIso8601String());
        JsonLd::addValue('dateModified', $post->updated_at->toIso8601String());
        if ($post->featured_image) {
            JsonLd::addImage(asset("storage/{$post->featured_image}"));
        }
    }
}

?>

<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Enhanced Hero Section with Parallax Effect -->
    <div class="relative overflow-hidden bg-gradient-to-br from-zinc-900 to-[#012e2b] dark:from-zinc-950 dark:to-[#012e2b]">
        <!-- Background elements with parallax effect -->
        <div class="absolute inset-0" x-data="{}"
            x-on:scroll.window="$el.style.transform = `translateY(${window.scrollY * 0.1}px)`">
            <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/85 via-[#012e2b]/75 to-[#02c9c2]/30 backdrop-blur-sm"></div>
            @if($post->featured_image)
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-full w-full object-cover opacity-40">
            @else
                <img src="{{ asset('images/placeholder.webp') }}" alt="{{ $post->title }}" class="h-full w-full object-cover opacity-40">
            @endif
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>

        <!-- Enhanced Content with Animation -->
        <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8 flex justify-center">
            <div class="mx-auto max-w-2xl lg:mx-0 text-center" 
                 x-data="{}" 
                 x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    Blog Post
                </span>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display mb-4">
                    {{ $post->title }}
                </h1>
                <div class="flex items-center justify-center space-x-4 text-zinc-300">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>By {{ $post->author->name }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $post->published_at->format('F j, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <article class="prose dark:prose-invert lg:prose-lg mx-auto bg-white dark:bg-gray-800/50 rounded-2xl p-8 shadow-xl relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-[#02c9c2]/10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-[#02c9c2]/5 rounded-full blur-xl"></div>

            <div class="relative">

        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $post->title }}
        </h1>

        <div class="flex items-center space-x-4 text-gray-500 dark:text-gray-400 mb-8">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>By {{ $post->author->name }}</span>
            </div>
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ $post->published_at->format('F j, Y') }}</span>
            </div>
        </div>

        <div class="prose dark:prose-invert lg:prose-lg max-w-none">
            {!! $post->content !!}
        </div>

            </div>
        </article>

        <!-- Enhanced Back to Blog Link -->
        <div class="mt-12 flex justify-center">
            <a href="{{ route('blog.index') }}" 
               class="inline-flex items-center px-6 py-3 rounded-full bg-[#02c9c2]/10 text-[#02c9c2] hover:bg-[#02c9c2]/20 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Blog
            </a>
        </div>
    </div>
</div>
