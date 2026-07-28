<div>
    <x-campaign.header :campaign="$this->campaign" route="bags" />
    
    <div class="flex justify-between items-center my-6 gap-4">
        <h2 class="text-xl font-semibold dark:text-gray-300">Sacolas</h2>
        @island('bag-create')
            <livewire:panel.bag.create :campaignId="$this->campaign->id" :campaignName="$this->campaign->name" />
        @endisland
    </div>

    @island('bags-table')
        <livewire:panel.tables.campaign-bags :campaign-id="$this->campaign->id" />
    @endisland
    {{-- @island('item-bags')
        <livewire:panel.campaign.item-bags :campaign-id="$this->campaign->id" />
    @endisland --}}
</div>
