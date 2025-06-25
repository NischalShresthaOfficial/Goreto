<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Location;
use App\Models\Post;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-c-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Posts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                MarkdownEditor::make('description')
                    ->required()
                    ->maxLength(65535),

                TextInput::make('likes')
                    ->numeric()
                    ->default(0),

                Repeater::make('postLocations')
                    ->relationship()
                    ->label('Post Locations')
                    ->schema([
                        Select::make('location_id')
                            ->label('Location')
                            ->options(Location::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(1)
                    ->label('Add Location'),

                Repeater::make('postContents')
                    ->relationship()
                    ->label('Post Contents')
                    ->schema([
                        FileUpload::make('content_path')
                            ->label('Content File')
                            ->directory('post-contents')
                            ->required(),
                    ])
                    ->columns(1)
                    ->label('Add Content'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('description')->limit(50),
                TextColumn::make('likes')->sortable(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
