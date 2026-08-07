<div>
    <x-button text="Adicionar item" icon="plus" wire:click="$toggle('modal')" />

    <x-modal title="Adicionar item" wire x-on:open="setTimeout(() => $refs.name.focus(), 250)">
        <form id="item-create" wire:submit="save" class="space-y-4">
            <x-input label="Nome do item *" x-ref="name" wire:model="name" required />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                
                <x-input label="Complemento" placeholder="Ex.: Pacote de 500g" hint="Opcional" wire:model="complement" />

                <x-select.native label="Categoria *"
                                 wire:model="category"
                                 :options="$this->categoryOptions()"
                                 select="label:label|value:value"
                                 required />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-select.native label="Unidade de medida *"
                                 wire:model="unit"
                                 :options="$this->unitOptions()"
                                 select="label:label|value:value"
                                 required />

                <x-number label="Quantidade necessária *"
                          wire:model="required_quantity"
                          :min="0.5"
                          step="0.5"
                          centralized
                          required />
    
                <x-date label="Data limite de entrega"
                        wire:model="delivery_date"
                        name="delivery_date"
                        format="DD/MM/YYYY"
                        :min-date="$this->minimumDeliveryDate()"
                        :max-date="$this->maximumDeliveryDate()"
                        hint="Informe se este item precisar ser entregue antes dos demais" />

                <x-textarea label="Observações" placeholder="Ex.: Manter congelado até a entrega." wire:model="note" />
            </div>
        </form>

        <x-slot:footer>
            <x-button type="submit" form="item-create" text="Adicionar item" />
        </x-slot:footer>
    </x-modal>
</div>
