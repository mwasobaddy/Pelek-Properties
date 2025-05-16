<?php

use App\Services\BlogService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;
    
    public $posts = [];
    public $showCreateModal = false;
    public $showEditModal = false;
    public $title = '';
    public $content = '';
    public $featuredImage = '';
    public $editingPost = null;

    public function mount(BlogService $blogService)
    {
        $this->posts = $blogService->getAllPosts();
    }

    public function createPost(BlogService $blogService)
    {
        $this->authorize('create', \App\Models\BlogPost::class);

        $validated = $this->validate([
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'featuredImage' => 'nullable|url',
        ]);

        $blogService->createPost($validated, Auth::id());

        $this->reset(['title', 'content', 'featuredImage', 'showCreateModal']);
        $this->posts = $blogService->getAllPosts();
        $this->dispatch('notify', type: 'success', message: 'Post created successfully');
    }

    public function editPost(\App\Models\BlogPost $post)
    {
        $this->authorize('update', $post);
        
        $this->editingPost = $post;
        $this->title = $post->title;
        $this->content = $post->content;
        $this->featuredImage = $post->featured_image;
        $this->showEditModal = true;
    }

    public function updatePost(BlogService $blogService)
    {
        $this->authorize('update', $this->editingPost);

        $validated = $this->validate([
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'featuredImage' => 'nullable|url',
        ]);

        $blogService->updatePost($this->editingPost, $validated);

        $this->reset(['title', 'content', 'featuredImage', 'showEditModal', 'editingPost']);
        $this->posts = $blogService->getAllPosts();
        $this->dispatch('notify', type: 'success', message: 'Post updated successfully');
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

        $this->posts = $blogService->getAllPosts();
        $this->dispatch('notify', type: 'success', message: $message);
    }

    public function toggleFeatured(BlogService $blogService, \App\Models\BlogPost $post)
    {
        $this->authorize('update', $post);

        $blogService->toggleFeatured($post);
        $this->posts = $blogService->getAllPosts();
        $this->dispatch('notify', type: 'success', message: 'Post featured status updated');
    }

    public function deletePost(BlogService $blogService, \App\Models\BlogPost $post)
    {
        $this->authorize('delete', $post);

        $blogService->deletePost($post);
        $this->posts = $blogService->getAllPosts();
        $this->dispatch('notify', type: 'success', message: 'Post deleted successfully');
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h2>
        @can('create', \App\Models\BlogPost::class)
            <flux:button wire:click="$set('showCreateModal', true)" icon="plus">
                New Post
            </flux:button>
        @endcan
    </div>

    <!-- Posts Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Featured</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($posts as $post)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $post->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-300">{{ $post->author->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @can('update', $post)
                                <flux:button wire:click="togglePublishStatus({{ $post->id }})" 
                                    :icon="$post->is_published ? 'eye-slash' : 'eye'"
                                    :variant="$post->is_published ? 'success' : 'secondary'">
                                    {{ $post->is_published ? 'Published' : 'Draft' }}
                                </flux:button>
                            @endcan
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @can('update', $post)
                                <flux:button wire:click="toggleFeatured({{ $post->id }})" 
                                    :icon="$post->is_featured ? 'star' : 'star-outline'"
                                    :variant="$post->is_featured ? 'warning' : 'secondary'">
                                    {{ $post->is_featured ? 'Featured' : 'Normal' }}
                                </flux:button>
                            @endcan
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @can('update', $post)
                                <flux:button wire:click="editPost({{ $post->id }})" icon="pencil">
                                    Edit
                                </flux:button>
                            @endcan
                            @can('delete', $post)
                                <flux:button wire:click="deletePost({{ $post->id }})" 
                                    icon="trash" 
                                    variant="danger"
                                    wire:confirm="Are you sure you want to delete this post?">
                                    Delete
                                </flux:button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $posts->links() }}
        </div>
    </div>

    <!-- Create Modal -->
    <x-modal-dialog wire:model="showCreateModal">
        <x-slot name="title">Create New Post</x-slot>
        <x-slot name="content">
            <form wire:submit="createPost">
                <div class="space-y-4">
                    <div>
                        <x-label for="title" value="Title" />
                        <x-input id="title" wire:model="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error for="title" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="content" value="Content" />
                        <x-textarea id="content" wire:model="content" class="mt-1 block w-full" rows="6" required />
                        <x-input-error for="content" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="featuredImage" value="Featured Image URL" />
                        <x-input id="featuredImage" wire:model="featuredImage" type="url" class="mt-1 block w-full" />
                        <x-input-error for="featuredImage" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <x-secondary-button wire:click="$set('showCreateModal', false)">Cancel</x-secondary-button>
                    <x-button type="submit">Create Post</x-button>
                </div>
            </form>
        </x-slot>
    </x-modal-dialog>

    <!-- Edit Modal -->
    <x-modal-dialog wire:model="showEditModal">
        <x-slot name="title">Edit Post</x-slot>
        <x-slot name="content">
            <form wire:submit="updatePost">
                <div class="space-y-4">
                    <div>
                        <x-label for="edit-title" value="Title" />
                        <x-input id="edit-title" wire:model="title" type="text" class="mt-1 block w-full" required />
                        <x-input-error for="title" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit-content" value="Content" />
                        <x-textarea id="edit-content" wire:model="content" class="mt-1 block w-full" rows="6" required />
                        <x-input-error for="content" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="edit-featuredImage" value="Featured Image URL" />
                        <x-input id="edit-featuredImage" wire:model="featuredImage" type="url" class="mt-1 block w-full" />
                        <x-input-error for="featuredImage" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <x-secondary-button wire:click="$set('showEditModal', false)">Cancel</x-secondary-button>
                    <x-button type="submit">Update Post</x-button>
                </div>
            </form>
        </x-slot>
    </x-modal-dialog>
</div>
