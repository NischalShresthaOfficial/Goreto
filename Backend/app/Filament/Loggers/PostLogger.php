<?php

namespace App\Filament\Loggers;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Illuminate\Contracts\Support\Htmlable;
use Noxo\FilamentActivityLog\Loggers\Logger;
use Noxo\FilamentActivityLog\ResourceLogger\Field;
use Noxo\FilamentActivityLog\ResourceLogger\ResourceLogger;
use Spatie\Activitylog\Models\Activity;

class PostLogger extends Logger
{
    public static ?string $model = Post::class;

    public static function getLabel(): string|Htmlable|null
    {
        return 'Post';
    }

    public function getSubjectRoute(Activity $activity): ?string
    {
        return PostResource::getUrl('edit', ['record' => $activity->subject_id]);
    }

    public static function resource(ResourceLogger $logger): ResourceLogger
    {
        return $logger
            ->fields([
                Field::make('title')
                    ->label('Title'),

                Field::make('description')
                    ->label('Description'),

                Field::make('likes')
                    ->label('Likes'),

                Field::make('user.name')
                    ->label('Author'),
            ]);
    }
}
