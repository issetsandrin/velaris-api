# Velaris API

Backend da loja Velaris: catálogo, carrinho persistido no servidor e pedidos. Laravel 13, SQLite por padrão.

## Rodar

Sem PHP no host, tudo roda em container. A imagem é construída pelo `Dockerfile` (PHP 8.4 com `intl` e `gd`, exigidas pelo painel):

```bash
export UID GID=$(id -g)
docker compose up -d --build         # API em http://localhost:8100, painel em /admin
docker compose exec api php artisan migrate --seed
docker compose exec api php artisan velaris:admin voce@exemplo.com --senha=SuaSenha
```

Comandos avulsos (sem o container de pé):

```bash
docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp -v $PWD:/var/www -w /var/www velaris-api:local php artisan migrate:fresh --seed
docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp -v $PWD:/var/www -w /var/www velaris-api:local php artisan test
docker run --rm -u $(id -u):$(id -g) -e HOME=/tmp -v $PWD:/var/www -w /var/www velaris-api:local php vendor/bin/pint
```

## Painel administrativo

Em `http://localhost:8100/admin`, feito com Laravel Filament, sempre em tema claro com a paleta da marca (marfim, dourados e marrom). As cores ficam em `AdminPanelProvider` (escala `gray` e `primary`) e os ajustes finos em `resources/views/filament/brand-style.blade.php`, injetado por render hook, sem build de tema. O menu lateral é plano (Produtos, Pedidos, Cupons, Clientes, Configurações) e tem um campo de busca que filtra os módulos pelo nome (`resources/views/filament/sidebar-search.blade.php`, injetado por render hook). A busca global do topo está desativada. Só entra quem tem `is_admin` na conta; o primeiro administrador é criado com `php artisan velaris:admin e-mail --senha=...` e pode promover outros na tela de clientes.

- **Produtos**: dados, notas, cor da cera, destaque e ativo. Em cada tamanho: preço, estoque, preço promocional com período de início e fim.
- **Cupons**: código, tipo (percentual, valor fixo ou frete grátis), compra mínima, validade, limite de usos e ativo.
- **Formas de pagamento**: nome exibido, tipo (Pix, cartão, boleto, outro), desconto %, parcelas sem juros, valor mínimo da parcela, ativa e ordem (arrastável). O checkout lista só as ativas e o cliente escolhe as parcelas.
- **Pedidos**: lista com status, pagamento, cupom e total. A tela do pedido mostra Pedido, Entrega (endereço, CEP, cidade, bairro, complemento) e Contato (e-mail, telefone, WhatsApp com link) lado a lado, depois a tabela de itens com preço unitário (e o preço cheio quando houve promoção) e os totais; só o status é editável. Todas as listas têm Editar e Excluir por linha. O menu mostra quantos estão "recebidos".
- **Clientes**: lista, pedidos por cliente e a chave de acesso ao painel.
- **Configurações**: frete grátis a partir de, valor do frete, máximo por item e limite de estoque baixo. Ficam na tabela `settings` e sobrepõem o `.env`. O desconto do Pix passou para Formas de pagamento.
- **Início**: pedidos de hoje, receita do mês, pedidos aguardando, estoque baixo, cupons ativos e a lista de tamanhos para repor.

## Regras de estoque e promoções

- Estoque por tamanho. Em zero, a API recusa adicionar ao carrinho (422) e a loja mostra "Esgotado". Abaixo do limite configurado, "Últimas unidades". O pedido baixa o estoque com trava de linha e falha se outro pedido consumiu antes.
- Preço promocional vale só dentro do período e só se menor que o preço cheio. A API devolve `price` (o cobrado agora), `listPrice`, `onSale` e `promoEndsAt`. O pedido grava os dois preços.
- Formas de pagamento: o desconto da forma incide sobre o subtotal já com cupom. Parcelas sem juros limitadas pelo máximo da forma e pelo valor mínimo da parcela. O pedido grava forma, nome e número de parcelas.
- Cupons: `POST /api/checkout/totais` devolve a prévia e `POST /api/pedidos` aceita `cupom`, incrementa o uso e grava código e desconto no pedido.

Configuração relevante no `.env`:

- `FRONTEND_URLS`: origens autorizadas no CORS, separadas por vírgula.
- `FRETE_GRATIS_A_PARTIR_DE`, `FRETE_VALOR`, `DESCONTO_PIX`, `QUANTIDADE_MAXIMA_POR_ITEM`: regras comerciais (`config/velaris.php`).

## Endpoints

Autenticação por token (Laravel Sanctum) no cabeçalho `Authorization: Bearer`. Criar pedido e consultar a conta exigem login.

