<div>
    <x-slide wire title="Promessas de doação" id="promises-slide" persistent size="xl">
        <div class="space-y-5">
            @if ($itemName)
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Item selecionado</p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $itemName }}</h3>
                </div>

                {{-- <x-label label="Quantidade solicitada" />
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</p> --}}

                <div class="my-6 flex gap-4">
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade prometida" />
                        <x-label :label="$itemPromisedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemPromisedQuantity / $itemRequiredQuantity * 100" color="cyan" />
                    </div>
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade recebida" />
                        <x-label :label="$itemReceivedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemReceivedQuantity / $itemRequiredQuantity * 100" color="green" />
                    </div>
                </div>
            @endif


            <x-table :headers="[
                ['index' => 'donor', 'label' => 'Doador'],
                ['index' => 'promised_quantity', 'label' => 'Quantidade'],
                ['index' => 'status', 'label' => 'Status'],
                ['index' => 'actions'],
            ]"
                :rows="$this->promiseItems"
                loading>
                @interact('column_donor', $row)
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $row->promise->donor_name }}
                    </span>
                @endinteract

                @interact('column_promised_quantity', $row)
                    {{ $row->promised_quantity }}
                @endinteract

                @interact('column_status', $row)
                    <x-badge :text="$this->statusLabel($row)" :color="$this->statusColor($row)" light />
                @endinteract

                @interact('column_actions', $row)
                    <x-dropdown icon="bars-3">
                        <x-slot:header>
                            <p class="text-sm text-center">Ações</p>
                        </x-slot:header>
                        @if ($row->status->value === 'pending')
                            <x-dropdown.items text="Confirmar" wire:click="confirm({{ $row->id }})" />
                        @endif
                        @if (in_array($row->status->value, ['pending', 'promised']))
                            <x-dropdown.items text="Confirmar recebimento" wire:click="receive({{ $row->id }})" />
                        @endif
                        <x-dropdown.items text="Alterar quantidade" />
                        <x-dropdown.items text="Excluir" wire:click="askToDelete({{ $row->id }})" separator />
                    </x-dropdown>
                @endinteract

                <x-slot:empty>
                    Nenhuma promessa encontrada para este item.
                </x-slot:empty>
            </x-table>
        </div>

        @if ($itemId)
            <livewire:promise.add-promise :itemId="$itemId" />
        @endif
    </x-slide>
</div>
