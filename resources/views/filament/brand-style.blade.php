<style>
    /* Paleta Velaris: superfícies brancas do Filament passam para o tom de cera da loja */
    :root {
        --color-white: #fbf8f2;
    }

    .fi-body {
        background-color: #f5f0e8;
        color: #4a3818;
    }

    /* Menu lateral um tom acima do fundo: dourado claro bem diluído */
    .fi-sidebar {
        background-color: #ede4cf !important;
        border-color: rgba(122, 93, 41, 0.2) !important;
    }

    .fi-sidebar-item-label {
        transition-property: none !important;
    }

    .fi-sidebar-item-btn:hover {
        background-color: rgba(210, 182, 115, 0.35) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: rgba(185, 153, 77, 0.35) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon {
        color: #634a20 !important;
    }

    .fi-topbar {
        border-color: rgba(122, 93, 41, 0.18) !important;
    }

    /* Menu mais compacto: menos respiro lateral e vertical */
    .fi-sidebar-nav {
        padding-inline: 0.75rem !important;
        padding-block: 0.75rem !important;
        row-gap: 1rem !important;
    }

    .fi-sidebar-nav-groups {
        margin-inline: 0 !important;
    }

    /* Busca de módulos no menu lateral */
    .velaris-nav-search {
        padding: 0 0 0.25rem;
    }

    .velaris-nav-search-field {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.65rem;
        height: 2.25rem;
        border: 1px solid rgba(122, 93, 41, 0.28);
        border-radius: 0.5rem;
        background: #fbf8f2;
        color: #7a5d29;
        transition: border-color 150ms, box-shadow 150ms;
    }

    .velaris-nav-search-field:focus-within {
        border-color: #b9994d;
        box-shadow: 0 0 0 3px rgba(210, 182, 115, 0.35);
    }

    .velaris-nav-search-icon {
        width: 1.1rem;
        height: 1.1rem;
        flex-shrink: 0;
        color: #9a7935;
    }

    .velaris-nav-search-field input {
        flex: 1;
        min-width: 0;
        border: 0;
        background: transparent;
        font-size: 0.875rem;
        color: #4a3818;
        outline: none;
        padding: 0;
    }

    .velaris-nav-search-field input::placeholder {
        color: #9a7935;
        opacity: 0.8;
    }

    .velaris-nav-search-field input::-webkit-search-cancel-button {
        display: none;
    }

    .velaris-nav-search-field button {
        color: #9a7935;
        font-size: 1.1rem;
        line-height: 1;
    }

    .velaris-nav-search-empty {
        margin: 0.5rem 0.25rem 0;
        font-size: 0.8rem;
        color: #9a7935;
    }

    /* Cards lado a lado com a mesma altura */
    .fi-grid-col > .fi-sc-component,
    .fi-sc-component > .fi-sc-section,
    .fi-sc-section > .fi-section {
        height: 100%;
    }

    .fi-sc-section > .fi-section {
        display: flex;
        flex-direction: column;
    }

    .fi-sc-section > .fi-section > .fi-section-content-ctn {
        flex: 1;
    }

    /* Tabela de itens na tela do pedido */
    .velaris-itens {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    .velaris-itens th {
        text-align: left;
        font-weight: 600;
        color: #7a5d29;
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid rgba(122, 93, 41, 0.25);
        background: rgba(210, 182, 115, 0.18);
    }

    .velaris-itens td {
        padding: 0.7rem 0.75rem;
        border-bottom: 1px solid rgba(122, 93, 41, 0.12);
        color: #4a3818;
    }

    .velaris-itens th.num,
    .velaris-itens td.num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .velaris-itens tr:last-child td {
        border-bottom: 0;
    }

    .velaris-itens .muted {
        color: #9a7935;
        font-size: 0.8rem;
    }

    .velaris-totais {
        width: 100%;
        font-size: 0.9rem;
    }

    .velaris-totais div {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        color: #7a5d29;
    }

    .velaris-totais div span:last-child {
        color: #4a3818;
        font-variant-numeric: tabular-nums;
    }

    .velaris-totais .total {
        margin-top: 0.4rem;
        padding-top: 0.6rem;
        border-top: 1px solid rgba(122, 93, 41, 0.3);
        font-size: 1.1rem;
        font-weight: 700;
        color: #4a3818;
    }

    /* Menu recolhido no desktop: esconde a busca */
    .fi-sidebar:not(.fi-sidebar-open) .velaris-nav-search {
        display: none;
    }

    .fi-logo {
        color: #7a5d29;
        letter-spacing: -0.03em;
    }

    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat,
    .fi-simple-main {
        border-color: rgba(122, 93, 41, 0.16) !important;
        box-shadow: 0 1px 2px rgba(122, 93, 41, 0.06), 0 8px 24px rgba(122, 93, 41, 0.06) !important;
    }

    .fi-input,
    .fi-select-input,
    .fi-fo-color-picker input,
    textarea.fi-input {
        background-color: #fffdf9 !important;
    }
</style>
