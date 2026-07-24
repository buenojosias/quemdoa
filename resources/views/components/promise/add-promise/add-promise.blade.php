<div>
    <x-slot:footer>
        <x-button text="Adicionar promessa" x-on:click="$dispatch('open-add-modal')" />
    </x-slot:footer>

    <x-modal title="Adicionar promessa" size="sm" wire x-on:close="$dispatch('add-modal-closed')">
        <form wire:submit="save" id="add-promise-form" class="space-y-4">
            <x-input label="Nome do doador *" wire:model="donor_name" required />
            <x-input label="WhatsApp do doador" wire:model="donor_whatsapp" placeholder="99 99999-9999" x-mask="99 99999-9999" />
            <x-side-bar.separator text="Item" line />
            <x-input label="Item" wire:model="item_name" readonly />
            <x-number :label="'Quantidade (' .$item?->unit?->label() ?? '' . ') *'" wire:model="promised_quantity" min="1" centralized />
            <x-toggle label="Recebido" wire:model="received" />
        </form>
        <x-slot:footer>
            <x-button text="Cancelar" x-on:click="$dispatch('close-modal')" color="gray" />
            <x-button type="submit" form="add-promise-form" text="Salvar" loading="save" />
        </x-slot>
    </x-modal>
</div>