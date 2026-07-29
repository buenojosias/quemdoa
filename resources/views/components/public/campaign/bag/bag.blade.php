<x-slide id="public-campaign-bag-slide" title="Itens da sacola" size="sm" wire>
    <div class="space-y-3">
        @forelse ($bagItems as $item)
            <div wire:key="public-campaign-bag-item-{{ $item['id'] }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $item['name'] }}</h3>
                        @if ($item['complement'])
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ $item['complement'] }}
                            </p>
                        @endif
                        <p class="mt-2 text-sm font-medium text-primary-700 dark:text-primary-300">
                            {{ $item['formattedQuantity'] }} {{ $item['unitAbbreviation'] }}
                        </p>
                        @if ($item['deliveryDate'])
                            <p class="mt-2 flex gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <x-icon name="calendar" outline class="h-3.5 w-3.5 shrink-0" />
                                Entregar até {{ $item['deliveryDate'] }}
                            </p>
                        @endif
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <x-button.group>
                            <x-button
                                icon="minus"
                                color="gray"
                                flat
                                sm
                                :disabled="$item['quantity'] <= 0.1"
                                x-on:click="$dispatch('public-campaign-bag-decrement.{{ $campaignId }}', { item: {{ $item['id'] }} })" />
                            <x-button
                                icon="plus"
                                color="gray"
                                flat
                                sm
                                :disabled="$item['quantity'] >= $item['pendingBaggedQuantity']"
                                x-on:click="$dispatch('public-campaign-bag-increment.{{ $campaignId }}', { item: {{ $item['id'] }} })" />
                        </x-button.group>
                        <x-button
                            text="Remover"
                            icon="trash"
                            color="red"
                            flat
                            sm
                            x-on:click="$dispatch('public-campaign-bag-remove.{{ $campaignId }}', { item: {{ $item['id'] }} })" />
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                Sua sacola ainda está vazia.
            </div>
        @endforelse
    </div>

    <x-slot:footer>
        <x-button
            text="Concluir"
            block
            :disabled="$bagItems === []"
            x-on:click="$tsui.close.slide('public-campaign-bag-slide'); $dispatch('public-campaign-bag-finish.{{ $campaignId }}')" />
    </x-slot:footer>
</x-slide>
