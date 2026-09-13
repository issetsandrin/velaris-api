<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvisoController;
use App\Http\Controllers\Api\CarrinhoController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\ContaController;
use App\Http\Controllers\Api\CupomController;
use App\Http\Controllers\Api\PagamentoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::get('/config', [ConfigController::class, 'show']);
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::get('/produtos/{produto:slug}', [ProdutoController::class, 'show']);

// Carrinho: funciona para visitante (X-Cart-Token) e para cliente autenticado (Bearer).
Route::get('/carrinho', [CarrinhoController::class, 'show']);
Route::post('/carrinho/itens', [CarrinhoController::class, 'adicionarItem']);
Route::patch('/carrinho/itens/{item}', [CarrinhoController::class, 'atualizarItem']);
Route::delete('/carrinho/itens/{item}', [CarrinhoController::class, 'removerItem']);

// Avisos: aberto para visitante, e com token traz também os do pedido de quem entrou.
Route::get('/avisos', [AvisoController::class, 'index']);

Route::post('/auth/registrar', [AuthController::class, 'registrar'])->middleware('throttle:10,1');
Route::post('/auth/entrar', [AuthController::class, 'entrar'])->middleware('throttle:10,1');
Route::post('/auth/google', [AuthController::class, 'google'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/sair', [AuthController::class, 'sair']);
    Route::get('/auth/eu', [AuthController::class, 'eu']);

    Route::get('/conta/contatos', [ContaController::class, 'contatos']);
    Route::post('/conta/contatos', [ContaController::class, 'criarContato']);
    Route::patch('/conta/contatos/{contato}', [ContaController::class, 'atualizarContato']);
    Route::delete('/conta/contatos/{contato}', [ContaController::class, 'removerContato']);
    Route::get('/conta/enderecos', [ContaController::class, 'enderecos']);
    Route::post('/conta/enderecos', [ContaController::class, 'criarEndereco']);
    Route::patch('/conta/enderecos/{endereco}', [ContaController::class, 'atualizarEndereco']);
    Route::delete('/conta/enderecos/{endereco}', [ContaController::class, 'removerEndereco']);
    Route::get('/conta/pedidos', [ContaController::class, 'pedidos']);
    Route::post('/avisos/lidos', [AvisoController::class, 'marcarLidos']);

    Route::post('/checkout/totais', [CupomController::class, 'previa']);
    Route::post('/pedidos', [PedidoController::class, 'store']);
    Route::get('/pedidos/{pedido:number}', [PedidoController::class, 'show']);

    // Pagamento do pedido: Pix (QR + confirmação) e cartão.
    Route::get('/pedidos/{number}/pagamento', [PagamentoController::class, 'show']);
    Route::post('/pedidos/{number}/pagamento/pix', [PagamentoController::class, 'pix']);
    Route::post('/pedidos/{number}/pagamento/pix/confirmar', [PagamentoController::class, 'confirmarPix']);
    Route::post('/pedidos/{number}/pagamento/cartao', [PagamentoController::class, 'cartao']);
});
