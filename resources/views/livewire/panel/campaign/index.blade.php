<div>
    <div class="header">
        <h1>Minhas campanhas</h1>
        <div>
            <livewire:panel.campaign.create />
        </div>
    </div>
    
    <div class="space-y-4">
        @forelse ($this->campaigns as $campaign)
            {{-- <livewire:panel.campaign.card :campaign="$campaign" :wire:key="$campaign->id" /> --}}
            <x-card class="space-y-3">
                <div class="flex justify-between items-start gap-4">
                    <a href="{{ route('panel.campaigns.show', $campaign) }}" class="font-semibold flex-1 pb-1">{{ $campaign->name }}</a>
                    <x-badge
                        :text="$campaign->is_active ? 'Ativa' : 'Inativa'"
                        :color="$campaign->is_active ? 'green' : 'neutral'"
                        light />
                </div>
                <p class="text-gray-500 dark:text-gray-300 text-sm">{{ $campaign->description }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 text-sm gap-3">
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">Prazo de confirmação</span>
                        <span class="text-gray-500 dark:text-gray-300">{{ $campaign->confirmation_deadline->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">Prazo de entrega</span>
                        <span class="text-gray-500 dark:text-gray-300">{{ $campaign->delivery_deadline->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">Itens</span>
                        <span class="text-gray-500 dark:text-gray-300">{{ $campaign->items_count }}</span>
                    </div>
                    <a href="{{ route('panel.campaigns.bags', $campaign) }}" class="flex flex-col">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">Sacolas</span>
                        <span class="text-gray-500 dark:text-gray-300">{{ $campaign->bags_count }}</span>
                    </a>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-alert color="secondary" light icon="heart" title="Você ainda não tem campanhas">
                    Que tal criar sua primeira campanha? Clique no botão acima para começar!
                </x-alert>
            </div>
        @endforelse
        <div class="mt-4">
            {{ $this->campaigns->links() }}
        </div>
    </div>

</div>
