<?php

namespace App\Filament\Resources\PostReviewResource\Pages;

use App\Filament\Resources\PostReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostReviews extends ListRecords
{
    protected static string $resource = PostReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
