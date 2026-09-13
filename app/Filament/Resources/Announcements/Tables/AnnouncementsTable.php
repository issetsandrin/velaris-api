<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Aviso')->weight('bold')->description(fn (Announcement $record): string => $record->body)->wrap(),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => Announcement::TIPOS[$state] ?? $state),
                TextColumn::make('starts_at')->label('A partir de')->dateTime('d/m/Y H:i')->placeholder('já vale'),
                TextColumn::make('ends_at')->label('Até')->dateTime('d/m/Y H:i')->placeholder('sem fim'),
                ToggleColumn::make('active')->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
