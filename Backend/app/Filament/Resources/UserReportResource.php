<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserReportResource\Pages;
use App\Models\User;
use App\Models\UserReport;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserReportResource extends Resource
{
    protected static ?string $model = UserReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $label = 'User Report';

    protected static ?string $pluralLabel = 'User Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Select::make('issue_type')
                        ->options([
                            'bug' => 'Bug',
                            'security' => 'Security',
                            'feature_request' => 'Feature Request',
                            'performance' => 'Performance',
                        ])
                        ->required()
                        ->label('Issue Type'),

                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->required(),

                    Select::make('user_id')
                        ->label('Reported By')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('issue_type')
                    ->label('Type')
                    ->sortable()
                    ->badge(),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Reported At'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListUserReports::route('/'),
            'create' => Pages\CreateUserReport::route('/create'),
            'edit' => Pages\EditUserReport::route('/{record}/edit'),
        ];
    }
}
