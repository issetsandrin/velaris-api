<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContatoTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    private array $contato = ['nome' => 'Ana Silva', 'telefone' => '(41) 99999-0000', 'cpf' => '529.982.247-25'];

    protected function setUp(): void
    {
        parent::setUp();
        $token = $this->postJson('/api/auth/registrar', [
            'nome' => 'Ana', 'email' => 'ana@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');
        $this->headers = ['Authorization' => 'Bearer '.$token];
    }

    public function test_cria_contato_normalizando_e_primeiro_vira_padrao(): void
    {
        $this->postJson('/api/conta/contatos', $this->contato, $this->headers)
            ->assertCreated()
            ->assertJsonPath('phone', '41999990000')
            ->assertJsonPath('cpf', '52998224725')
            ->assertJsonPath('isDefault', true)
            ->assertJsonPath('complete', true);
    }

    public function test_recusa_cpf_invalido(): void
    {
        $this->postJson('/api/conta/contatos', [...$this->contato, 'cpf' => '111.111.111-11'], $this->headers)
            ->assertUnprocessable()->assertJsonValidationErrors('cpf');

        $this->postJson('/api/conta/contatos', [...$this->contato, 'cpf' => '529.982.247-26'], $this->headers)
            ->assertUnprocessable()->assertJsonValidationErrors('cpf');
    }

    public function test_tornar_padrao_e_remover(): void
    {
        $a = $this->postJson('/api/conta/contatos', $this->contato, $this->headers)->json('id');
        $b = $this->postJson('/api/conta/contatos', [...$this->contato, 'nome' => 'Bia'], $this->headers)->json('id');

        $this->patchJson("/api/conta/contatos/{$b}", [...$this->contato, 'padrao' => true], $this->headers)
            ->assertOk()->assertJsonPath('isDefault', true);
        $this->assertDatabaseHas('contacts', ['id' => $a, 'is_default' => false]);

        $this->deleteJson("/api/conta/contatos/{$b}", [], $this->headers)->assertNoContent();
        $this->assertDatabaseHas('contacts', ['id' => $a, 'is_default' => true]);
    }

    public function test_nao_acessa_contato_de_outro_cliente(): void
    {
        $id = $this->postJson('/api/conta/contatos', $this->contato, $this->headers)->json('id');
        $outro = $this->postJson('/api/auth/registrar', [
            'nome' => 'Bia', 'email' => 'bia@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');

        $this->deleteJson("/api/conta/contatos/{$id}", [], ['Authorization' => 'Bearer '.$outro])->assertNotFound();
    }
}
