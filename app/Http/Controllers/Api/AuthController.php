<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntrarRequest;
use App\Http\Requests\GoogleRequest;
use App\Http\Requests\RegistrarRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CarrinhoService;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const TOKEN_NAME = 'loja';

    public function __construct(
        private readonly CarrinhoService $carrinho,
        private readonly GoogleAuthService $google,
    ) {}

    public function registrar(RegistrarRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('nome'),
            'email' => $request->string('email')->lower(),
            'password' => $request->string('senha'),
        ]);

        return $this->responderComToken($request, $user, 201);
    }

    public function entrar(EntrarRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->lower())->first();

        if (! $user || ! $user->password || ! Hash::check($request->string('senha'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('errors.credenciais_invalidas'),
            ]);
        }

        return $this->responderComToken($request, $user);
    }

    /**
     * Entrar ou cadastrar com Google: recebe o ID token do botão do Google,
     * localiza a conta pelo google_id ou pelo e-mail e, se não existir, cria.
     */
    public function google(GoogleRequest $request): JsonResponse
    {
        if (! $this->google->configurado()) {
            return response()->json(['message' => __('errors.google_nao_configurado')], 503);
        }

        $perfil = $this->google->verificar($request->string('credential'));

        $user = User::where('google_id', $perfil['sub'])->first()
            ?? User::where('email', $perfil['email'])->first();

        if ($user) {
            $user->update(['google_id' => $perfil['sub']]);
        } else {
            $user = User::create([
                'name' => $perfil['name'],
                'email' => $perfil['email'],
                'google_id' => $perfil['sub'],
                'email_verified_at' => now(),
            ]);
        }

        return $this->responderComToken($request, $user, $user->wasRecentlyCreated ? 201 : 200);
    }

    public function sair(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function eu(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    private function responderComToken(Request $request, User $user, int $status = 200): JsonResponse
    {
        $this->carrinho->mesclarNoUsuario($user, $request->header(CarrinhoService::HEADER));

        return response()->json([
            'token' => $user->createToken(self::TOKEN_NAME)->plainTextToken,
            'user' => new UserResource($user),
        ], $status);
    }
}
