<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_category');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('view_category');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_category');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('update_category');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('delete_category');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_category');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('force_delete_category');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_category');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('restore_category');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_category');
    }

    public function replicate(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('replicate_category');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_category');
    }
}
