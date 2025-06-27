<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationReviewResource\Pages;
use App\Models\LocationReview;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationReviewResource extends Resource
{
    protected static ?string $model = LocationReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Locations';

    protected static ?string $navigationLabel = 'Location Reviews';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make([
                Select::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Textarea::make('review')
                    ->label('Review')
                    ->required()
                    ->maxLength(65535),

                TextInput::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('id')->sortable(),

            TextColumn::make('location.name')->label('Location')->sortable()->searchable(),

            TextColumn::make('user.name')->label('User')->sortable()->searchable(),

            TextColumn::make('review')->limit(50)->wrap(),

            TextColumn::make('rating')->sortable(),

            TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocationReviews::route('/'),
            'create' => Pages\CreateLocationReview::route('/create'),
            'edit' => Pages\EditLocationReview::route('/{record}/edit'),
        ];
    }
}
