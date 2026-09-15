<x-mail-layout :titulo="'Seu código de acesso'">
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:600; color:#4a3818;">Seu código de acesso</h1>

    <p style="margin:0 0 20px; font-size:15px; line-height:1.6; color:#4a3818;">
        Olá, {{ $user->name }}. Use o código abaixo para terminar de entrar na sua conta.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td align="center" style="background:#f5f0e8; border:1px solid rgba(122,93,41,0.18); border-radius:8px; padding:20px;">
                <span style="font-family:'Courier New',Courier,monospace; font-size:32px; font-weight:700; letter-spacing:0.32em; color:#4a3818;">{{ $codigo }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#7a5d29;">
        O código vale por {{ $minutos }} minutos e serve uma vez só.
    </p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#7a5d29;">
        Se não foi você que tentou entrar, troque sua senha por precaução.
    </p>
</x-mail-layout>
