<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostReportResource\Pages;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostReportResource extends Resource
{
    protected static ?string $model = PostReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                Select::make('offense_type')
                    ->label('Offense Type')
                    ->options([
                        'spam' => 'Spam',
                        'harassment' => 'Harassment',
                        'hate_speech' => 'Hate Speech',
                        'nudity' => 'Nudity',
                        'violence' => 'Violence',
                    ])
                    ->required(),

                Select::make('user_id')
                    ->label('Reported By')
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
        return $table->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('offense_type')
                ->label('Offense')
                ->sortable()
                ->badge()
                ->colors([
                    'danger' => fn ($state) => $state === 'harassment' || $state === 'hate_speech' || $state === 'violence',
                    'warning' => fn ($state) => $state === 'spam' || $state === 'nudity',
                    'primary' => fn ($state) => $state === 'hate_speech',
                    'secondary' => fn ($state) => $state === null,
                ]),

            TextColumn::make('user.name')->label('Reported By')->searchable(),
            TextColumn::make('post.title')->label('Post')->searchable()->limit(30),
            TextColumn::make('created_at')->label('Reported At')->dateTime()->sortable(),
        ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostReports::route('/'),
            'create' => Pages\CreatePostReport::route('/create'),
            'edit' => Pages\EditPostReport::route('/{record}/edit'),
        ];
    }
}
