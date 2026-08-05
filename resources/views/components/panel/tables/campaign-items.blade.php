<?php

use App\Enums\CategoryEnum;
use App\Models\Campaign;
use App\Models\CampaignItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $category = '';

    #[Locked]
    public Campaign $campaign;

    public string $campaignId;

    public string $campaignName = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->campaignId = (string) $campaign->id;
        $this->campaignName = (string) $campaign->name;
    }

    #[Computed]
    public function items()
    {
        $query = CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->orderBy('name');

        if ($this->category) {
            $query->where('category', $this->category);
        }

        return $query->paginate();
    }


    #[On('navigated')]
    public function navigated(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return [
            [
                'label' => 'Todas',
                'value' => '',
            ],
            ...array_map(
                fn (CategoryEnum $category): array => [
                    'label' => $category->value,
                    'value' => $category->value,
                ],
                CategoryEnum::cases(),
            ),
        ];
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'item', 'label' => 'Item'],
                ['index' => 'category', 'label' => 'Categoria'],
                ['index' => 'quantity', 'label' => 'Quantidade'],
                ['index' => 'date', 'label' => 'Prazo de entrega'],
                ['index' => 'actions'],
            ],
            'rows' => $this->items,
        ];
    }

    #[On('item-created.{campaignId}')]
    public function refreshItems(): void
    {
        unset($this->items);
    }
};
?>

<div class="space-y-4">
    <div class="flex justify-between flex-col sm:flex-row sm:items-end gap-4">
        <x-select.native label="Categoria"
            wire:model.live="category"
            :options="$this->categoryOptions()"
            select="label:label|value:value" />
        @island('item-create')
            <livewire:panel.item.create :campaign-id="$this->campaignId" :$campaignName />
        @endisland
    </div>

    <x-table :$headers :$rows paginate loading>
        @interact('column_item', $row)
            <div class="text-sm font-medium text-gray-700 dark:text-gray-100">
                {{ $row->name }}
                @if ($row->complement)
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $row->note }}</p>
                @endif
            </div>
        @endinteract
        @interact('column_quantity', $row)
            <div class="space-y-0.5">
                {{ number_format($row->required_quantity, 0) }} {{ $row->unit->label() }}
                <x-progress :percent="$row->bagged_quantity / $row->required_quantity * 100" color="cyan" sm />
                <x-progress :percent="$row->received_quantity / $row->required_quantity * 100" color="green" sm />
            </div>
        @endinteract
        @interact('column_date', $row)
            {{ $row->delivery_date ? $row->delivery_date->format('d/m/Y') : '-' }}
        @endinteract
        @interact('column_actions', $row)
            <div class="flex">
                <x-button icon="pencil-square" title="Editar" color="dark" flat
                    wire:click="$dispatch('open-item-edit.{{ $this->campaignId }}', { item: {{ $row->id }} })" />
                <x-button icon="list-bullet" title="Sacolas" color="dark" flat
                    wire:click="$dispatch('open-item-bags.{{ $this->campaignId }}', { item: {{ $row->id }} })" />
                <x-button icon="eye" title="Detalhes" color="dark" flat
                    wire:click="$dispatch('open-item-details.{{ $this->campaignId }}', { item: {{ $row->id }} })" />
            </div>
        @endinteract
    </x-table>
    @island('item-edit')
        <livewire:panel.item.edit :campaign-id="$this->campaignId" />
    @endisland
    @island('item-bags')
        <livewire:panel.campaign.item-bags :campaign-id="$this->campaign->id" />
    @endisland
    @island('item-details')
        <livewire:panel.campaign.item-details :campaign-id="$this->campaign->id" />
    @endisland
</div>
