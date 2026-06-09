<x-filament-panels::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <x-filament::button type="submit" color="primary" class="mt-6">
            Send Broadcast
        </x-filament::button>
    </form>
</x-filament-panels::page>
