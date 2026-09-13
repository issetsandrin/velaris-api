<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Product;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstoquePromocaoCupomTest extends TestCase
{
    use RefreshDatabase;

    private array $headers;

    private array $pedido = [
        'nome' => 'Ana', 'telefone' => '41999990000', 'cpf' => '52998224725', 'pagamento' => 'cartao',
        'cep' => '80000000', 'cidade' => 'Curitiba',
        'bairro' => 'Centro', 'endereco' => 'Rua A', 'numero' => '1',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductSeeder::class, PaymentMethodSeeder::class]);

        $token = $this->postJson('/api/auth/registrar', [
            'nome' => 'Ana', 'email' => 'ana@exemplo.com', 'senha' => 'segredo123', 'senha_confirmation' => 'segredo123',
        ])->json('token');
        $this->headers = ['Authorization' => 'Bearer '.$token];
    }

    private function tamanho(string $slug, string $key)
    {
        return Product::where('slug', $slug)->firstOrFail()->sizes()->where('key', $key)->firstOrFail();
    }

    public function test_nao_adiciona_tamanho_esgotado_nem_acima_do_estoque(): void
    {
        $this->tamanho('pitanga', 'g')->update(['stock' => 0]);
        $this->tamanho('pitanga', 'p')->update(['stock' => 2]);

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $this->headers)
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 3], $this->headers)
            ->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 2], $this->headers)
            ->assertCreated();
    }

    public function test_promocao_no_periodo_muda_preco_e_pedido_grava_preco_cheio(): void
    {
        $this->tamanho('pitanga', 'm')->update(['promo_price' => 99, 'promo_starts_at' => now()->subDay(), 'promo_ends_at' => now()->addDay()]);
        $this->tamanho('pitanga', 'g')->update(['promo_price' => 100, 'promo_starts_at' => now()->addDay()]);

        $produto = $this->getJson('/api/produtos/pitanga')->assertOk()->json();
        $medio = collect($produto['sizes'])->firstWhere('key', 'm');
        $grande = collect($produto['sizes'])->firstWhere('key', 'g');
        $this->assertSame(99.0, (float) $medio['price']);
        $this->assertSame(114.0, (float) $medio['listPrice']);
        $this->assertTrue($medio['onSale']);
        $this->assertFalse($grande['onSale']);

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'm', 'quantity' => 1], $this->headers)
            ->assertJsonPath('subtotal', 99);

        $this->postJson('/api/pedidos', $this->pedido, $this->headers)
            ->assertCreated()
            ->assertJsonPath('subtotal', 99)
            ->assertJsonPath('items.0.unitPrice', 99)
            ->assertJsonPath('items.0.listPrice', 114);
    }

    public function test_pedido_baixa_estoque(): void
    {
        $this->tamanho('pitanga', 'm')->update(['stock' => 5]);
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'm', 'quantity' => 2], $this->headers);

        $this->postJson('/api/pedidos', $this->pedido, $this->headers)->assertCreated();

        $this->assertSame(3, $this->tamanho('pitanga', 'm')->stock);
    }

    public function test_cupom_percentual_e_frete_gratis_e_regras(): void
    {
        Coupon::create(['code' => 'DEZ', 'type' => 'percent', 'value' => 10, 'active' => true]);
        Coupon::create(['code' => 'FRETE', 'type' => 'free_shipping', 'min_subtotal' => 500, 'active' => true]);
        Coupon::create(['code' => 'VENCIDO', 'type' => 'fixed', 'value' => 20, 'ends_at' => now()->subDay(), 'active' => true]);
        Coupon::create(['code' => 'CHEIO', 'type' => 'fixed', 'value' => 20, 'max_uses' => 1, 'uses_count' => 1, 'active' => true]);

        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 1], $this->headers); // 66

        $this->postJson('/api/checkout/totais', ['pagamento' => 'cartao', 'cupom' => 'dez'], $this->headers)
            ->assertOk()
            ->assertJsonPath('couponDiscount', 6.6)
            ->assertJsonPath('shipping', 18)
            ->assertJsonPath('total', 77.4);

        foreach (['FRETE' => 'mínimo', 'VENCIDO' => 'período', 'CHEIO' => 'limite', 'NAOEXISTE' => 'inválido'] as $codigo => $motivo) {
            $this->postJson('/api/checkout/totais', ['pagamento' => 'cartao', 'cupom' => $codigo], $this->headers)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('cupom');
        }

        $pedido = $this->postJson('/api/pedidos', [...$this->pedido, 'cupom' => 'DEZ'], $this->headers)
            ->assertCreated()
            ->assertJsonPath('couponCode', 'DEZ')
            ->assertJsonPath('couponDiscount', 6.6)
            ->assertJsonPath('total', 77.4);

        $this->assertSame(1, Coupon::where('code', 'DEZ')->value('uses_count'));
    }

    public function test_cupom_de_frete_gratis_zera_frete(): void
    {
        Coupon::create(['code' => 'FRETE', 'type' => 'free_shipping', 'active' => true]);
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 1], $this->headers);

        $this->postJson('/api/checkout/totais', ['pagamento' => 'cartao', 'cupom' => 'FRETE'], $this->headers)
            ->assertOk()
            ->assertJsonPath('shipping', 0)
            ->assertJsonPath('total', 66);
    }

    public function test_painel_admin_so_para_administradores(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
