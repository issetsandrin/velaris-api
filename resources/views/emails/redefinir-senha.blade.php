<x-mail-layout :titulo="'Redefinir sua senha'">
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:600; color:#4a3818;">Redefinir sua senha</h1>

    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#4a3818;">
        Olá, {{ $user->name }}. Recebemos um pedido para trocar a senha da sua conta.
        Use o botão abaixo para escolher uma nova.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
        <tr>
            <td style="background:#9a7935; border-radius:999px;">
                <a href="{{ $link }}" style="display:inline-block; padding:12px 28px; font-family:Helvetica,Arial,sans-serif; font-size:15px; font-weight:600; color:#fbf8f2; text-decoration:none;">
                    Escolher nova senha
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#7a5d29;">
        O link vale por {{ $minutos }} minutos. Se o botão não funcionar, copie o endereço abaixo:
    </p>
    <p style="margin:0 0 20px; font-size:12px; line-height:1.5; word-break:break-all; color:#9a7935;">{{ $link }}</p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#7a5d29;">
        Se não foi você quem pediu, ignore esta mensagem: a senha atual continua valendo.
    </p>
</x-mail-layout>
