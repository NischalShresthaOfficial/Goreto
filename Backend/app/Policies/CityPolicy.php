<?php

namespace App\Policies;

use App\Models\User;
use App\Models\City;

class CityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_city');
    }

    public function view(User $user, City $city): bool
    {
        return $user->hasPermissionTo('view_city');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_city');
    }

    public function update(User $user, City $city): bool
    {
        return $user->hasPermissionTo('update_city');
    }

    public function delete(User $user, City $city): bool
    {
        return $user->hasPermissionTo('delete_city');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_city');
    }

    public function forceDelete(User $user, City $city): bool
    {
        return $user->hasPermissionTo('force_delete_city');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_city');
    }

    public function restore(User $user, City $city): bool
    {
        return $user->hasPermissionTo('restore_city');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_city');
    }

    public function replicate(User $user, City $city): bool
    {
        return $user->hasPermissionTo('replicate_city');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_city');
    }
}
