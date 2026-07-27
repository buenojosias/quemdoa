<div>
    <x-slide wire title="Sacolas" id="item-bags-slide" persistent size="xl">
        <div class="space-y-4">
            @if ($itemName)
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Item selecionado</p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $itemName }}</h3>
                </div>

                <div class="my-6 flex gap-4">
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade prometida" />
                        <x-label :label="$formattedItemBaggedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemBaggedQuantity / $itemRequiredQuantity * 100" color="cyan" />
                    </div>
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade recebida" />
                        <x-label :label="$formattedItemReceivedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemReceivedQuantity / $itemRequiredQuantity * 100" color="green" />
                    </div>
                </div>
            @endif

            <h3 class="font-semibold">Sacolas</h3>
            <div class="space-y-2">
                @forelse ($this->bagItems as $item)
                    <x-panel.bag.list-item :item="$item" :unit="$itemUnitLabel" />
                @empty
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Nenhuma sacola de doação encontrada para este item.</p>
                @endforelse
            </div>
        </div>

        @if ($itemId)
            <livewire:panel.bag.add-bag :itemId="$itemId" />
        @endif
    </x-slide>
</div>
