<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExchangeApiKeyResource\Pages;
use App\Models\ExchangeApiKey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExchangeApiKeyResource extends Resource
{
    protected static ?string $model = ExchangeApiKey::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'API Ключи';
    protected static ?string $navigationGroup = 'Биржи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('exchange_id')
                    ->label('Биржа')
                    ->relationship('exchange', 'name')
                    ->required(),
                Forms\Components\TextInput::make('api_key')
                    ->label('API Ключ')
                    ->required()
                    ->maxLength(500),
                Forms\Components\TextInput::make('api_secret')
                    ->label('API Секрет')
                    ->required()
                    ->password()
                    ->maxLength(500),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exchange.name')
                    ->label('Биржа')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('api_key')
                    ->label('API Ключ')
                    ->formatStateUsing(fn(string $state): string => '••••' . substr($state, -4)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exchange')
                    ->label('Биржа')
                    ->relationship('exchange', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Редактирование API ключа')
                    ->modalDescription('Внимание! Вы редактируете секретные данные.'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Внимание! Удаление ключа может нарушить работу системы.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalDescription('Внимание! Удаление ключей может нарушить работу системы.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExchangeApiKeys::route('/'),
            'create' => Pages\CreateExchangeApiKey::route('/create'),
            'edit' => Pages\EditExchangeApiKey::route('/{record}/edit'),
        ];
    }
}
