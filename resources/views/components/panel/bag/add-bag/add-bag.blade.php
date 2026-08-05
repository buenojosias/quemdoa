<div>
    <x-slot:footer class="flex justify-between">
        <x-button text="Adicionar sacola" x-on:click="$dispatch('open-add-modal')" />
        <x-button text="Fechar" color="neutral" flat x-on:click="$tsui.close.slide('item-bags-slide')" />
    </x-slot:footer>

    <x-modal title="Adicionar sacola" size="sm" id="add-bag-modal" wire x-on:close="$dispatch('add-modal-closed')">
        <form wire:submit="save" id="add-bag-form" class="space-y-4">
            <x-input label="Nome do participante *" wire:model="participant_name" required />
            <x-input label="WhatsApp do participante" wire:model="participant_whatsapp" placeholder="99 99999-9999" x-mask="99 99999-9999" />
            <x-side-bar.separator text="Item" line />
            <x-input label="Item" wire:model="item_name" readonly />
            <x-number :label="'Quantidade (' . ($item?->unit?->label() ?? '') . ') *'" wire:model="quantity" min="0.1" step="0.5" centralized />
            <x-toggle label="Recebido" wire:model="received" />
        </form>
        <x-slot:footer>
            <x-button text="Cancelar" x-on:click="$tsui.close.modal('add-bag-modal')" color="gray" />
            <x-button type="submit" form="add-bag-form" text="Salvar" loading="save" />
        </x-slot>
    </x-modal>
</div>
