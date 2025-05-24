<?php

use App\Services\BlogService;
use App\Services\SEOService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;
    
    public $featuredPosts = [];

    public function mount(BlogService $blogService, SEOService $seoService)
    {
        $this->featuredPosts = $blogService->getFeaturedPosts();
        
        // Meta tags
        SEOMeta::setTitle('Real Estate Blog - Expert Insights & Market Updates | Pelek Properties');
        SEOMeta::setDescription('Stay informed with expert real estate insights, market trends, and property tips from Kenya\'s leading property professionals. Discover the latest in Kenyan real estate.');
        SEOMeta::setCanonical(route('blog.index'));
        SEOMeta::addKeyword([
            'kenya real estate blog',
            'property market insights',
            'real estate tips kenya',
            'property investment advice',
            'nairobi real estate market',
            'kenyan property trends',
            'property management kenya',
            'real estate investment kenya'
        ]);
        
        // Open Graph
        OpenGraph::setTitle('Real Estate Blog - Expert Insights & Updates | Pelek Properties');
        OpenGraph::setDescription('Expert insights, market analysis, and property tips from Kenya\'s leading real estate professionals. Stay updated with Pelek Properties.');
        OpenGraph::setUrl(route('blog.index'));
        OpenGraph::setType('blog');
        OpenGraph::setSiteName('Pelek Properties');
        
        if (count($this->featuredPosts) > 0 && $this->featuredPosts[0]->featured_image) {
            $ogImage = $this->featuredPosts[0]->featured_image;
            OpenGraph::addImage(asset("storage/{$ogImage}"), [
                'height' => 630,
                'width' => 1200,
                'type' => 'image/jpeg'
            ]);
        } else {
            OpenGraph::addImage(asset('favicon.svg'), [
                'height' => 512,
                'width' => 512,
                'type' => 'image/svg+xml'
            ]);
        }
        
        // Twitter Card
        TwitterCard::setType('summary_large_image');
        TwitterCard::setTitle('Real Estate Blog - Expert Insights | Pelek Properties');
        TwitterCard::setDescription('Expert real estate insights and property tips from Kenya\'s leading property professionals.');
        if (count($this->featuredPosts) > 0 && $this->featuredPosts[0]->featured_image) {
            TwitterCard::setImage(asset("storage/{$this->featuredPosts[0]->featured_image}"));
        } else {
            TwitterCard::setImage(asset('favicon.svg'));
        }
        TwitterCard::setSite('@PelekProperties');
        
        // JSON-LD Schema
        JsonLd::setType('Blog');
        JsonLd::addValue('@context', 'https://schema.org');
        JsonLd::setTitle('Real Estate Blog - Pelek Properties');
        JsonLd::setDescription('Expert insights and updates about the Kenyan real estate market.');
        JsonLd::addValue('publisher', [
            '@type' => 'Organization',
            'name' => 'Pelek Properties',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('favicon.svg')
            ]
        ]);
        JsonLd::addValue('mainEntityOfPage', [
            '@type' => 'WebPage',
            '@id' => route('blog.index')
        ]);
        
        // Add blog posts to JSON-LD
        $blogPosts = [];
        foreach ($this->featuredPosts as $post) {
            $blogPosts[] = [
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'datePublished' => $post->published_at->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->author->name
                ],
                'url' => route('blog.show', $post->slug),
                'image' => $post->featured_image ? asset("storage/{$post->featured_image}") : asset('favicon.svg'),
                'description' => $post->excerpt ?? substr(strip_tags($post->content), 0, 160)
            ];
        }
        JsonLd::addValue('blogPost', $blogPosts);
    }

    #[Computed]
    public function posts()
    {
        return app(BlogService::class)->getPublishedPosts();
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
            <img src="{{ asset('images/placeholder.webp') }}" alt="Pelek Properties Blog"
                class="h-full w-full object-cover opacity-40">
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-[#02c9c2]/20 blur-3xl"></div>
            <div class="absolute right-0 bottom-0 h-80 w-80 rounded-full bg-[#02c9c2]/15 blur-3xl"></div>
        </div>

        <!-- Enhanced Content with Animation -->
        <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8 flex justify-center">
            <div class="mx-auto max-w-2xl lg:mx-0" 
                 x-data="{}" 
                 x-intersect="$el.classList.add('animate-fade-in')">
                <span class="inline-flex items-center rounded-full bg-[#02c9c2]/10 px-3 py-1 text-sm font-medium text-[#02c9c2] ring-1 ring-inset ring-[#02c9c2]/20 mb-4 backdrop-blur-md">
                    Insights & Updates
                </span>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl font-display">
                    Our Blog
                </h1>
                <p class="mt-6 text-lg leading-8 text-zinc-300">
                    Stay informed about real estate trends, property management tips, and market insights.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Featured Posts -->
    @if($featuredPosts->isNotEmpty())
        <div class="mb-12">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Featured Posts</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($featuredPosts as $post)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                        @if($post->featured_image)
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                        @endif
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                                <a href="{{ route('blog.show', ['post' => $post->slug]) }}" class="hover:text-primary-600">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                {{ Str::limit(strip_tags($post->content), 150) }}
                            </p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at->diffForHumans() }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    By {{ $post->author->name }}
                                </span>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('blog.show', ['post' => $post->slug]) }}" 
                                   class="inline-flex items-center justify-center w-full px-4 py-2 bg-[#02c9c2]/10 hover:bg-[#02c9c2]/20 text-[#02c9c2] rounded-full transition-colors duration-200">
                                    Read More
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- All Posts with Enhanced Styling -->
    <div class="mt-20 bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-8 shadow-xl relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-[#02c9c2]/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-[#02c9c2]/5 rounded-full blur-xl"></div>

        <div class="relative">
            <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Latest Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($this->posts as $post)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                            <a href="{{ route('blog.show', ['post' => $post->slug]) }}" class="hover:text-primary-600">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                            {{ Str::limit(strip_tags($post->content), 150) }}
                        </p>                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at->diffForHumans() }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    By {{ $post->author->name }}
                                </span>
                            </div>
                            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('blog.show', ['post' => $post->slug]) }}" 
                                   class="inline-flex items-center justify-center w-full px-4 py-2 bg-[#02c9c2]/10 hover:bg-[#02c9c2]/20 text-[#02c9c2] rounded-full transition-colors duration-200">
                                    Read More
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($this->posts->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $this->posts->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Newsletter Subscription -->
    <div class="mt-16 bg-gradient-to-br from-[#02c9c2]/5 to-[#012e2b]/10 dark:from-[#02c9c2]/10 dark:to-[#012e2b]/20 rounded-2xl p-8 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-[#02c9c2]/10 to-transparent"></div>
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-[#012e2b]/10 to-transparent"></div>
        </div>
        
        <div class="relative max-w-2xl mx-auto text-center">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Stay Updated</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6">Subscribe to our newsletter for the latest property insights and market updates.</p>
            
            <form wire:submit.prevent="subscribe" class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                <div class="flex-1">
                    <input 
                        type="email" 
                        wire:model.live="email" 
                        placeholder="Enter your email"
                        class="w-full px-4 py-2 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#02c9c2] focus:border-transparent"
                        required
                    >
                </div>
                <button 
                    type="submit"
                    class="inline-flex items-center justify-center px-6 py-2 bg-[#02c9c2] hover:bg-[#02c9c2]/90 text-white rounded-full transition-colors duration-200"
                >
                    Subscribe
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Back to Top Button -->
<button
    x-data="{ show: false }"
    x-show="show"
    x-on:scroll.window="show = window.scrollY > 500"
    x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="fixed bottom-8 right-8 p-2 rounded-full bg-[#02c9c2] text-white shadow-lg hover:bg-[#02c9c2]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] transition-colors duration-200"
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>
</div>
