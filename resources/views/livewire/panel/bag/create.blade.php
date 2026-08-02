<div>
    <x-button text="Adicionar sacola" icon="plus" x-on:click="$tsui.open.modal('create-modal')" />

    <x-modal title="Criar sacola" id="create-modal" size="md" x-on:open="setTimeout(() => $refs.name.focus(), 250)">
        <form id="bag-create" wire:submit="save" class="space-y-4">
            <x-input label="Campanha" value="{{ $this->campaign_name }}" disabled />
            <x-input label="Nome do participante *" x-ref="name" wire:model="participant_name"  />
            <x-input label="WhatsApp do participante" wire:model="participant_whatsapp" placeholder="99 99999-9999" x-mask="99 99999-9999" />
        </form>

        <x-slot:footer>
            <x-button type="submit" form="bag-create" text="Salvar e continuar" />
        </x-slot:footer>
    </x-modal>
</div>
