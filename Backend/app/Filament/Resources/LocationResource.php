<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Locations';

    protected static ?string $navigationLabel = 'Locations';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('latitude')
                    ->required()
                    ->numeric()
                    ->rule('between:-90,90'),

                TextInput::make('longitude')
                    ->required()
                    ->numeric()
                    ->rule('between:-180,180'),

                Select::make('city_id')
                    ->label('City')
                    ->relationship('city', 'city')
                    ->required()
                    ->searchable()
                    ->preload(),

                Repeater::make('locationImages')
                    ->relationship()
                    ->label('Location Images')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Image')
                            ->image()
                            ->required()
                            ->directory('location-images'),
                    ])
                    ->columns(1)
                    ->label('Add Image')
                    ->collapsible()
                    ->collapsed(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('city.city')->label('City')->sortable()->searchable(),

                TextColumn::make('latitude')->sortable(),
                TextColumn::make('longitude')->sortable(),

                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
