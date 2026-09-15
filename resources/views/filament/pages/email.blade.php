<x-filament-panels::page>
    <form wire:submit="salvar" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit">
                Salvar configurações
            </x-filament::button>

            <x-filament::button type="button" color="gray" wire:click="testar" wire:loading.attr="disabled">
                Enviar e-mail de teste
            </x-filament::button>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            O teste salva o que está na tela e manda uma mensagem para o e-mail da sua conta de administrador.
        </p>
    </form>
</x-filament-panels::page>
