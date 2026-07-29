<x-slide title="Itens da sacola" size="sm" wire>
    <div class="space-y-3">

        @foreach ($bagItems as $item)
            <div class="flex justify-between items-center gap-4 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div>
                    <h3 class="text-sm font-medium">{{ $item['name'] }}</h3>
                    @if ($item['complement'])
                        <p class="text-xs text-gray-500">
                            {{ $item['complement'] }}
                        </p>
                    @endif
                    <p class="text-sm text-gray-500">
                        {{ $item['quantity'] }} {{ $item['unitAbbreviation'] }}
                    </p>
                    @if ($item['deliveryDate'])
                        <p class="text-xs text-gray-500">
                            Entregar até: {{ $item['deliveryDate'] }}
                        </p>
                    @endif
                </div>
                <div>
                    <x-button.group>
                        <x-button icon="minus" color="gray" flat sm />
                        <x-button icon="plus" color="gray" flat sm />
                    </x-button.group>
                    <x-button icon="trash" color="gray" flat sm />
                </div>
            </div>
        @endforeach

    </div>
</x-slide>