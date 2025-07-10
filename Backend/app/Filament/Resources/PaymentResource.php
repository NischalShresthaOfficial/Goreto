<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Payments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Select::make('user_id')
                        ->options(function () {
                            return \App\Models\User::pluck('name', 'id')->toArray();
                        })
                        ->required()
                        ->searchable(),

                    Select::make('subscription_id')
                        ->options(function () {
                            return \App\Models\Subscription::pluck('name', 'id')->toArray();
                        })
                        ->required()
                        ->searchable(),

                    TextInput::make('stripe_payment_id')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('amount')
                        ->numeric()
                        ->required(),

                    TextInput::make('currency')
                        ->required()
                        ->maxLength(10),

                    Select::make('status')
                        ->options([
                            'requires_payment_method' => 'Requires Payment Method',
                            'requires_confirmation' => 'Requires Confirmation',
                            'requires_action' => 'Requires Action',
                            'processing' => 'Processing',
                            'requires_capture' => 'Requires Capture',
                            'canceled' => 'Canceled',
                            'succeeded' => 'Succeeded',
                        ])
                        ->required(),

                    TextInput::make('payment_method')
                        ->maxLength(255),

                    DateTimePicker::make('paid_at')
                        ->required(),

                    DateTimePicker::make('expires_at')
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('User')->searchable()->sortable(),
                TextColumn::make('subscription.name')->label('Subscription')->searchable()->sortable(),
                TextColumn::make('amount')->money('usd', true),
                TextColumn::make('currency')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'succeeded',
                        'warning' => 'pending',
                        'danger' => 'failed',
                        'primary' => 'requires_payment_method',
                    ]),
                TextColumn::make('payment_method')->sortable(),
                TextColumn::make('paid_at')->dateTime()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
