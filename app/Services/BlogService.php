<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BlogService
{
    public function getPublishedPosts(int $perPage = 10): LengthAwarePaginator
    {
        return BlogPost::with('author')
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getFeaturedPosts(int $limit = 3): Collection
    {
        return BlogPost::with('author')
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function getAllPosts(int $perPage = 10): LengthAwarePaginator
    {
        return BlogPost::with('author')
            ->latest()
            ->paginate($perPage);
    }

    public function getDrafts(int $perPage = 10): LengthAwarePaginator
    {
        return BlogPost::with('author')
            ->whereNull('published_at')
            ->latest()
            ->paginate($perPage);
    }

    public function createPost(array $data, int $authorId): BlogPost
    {
        $data['author_id'] = $authorId;
        return BlogPost::create($data);
    }

    public function updatePost(BlogPost $post, array $data): bool
    {
        return $post->update($data);
    }

    public function publishPost(BlogPost $post): bool
    {
        return $post->update(['published_at' => now()]);
    }

    public function unpublishPost(BlogPost $post): bool
    {
        return $post->update(['published_at' => null]);
    }

    public function deletePost(BlogPost $post): bool
    {
        return $post->delete();
    }

    public function toggleFeatured(BlogPost $post): bool
    {
        return $post->update(['is_featured' => !$post->is_featured]);
    }
}
