<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    public const ALINHAMENTOS = [
        'esquerda' => 'Esquerda',
        'centro' => 'Centro',
        'direita' => 'Direita',
    ];

    protected $fillable = [
        'image_path', 'title', 'text', 'button_label', 'button_link', 'text_align', 'active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('position')->orderBy('id');
    }

    /** Endereço completo da imagem, para a loja usar direto no <img>. */
    public function url(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
