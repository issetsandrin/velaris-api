<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Models\Banner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Imagem')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Arte do banner')
                            ->image()
                            ->imageEditor()
                            ->directory('banners')
                            ->disk('public')
                            ->maxSize(4096)
                            ->required()
                            ->helperText('Fica no fundo, ocupando a largura da tela. Use algo largo, a partir de 1600 x 900.'),
                    ]),
                Section::make('Texto por cima')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->label('Título')->maxLength(120)->columnSpanFull(),
                        Textarea::make('text')->label('Frase de apoio')->maxLength(300)->rows(2)->columnSpanFull(),
                        TextInput::make('button_label')->label('Texto do botão')->placeholder('Ver a coleção')->maxLength(40),
                        TextInput::make('button_link')->label('Link do botão')->placeholder('/colecao?colecao=noite')->maxLength(200),
                        Radio::make('text_align')
                            ->label('Posição do texto')
                            ->options(Banner::ALINHAMENTOS)
                            ->default('esquerda')
                            ->inline()
                            ->inlineLabel(false)
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Onde o título, a frase e o botão ficam sobre a imagem.'),
                    ]),
                Section::make('Exibição')
                    ->columns(2)
                    ->schema([
                        Toggle::make('active')->label('Ativo')->default(true)->inline(false),
                        TextInput::make('position')->label('Ordem')->numeric()->minValue(0)->default(0),
                    ]),
            ]);
    }
}
