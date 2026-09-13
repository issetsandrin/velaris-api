<?php

namespace App\Filament\Resources\Announcements\Schemas;

use App\Models\Announcement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aviso')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Título')->required()->maxLength(120)->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Texto')
                            ->required()
                            ->maxLength(400)
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Aparece na gaveta de avisos da loja, abaixo do título.'),
                        Select::make('type')
                            ->label('Tipo')
                            ->options(Announcement::TIPOS)
                            ->default('promocao')
                            ->required()
                            ->native(false)
                            ->helperText('Define o ícone mostrado na loja.'),
                        TextInput::make('link')
                            ->label('Link')
                            ->placeholder('/colecao?promocao=1')
                            ->maxLength(200)
                            ->helperText('Para onde o aviso leva ao ser clicado. Opcional.'),
                    ]),
                Section::make('Quando aparece')
                    ->columns(3)
                    ->schema([
                        Toggle::make('active')->label('Ativo')->default(true)->inline(false),
                        DateTimePicker::make('starts_at')
                            ->label('A partir de')
                            ->seconds(false)
                            ->helperText('Vazio: já vale.'),
                        DateTimePicker::make('ends_at')
                            ->label('Até')
                            ->seconds(false)
                            ->after('starts_at')
                            ->helperText('Vazio: fica no ar até ser desativado.'),
                    ]),
            ]);
    }
}
