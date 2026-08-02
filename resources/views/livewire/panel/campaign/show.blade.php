<div class="space-y-6">
    <x-campaign.header :campaign="$this->campaign" route="show" />

    <livewire:panel.campaign.edit :campaign="$this->campaign" @updated="$refresh" />

    <x-tab wire:model.live="tab" scroll-on-mobile>
        <x-tab.items tab="info" title="Informações">
            <x-slot:left>
                <x-icon name="information-circle" class="w-5 h-5" />
            </x-slot:left>
            {{-- @island(lazy: true) --}}
                <livewire:panel.campaign.info :campaign="$this->campaign" />
            {{-- @endisland --}}
        </x-tab.items>
        <x-tab.items tab="items" title="Itens">
            <x-slot:left>
                <x-icon name="list-bullet" class="w-5 h-5" />
            </x-slot:left>
            @island(lazy: true)
                <livewire:panel.tables.campaign-items :campaign="$this->campaign" />
            @endisland
        </x-tab.items>
        <x-tab.items tab="bags" title="Sacolas">
            <x-slot:left>
                <x-icon name="shopping-bag" class="w-5 h-5" />
            </x-slot:left>
            @island(lazy: true)
                <livewire:panel.tables.campaign-bags :campaign="$this->campaign" />
            @endisland
        </x-tab.items>
    </x-tab>
</div>
