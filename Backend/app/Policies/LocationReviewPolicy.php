<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LocationReview;

class LocationReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_location::review');
    }

    public function view(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('view_location::review');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_location::review');
    }

    public function update(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('update_location::review');
    }

    public function delete(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('delete_location::review');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_location::review');
    }

    public function forceDelete(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('force_delete_location::review');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_location::review');
    }

    public function restore(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('restore_location::review');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_location::review');
    }

    public function replicate(User $user, LocationReview $review): bool
    {
        return $user->hasPermissionTo('replicate_location::review');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_location::review');
    }
}
