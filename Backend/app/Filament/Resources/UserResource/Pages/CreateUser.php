<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleId = $data['role_id'];
        unset($data['role_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record && $this->roleId) {
            $role = Role::find($this->roleId);
            if ($role) {
                $this->record->syncRoles([$role->name]);
                $this->record->role_id = $role->id;
                $this->record->save();
            }
        }
    }

    protected int $roleId;
}
