<?php

namespace Tests\Feature;

use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrinhoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductSeeder::class, PaymentMethodSeeder::class]);
    }

    public function test_lista_produtos_sem_wrapper(): void
    {
        $this->getJson('/api/produtos')
            ->assertOk()
            ->assertJsonCount(9)
            ->assertJsonStructure([['slug', 'name', 'collection', 'family', 'wax', 'sizes' => [['id', 'key', 'label', 'price']]]]);
    }

    public function test_cria_carrinho_sem_token_e_devolve_token(): void
    {
        $response = $this->getJson('/api/carrinho')->assertOk();

        $this->assertNotEmpty($response->json('token'));
        $this->assertSame(0, $response->json('count'));
        $this->assertSame($response->json('token'), $response->headers->get('X-Cart-Token'));
    }

    public function test_adiciona_soma_e_atualiza_item(): void
    {
        $token = $this->getJson('/api/carrinho')->json('token');
        $headers = ['X-Cart-Token' => $token];

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 2], $headers)
            ->assertCreated()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('subtotal', 364)
            ->assertJsonPath('items.0.product.name', 'Pitanga');

        $itemId = $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $headers)
            ->assertJsonPath('items.0.quantity', 3)
            ->json('items.0.id');

        $this->patchJson("/api/carrinho/itens/{$itemId}", ['quantity' => 1], $headers)
            ->assertOk()
            ->assertJsonPath('items.0.quantity', 1);

        $this->patchJson("/api/carrinho/itens/{$itemId}", ['quantity' => 0], $headers)
            ->assertOk()
            ->assertJsonPath('count', 0);
    }

    public function test_nao_altera_item_de_outro_carrinho(): void
    {
        $tokenA = $this->getJson('/api/carrinho')->json('token');
        $itemId = $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'm', 'quantity' => 1], ['X-Cart-Token' => $tokenA])
            ->json('items.0.id');

        $tokenB = $this->getJson('/api/carrinho')->json('token');

        $this->patchJson("/api/carrinho/itens/{$itemId}", ['quantity' => 5], ['X-Cart-Token' => $tokenB])
            ->assertNotFound();
    }

    public function test_respeita_quantidade_maxima(): void
    {
        $token = $this->getJson('/api/carrinho')->json('token');

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 11], ['X-Cart-Token' => $token])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }
}
