<div>
    <x-slide title="Detalhes do item" wire>
        @if ($this->itemId)
            <div class="flex items-center gap-4">
                <img
                    src="{{ asset('assets/images/category-illustrations/'.$this->categoryIllustration) }}"
                    alt="{{ $this->category }}"
                    class="w-28">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $this->name }}</h3>
                    @if ($this->complement)
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $this->complement }}</p>
                    @endif
                    <x-badge :text="$this->statusLabel" :color="$this->statusColor" class="mt-3" />
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <h4 class="font-semibold text-gray-800 dark:text-gray-200">Informações do item</h4>
                <x-item-info label="Categoria" :value="$this->category" />
                <x-item-info label="Complemento" :value="$this->complement ?: '-'" />
                <x-item-info label="Unidade de medida" :value="$this->unitLabel" />
                <x-item-info label="Quantidade" :value="$this->requiredQuantity . ' ' . $this->unitAbbreviation" />
                <x-item-info label="Observações" :value="$this->note ?: '-'" />
                <hr class="my-4 border-gray-300 dark:border-gray-700" />
                <h4 class="font-semibold text-gray-800 dark:text-gray-200">Metas e entregas</h4>
                <x-item-info label="Quantidade prometida">
                    <div class="flex items-center gap-4 pb-2">
                        <div class="flex-1">
                            <x-label :label="$this->baggedQuantity . ' ' . $this->unitAbbreviation" />
                            <x-progress :percent="$this->baggedPercent" without-text xs />
                        </div>
                        <x-badge :text="$this->baggedPercent . '%'" round="xl" color="cyan" light />
                    </div>
                </x-item-info>
                <x-item-info label="Quantidade entregue">
                    <div class="flex items-center gap-4 pb-2">
                        <div class="flex-1">
                            <x-label :label="$this->receivedQuantity . ' ' . $this->unitAbbreviation" />
                            <x-progress :percent="$this->receivedPercent" without-text xs />
                        </div>
                        <x-badge :text="$this->receivedPercent . '%'" round="xl" color="green" light />
                    </div>
                </x-item-info>
                <x-item-info label="Quantidade pendente">
                    <div class="flex items-center gap-4 pb-2">
                        <div class="flex-1">
                            <x-label :label="$this->pendingQuantity . ' ' . $this->unitAbbreviation" />
                            <x-progress :percent="$this->pendingPercent" without-text xs />
                        </div>
                        <x-badge :text="$this->pendingPercent . '%'" round="xl" color="yellow" light />
                    </div>
                </x-item-info>
                <x-item-info label="Prazo de entrega do item" :value="$this->deliveryDate ?: '-'" />
                <hr class="my-4 border-gray-300 dark:border-gray-700" />
                <h4 class="font-semibold text-gray-800 dark:text-gray-200">Sacolas</h4>
                <x-item-info label="Pendentes" :value="$this->pendingBagsCount . ' ' . ($this->pendingBagsCount === 1 ? 'sacola' : 'sacolas')" />
                <x-item-info label="Confirmadas" :value="$this->confirmedBagsCount . ' ' . ($this->confirmedBagsCount === 1 ? 'sacola' : 'sacolas')" />
                <x-item-info label="Recebidas" :value="$this->receivedBagsCount . ' ' . ($this->receivedBagsCount === 1 ? 'sacola' : 'sacolas')" />
            </div>
        @endif

        <x-slot:footer class="flex justify-between">
            <div>
                @if ($this->itemId)
                    <x-button
                        color="primary"
                        wire:click="$dispatch('open-item-edit.{{ $this->campaignId }}', { item: {{ $this->itemId }} })">
                        Editar
                    </x-button>
                @endif
            </div>
            <x-button color="neutral" flat wire:click="$set('slide', false)">Fechar</x-button>
        </x-slot:footer>
    </x-slide>
</div>
