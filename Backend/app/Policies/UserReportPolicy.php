<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserReport;

class UserReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_user::report');
    }

    public function view(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('view_user::report');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_user::report');
    }

    public function update(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('update_user::report');
    }

    public function delete(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('delete_user::report');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_user::report');
    }

    public function forceDelete(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('force_delete_user::report');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_user::report');
    }

    public function restore(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('restore_user::report');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_user::report');
    }

    public function replicate(User $user, UserReport $userReport): bool
    {
        return $user->hasPermissionTo('replicate_user::report');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_user::report');
    }
}
