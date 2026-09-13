<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AvisoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function __construct(private readonly AvisoService $avisos) {}

    /** Aberto: visitante recebe só os avisos da loja; com token entram os do pedido. */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->avisos->para($request->user('sanctum')));
    }

    /** Marca avisos como lidos. Sem lista, marca todos os que a pessoa vê agora. */
    public function marcarLidos(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'chaves' => ['nullable', 'array', 'max:200'],
            'chaves.*' => ['string', 'max:120'],
        ]);

        $user = $request->user();
        $chaves = $dados['chaves'] ?? $this->avisos->chaves($user);

        $this->avisos->marcarComoLidos($user, $chaves);

        return response()->json($this->avisos->para($user));
    }
}
