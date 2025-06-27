<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostReviewResource\Pages;
use App\Models\Post;
use App\Models\PostReview;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostReviewResource extends Resource
{
    protected static ?string $model = PostReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Post Reviews';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make([
                Textarea::make('review')
                    ->label('Review')
                    ->required()
                    ->maxLength(65535),

                Select::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('post_id')
                    ->label('Post')
                    ->options(Post::pluck('title', 'id'))
                    ->searchable()
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('review')->limit(50),
                TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                TextColumn::make('post.title')->label('Post')->sortable()->searchable(),
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
            'index' => Pages\ListPostReviews::route('/'),
            'create' => Pages\CreatePostReview::route('/create'),
            'edit' => Pages\EditPostReview::route('/{record}/edit'),
        ];
    }
}
