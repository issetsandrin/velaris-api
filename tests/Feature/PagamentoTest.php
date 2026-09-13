<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductSeeder::class, PaymentMethodSeeder::class]);
        $token = $this->postJson('/api/auth/registrar', [
            'nome' => 'Ana', 'email' => 'ana@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');
        $this->headers = ['Authorization' => 'Bearer '.$token];
    }

    public function test_config_publica_expoe_frete_limites_e_formas_ativas(): void
    {
        PaymentMethod::create(['code' => 'boleto', 'name' => 'Boleto', 'type' => 'boleto', 'active' => false]);

        $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonPath('shipping.freeFrom', 180)
            ->assertJsonPath('shipping.price', 18)
            ->assertJsonPath('maxQuantityPerItem', 10)
            ->assertJsonCount(2, 'paymentMethods')
            ->assertJsonPath('paymentMethods.0.code', 'pix')
            ->assertJsonPath('paymentMethods.0.discountPercent', 5)
            ->assertJsonPath('paymentMethods.1.maxInstallments', 3);
    }

    public function test_previa_aplica_desconto_da_forma_e_limita_parcelas_pelo_minimo(): void
    {
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 1], $this->headers); // 66

        $this->postJson('/api/checkout/totais', ['pagamento' => 'pix'], $this->headers)
            ->assertOk()
            ->assertJsonPath('paymentDiscount', 3.3)
            ->assertJsonPath('installments.count', 1);

        // cartão: 66 + 18 frete = 84; parcela mínima 30 => no máximo 2x
        $this->postJson('/api/checkout/totais', ['pagamento' => 'cartao', 'parcelas' => 3], $this->headers)
            ->assertOk()
            ->assertJsonPath('paymentDiscount', 0)
            ->assertJsonPath('installments.max', 2)
            ->assertJsonPath('installments.count', 2)
            ->assertJsonPath('installments.value', 42);
    }

    public function test_recusa_forma_inativa_e_grava_parcelas_no_pedido(): void
    {
        PaymentMethod::where('code', 'pix')->update(['active' => false]);
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $this->headers); // 182

        $pedido = [
            'nome' => 'Ana', 'telefone' => '41999990000', 'cpf' => '52998224725',
            'cep' => '80000000', 'cidade' => 'Curitiba', 'bairro' => 'Centro', 'endereco' => 'Rua A', 'numero' => '1',
        ];

        $this->postJson('/api/pedidos', [...$pedido, 'pagamento' => 'pix'], $this->headers)
            ->assertUnprocessable()->assertJsonValidationErrors('pagamento');

        $this->postJson('/api/pedidos', [...$pedido, 'pagamento' => 'cartao', 'parcelas' => 3], $this->headers)
            ->assertCreated()
            ->assertJsonPath('paymentMethodName', 'Cartão de crédito')
            ->assertJsonPath('installments', 3)
            ->assertJsonPath('total', 182);
    }
}
