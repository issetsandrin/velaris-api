@php
    // Frases de bastidor: falam com quem administra, não com quem compra.
    $frases = [
        ['titulo' => 'O balcão da loja, por dentro.', 'texto' => 'Pedidos, estoque, cupons e vitrine no mesmo lugar. O que muda aqui a loja mostra na hora.'],
        ['titulo' => 'Estoque é por tamanho.', 'texto' => 'Cada vela tem três tamanhos, e cada tamanho o seu próprio estoque. Abaixo do alerta, a loja anuncia "últimas unidades".'],
        ['titulo' => 'Frete e pagamento são cadastro.', 'texto' => 'Formas de entrega e de pagamento ficam em tabelas: criar outra é preencher e ativar, sem mexer em código.'],
        ['titulo' => 'Ambiente de demonstração.', 'texto' => 'Os pedidos são gravados de verdade, mas nenhuma cobrança é feita e nenhum dado de cartão é pedido.'],
    ];
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div class="velaris-auth">
        <aside
            class="velaris-auth-aside"
            x-data="{
                atual: 0,
                total: {{ count($frases) }},
                init() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    setInterval(() => this.atual = (this.atual + 1) % this.total, 6000);
                },
            }"
        >
            <div class="velaris-auth-glow" aria-hidden="true"></div>

            <div class="velaris-auth-slides">
                @foreach ($frases as $indice => $frase)
                    <figure class="velaris-auth-slide" x-show="atual === {{ $indice }}" x-cloak>
                        <svg class="velaris-auth-flame" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z" />
                        </svg>
                        <h2 class="velaris-auth-title">{{ $frase['titulo'] }}</h2>
                        <figcaption class="velaris-auth-text">{{ $frase['texto'] }}</figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="velaris-auth-dots" aria-hidden="true">
                @foreach ($frases as $indice => $frase)
                    <span class="velaris-auth-dot" :data-ativo="atual === {{ $indice }} ? '' : null"></span>
                @endforeach
            </div>
        </aside>

        <div class="velaris-auth-side">
            <div class="velaris-auth-topbar">
                <span class="velaris-auth-brand">{{ filament()->getBrandName() }}</span>
                <a href="{{ config('velaris.loja_url') }}" class="velaris-auth-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5" /><path d="m12 19-7-7 7-7" />
                    </svg>
                    Voltar à loja
                </a>
            </div>

            <main class="velaris-auth-card" id="fi-main-content">
                {{ $slot }}
            </main>
        </div>
    </div>

    @include('filament.auth-style')
</x-filament-panels::layout.base>
