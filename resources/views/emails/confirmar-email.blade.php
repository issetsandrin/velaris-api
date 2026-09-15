<x-mail-layout :titulo="'Confirme seu e-mail'">
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:600; color:#4a3818;">Confirme seu e-mail</h1>

    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#4a3818;">
        Olá, {{ $user->name }}. Sua conta na Velaris está criada. Confirme o endereço
        para receber o andamento dos seus pedidos por aqui.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
        <tr>
            <td style="background:#9a7935; border-radius:999px;">
                <a href="{{ $link }}" style="display:inline-block; padding:12px 28px; font-family:Helvetica,Arial,sans-serif; font-size:15px; font-weight:600; color:#fbf8f2; text-decoration:none;">
                    Confirmar e-mail
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#7a5d29;">
        O link vale por {{ $horas }} {{ $horas === 1 ? 'hora' : 'horas' }}. Se o botão não funcionar, copie o endereço abaixo:
    </p>
    <p style="margin:0 0 20px; font-size:12px; line-height:1.5; word-break:break-all; color:#9a7935;">{{ $link }}</p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#7a5d29;">
        Se não foi você quem criou a conta, pode ignorar esta mensagem.
    </p>
</x-mail-layout>
