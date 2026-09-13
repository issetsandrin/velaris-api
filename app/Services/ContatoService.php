<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use App\Rules\Cpf;

class ContatoService
{
    /**
     * @param  array{nome: string, telefone: string, whatsapp?: string|null, cpf: string, padrao?: bool}  $dados
     */
    public function criar(User $user, array $dados): Contact
    {
        $primeiro = ! $user->contacts()->exists();

        $contact = $user->contacts()->create([
            ...$this->normalizar($dados),
            'is_default' => $primeiro,
        ]);

        if (! $primeiro && ($dados['padrao'] ?? false)) {
            $contact->tornarPadrao();
        }

        return $contact->refresh();
    }

    /**
     * @param  array{nome: string, telefone: string, whatsapp?: string|null, cpf: string, padrao?: bool}  $dados
     */
    public function atualizar(Contact $contact, array $dados): Contact
    {
        $contact->update($this->normalizar($dados));

        if ($dados['padrao'] ?? false) {
            $contact->tornarPadrao();
        }

        return $contact->refresh();
    }

    public function remover(Contact $contact): void
    {
        $user = $contact->user;
        $eraPadrao = $contact->is_default;

        $contact->delete();

        if ($eraPadrao) {
            $user->contacts()->first()?->update(['is_default' => true]);
        }
    }

    /** @return array<string, string|null> */
    private function normalizar(array $dados): array
    {
        return [
            'name' => trim($dados['nome']),
            'phone' => Cpf::limpar($dados['telefone']),
            'whatsapp' => filled($dados['whatsapp'] ?? null) ? Cpf::limpar($dados['whatsapp']) : null,
            'cpf' => Cpf::limpar($dados['cpf']),
        ];
    }
}
