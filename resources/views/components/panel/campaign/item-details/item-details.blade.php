<div>
    <x-slide title="Detalhes do item" wire>
        <div class="flex items-center gap-4">
            <img src="{{ asset('assets/images/category-illustrations/candies.png') }}" alt="Comidas" class="w-28">
            <div>
                <h3 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Arroz</h3>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Pacote 5kg</p>
                <x-badge text="Coletando ou Meta atingida" color="blue" class="mt-3" />
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <h4 class="font-semibold text-gray-800 dark:text-gray-200">Informações do item</h4>
            <x-item-info label="Categoria" value="Comidas" />
            <x-item-info label="Complemento" value="Pacote 5kg" />
            <x-item-info label="Unidade de medida" value="Pacote" />
            <x-item-info label="Quantidade" value="8 pct" />
            <x-item-info label="Observações" value="Lorem ipsum" />
            <hr class="my-4 border-gray-300" />
            <h4 class="font-semibold text-gray-800 dark:text-gray-200">Metas e entregas</h4>
            <x-item-info label="Quantidade prometida">
                <div class="flex items-center gap-4 py-1">
                    <div class="flex-1">
                        <x-label label="4 pacotes" />
                        <x-progress :percent="50" without-text xs />
                    </div>
                    <x-badge text="50%" round="xl" color="green" light />
                </div>
            </x-item-info>
            <x-item-info label="Quantidade entregue">
                <div class="flex items-center gap-4 py-1">
                    <div class="flex-1">
                        <x-label label="2 pacotes" />
                        <x-progress :percent="25" without-text xs />
                    </div>
                    <x-badge text="25%" round="xl" color="green" light />
                </div>
            </x-item-info>
            <x-item-info label="Quantidade pendente">
                <div class="flex items-center gap-4 py-1">
                    <div class="flex-1">
                        <x-label label="2 pacotes" />
                        <x-progress :percent="25" without-text xs />
                    </div>
                    <x-badge text="25%" round="xl" color="yellow" light />
                </div>
            </x-item-info>
            <x-item-info label="Prazo de entrega do item" value="20/08/2026" />
            <hr class="my-4 border-gray-300" />
            <h4 class="font-semibold text-gray-800 dark:text-gray-200">Sacolas</h4>
            <x-item-info label="Pendentes" value="4 sacolas" />
            <x-item-info label="Confirmadas" value="2 sacolas" />
        </div>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-button color="gray" wire:click="$set('slide', false)">Fechar</x-button>
                <x-button color="blue">Editar</x-button>
                <x-button color="red" outline>Excluir</x-button>
            </div>
        </x-slot:footer>
    </x-slide>
</div>