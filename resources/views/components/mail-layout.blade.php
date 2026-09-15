{{-- Moldura dos e-mails da loja: tabela simples, cores da marca e nada de
     imagem externa, para chegar inteiro em qualquer leitor. --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f0e8;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0e8; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#fbf8f2; border:1px solid rgba(122,93,41,0.18); border-radius:10px;">
                    <tr>
                        <td style="padding:32px 32px 8px; text-align:center;">
                            <p style="margin:0; font-family:Georgia,'Times New Roman',serif; font-size:26px; letter-spacing:0.08em; color:#9a7935;">Velaris</p>
                            <p style="margin:6px 0 0; font-family:Helvetica,Arial,sans-serif; font-size:12px; letter-spacing:0.14em; text-transform:uppercase; color:#b9994d;">Velas de cera de coco</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 32px; font-family:Helvetica,Arial,sans-serif; color:#4a3818;">
                            {{ $slot }}
                        </td>
                    </tr>
                </table>

                <p style="margin:20px 0 0; font-family:Helvetica,Arial,sans-serif; font-size:12px; color:#7a5d29;">
                    Velaris Velas Ltda. &middot; Curitiba, PR
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
