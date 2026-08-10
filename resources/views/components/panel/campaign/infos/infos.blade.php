<div class="p-4 space-y-4 rounded-lg border border-gray-200 dark:border-gray-500">
    @if($this->infos->isNotEmpty())
        <div wire:sort="sortInfo" class="space-y-4">
            @foreach($this->infos as $info)
                <div wire:key="campaign-info-{{ $info->id }}" wire:sort:item="{{ $info->id }}" class="flex gap-4 text-sm items-center">
                    <button type="button" wire:sort:handle class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab active:cursor-grabbing">
                        <x-icon name="bars-3" class="w-5 h-5" />
                    </button>

                    <div class="flex-1">
                        <p class="text-gray-800 dark:text-gray-300 font-medium">{{ $info->title }}</p>
                        <p class="text-gray-600 dark:text-gray-400">{{ $info->content }}</p>
                    </div>
                    <div wire:sort:ignore class="flex">
                        <x-dropdown icon="ellipsis-vertical" static>
                            <x-dropdown.items text="Editar" icon="pencil-square" wire:click="openEditModal({{ $info->id }})" />
                            <x-dropdown.items text="Excluir" icon="trash" wire:click="askToDelete({{ $info->id }})" />
                        </x-dropdown>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="py-6 text-center text-gray-500">Nenhuma informação adicionada<br>
            <span class="text-sm">Adicione mais informações sanar as dúvidas os participantes da campanha.</span>
        </p>
    @endif

    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-500 text-right">
        <x-button text="Adicionar informação" icon="plus" outline sm wire:click="openModal" />
    </div>

    <x-panel.campaign.infos.add-info />
    <x-panel.campaign.infos.edit-info />
    <x-panel.campaign.infos.delete-info />
</div>
