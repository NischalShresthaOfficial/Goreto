<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Groups';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('created_by')
                    ->label('Created By')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

                Repeater::make('userGroups')
                    ->relationship()
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->options(fn () => \App\Models\User::pluck('name', 'id'))
                            ->searchable(),

                        Select::make('member_role')
                            ->label('Role')
                            ->options([
                                'admin' => 'Admin',
                                'member' => 'Member',
                            ])
                            ->default('member'),
                    ])
                    ->columns(2)
                    ->label('Group Members')
                    ->collapsible()
                    ->collapsed(false),

                Repeater::make('groupLocations')
                    ->relationship()
                    ->schema([
                        Select::make('location_id')
                            ->label('Location')
                            ->options(Location::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(1)
                    ->label('Add Location')
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
                TextColumn::make('created_by')->sortable()->searchable(),
                TextColumn::make('members')
                    ->label('Members')
                    ->getStateUsing(function ($record) {
                        $creatorId = $record->created_by;
                        $members = $record->userGroups()->with('user')->get()->pluck('user.name')->filter();
                        if ($creatorId && ! $members->contains(function ($name) use ($creatorId) {
                            $creatorName = \App\Models\User::find($creatorId)?->name;

                            return $name === $creatorName;
                        })) {
                            $creatorName = \App\Models\User::find($creatorId)?->name;
                            if ($creatorName) {
                                $members->prepend($creatorName);
                            }
                        }

                        return $members->isNotEmpty() ? $members->implode(', ') : 'No members';
                    })
                    ->wrap()
                    ->tooltip(fn ($state) => $state),

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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
