<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\RelationManager;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class UserLogger extends Logger
{
    public static ?string $model = User::class;

    public static function getLabel(): string|Htmlable|null
    {
          return 'User';
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('Name'),
                Field::make('email')->label('Email'),
                Field::make('email_verified_at')->label('Verified At'),
                Field::make('role.name')->label('Role')->badge(),
                Field::make('country.country')->label('Country')->badge(),
            ])
            ->relationManagers([
                RelationManager::make('profilePicture')
                    ->label('Profile Pictures')
                    ->fields([
                        Field::make('profile_picture_url')->label('Picture URL'),
                        Field::make('is_active')->label('Active')->boolean(),
                    ]),
            ]);
    }
}
