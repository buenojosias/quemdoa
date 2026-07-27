<?php

use App\Enums\CategoryEnum;
use App\Models\Bag;
use App\Models\BagItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $quantity = 10;

    public string $category = '';

    #[Locked]
    public string $bagId;

    public function mount(Bag|int|string|null $bag = null, int|string|null $bagId = null): void
    {
        $this->bagId = $bag instanceof Bag
            ? (string) $bag->getKey()
            : (string) ($bag ?? $bagId);
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }

    #[Computed]
    public function items()
    {
        return BagItem::query()
            ->with('item')
            ->whereBelongsTo($this->bag)
            ->when($this->category !== '', fn ($query) => $query->whereHas('item', fn ($query) => $query->where('category', $this->category)))
            ->latest()
            ->paginate($this->quantity);
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
        @interact('column_status', $row)
            <x-badge :text="$row->status->label()" :color="$row->status->color()" light />
        @endinteract
    </x-table>
</div>
