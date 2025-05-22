<?php

use App\Services\BlogService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;
    use WithFileUploads;
    
    #[State]
    public $showFormModal = false;
    
    #[State]
    public $modalMode = 'create'; // create, edit, view
    
    #[State]
    public $showDeleteModal = false;

    #[State]
    public $postToDelete = null;
    
    #[State]
    public $title = '';
    
    #[State]
    public $content = '';
    
    #[State]
    public $featuredImage = '';
    
    #[State]
    public $selectedPost = null;
    
    #[State]
    public $isLoading = false;
    
    #[State]
    public $tempImage = null;

    #[State] 
    public $publishedAt = null;
    
    // New variables for search, filters, and sorting
    #[State]
    public $search = '';
    
    #[State]
    public $showFilters = false;
    
    #[State]
    public $filters = [
        'status' => '',
        'featured' => '',
        'date_range' => '',
    ];
    
    #[State]
    public $sortField = 'created_at';
    
    #[State]
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filters' => ['except' => ['status' => '', 'featured' => '', 'date_range' => '']]
    ];

    // Add method to handle image upload
    public function updatedTempImage($value)
    {
        try {
            $this->validate([
                'tempImage' => 'image|max:2048', // 2MB Max
            ]);

            $filename = Str::random(40) . '.' . $value->getClientOriginalExtension();
            $path = $value->storeAs('public/blogs', $filename);
            
            $this->featuredImage = Storage::url($path);
        } catch (\Exception $e) {
            $this->addError('tempImage', 'Failed to upload image: ' . $e->getMessage());
        }
    }

    // Add method to remove image
    public function removeImage()
    {
        if ($this->featuredImage) {
            $path = str_replace('/storage/', 'public/', $this->featuredImage);
            Storage::delete($path);
            $this->featuredImage = null;
            $this->tempImage = null;
        }
    }
    
    public function with(): array
    {
        $this->isLoading = true;
        try {
            // Use the BlogPost model directly
            $query = \App\Models\BlogPost::query()
                ->when($this->search, function ($query) {
                    return $query->where('title', 'like', '%' . $this->search . '%');
                })
                ->when($this->filters['status'], function ($query, $status) {
                    if ($status === 'published') {
                        return $query->where('is_published', true);
                    } elseif ($status === 'draft') {
                        return $query->where('is_published', false);
                    }
                    return $query;
                })
                ->when($this->filters['featured'], function ($query, $featured) {
                    if ($featured === 'featured') {
                        return $query->where('is_featured', true);
                    } elseif ($featured === 'not_featured') {
                        return $query->where('is_featured', false);
                    }
                    return $query;
                })
                ->when($this->filters['date_range'], function ($query, $dateRange) {
                    return match($dateRange) {
                        'today' => $query->whereDate('created_at', now()),
                        'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                        'this_month' => $query->whereMonth('created_at', now()->month),
                        'this_year' => $query->whereYear('created_at', now()->year),
                        default => $query
                    };
                });
                
            // Apply sorting
            if ($this->sortField === 'author') {
                $query->join('users', 'blog_posts.author_id', '=', 'users.id')
                    ->select('blog_posts.*')
                    ->orderBy('users.name', $this->sortDirection);
            } else {
                $query->orderBy($this->sortField, $this->sortDirection);
            }
            
            return [
                'posts' => $query->paginate(10)
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showFormModal = true;
    }
    
    public function edit(\App\Models\BlogPost $post)
    {
        $this->authorize('update', $post);
        
        $this->resetForm();
        $this->selectedPost = $post;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->featuredImage = $post->featured_image;
        $this->publishedAt = $post->published_at;
        $this->modalMode = 'edit';
        $this->showFormModal = true;
    }
    
    public function view(\App\Models\BlogPost $post)
    {
        $this->resetForm();
        $this->selectedPost = $post;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->featuredImage = $post->featured_image;
        $this->publishedAt = $post->published_at;
        $this->modalMode = 'view';
        $this->showFormModal = true;
    }

    public function save(BlogService $blogService)
    {
        if ($this->modalMode === 'create') {
            $this->authorize('create', \App\Models\BlogPost::class);
            
            $validated = $this->validate([
                'title' => 'required|min:3|max:255',
                'content' => 'required|min:10',
                'featuredImage' => 'nullable|string',
                'publishedAt' => 'nullable|date',
            ]);

            $blogService->createPost($validated, Auth::id());
            
            $this->dispatch('notify', type: 'success', message: 'Post created successfully');
        } else {
            $this->authorize('update', $this->selectedPost);

            $validated = $this->validate([
                'title' => 'required|min:3|max:255',
                'content' => 'required|min:10',
                'featuredImage' => 'nullable|string',
                'publishedAt' => 'nullable|date',
            ]);

            $blogService->updatePost($this->selectedPost, $validated);
            
            $this->dispatch('notify', type: 'success', message: 'Post updated successfully');
        }
        
        $this->resetForm();
        $this->showFormModal = false;
    }

    public function resetForm()
    {
        $this->title = '';
        $this->content = '';
        $this->featuredImage = '';
        $this->publishedAt = null;
        $this->tempImage = null;
        $this->selectedPost = null;
        return $this;
    }

    public function togglePublishStatus(BlogService $blogService, \App\Models\BlogPost $post)
    {
        $this->authorize('update', $post);

        if ($post->is_published) {
            $blogService->unpublishPost($post);
            $message = 'Post unpublished successfully';
        } else {
            $blogService->publishPost($post);
            $message = 'Post published successfully';
        }

        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function toggleFeatured(BlogService $blogService, \App\Models\BlogPost $post)
    {
        $this->authorize('update', $post);

        $blogService->toggleFeatured($post);
        $this->dispatch('notify', type: 'success', message: 'Post featured status updated');
    }

    public function deletePost(BlogService $blogService, \App\Models\BlogPost $post)
    {
        if ($this->postToDelete) {
            $this->authorize('delete', $this->postToDelete);
            $blogService->deletePost($this->postToDelete);
            $this->dispatch('notify', type: 'success', message: 'Post deleted successfully');
            $this->postToDelete = null;
            $this->showDeleteModal = false;
        }
    }
    
    public function confirmDelete(\App\Models\BlogPost $post)
    {
        $this->authorize('delete', $post);
        $this->postToDelete = $post;
        $this->showDeleteModal = true;
    }
    
    // New methods for search, filters, and sorting
    public function sort($field)
    {
        if ($this->sortField === $field) {
            // Toggle direction if clicking on the same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
    }
}
?>

<div class="bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl shadow-xl overflow-hidden relative">
    <!-- Header Section with Gradient -->
    <div class="h-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b]"></div>
    
    <div class="p-8 border-b border-gray-200 dark:border-gray-700">
        <!-- Animated Header -->
        <div class="sm:flex sm:items-center sm:justify-between" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="space-y-2">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="document-text" class="w-8 h-8 text-[#02c9c2]" />
                    Blog Posts
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Manage and publish blog content for your website
                </p>
            </div>
            
            @can('create', \App\Models\BlogPost::class)
                <button 
                    wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white font-medium rounded-lg text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-lg"
                    wire:loading.attr="disabled"
                >
                    <flux:icon name="plus" class="w-5 h-5 mr-2" />
                    New Post
                </button>
            @endcan
        </div>

        <!-- Enhanced Search and Filters with Animation -->
        <div class="mt-8 space-y-4" 
             x-data="{}"
             x-intersect="$el.classList.add('animate-fade-in')">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <flux:icon wire:loading.remove wire:target="search" name="magnifying-glass"
                            class="h-5 w-5 text-gray-400 group-focus-within:text-[#02c9c2] transition-colors duration-200" />
                        <flux:icon wire:loading wire:target="search" name="arrow-path" class="h-5 w-5 text-[#02c9c2] animate-spin" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Search blog posts..."
                        class="block w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                        aria-label="Search blog posts"
                    >
                </div>

                <!-- Filter Toggle Button -->
                <button
                    wire:click="toggleFilters"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#02c9c2] to-[#012e2b] text-white rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#02c9c2] dark:focus:ring-offset-gray-900 transition-all duration-150 shadow-sm backdrop-blur-xl"
                >
                    <flux:icon name="funnel" class="w-5 h-5 mr-2" />
                    Filters
                    <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full px-2 py-0.5">
                        {{ array_filter($filters) ? count(array_filter($filters)) : '0' }}
                    </span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div x-show="$wire.showFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl shadow-sm space-y-4"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="check-circle" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.status"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>

                    <!-- Featured Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Featured</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="star" class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                wire:model.live="filters.featured"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                            >
                                <option value="">All Posts</option>
                                <option value="featured">Featured Only</option>
                                <option value="not_featured">Not Featured</option>
                            </select>
                        </div>
                    </div>

                    <!-- Date Range Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                                </div>
                                <select
                                    wire:model.live="filters.date_range"
                                    class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-10 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2] sm:text-sm"
                                >
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="this_year">This Year</option>
                                </select>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Actions -->
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 col-span-2 mt-2">
                    <!-- Reset Filters Button -->
                    <button wire:click="resetFilters"
                        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-[#02c9c2] to-[#02a8a2] px-5 py-2.5 text-sm font-medium text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                        <!-- Background animation on hover -->
                        <span
                            class="absolute inset-0 translate-y-full bg-gradient-to-r from-[#012e2b] to-[#014e4a] group-hover:translate-y-0 transition-transform duration-300 ease-out"></span>
                        <!-- Content remains visible -->
                        <span class="relative flex items-center gap-2">
                            <flux:icon name="arrow-path"
                                class="h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                            <span>Clear All Filters</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="p-8">
        <div class="relative overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 backdrop-blur-xl">
            <!-- Loading Overlay -->
            <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex items-center space-x-4">
                    <flux:icon name="arrow-path" class="w-8 h-8 text-[#02c9c2] animate-spin" />
                    <span class="text-gray-600 dark:text-gray-300 font-medium">Loading posts...</span>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-300 text-sm">
                    <tr>
                        <th wire:click="sort('title')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Title</span>
                                @if($sortField === 'title')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('author')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Author</span>
                                @if($sortField === 'author')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('is_published')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Status</span>
                                @if($sortField === 'is_published')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('is_featured')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Featured</span>
                                @if($sortField === 'is_featured')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th wire:click="sort('created_at')" class="px-6 py-4 font-medium cursor-pointer hover:text-[#02c9c2] transition-colors duration-150">
                            <div class="flex items-center space-x-1">
                                <span>Created</span>
                                @if($sortField === 'created_at')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-4 h-4" />
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($posts as $post)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                        @if($post->featured_image)
                                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                        @else
                                            <flux:icon name="document" class="w-6 h-6 text-gray-500 dark:text-gray-400" />
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $post->title }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ $post->author->name }}
                            </td>
                            <td class="px-6 py-4">
                                @can('update', $post)
                                    <button 
                                        wire:click="togglePublishStatus({{ $post->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="togglePublishStatus({{ $post->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                            {{ $post->is_published 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' }}"
                                    >
                                        <flux:icon wire:loading.remove wire:target="togglePublishStatus({{ $post->id }})" 
                                            name="{{ $post->is_published ? 'eye' : 'eye-slash' }}" 
                                            class="w-4 h-4 mr-1" />
                                        <flux:icon wire:loading wire:target="togglePublishStatus({{ $post->id }})" 
                                            name="arrow-path" 
                                            class="w-4 h-4 mr-1 animate-spin" />
                                        {{ $post->is_published ? 'Published' : 'Draft' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                        {{ $post->is_published 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' }}">
                                        <flux:icon name="{{ $post->is_published ? 'eye' : 'eye-slash' }}" class="w-4 h-4 mr-1" />
                                        {{ $post->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4">
                                @can('update', $post)
                                    <button 
                                        wire:click="toggleFeatured({{ $post->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleFeatured({{ $post->id }})"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                            {{ $post->is_featured 
                                                ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' }}"
                                    >
                                        <flux:icon wire:loading.remove wire:target="toggleFeatured({{ $post->id }})" 
                                            name="{{ $post->is_featured ? 'star' : 'sparkles' }}" 
                                            class="w-4 h-4 mr-1" />
                                        <flux:icon wire:loading wire:target="toggleFeatured({{ $post->id }})" 
                                            name="arrow-path" 
                                            class="w-4 h-4 mr-1 animate-spin" />
                                        {{ $post->is_featured ? 'Featured' : 'Normal' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-medium
                                        {{ $post->is_featured 
                                            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300' }}">
                                        <flux:icon name="{{ $post->is_featured ? 'star' : 'sparkles' }}" class="w-4 h-4 mr-1" />
                                        {{ $post->is_featured ? 'Featured' : 'Normal' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-3">
                                    <!-- View button -->
                                    <button 
                                        wire:click="view({{ $post->id }})"
                                        class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-indigo-500 dark:bg-indigo-700/50 rounded-lg p-2"
                                        title="View Post"
                                    >
                                        <flux:icon wire:loading.remove wire:target="view({{ $post->id }})" name="eye" class="w-5 h-5" />
                                        <flux:icon wire:loading wire:target="view({{ $post->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                    </button>
                                    
                                    @can('update', $post)
                                        <button 
                                            wire:click="edit({{ $post->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-[#02c9c2] dark:hover:text-[#02c9c2] transition-colors duration-150 bg-green-500 dark:bg-green-700/50 rounded-lg p-2"
                                            title="Edit Post"
                                        >
                                            <flux:icon wire:loading.remove wire:target="edit({{ $post->id }})" name="pencil-square" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="edit({{ $post->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                    
                                    @can('delete', $post)
                                        <button 
                                            wire:click="confirmDelete({{ $post->id }})"
                                            class="text-gray-200 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-500 transition-colors duration-150 bg-red-500 dark:bg-red-700/50 rounded-lg p-2"
                                            title="Delete Post"
                                        >
                                            <flux:icon wire:loading.remove wire:target="confirmDelete({{ $post->id }})" name="trash" class="w-5 h-5" />
                                            <flux:icon wire:loading wire:target="confirmDelete({{ $post->id }})" name="arrow-path" class="w-5 h-5 animate-spin" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <div class="text-center">
                                    <flux:icon name="document" class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No blog posts found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $search || array_filter($filters) ? 'Try adjusting your search or filter criteria.' : 'Get started by creating a new blog post.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $posts->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Unified Modal for Create, Edit, View -->
    <flux:modal wire:model="showFormModal" class="w-full max-w-4xl !p-0">
        <div
            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden" 
            x-data="{
                isViewMode: function() { return '{{ $modalMode }}' === 'view' },
                init() {
                    // If in view mode, disable all form elements
                    if (this.isViewMode()) {
                        this.$nextTick(() => {
                            this.$el.querySelectorAll('input, select, textarea').forEach(el => {
                                el.disabled = true;
                            });
                        });
                    }
                }
            }"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @if($modalMode === 'create')
                        Create New Blog Post
                    @elseif($modalMode === 'edit')
                        Edit Blog Post
                    @else
                        View Blog Post
                    @endif
                </h3>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                <form wire:submit.prevent="save" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="document-text" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input type="text" wire:model="title" required 
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2]">
                        </div>
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content</label>
                        <div class="relative">
                            <textarea wire:model="content" rows="6" required 
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 px-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2]"></textarea>
                        </div>
                        @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Featured Image</label>
                        <div class="space-y-3">
                            <!-- Image Preview -->
                            @if($featuredImage)
                                <div class="relative w-full h-48 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <img src="{{ $featuredImage }}" alt="Preview" class="w-full h-full object-cover">
                                    <button 
                                        type="button" 
                                        wire:click="removeImage"
                                        x-show="!isViewMode()"
                                        class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none">
                                        <flux:icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif

                            <!-- Image Upload Input (hidden in view mode) -->
                            <div class="relative" x-show="!isViewMode()">
                                <input type="file" wire:model="tempImage" accept="image/*" class="hidden" id="image-upload">
                                <label for="image-upload" 
                                    class="flex items-center justify-center w-full px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-[#02c9c2] dark:hover:border-[#02c9c2] transition-colors">
                                    <div class="space-y-1 text-center">
                                        <flux:icon name="cloud-arrow-up" class="mx-auto h-8 w-8 text-gray-400" />
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-medium text-[#02c9c2]">Click to upload</span> or drag and drop
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </label>
                                @error('tempImage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Add Published At Date Picker -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Date</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <flux:icon name="calendar" class="h-5 w-5 text-gray-400" />
                            </div>
                            <input type="datetime-local" wire:model="publishedAt"
                                class="appearance-none w-full rounded-lg border-0 bg-white/50 dark:bg-gray-700/50 py-3 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200 placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-[#02c9c2]">
                        </div>
                        @error('publishedAt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                <flux:button variant="danger" wire:click="$set('showFormModal', false)">
                    @if($modalMode === 'view')
                        Close
                    @else
                        Cancel
                    @endif
                </flux:button>
                @if($modalMode !== 'view')
                <flux:button variant="primary" wire:click="save" class="bg-[#02c9c2] hover:bg-[#02c9c2]/90">
                    <flux:icon wire:loading wire:target="save" name="arrow-path" class="w-4 h-4 mr-2 animate-spin" />
                    {{ $modalMode === 'create' ? 'Create Post' : 'Update Post' }}
                </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal wire:model="showDeleteModal" max-width="md" class="!p-0">
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
            <div class="bg-gradient-to-r from-red-500/20 to-red-600/20 dark:from-red-900/30 dark:to-red-700/30 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-circle" class="w-6 h-6 text-red-600" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Confirm Deletion
                    </h3>
                </div>
            </div>

            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this blog post? This action cannot be undone.
                </p>
                @if($postToDelete)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $postToDelete->title }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Author: {{ $postToDelete->author->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Created: {{ \Carbon\Carbon::parse($postToDelete->created_at)->format('M d, Y') }}</p>
                    </div>
                @endif

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                        Cancel
                    </button>
                    <button type="button" wire:click="deletePost"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900"
                            wire:loading.attr="disabled">
                        <flux:icon wire:loading.remove wire:target="deletePost" name="trash" class="w-4 h-4 mr-1.5" />
                        <flux:icon wire:loading wire:target="deletePost" name="arrow-path" class="w-4 h-4 mr-1.5 animate-spin" />
                        Delete Post
                    </button>
                </div>
            </div>
        </div>
    </flux:modal>
    
    <!-- Decorative Elements -->
    <div class="absolute top-40 left-0 w-64 h-64 bg-gradient-to-br from-[#02c9c2]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
    <div class="absolute bottom-20 right-0 w-96 h-96 bg-gradient-to-tl from-[#012e2b]/10 to-transparent rounded-full blur-3xl -z-10 hidden lg:block"></div>
</div>
