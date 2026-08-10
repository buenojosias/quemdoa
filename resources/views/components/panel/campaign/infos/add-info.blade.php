<x-modal id="campaign-info-add-modal" size="sm" title="Adicionar informação" wire x-on:open="setTimeout(() => $refs.title.focus(), 250)" x-on:close="$wire.closeModal()">
    <form id="campaign-info-add-form" wire:submit="save" class="space-y-4">
        <x-input label="Título *" x-ref="title" wire:model="title" required />
        <x-textarea label="Informação *" wire:model="content" required />
    </form>

    <x-slot:footer>
        <x-button text="Cancelar" color="gray" x-on:click="$tsui.close.modal('campaign-info-add-modal')" />
        <x-button type="submit" form="campaign-info-add-form" text="Salvar" loading="save" />
    </x-slot:footer>
</x-modal>
