<style>
    /* ---------------------------------------------------------------------
       Login do painel, espelhando /entrar da loja: dois painéis, gradiente
       dourado à esquerda e o formulário à direita, na tipografia da marca.
    --------------------------------------------------------------------- */
    [x-cloak] { display: none !important; }

    .fi-body:has(.velaris-auth) { background-color: #f5f0e8; }

    .velaris-auth {
        --paper: #f5f0e8;
        --wax: #fbf8f2;
        --ink: #4a3818;
        --ink-soft: #7a5d29;
        --brown: #7a5d29;
        --amber: #b9994d;
        --line-strong: rgba(122, 93, 41, 0.4);
        --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        min-height: 100vh;
        color: var(--ink);
    }

    .velaris-auth-aside {
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 2.5rem;
        padding: clamp(2rem, 5vw, 4rem);
        background: linear-gradient(160deg, #efe4cc 0%, #d2b673 55%, #b9994d 100%);
    }

    .velaris-auth-glow {
        position: absolute;
        inset: 5% -20% auto -20%;
        height: 80%;
        background: radial-gradient(ellipse at 50% 40%, rgba(251, 248, 242, 0.8) 0%, rgba(251, 248, 242, 0.25) 40%, transparent 68%);
        pointer-events: none;
    }

    .velaris-auth-slides {
        position: relative;
        display: grid;
        place-items: center;
        width: min(100%, 30rem);
        min-height: 16rem;
        text-align: center;
    }

    .velaris-auth-slide {
        grid-area: 1 / 1;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        animation: velaris-slide-in 600ms var(--ease-out) both;
    }

    @keyframes velaris-slide-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }

    .velaris-auth-flame { width: 2rem; height: 2rem; color: var(--brown); }

    .velaris-auth-title {
        font-size: clamp(1.6rem, 2.6vw, 2.2rem);
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.02em;
        margin: 0;
        color: var(--ink);
    }

    .velaris-auth-text {
        max-width: 34ch;
        font-size: 1rem;
        line-height: 1.55;
        color: var(--ink-soft);
    }

    .velaris-auth-dots { position: relative; display: flex; gap: 0.4rem; }

    .velaris-auth-dot {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 999px;
        background: rgba(122, 93, 41, 0.35);
        transition: width 220ms var(--ease-out), background-color 220ms var(--ease-out);
    }

    .velaris-auth-dot[data-ativo] { width: 1.5rem; background: var(--brown); }

    .velaris-auth-side {
        display: grid;
        grid-template-rows: auto 1fr;
        justify-items: center;
        padding: 1.5rem clamp(1.25rem, 4vw, 3.5rem) clamp(2.5rem, 6vw, 5rem);
    }

    .velaris-auth-topbar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: clamp(1.5rem, 4vw, 3rem);
    }

    .velaris-auth-brand {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1;
    }

    .velaris-auth-back {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.95rem;
        color: var(--ink-soft);
        text-decoration: none;
        transition: color 160ms var(--ease-out);
    }

    .velaris-auth-back:hover { color: var(--ink); }
    .velaris-auth-back svg { width: 1rem; height: 1rem; }

    .velaris-auth-card {
        width: min(100%, 26rem);
        align-self: center;
    }

    /* ---- o formulário do Filament vestido com a cara da loja ---- */
    .velaris-auth .fi-simple-page,
    .velaris-auth .fi-simple-page-content { width: 100%; gap: 1.5rem; }

    .velaris-auth .fi-simple-header {
        text-align: left !important;
        align-items: flex-start !important;
        gap: 0.5rem;
    }

    .velaris-auth .fi-simple-header-heading {
        font-size: clamp(2.2rem, 4vw, 3.25rem) !important;
        font-weight: 600 !important;
        line-height: 1.05 !important;
        letter-spacing: -0.02em !important;
        text-align: left !important;
        color: var(--ink) !important;
    }

    .velaris-auth .fi-simple-header-subheading {
        font-size: 0.95rem !important;
        line-height: 1.5 !important;
        text-align: left !important;
        color: var(--ink-soft) !important;
        max-width: 36ch;
    }

    .velaris-auth .fi-fo-field-label,
    .velaris-auth .fi-fo-field-label-content {
        font-size: 0.9rem !important;
        font-weight: 400 !important;
        color: var(--ink-soft) !important;
    }

    /* A loja não marca campo obrigatório com asterisco vermelho */
    .velaris-auth .fi-fo-field-label-required-mark { display: none !important; }

    .velaris-auth .fi-input-wrp {
        background-color: var(--wax) !important;
        border-radius: 3px !important;
        box-shadow: none !important;
        outline: 1px solid var(--line-strong);
        outline-offset: -1px;
        transition: outline-color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
    }

    .velaris-auth .fi-input-wrp:has(:focus) {
        outline-color: var(--amber);
        box-shadow: 0 0 0 3px rgba(210, 182, 115, 0.35) !important;
    }

    .velaris-auth .fi-input { padding-block: 0.75rem !important; color: var(--ink) !important; }

    .velaris-auth .fi-btn {
        border-radius: 3px !important;
        min-height: 3rem;
        font-weight: 600 !important;
        letter-spacing: 0;
        background: var(--brown) !important;
        color: var(--wax) !important;
        border: 0 !important;
        box-shadow: none !important;
        transition: background-color 180ms var(--ease-out) !important;
    }

    .velaris-auth .fi-btn:hover { background: var(--ink) !important; }
    .velaris-auth .fi-btn-label { color: var(--wax) !important; }

    .velaris-auth .fi-fo-checkbox-input:checked { background-color: var(--brown) !important; }

    @media (max-width: 900px) {
        .velaris-auth { grid-template-columns: 1fr; min-height: 0; }

        .velaris-auth-aside {
            flex-direction: row;
            justify-content: flex-start;
            gap: 1.5rem;
            padding: 1.5rem clamp(1.25rem, 4vw, 3.5rem);
        }

        .velaris-auth-slides { min-height: 0; text-align: left; width: auto; }
        .velaris-auth-slide { align-items: flex-start; }
        .velaris-auth-glow { display: none; }
        .velaris-auth-card { align-self: start; }
    }

    @media (prefers-reduced-motion: reduce) {
        .velaris-auth-slide { animation: none; }
    }
</style>
