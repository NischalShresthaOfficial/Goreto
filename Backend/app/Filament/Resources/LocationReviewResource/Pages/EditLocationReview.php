<?php

namespace App\Filament\Resources\LocationReviewResource\Pages;

use App\Filament\Resources\LocationReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLocationReview extends EditRecord
{
    protected static string $resource = LocationReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
