
<div>
    <x-button text="Adicionar item" icon="plus" wire:click="openModal" />

    <x-modal title="Adicionar item à sacola {{ $bagCode }}" wire center size="lg" scrollable x-on:close="$wire.closeModal()">
        @if ($itemsByCategory === [])
            <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Nenhum item pendente para adicionar.
            </div>
        @else
            <x-accordion multiple>
                @foreach ($itemsByCategory as $category => $items)
                    <x-accordion.items :title="$category" :id="'add-item-category-'.md5($category)">
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($items as $item)
                                <div wire:key="available-item-{{ $item['id'] }}" class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-700 dark:text-gray-200">{{ $item['name'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Pendente: {{ $item['formatted_pending_quantity'] }} {{ $item['unit_abbreviation'] }}
                                        </p>
                                    </div>

                                    <x-button text="Adicionar" sm wire:click="openAddModal({{ $item['id'] }})" />
                                </div>
                            @endforeach
                        </div>
                    </x-accordion.items>
                @endforeach
            </x-accordion>
        @endif
    </x-modal>

    <x-modal title="Adicionar {{ $selectedItemName }}" id="add-bag-item-modal" wire="addModal" size="sm" center x-on:close="$wire.closeAddModal()">
        <form wire:submit="save" id="add-bag-item-form" class="space-y-4">
            <div class="rounded-md border border-gray-200 p-3 text-sm dark:border-gray-700">
                <p class="font-medium text-gray-700 dark:text-gray-200">{{ $selectedItemName }}</p>
                <p class="text-gray-500 dark:text-gray-400">
                    Pendente: {{ $selectedItemFormattedPendingQuantity }} {{ $selectedItemUnitLabel }}
                </p>
            </div>

            <x-number :label="'Quantidade (' . $selectedItemUnitLabel . ') *'" wire:model="quantity" min="0.1" step="0.5" centralized />
            <x-toggle label="Recebido" wire:model="received" />
        </form>

        <x-slot:footer>
            <x-button text="Cancelar" color="gray" x-on:click="$tsui.close.modal('add-bag-item-modal')" />
            <x-button type="submit" form="add-bag-item-form" text="Salvar" loading="save" />
        </x-slot:footer>
    </x-modal>
</div>
