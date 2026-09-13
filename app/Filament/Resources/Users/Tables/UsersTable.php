<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('contacts_count')->label('Contatos')->counts('contacts'),
                TextColumn::make('orders_count')->label('Pedidos')->counts('orders')->sortable(),
                IconColumn::make('google_id')->label('Google')->boolean()->state(fn ($record): bool => filled($record->google_id)),
                IconColumn::make('is_admin')->label('Admin')->boolean(),
                TextColumn::make('created_at')->label('Cadastro')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Administrador'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn ($record): bool => $record->id === auth()->id()),
            ]);
    }
}
