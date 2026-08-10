<x-modal id="campaign-info-edit-modal" size="sm" title="Editar informação" wire="editModal" x-on:open="setTimeout(() => $refs.editTitle.focus(), 250)" x-on:close="$wire.closeEditModal()">
    <form id="campaign-info-edit-form" wire:submit="update" class="space-y-4">
        <x-input label="Título *" x-ref="editTitle" wire:model="editTitle" required />
        <x-textarea label="Informação *" wire:model="editContent" required />
    </form>

    <x-slot:footer>
        <x-button text="Cancelar" color="gray" x-on:click="$tsui.close.modal('campaign-info-edit-modal')" />
        <x-button type="submit" form="campaign-info-edit-form" text="Salvar alterações" loading="update" />
    </x-slot:footer>
</x-modal>
