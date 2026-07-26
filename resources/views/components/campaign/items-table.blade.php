<?php

use App\Enums\CategoryEnum;
use App\Models\CampaignItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $quantity = 10;
    public string $category = '';

    #[Locked]
    public string $campaignId;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
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

        return $query->paginate($this->quantity);
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
    <div class="flex justify-between items-center gap-4">
        <x-select.native wire:model.live="quantity" label="Itens por página">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </x-select>

        <x-select.native label="Categoria"
            wire:model.live="category"
            :options="$this->categoryOptions()"
            select="label:label|value:value" />
    </div>

    <x-table :$headers :$rows paginate>
        @interact('column_item', $row)
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                {{ $row->name }}

                @if ($row->complement)
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $row->note }}</p>
                @endif
            </div>
        @endinteract
        @interact('column_quantity', $row)
            <div class="space-y-0.5">
                {{ number_format($row->required_quantity, 0) }} {{ $row->unit->abbreviation() }}
                <x-progress :percent="$row->bagged_quantity / $row->required_quantity * 100" color="cyan" sm />
                <x-progress :percent="$row->received_quantity / $row->required_quantity * 100" color="green" sm />
            </div>
        @endinteract
        @interact('column_date', $row)
            {{ $row->delivery_date ? $row->delivery_date->format('d/m/Y') : 'Padrão' }}
        @endinteract
        @interact('column_actions', $row)
            <div class="flex">
                <x-button icon="pencil-square" title="Editar" flat />
                <x-button icon="list-bullet" title="Sacolas" flat
                    wire:click="$dispatch('open-item-bags.{{ $this->campaignId }}', { item: {{ $row->id }} })" />
            </div>
        @endinteract
    </x-table>
</div>
