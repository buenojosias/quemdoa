<div>
    <x-slide wire title="Sacolas" id="item-bags-slide" persistent size="xl">
        <div class="space-y-5">
            @if ($itemName)
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Item selecionado</p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $itemName }}</h3>
                </div>

                <div class="my-6 flex gap-4">
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade ensacada" />
                        <x-label :label="$itemBaggedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemBaggedQuantity / $itemRequiredQuantity * 100" color="cyan" />
                    </div>
                    <div class="w-1/2 flex flex-col items-center">
                        <x-label label="Quantidade recebida" />
                        <x-label :label="$itemReceivedQuantity . '/' . $itemRequiredQuantity . ' ' . $itemUnitLabel" />
                        <x-progress.circle :percent="$itemReceivedQuantity / $itemRequiredQuantity * 100" color="green" />
                    </div>
                </div>
            @endif


            <x-table :headers="[
                ['index' => 'participant', 'label' => 'Participante'],
                ['index' => 'quantity', 'label' => 'Quantidade'],
                ['index' => 'status', 'label' => 'Status'],
                ['index' => 'actions'],
            ]"
                :rows="$this->bagItems"
                loading>
                @interact('column_participant', $row)
                    <span class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $row->bag->participant_name }}
                    </span>
                @endinteract

                @interact('column_quantity', $row)
                    {{ $row->quantity }}
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
                        @if (in_array($row->status->value, ['pending', 'confirmed']))
                            <x-dropdown.items text="Confirmar recebimento" wire:click="receive({{ $row->id }})" />
                        @endif
                        <x-dropdown.items text="Alterar quantidade" />
                        <x-dropdown.items text="Excluir" wire:click="askToDelete({{ $row->id }})" separator />
                    </x-dropdown>
                @endinteract

                <x-slot:empty>
                    Nenhuma sacola encontrada para este item.
                </x-slot:empty>
            </x-table>
        </div>

        @if ($itemId)
            <livewire:bag.add-bag :itemId="$itemId" />
        @endif
    </x-slide>
</div>
