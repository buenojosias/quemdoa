
<div>
    <x-button text="Adicionar item" icon="plus" wire:click="$toggle('modal')" />

    <x-modal title="Adicionar item à sacola {{ $bagCode }}" wire size="lg">
        
    </x-modal>
</div>
