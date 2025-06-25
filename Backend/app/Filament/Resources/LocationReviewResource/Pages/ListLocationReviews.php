<?php

namespace App\Filament\Resources\LocationReviewResource\Pages;

use App\Filament\Resources\LocationReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLocationReviews extends ListRecords
{
    protected static string $resource = LocationReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
