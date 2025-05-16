<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogPostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view blog posts
    }

    public function view(?User $user, BlogPost $blogPost): bool
    {
        if ($blogPost->is_published) {
            return true;
        }

        return $user && $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $user->hasRole('admin');
    }
}
