<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EsqueciSenhaRequest;
use App\Http\Requests\RedefinirSenhaRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Recuperação de senha. Usa o broker do Laravel: token com hash na tabela
 * password_reset_tokens, validade e limite de pedidos vindos de config/auth.php.
 */
class SenhaController extends Controller
{
    /**
     * Pede o link de redefinição. A resposta é sempre a mesma, exista a conta
     * ou não: quem pergunta não descobre quais e-mails estão cadastrados.
     */
    public function esqueci(EsqueciSenhaRequest $request): JsonResponse
    {
        $email = $request->string('email')->lower()->value();
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Já enviamos um link há pouco. Confira sua caixa de entrada antes de pedir outro.',
            ], 429);
        }

        return response()->json([
            'message' => 'Se houver uma conta com este e-mail, o link de redefinição chega em instantes.',
        ]);
    }

    /** Troca a senha a partir do token do link. */
    public function redefinir(RedefinirSenhaRequest $request): JsonResponse
    {
        $status = Password::reset(
            [
                'email' => $request->string('email')->lower()->value(),
                'password' => $request->string('senha')->value(),
                'password_confirmation' => (string) $request->input('senha_confirmation'),
                'token' => $request->string('token')->value(),
            ],
            function (User $user, string $senha): void {
                $user->forceFill(['password' => $senha])->save();

                // Senha nova encerra as sessões antigas, no aparelho que for.
                $user->tokens()->delete();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => __('errors.senha_token_invalido'),
            ]);
        }

        return response()->json(['message' => 'Senha alterada. Entre com a nova senha.']);
    }
}
