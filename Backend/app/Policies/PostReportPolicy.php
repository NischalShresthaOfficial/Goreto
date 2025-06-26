<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PostReport;

class PostReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_post::report');
    }

    public function view(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('view_post::report');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_post::report');
    }

    public function update(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('update_post::report');
    }

    public function delete(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('delete_post::report');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('delete_any_post::report');
    }

    public function forceDelete(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('force_delete_post::report');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasPermissionTo('force_delete_any_post::report');
    }

    public function restore(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('restore_post::report');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('restore_any_post::report');
    }

    public function replicate(User $user, PostReport $postReport): bool
    {
        return $user->hasPermissionTo('replicate_post::report');
    }

    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('reorder_post::report');
    }
}
