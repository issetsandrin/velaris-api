<?php

namespace App\Services;

use App\Mail\CodigoDeAcesso;
use App\Mail\ConfirmarEmail;
use App\Models\EmailVerification;
use App\Models\LoginCode;
use App\Models\User;
use App\Support\ResultadoConfirmacao;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Confirmação de e-mail e código de acesso do login. Tokens e códigos só saem
 * daqui dentro do e-mail: o banco guarda apenas o hash.
 */
class AcessoService
{
    /** Envia (ou reenvia) o link de confirmação de e-mail. */
    public function enviarConfirmacao(User $user): void
    {
        if ($user->email_verified_at) {
            return;
        }

        // Só os links ainda não usados saem de circulação: os usados ficam
        // guardados para reconhecer quem abre o mesmo link duas vezes.
        $user->emailVerifications()->whereNull('used_at')->delete();

        $token = Str::random(48);

        $user->emailVerifications()->create([
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes((int) config('velaris.acesso.confirmacao_validade')),
        ]);

        Mail::to($user->email)->send(new ConfirmarEmail($user, $token));
    }

    /**
     * Marca o e-mail como confirmado a partir do token do link.
     *
     * Abrir o mesmo link outra vez — ou o navegador recarregando a página —
     * não é erro: devolve JaConfirmado, com a conta intacta.
     *
     * @return array{estado: ResultadoConfirmacao, user: ?User}
     */
    public function confirmar(string $token): array
    {
        $registro = EmailVerification::with('user')
            ->where('token', hash('sha256', $token))
            ->first();

        if (! $registro || ! $registro->user) {
            return ['estado' => ResultadoConfirmacao::Invalido, 'user' => null];
        }

        $user = $registro->user;

        if ($registro->used_at) {
            return ['estado' => ResultadoConfirmacao::JaConfirmado, 'user' => $user];
        }

        if ($registro->expirou()) {
            $registro->delete();

            return ['estado' => ResultadoConfirmacao::Invalido, 'user' => null];
        }

        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();
        $registro->forceFill(['used_at' => now()])->save();

        return ['estado' => ResultadoConfirmacao::Confirmado, 'user' => $user];
    }

    /**
     * Abre uma tentativa de login: gera o código, manda por e-mail e devolve o
     * identificador que a loja usa para conferir a resposta.
     */
    public function abrirDesafio(User $user, bool $lembrar = false): LoginCode
    {
        $user->loginCodes()->whereNull('used_at')->delete();

        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $desafio = $user->loginCodes()->create([
            'challenge' => Str::random(40),
            'code' => Hash::make($codigo),
            'remember' => $lembrar,
            'expires_at' => now()->addMinutes((int) config('velaris.acesso.codigo_validade')),
        ]);

        Mail::to($user->email)->send(new CodigoDeAcesso($user, $codigo, $desafio->expires_at));

        return $desafio;
    }

    /** Reabre o desafio com um código novo, mantendo a tentativa em curso. */
    public function reenviarCodigo(LoginCode $desafio): LoginCode
    {
        $lembrar = (bool) $desafio->remember;
        $desafio->delete();

        return $this->abrirDesafio($desafio->user, $lembrar);
    }

    /**
     * Confere o código digitado. Cada erro consome uma tentativa; esgotadas,
     * o desafio morre e a pessoa recomeça o login.
     */
    public function conferirCodigo(LoginCode $desafio, string $codigo): bool
    {
        if (Hash::check($codigo, $desafio->code)) {
            $desafio->forceFill(['used_at' => now()])->save();

            return true;
        }

        $desafio->increment('attempts');

        if ($desafio->attempts >= (int) config('velaris.acesso.codigo_tentativas')) {
            $desafio->delete();
        }

        return false;
    }

    /** Desafio aberto e ainda válido, ou null. */
    public function desafio(string $challenge): ?LoginCode
    {
        $desafio = LoginCode::with('user')->where('challenge', $challenge)->first();

        return $desafio && $desafio->valido() ? $desafio : null;
    }
}
