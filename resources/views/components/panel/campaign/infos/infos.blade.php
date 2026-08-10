<div class="p-4 space-y-4 rounded-lg border border-gray-200 dark:border-gray-500">
    @forelse($this->infos as $info)
        {{-- <x-inline-info :label="$info->title" :value="$info->content" /> --}}
        <div class="flex gap-4 text-sm items-center">
            <div class="flex-1">
                <p class="text-gray-800 dark:text-gray-300 font-medium">{{ $info->title }}</p>
                <p class="text-gray-600 dark:text-gray-400">{{ $info->content }}</p>
            </div>
            <div class="flex">
                <x-dropdown icon="ellipsis-vertical" static>
                    <x-dropdown.items text="Editar" icon="pencil-square" />
                    <x-dropdown.items text="Excluir" icon="trash" />
                </x-dropdown>
            </div>
        </div>
    @empty
        <p class="py-6 text-center text-gray-500">Nenhuma informação adicionada<br>
            <span class="text-sm">Adicione mais informações sanar as dúvidas os participantes da campanha.</span>
        </p>
    @endforelse
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-500 text-right">
        <x-button text="Adicionar informação" icon="plus" outline sm wire:click="openModal" />
    </div>

    <x-panel.campaign.infos.add-info />
</div>
