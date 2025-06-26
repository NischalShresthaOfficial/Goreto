<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Country;

class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_country');
    }

    public function view(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('view_country');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_country');
    }

    public function update(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('update_country');
    }

    public function delete(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('delete_country');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_country');
    }

    public function forceDelete(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('force_delete_country');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_country');
    }

    public function restore(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('restore_country');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_country');
    }

    public function replicate(User $user, Country $country): bool
    {
        return $user->hasPermissionTo('replicate_country');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_country');
    }
}
