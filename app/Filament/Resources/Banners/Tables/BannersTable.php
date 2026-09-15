<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                ImageColumn::make('image_path')->label('Arte')->disk('public')->height(48),
                TextColumn::make('title')->label('Título')->weight('bold')->placeholder('sem título')->wrap(),
                TextColumn::make('button_label')->label('Botão')->placeholder('—'),
                TextColumn::make('text_align')->label('Texto')->badge()->formatStateUsing(fn (string $state): string => \App\Models\Banner::ALINHAMENTOS[$state] ?? $state),
                ToggleColumn::make('active')->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
