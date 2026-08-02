<x-modal id="public-campaign-item-add-modal" wire size="sm" title="Vou doar" center x-on:close="$dispatch('public-campaign-item-add-modal-closed')">
    @if ($itemId)
        <div class="space-y-4">
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $itemName }}</p>
                @if ($itemComplement)
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ $itemComplement }}</p>
                @endif
            </div>

            <p class="text-sm text-gray-700 dark:text-gray-200">
                Informe a quantidade deste item que você irá doar.
            </p>

            <form
                id="public-campaign-item-add-form"
                x-on:submit.prevent="$dispatch('public-campaign-item-add-save', { quantity: $el.querySelector('[dusk=tallstackui_form_number_input]').value })"
                class="grid grid-cols-1 gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                <div
                    wire:key="public-campaign-item-add-number-{{ $itemId }}-{{ str_replace('.', '-', (string) $pendingBaggedQuantity) }}"
                    x-on:input.debounce.150ms="$dispatch('public-campaign-item-add-quantity-updated', { quantity: $event.target.value })"
                    x-on:change="$dispatch('public-campaign-item-add-quantity-updated', { quantity: $event.target.value })">
                    <x-number
                        :label="'Quantidade (' . $unitAbbreviation . ') *'"
                        wire:model="quantity"
                        :min="0.1"
                        :max="$pendingBaggedQuantity"
                        step="0.5"
                        centralized />
                </div>

                <div class="rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-300 sm:mt-7">
                    Pendente: <span class="font-semibold">{{ $formattedPendingBaggedQuantity }} {{ $unitAbbreviation }}</span>
                </div>
            </form>

            @if ($deliveryDate)
                <p class="flex gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <x-icon name="calendar" outline class="mt-0.5 h-4 w-4 shrink-0" />
                    Entregar até {{ $deliveryDate }}
                </p>
            @endif

            @if ($note)
                <div class="rounded-md border border-gray-200 p-3 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">
                    <p class="font-medium text-gray-500 dark:text-gray-400">Observações</p>
                    <p class="mt-1">{{ $note }}</p>
                </div>
            @endif
        </div>
    @endif

    <x-slot:footer>
        <x-button text="Cancelar" class="w-1/2" color="gray" outline x-on:click="$dispatch('public-campaign-item-add-modal-closed')" />
        <x-button type="submit" form="public-campaign-item-add-form" text="Adicionar à sacola" class="w-1/2" loading="addToBag" />
    </x-slot:footer>
</x-modal>
