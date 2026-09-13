<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;

class EnderecoService
{
    /**
     * @param  array{apelido?: string|null, cep: string, cidade: string, endereco: string, numero: string, complemento?: string|null, padrao?: bool}  $dados
     */
    public function criar(User $user, array $dados): Address
    {
        $primeiro = ! $user->addresses()->exists();

        $address = $user->addresses()->create([
            'label' => $dados['apelido'] ?? null,
            'postal_code' => preg_replace('/\D/', '', $dados['cep']),
            'city' => $dados['cidade'],
            'neighborhood' => $dados['bairro'] ?? null,
            'street' => $dados['endereco'],
            'street_number' => $dados['numero'],
            'complement' => $dados['complemento'] ?? null,
            'is_default' => $primeiro,
        ]);

        if (! $primeiro && ($dados['padrao'] ?? false)) {
            $address->tornarPadrao();
        }

        return $address->refresh();
    }

    /**
     * @param  array{apelido?: string|null, cep: string, cidade: string, endereco: string, numero: string, complemento?: string|null, padrao?: bool}  $dados
     */
    public function atualizar(Address $address, array $dados): Address
    {
        $address->update([
            'label' => $dados['apelido'] ?? null,
            'postal_code' => preg_replace('/\D/', '', $dados['cep']),
            'city' => $dados['cidade'],
            'neighborhood' => $dados['bairro'] ?? null,
            'street' => $dados['endereco'],
            'street_number' => $dados['numero'],
            'complement' => $dados['complemento'] ?? null,
        ]);

        if ($dados['padrao'] ?? false) {
            $address->tornarPadrao();
        }

        return $address->refresh();
    }

    public function remover(Address $address): void
    {
        $user = $address->user;
        $eraPadrao = $address->is_default;

        $address->delete();

        if ($eraPadrao) {
            $user->addresses()->first()?->update(['is_default' => true]);
        }
    }
}
