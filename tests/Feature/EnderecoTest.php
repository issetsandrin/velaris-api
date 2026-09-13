<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnderecoTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    private array $endereco = [
        'apelido' => 'Casa',
        'cep' => '80000-000',
        'cidade' => 'Curitiba',
        'bairro' => 'Centro',
        'endereco' => 'Rua das Velas',
        'numero' => '12',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $token = $this->postJson('/api/auth/registrar', [
            'nome' => 'Ana', 'email' => 'ana@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');

        $this->headers = ['Authorization' => 'Bearer '.$token];
    }

    public function test_primeiro_endereco_vira_padrao_e_o_segundo_nao(): void
    {
        $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)
            ->assertCreated()
            ->assertJsonPath('isDefault', true)
            ->assertJsonPath('postalCode', '80000000');

        $this->postJson('/api/conta/enderecos', [...$this->endereco, 'apelido' => 'Trabalho'], $this->headers)
            ->assertCreated()
            ->assertJsonPath('isDefault', false);

        $this->getJson('/api/conta/enderecos', $this->headers)->assertJsonCount(2);
    }

    public function test_tornar_padrao_desmarca_o_anterior(): void
    {
        $primeiro = $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)->json('id');
        $segundo = $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)->json('id');

        $this->patchJson("/api/conta/enderecos/{$segundo}", [...$this->endereco, 'padrao' => true], $this->headers)
            ->assertOk()
            ->assertJsonPath('isDefault', true);

        $this->assertDatabaseHas('addresses', ['id' => $primeiro, 'is_default' => false]);
    }

    public function test_remover_o_padrao_promove_outro(): void
    {
        $primeiro = $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)->json('id');
        $segundo = $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)->json('id');

        $this->deleteJson("/api/conta/enderecos/{$primeiro}", [], $this->headers)->assertNoContent();

        $this->assertDatabaseHas('addresses', ['id' => $segundo, 'is_default' => true]);
    }

    public function test_nao_mexe_em_endereco_de_outro_cliente(): void
    {
        $id = $this->postJson('/api/conta/enderecos', $this->endereco, $this->headers)->json('id');
        $outro = $this->postJson('/api/auth/registrar', [
            'nome' => 'Bia', 'email' => 'bia@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');

        $this->deleteJson("/api/conta/enderecos/{$id}", [], ['Authorization' => 'Bearer '.$outro])->assertNotFound();
    }
}
