<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private array $cadastro = [
        'nome' => 'Ana Teste',
        'email' => 'ana@exemplo.com',
        'senha' => 'segredo123',
        'senha_confirmation' => 'segredo123',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ProductSeeder::class, PaymentMethodSeeder::class]);
    }

    public function test_registra_e_mescla_carrinho_de_visitante(): void
    {
        $tokenVisitante = $this->getJson('/api/carrinho')->json('token');
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'm', 'quantity' => 2], ['X-Cart-Token' => $tokenVisitante]);

        $response = $this->postJson('/api/auth/registrar', $this->cadastro, ['X-Cart-Token' => $tokenVisitante])
            ->assertCreated()
            ->assertJsonPath('user.email', 'ana@exemplo.com')
            ->assertJsonPath('user.addresses', []);

        $bearer = ['Authorization' => 'Bearer '.$response->json('token')];

        $this->getJson('/api/carrinho', $bearer)
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('items.0.product.slug', 'pitanga');

        $this->getJson('/api/carrinho', ['X-Cart-Token' => $tokenVisitante])
            ->assertJsonPath('count', 0);
    }

    public function test_recusa_email_duplicado_e_senha_sem_confirmacao(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com']);

        $this->postJson('/api/auth/registrar', [...$this->cadastro, 'senha_confirmation' => 'outra'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'senha']);
    }

    public function test_entra_com_credenciais_validas_e_recusa_invalidas(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com', 'password' => 'segredo123']);

        $this->postJson('/api/auth/entrar', ['email' => 'ana@exemplo.com', 'senha' => 'errada'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/auth/entrar', ['email' => 'ana@exemplo.com', 'senha' => 'segredo123'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'addresses']]);
    }

    public function test_sair_revoga_o_token(): void
    {
        $token = $this->postJson('/api/auth/registrar', $this->cadastro)->json('token');
        $bearer = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/auth/eu', $bearer)->assertOk();
        $this->postJson('/api/auth/sair', [], $bearer)->assertNoContent();
        $this->getJson('/api/auth/eu', $bearer)->assertUnauthorized();
    }

    public function test_google_sem_client_id_configurado_responde_503(): void
    {
        config(['services.google.client_id' => null]);

        $this->postJson('/api/auth/google', ['credential' => 'qualquer'])->assertStatus(503);
    }

    public function test_google_com_token_invalido_responde_422(): void
    {
        config(['services.google.client_id' => 'cliente-teste']);
        Http::fake([
            'https://www.googleapis.com/oauth2/v3/certs' => Http::response(['keys' => []]),
        ]);

        $this->postJson('/api/auth/google', ['credential' => 'token.invalido.aqui'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('credential');
    }

    public function test_pedido_exige_login_e_salva_endereco_na_conta(): void
    {
        $this->postJson('/api/pedidos', [])->assertUnauthorized();

        $token = $this->postJson('/api/auth/registrar', $this->cadastro)->json('token');
        $bearer = ['Authorization' => 'Bearer '.$token];
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'g', 'quantity' => 1], $bearer);

        $this->postJson('/api/pedidos', [
            'nome' => 'Ana Teste',
            'email' => 'ana@exemplo.com',
            'cep' => '80000-000',
            'cidade' => 'Curitiba',
            'bairro' => 'Centro',
            'endereco' => 'Rua das Velas',
            'numero' => '12',
            'pagamento' => 'cartao',
        ], $bearer)->assertCreated();

        $this->getJson('/api/auth/eu', $bearer)
            ->assertJsonPath('addresses.0.postalCode', '80000000')
            ->assertJsonPath('addresses.0.street', 'Rua das Velas');

        $this->getJson('/api/conta/pedidos', $bearer)
            ->assertOk()
            ->assertJsonCount(1);

        $this->getJson('/api/carrinho', $bearer)->assertJsonPath('count', 0);
    }

    public function test_nao_consulta_pedido_de_outro_cliente(): void
    {
        $tokenA = $this->postJson('/api/auth/registrar', $this->cadastro)->json('token');
        $bearerA = ['Authorization' => 'Bearer '.$tokenA];
        $this->postJson('/api/carrinho/itens', ['slug' => 'pitanga', 'size' => 'p', 'quantity' => 1], $bearerA);
        $numero = $this->postJson('/api/pedidos', [
            'nome' => 'Ana', 'email' => 'ana@exemplo.com', 'cep' => '80000000', 'cidade' => 'Curitiba',
            'bairro' => 'Centro',
            'endereco' => 'Rua A', 'numero' => '1', 'pagamento' => 'pix',
        ], $bearerA)->json('number');

        $tokenB = $this->postJson('/api/auth/registrar', [...$this->cadastro, 'email' => 'bia@exemplo.com'])->json('token');

        $this->getJson("/api/pedidos/{$numero}", ['Authorization' => 'Bearer '.$tokenB])->assertNotFound();
        $this->getJson("/api/pedidos/{$numero}", $bearerA)->assertOk();
    }
}
