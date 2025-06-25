<?php

namespace App\Filament\Resources\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Noxo\FilamentActivityLog\Pages\ListActivities;

class ActivityLog extends ListActivities
{
    protected static ?string $navigationIcon = 'heroicon-m-inbox-stack';

    protected static ?string $navigationGroup = 'System Logs';

    protected function getTableQuery()
    {
        return parent::getTableQuery()
            ->with('causer')
            ->where('log_name', 'user');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('log_name')->label('Log Name')->sortable(),
                TextColumn::make('description')->limit(50),
                TextColumn::make('event')->sortable(),
                TextColumn::make('subject_type')->label('Model'),
                TextColumn::make('causer.name')->label('Causer')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->pagination(10);
    }
}
