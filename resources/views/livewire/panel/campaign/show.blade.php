<div>
    <x-campaign.header :campaign="$this->campaign" route="show" />

    <div class="flex justify-between items-center my-6 gap-4">
        <h2 class="text-xl font-semibold dark:text-gray-300">Itens</h2>
        @island('item-create')
            <livewire:panel.item.create :campaign-id="$this->campaign->id" />
        @endisland
    </div>

    @island('items-table')
        <livewire:panel.campaign.items-table :campaign-id="$this->campaign->id" />
    @endisland
    @island('item-bags')
        <livewire:panel.campaign.item-bags :campaign-id="$this->campaign->id" />
    @endisland
</div>
