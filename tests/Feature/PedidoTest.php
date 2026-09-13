<?php

namespace Tests\Feature;

use App\Models\Order;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    private array $dados = [
        'nome' => 'Teste Silva',
        'telefone' => '41999990000',
        'cpf' => '529.982.247-25',
        'cep' => '80000-000',
        'cidade' => 'Curitiba',
        'bairro' => 'Centro',
        'endereco' => 'Rua das Velas',
        'numero' => '12',
        'pagamento' => 'pix',
    ];

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductSeeder::class, PaymentMethodSeeder::class]);

        $token = $this->postJson('/api/auth/registrar', [
            'nome' => 'Teste Silva',
            'email' => 'teste@exemplo.com',
            'senha' => 'segredo123',
            'senha_confirmation' => 'segredo123',
        ])->json('token');

        $this->headers = ['Authorization' => 'Bearer '.$token];
    }

    public function test_cria_pedido_com_desconto_pix_salva_endereco_e_esvazia_carrinho(): void
    {
        $headers = $this->headers;
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $headers);

        $response = $this->postJson('/api/pedidos', $this->dados, $headers)
            ->assertCreated()
            ->assertJsonPath('subtotal', 182)
            ->assertJsonPath('discount', 9.1)
            ->assertJsonPath('shipping', 0)
            ->assertJsonPath('total', 172.9)
            ->assertJsonCount(1, 'items');

        $this->assertStringStartsWith('VL', $response->json('number'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('addresses', 1);
        $this->getJson('/api/carrinho', $headers)->assertJsonPath('count', 0);
        $this->getJson('/api/auth/eu', $headers)
            ->assertJsonPath('addresses.0.street', 'Rua das Velas')
            ->assertJsonPath('addresses.0.isDefault', true);
    }

    public function test_cria_pedido_com_endereco_salvo(): void
    {
        $headers = $this->headers;
        $enderecoId = $this->postJson('/api/conta/enderecos', [
            'apelido' => 'Casa', 'cep' => '80000000', 'cidade' => 'Curitiba',
            'bairro' => 'Centro', 'endereco' => 'Rua Salva', 'numero' => '7',
        ], $headers)->assertCreated()->json('id');
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $headers);

        $this->postJson('/api/pedidos', [
            'nome' => 'Teste Silva', 'telefone' => '41999990000', 'cpf' => '52998224725', 'pagamento' => 'pix', 'endereco_id' => $enderecoId,
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('address.street', 'Rua Salva');

        $this->assertDatabaseCount('addresses', 1);
    }

    public function test_recusa_endereco_salvo_de_outro_cliente(): void
    {
        $outro = $this->postJson('/api/auth/registrar', [
            'nome' => 'Outra', 'email' => 'outra@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');
        $enderecoId = $this->postJson('/api/conta/enderecos', [
            'cep' => '80000000', 'cidade' => 'Curitiba',
            'bairro' => 'Centro', 'endereco' => 'Rua Alheia', 'numero' => '1',
        ], ['Authorization' => 'Bearer '.$outro])->json('id');
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $this->headers);

        $this->postJson('/api/pedidos', [
            'nome' => 'Teste Silva', 'telefone' => '41999990000', 'cpf' => '52998224725', 'pagamento' => 'pix', 'endereco_id' => $enderecoId,
        ], $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('endereco_id');
    }

    public function test_cobra_frete_abaixo_do_minimo_no_cartao(): void
    {
        $headers = $this->headers;
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 1], $headers);

        $this->postJson('/api/pedidos', [...$this->dados, 'pagamento' => 'cartao'], $headers)
            ->assertCreated()
            ->assertJsonPath('subtotal', 66)
            ->assertJsonPath('discount', 0)
            ->assertJsonPath('shipping', 18)
            ->assertJsonPath('total', 84);
    }

    public function test_recusa_pedido_com_carrinho_vazio(): void
    {
        $this->postJson('/api/pedidos', $this->dados, $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('carrinho');

        $this->assertSame(0, Order::count());
    }

    public function test_valida_campos_obrigatorios(): void
    {
        $this->postJson('/api/pedidos', ['pagamento' => 'boleto'], $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome', 'telefone', 'cpf', 'cep', 'cidade', 'bairro', 'endereco', 'numero', 'pagamento']);
    }
}
