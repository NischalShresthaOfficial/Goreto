<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Location;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_location');
    }

    public function view(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('view_location');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_location');
    }

    public function update(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('update_location');
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('delete_location');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_location');
    }

    public function forceDelete(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('force_delete_location');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_location');
    }

    public function restore(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('restore_location');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_location');
    }

    public function replicate(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('replicate_location');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_location');
    }
}
