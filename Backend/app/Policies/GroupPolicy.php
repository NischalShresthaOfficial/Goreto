<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Group;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_group');
    }

    public function view(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('view_group');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_group');
    }

    public function update(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('update_group');
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('delete_group');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_group');
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('force_delete_group');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_group');
    }

    public function restore(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('restore_group');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_group');
    }

    public function replicate(User $user, Group $group): bool
    {
        return $user->hasPermissionTo('replicate_group');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_group');
    }
}
