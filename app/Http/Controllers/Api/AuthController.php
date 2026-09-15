<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CodigoRequest;
use App\Http\Requests\ConfirmarEmailRequest;
use App\Http\Requests\EntrarRequest;
use App\Http\Requests\GoogleRequest;
use App\Http\Requests\RegistrarRequest;
use App\Http\Resources\UserResource;
use App\Models\LoginCode;
use App\Models\User;
use App\Services\AcessoService;
use App\Support\ResultadoConfirmacao;
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
        private readonly AcessoService $acesso,
    ) {}

    public function registrar(RegistrarRequest $request): JsonResponse
    {
        // Texto puro, não Stringable: o objeto sobrevive no model e confunde
        // quem recebe o atributo depois, como o destinatário do e-mail.
        $user = User::create([
            'name' => $request->string('nome')->value(),
            'email' => $request->string('email')->lower()->value(),
            'password' => $request->string('senha')->value(),
        ]);

        $this->acesso->enviarConfirmacao($user);

        // Sem sessão: a conta só abre depois que o link do e-mail é aberto.
        return response()->json([
            'confirmacaoPendente' => true,
            'email' => $user->email,
            'message' => 'Enviamos um link de confirmação para '.$user->email.'.',
        ], 201);
    }

    /**
     * Primeiro passo do login: confere a senha e manda o código por e-mail.
     * O token da sessão só sai no segundo passo, em `codigo()`.
     */
    public function entrar(EntrarRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->lower()->value())->first();

        if ($user && ! $user->password && $user->google_id) {
            throw ValidationException::withMessages([
                'email' => __('errors.sem_senha_cadastrada'),
            ]);
        }

        if (! $user || ! $user->password || ! Hash::check($request->string('senha')->value(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('errors.credenciais_invalidas'),
            ]);
        }

        // 403 com marca própria: a loja reconhece o caso e oferece o reenvio,
        // em vez de mostrar só mais um erro de campo.
        if (! $user->email_verified_at) {
            return response()->json([
                'emailPendente' => true,
                'message' => __('errors.email_nao_confirmado'),
            ], 403);
        }

        $lembrar = $request->boolean('lembrar');

        // Sem a verificação em dois passos ligada, a senha já vale a sessão.
        if (! $user->two_factor_enabled) {
            return $this->responderComToken($request, $user, lembrar: $lembrar);
        }

        return $this->responderComDesafio($this->acesso->abrirDesafio($user, $lembrar));
    }

    /** Liga ou desliga a verificação em dois passos da própria conta. */
    public function definirDoisPassos(Request $request): JsonResponse
    {
        $ativo = $request->boolean('ativo');
        $request->user()->forceFill(['two_factor_enabled' => $ativo])->save();

        return response()->json([
            'message' => $ativo
                ? 'Verificação em dois passos ligada. O próximo acesso vai pedir o código do e-mail.'
                : 'Verificação em dois passos desligada.',
            'user' => new UserResource($request->user()->fresh()),
        ]);
    }

    /** Segundo passo: o código do e-mail vale o token da sessão. */
    public function codigo(CodigoRequest $request): JsonResponse
    {
        $desafio = $this->acesso->desafio($request->string('desafio')->value());

        if (! $desafio) {
            throw ValidationException::withMessages([
                'desafio' => __('errors.desafio_invalido'),
            ]);
        }

        if (! $this->acesso->conferirCodigo($desafio, $request->string('codigo')->value())) {
            throw ValidationException::withMessages([
                'codigo' => __('errors.codigo_invalido'),
            ]);
        }

        return $this->responderComToken($request, $desafio->user, lembrar: (bool) $desafio->remember);
    }

    /** Manda outro código para a mesma tentativa de entrada. */
    public function reenviarCodigo(Request $request): JsonResponse
    {
        $desafio = $this->acesso->desafio((string) $request->input('desafio'));

        if (! $desafio) {
            throw ValidationException::withMessages([
                'desafio' => __('errors.desafio_invalido'),
            ]);
        }

        return $this->responderComDesafio($this->acesso->reenviarCodigo($desafio));
    }

    /**
     * Reenvia o link para quem ainda não confirmou e por isso não entra.
     * A resposta é sempre a mesma: não revela quais e-mails existem.
     */
    public function reenviarConfirmacaoPublico(Request $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->input('email')));
        $user = User::where('email', $email)->first();

        if ($user && ! $user->email_verified_at) {
            $this->acesso->enviarConfirmacao($user);
        }

        return response()->json([
            'message' => 'Se houver uma conta pendente com este e-mail, o link de confirmação chega em instantes.',
        ]);
    }

    /** Confirma o e-mail a partir do token do link. */
    public function confirmarEmail(ConfirmarEmailRequest $request): JsonResponse
    {
        ['estado' => $estado, 'user' => $user] = $this->acesso->confirmar($request->string('token')->value());

        if ($estado === ResultadoConfirmacao::Invalido) {
            throw ValidationException::withMessages([
                'token' => __('errors.confirmacao_invalida'),
            ]);
        }

        return response()->json([
            'message' => $estado === ResultadoConfirmacao::JaConfirmado
                ? __('errors.email_ja_confirmado')
                : 'E-mail confirmado.',
            'jaEstava' => $estado === ResultadoConfirmacao::JaConfirmado,
            'user' => new UserResource($user),
        ]);
    }

    /** Manda de novo o link de confirmação para quem está logado. */
    public function reenviarConfirmacao(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => __('errors.email_ja_confirmado')], 422);
        }

        $this->acesso->enviarConfirmacao($user);

        return response()->json(['message' => 'Enviamos um novo link para '.$user->email.'.']);
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

    private function responderComDesafio(LoginCode $desafio): JsonResponse
    {
        return response()->json([
            'twoFactor' => true,
            'desafio' => $desafio->challenge,
            'email' => $this->mascarar($desafio->user->email),
            'expiraEm' => $desafio->expires_at->toIso8601String(),
        ], 202);
    }

    /** o***@dominio.com: confirma o endereço sem expor a caixa inteira. */
    private function mascarar(string $email): string
    {
        [$conta, $dominio] = explode('@', $email, 2);

        return mb_substr($conta, 0, 1).str_repeat('*', max(3, mb_strlen($conta) - 1)).'@'.$dominio;
    }

    /**
     * Devolve a sessão pronta. `$lembrar` estende o prazo do token de um dia
     * para trinta: a loja guarda o token, mas quem manda no prazo é o servidor.
     */
    private function responderComToken(Request $request, User $user, int $status = 200, bool $lembrar = false): JsonResponse
    {
        $this->carrinho->mesclarNoUsuario($user, $request->header(CarrinhoService::HEADER));

        $vence = now()->addMinutes((int) config($lembrar ? 'velaris.acesso.sessao_longa' : 'velaris.acesso.sessao_curta'));

        return response()->json([
            'token' => $user->createToken(self::TOKEN_NAME, ['*'], $vence)->plainTextToken,
            'expiraEm' => $vence->toIso8601String(),
            'user' => new UserResource($user),
        ], $status);
    }
}
