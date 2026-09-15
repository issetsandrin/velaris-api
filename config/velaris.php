<?php

return [
    // Endereço da loja, usado pelo link "Voltar à loja" no login do painel.
    'loja_url' => env('LOJA_URL', 'http://localhost:3002'),

    // Entrada na conta: confirmação de e-mail e código de acesso por e-mail.
    'acesso' => [
        // Minutos de validade do link de confirmação de e-mail.
        'confirmacao_validade' => (int) env('CONFIRMACAO_VALIDADE', 60 * 24),
        // Minutos de validade do código de acesso enviado no login.
        'codigo_validade' => (int) env('CODIGO_VALIDADE', 3),
        // Tentativas erradas aceitas antes de o código ser descartado.
        'codigo_tentativas' => (int) env('CODIGO_TENTATIVAS', 5),
        // Minutos de vida da sessão: a curta é o padrão, a longa vale para quem
        // marca "manter minha conta ativa" ao entrar.
        'sessao_curta' => (int) env('SESSAO_CURTA', 60 * 24),
        'sessao_longa' => (int) env('SESSAO_LONGA', 60 * 24 * 30),
    ],

    // Recebedor que aparece no copia e cola do Pix (ambiente de demonstração).
    'pix' => [
        'chave' => env('PIX_CHAVE', 'ola@velaris.com.br'),
        'nome' => env('PIX_NOME', 'VELARIS VELAS'),
        'cidade' => env('PIX_CIDADE', 'CURITIBA'),
    ],

    // Arquivo da logotipo enviado pelo painel; vazio usa a que vem na loja.
    'logo' => env('LOGO', ''),

    // Topo da home: 'vela' usa a arte animada; 'banner' usa as imagens cadastradas.
    'home' => [
        'estilo' => env('HOME_ESTILO', 'vela'),
        'intervalo' => (int) env('HOME_INTERVALO', 6),
    ],

    'frete' => [
        'gratis_a_partir_de' => (float) env('FRETE_GRATIS_A_PARTIR_DE', 180),
        // Só o valor da primeira forma de entrega, criada na migration. O valor de
        // cada entrega é editado no painel, em Formas de entrega.
        'valor' => (float) env('FRETE_VALOR', 18),
    ],
    'quantidade_maxima_por_item' => (int) env('QUANTIDADE_MAXIMA_POR_ITEM', 10),
    // Abaixo deste estoque a loja mostra "Últimas unidades" e o painel destaca o tamanho.
    'estoque_baixo' => (int) env('ESTOQUE_BAIXO', 5),
];
