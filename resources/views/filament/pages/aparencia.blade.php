<x-filament-panels::page>
    <form wire:submit="salvar" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Salvar aparência
        </x-filament::button>
    </form>
</x-filament-panels::page>
