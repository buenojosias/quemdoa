<x-modal title="Confirmar recebimento" id="set-item-received-modal" wire size="sm" center x-on:close="$dispatch('set-item-received-modal-closed')">
    <form
        x-on:submit.prevent="$dispatch('set-item-received-save', { receivedQuantity: $el.querySelector('[dusk=tallstackui_form_number_input]').value })"
        id="set-item-received-form"
        class="space-y-4">
        <div class="rounded-md border border-gray-200 p-3 text-sm dark:border-gray-700">
            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $itemName }}</p>
            <p class="text-gray-500 dark:text-gray-400">
                Quantidade prometida: {{ $formattedBagItemQuantity }} {{ $itemUnitLabel }}
            </p>
        </div>

        <div wire:key="set-item-received-number-{{ $bagItemId ?? 'empty' }}">
            <x-number
                :label="'Quantidade recebida (' . $itemUnitLabel . ') *'"
                wire:model="receivedQuantity"
                :value="$receivedQuantity"
                min="0.1"
                step="0.5"
                centralized />
        </div>
    </form>

    <x-slot:footer>
        <x-button text="Cancelar" color="gray" x-on:click="$tsui.close.modal('set-item-received-modal')" />
        <x-button type="submit" form="set-item-received-form" text="Confirmar" />
    </x-slot:footer>
</x-modal>
