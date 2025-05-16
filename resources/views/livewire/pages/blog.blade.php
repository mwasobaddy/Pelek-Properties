<?php

use App\Services\BlogService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.guest')] class extends Component {
    use WithPagination;
    
    public $posts = [];
    public $featuredPosts = [];

    public function mount(BlogService $blogService)
    {
        $this->posts = $blogService->getPublishedPosts();
        $this->featuredPosts = $blogService->getFeaturedPosts();
    }
} 
?>

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
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-primary-600">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                                {{ Str::limit(strip_tags($post->content), 150) }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at->diffForHumans() }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    By {{ $post->author->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- All Posts -->
    <div>
        <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Latest Posts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-primary-600">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
                            {{ Str::limit(strip_tags($post->content), 150) }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $post->published_at->diffForHumans() }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                By {{ $post->author->name }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </div>
</div>