Visitante: o carrinho é identificado pelo cabeçalho `X-Cart-Token` (UUID). Sem cabeçalho, ou com token desconhecido, `GET /api/carrinho` e `POST /api/carrinho/itens` criam um carrinho novo e devolvem o token no corpo e no cabeçalho da resposta. Cliente autenticado: o carrinho é o da conta, e ao entrar ou se cadastrar os itens do carrinho de visitante são movidos para ela.

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/api/config` | Regras comerciais para a loja: frete grátis a partir de, valor do frete, máximo por item, limite de estoque baixo e formas de pagamento ativas. |
| GET | `/api/produtos` | Lista produtos ativos. Filtros: `colecao`, `familia`, `destaque=1`. |
| GET | `/api/produtos/{slug}` | Detalhe do produto com tamanhos. |
| GET | `/api/carrinho` | Carrinho atual (cria se não existir). |
| POST | `/api/carrinho/itens` | `{ slug, size: p\|m\|g, quantity }`. Soma se o item já existe. |
| PATCH | `/api/carrinho/itens/{id}` | `{ quantity }`. Zero remove. |
| DELETE | `/api/carrinho/itens/{id}` | Remove o item. |
| POST | `/api/pedidos` | `{ pagamento (código), parcelas?, cupom? }` mais `contato_id` (contato salvo) **ou** `{ nome, telefone, whatsapp?, cpf }`, mais `endereco_id` (endereço salvo) **ou** `{ apelido?, cep, cidade, endereco, numero, complemento? }` (novo endereço com `bairro`, que fica salvo na conta). Nome, e-mail, telefone, WhatsApp e CPF do pedido vêm do contato e da conta. Grava o pedido com snapshot dos itens e do endereço e esvazia o carrinho. |
| GET | `/api/pedidos/{number}` | Consulta um pedido do cliente autenticado. |
| POST | `/api/checkout/totais` | `{ pagamento (código da forma), parcelas?, cupom? }`. Prévia: subtotal, desconto da forma, desconto do cupom, frete, total e parcelas (quantidade, valor, máximo). |
| POST | `/api/auth/registrar` | `{ nome, email, senha, senha_confirmation }`. Devolve `{ token, user }`. Mescla o carrinho do `X-Cart-Token` na conta. |
| POST | `/api/auth/entrar` | `{ email, senha }`. Devolve `{ token, user }` e mescla o carrinho de visitante. |
| POST | `/api/auth/sair` | Revoga o token atual. |
| GET | `/api/auth/eu` | Dados do cliente autenticado, com endereço salvo. |
| POST | `/api/auth/google` | `{ credential }` (ID token do botão do Google). Cria a conta ou vincula pelo e-mail; devolve `{ token, user }`. 503 se `GOOGLE_CLIENT_ID` não estiver configurado. |
| GET/POST/PATCH/DELETE | `/api/conta/contatos[/{id}]` | Contatos do cliente: `{ nome, telefone, whatsapp?, cpf, padrao? }`. CPF validado pelos dígitos verificadores. O primeiro vira padrão. |
| GET | `/api/conta/enderecos` | Endereços do cliente, padrão primeiro. |
| POST | `/api/conta/enderecos` | `{ apelido?, cep, cidade, bairro, endereco, numero, complemento?, padrao? }`. O primeiro vira padrão. |
| PATCH | `/api/conta/enderecos/{id}` | Mesmos campos. `padrao: true` desmarca os demais. |
| DELETE | `/api/conta/enderecos/{id}` | Remove; se era o padrão, o próximo assume. |
| GET | `/api/conta/pedidos` | Pedidos do cliente, mais recentes primeiro. |

Regras: frete grátis a partir de R$ 180, senão R$ 18. Pix aplica 5% de desconto. Máximo de 10 unidades por item. Erros de validação voltam como 422 com mensagens em português.

## Login com Google

O front usa o botão do Google Identity Services e envia o ID token para `POST /api/auth/google`. A API valida a assinatura com as chaves públicas do Google (`firebase/php-jwt`, cache de uma hora), confere emissor, audiência e e-mail verificado, e então cria a conta ou vincula pelo e-mail. Contas criadas pelo Google ficam sem senha.

Para ativar:

1. No [Google Cloud Console](https://console.cloud.google.com/apis/credentials), crie uma credencial **OAuth 2.0 Client ID** do tipo **Aplicativo da Web**.
2. Em **Origens JavaScript autorizadas**, adicione as URLs do front, por exemplo `http://localhost:3002`. O Google não aceita IPs privados como `192.168.x.x`; para testar pela rede use um hostname (ex.: `http://meu-pc.local:3002`) ou um túnel HTTPS.
3. Copie o Client ID para `GOOGLE_CLIENT_ID` no `.env` desta API e para `NEXT_PUBLIC_GOOGLE_CLIENT_ID` no `.env.local` do front. Reinicie os dois.

Sem o Client ID, o botão aparece desativado no front e a API responde 503.

## Estrutura

```
app/
├── Http/Controllers/Api/   ProdutoController, CarrinhoController, PedidoController, AuthController, ContaController
├── Http/Requests/          validação de entrada
├── Http/Resources/         formato JSON (camelCase, sem wrapper data)
├── Filament/               painel: Resources (Products, Coupons, Orders, Users), Pages/Configuracoes, Widgets
├── Models/                 User, Address, Contact, Product, ProductSize, Cart, CartItem, Order, OrderItem, Coupon, PaymentMethod, Setting
├── Support/Configuracao    parâmetros comerciais editáveis (settings) com fallback em config/velaris.php
└── Services/               CarrinhoService (token, mesclagem, estoque), PedidoService (totais, forma de pagamento, cupom, baixa de estoque), CupomService, EnderecoService, ContatoService, GoogleAuthService
database/seeders/ProductSeeder.php   catálogo inicial (9 velas)
lang/pt_BR/                 mensagens de validação e erros
tests/Feature/              CarrinhoTest, PedidoTest, AuthTest, EnderecoTest, ContatoTest, PagamentoTest, EstoquePromocaoCupomTest
```

## Próximos passos

- Gateway de pagamento real (Pix e cartão) e webhook de confirmação.
- Cálculo de frete por CEP.
- E-mail de confirmação do pedido.
- Recuperação de senha por e-mail.
