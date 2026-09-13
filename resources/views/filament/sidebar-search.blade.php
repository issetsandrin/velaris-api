<div class="velaris-nav-search" x-data="velarisNavSearch()" x-init="init()">
    <label class="velaris-nav-search-field">
        <x-filament::icon icon="heroicon-o-magnifying-glass" class="velaris-nav-search-icon" />
        <input
            type="search"
            x-model="termo"
            x-on:input="filtrar()"
            x-on:keydown.escape="limpar()"
            placeholder="Buscar módulo…"
            aria-label="Buscar módulo no menu"
            autocomplete="off"
        />
        <button type="button" x-show="termo.length" x-on:click="limpar()" aria-label="Limpar busca">×</button>
    </label>
    <p class="velaris-nav-search-empty" x-show="vazio" x-cloak>Nenhum módulo com esse nome.</p>
</div>

<script>
    function velarisNavSearch() {
        return {
            termo: '',
            vazio: false,
            init() {
                // Reaplica o filtro após navegação SPA do Livewire, se habilitada.
                document.addEventListener('livewire:navigated', () => this.filtrar());
            },
            normalizar(texto) {
                return (texto || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
            },
            limpar() {
                this.termo = '';
                this.filtrar();
            },
            filtrar() {
                const termo = this.normalizar(this.termo);
                const nav = this.$root.closest('.fi-sidebar-nav') || document;
                let visiveis = 0;

                nav.querySelectorAll('.fi-sidebar-item').forEach((item) => {
                    const rotulo = this.normalizar(item.querySelector('.fi-sidebar-item-label')?.textContent);
                    const mostra = !termo || rotulo.includes(termo);
                    item.style.display = mostra ? '' : 'none';
                    if (mostra) visiveis++;
                });

                nav.querySelectorAll('.fi-sidebar-group').forEach((grupo) => {
                    const temItem = [...grupo.querySelectorAll('.fi-sidebar-item')].some((item) => item.style.display !== 'none');
                    grupo.style.display = temItem ? '' : 'none';
                    // Com busca ativa, abre o grupo para mostrar o resultado.
                    const itens = grupo.querySelector('.fi-sidebar-group-items');
                    if (termo && temItem && itens) itens.style.display = '';
                });

                this.vazio = Boolean(termo) && visiveis === 0;
            },
        };
    }
</script>
