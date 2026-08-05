<?php

use App\Enums\BagItemStatusEnum;
use App\Enums\CategoryEnum;
use App\Models\BagItem;
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
    public string $bagId;

    #[Locked]
    public string $campaignId;

    public function mount(int|string $bagId, int|string $campaignId): void
    {
        $this->bagId = (string) $bagId;
        $this->campaignId = (string) $campaignId;
    }

    #[On('bag-item-added.{bagId}')]
    #[On('bag-item-received.{bagId}')]
    #[On('bag-item-quantity-updated.{bagId}')]
    #[On('bag-confirmed.{bagId}')]
    public function refreshAfterBagItemChanged(): void
    {
        $this->resetPage();

        unset($this->items);
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function items()
    {
        return BagItem::query()
            ->with('item')
            ->where('bag_id', $this->bagId)
            ->whereHas('bag', fn ($query) => $query
                ->where('campaign_id', $this->campaignId)
                ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id())))
            ->when($this->category !== '', fn ($query) => $query->whereHas('item', fn ($query) => $query->where('category', $this->category)))
            ->latest()
            ->paginate();
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
                ['index' => 'item.name', 'label' => 'Item'],
                ['index' => 'item.category', 'label' => 'Categoria'],
                ['index' => 'quantity', 'label' => 'Quantidade'],
                ['index' => 'status', 'label' => 'Status'],
                ['index' => 'actions'],
            ],
            'rows' => $this->items,
        ];
    }
};
?>

<div>
    <x-card class="space-y-4">
        <div class="flex justify-between items-center gap-4">
            <x-select.native label="Categoria"
                wire:model.live="category"
                :options="$this->categoryOptions()"
                select="label:label|value:value" />
        </div>

        <x-table :$headers :$rows paginate>
            @interact('column_quantity', $row)
                {{ $row->formatted_quantity }} {{ $row->item->unit->abbreviation() }}
            @endinteract
            @interact('column_status', $row)
                <x-badge :text="$row->status->label()" :color="$row->status->color()" light />
            @endinteract
            @interact('column_actions', $row)
                @if (in_array($row->status, [BagItemStatusEnum::PENDING, BagItemStatusEnum::CONFIRMED], true))
                    <x-button.circle
                        icon="check"
                        sm
                        title="Marcar como recebido"
                        wire:click="$dispatch('open-set-item-received', { bagItem: {{ $row->id }} })" />
                @endif
                <x-button.circle
                    icon="pencil"
                    sm
                    title="Alterar quantidade"
                    wire:click="$dispatch('open-change-item-quantity', { bagItem: {{ $row->id }} })" />
            @endinteract
        </x-table>
    </x-card>
    <livewire:panel.bag.set-item-received />
    <livewire:panel.bag.change-item-quantity />
</div>
