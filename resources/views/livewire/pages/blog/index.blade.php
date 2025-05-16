<?php

use App\Services\BlogService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;
    
    public $featuredPosts = [];

    public function mount(BlogService $blogService)
    {
        $this->featuredPosts = $blogService->getFeaturedPosts();
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

        <div class="mt-12">
            {{ $this->posts->links('components.pagination') }}
        </div>
    </div>
</div>
