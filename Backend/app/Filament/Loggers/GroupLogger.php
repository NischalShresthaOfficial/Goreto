<?php

namespace App\Filament\Loggers;

use App\Models\Group;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;

class GroupLogger extends Logger
{
    public static ?string $model = Group::class;

    public static function getLabel(): string|Htmlable|null
    {
        return 'Group';
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('name')->label('Group Name'),
                Field::make('created_by')->label('Created By'),
            ]);
    }
}
