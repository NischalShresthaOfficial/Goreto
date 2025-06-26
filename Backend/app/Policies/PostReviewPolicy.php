<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PostReview;

class PostReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_post::review');
    }

    public function view(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('view_post::review');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_post::review');
    }

    public function update(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('update_post::review');
    }

    public function delete(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('delete_post::review');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_post::review');
    }

    public function forceDelete(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('force_delete_post::review');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_post::review');
    }

    public function restore(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('restore_post::review');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_post::review');
    }

    public function replicate(User $user, PostReview $postReview): bool
    {
        return $user->hasPermissionTo('replicate_post::review');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_post::review');
    }
}
