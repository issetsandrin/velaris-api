<?php

return [
    // Endereço da loja, usado pelo link "Voltar à loja" no login do painel.
    'loja_url' => env('LOJA_URL', 'http://localhost:3002'),

    // Recebedor que aparece no copia e cola do Pix (ambiente de demonstração).
    'pix' => [
        'chave' => env('PIX_CHAVE', 'ola@velaris.com.br'),
        'nome' => env('PIX_NOME', 'VELARIS VELAS'),
        'cidade' => env('PIX_CIDADE', 'CURITIBA'),
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
