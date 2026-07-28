<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    #[Locked]
    public string $bagId;

    #[Locked]
    public string $bagCode;

    #[Locked]
    public string $campaignName = '';

    public bool $modal = false;

    public bool $addModal = false;

    public bool $itemsLoaded = false;

    public array $itemsByCategory = [];

    public ?int $selectedItemId = null;

    public string $selectedItemName = '';

    public string $selectedItemUnitLabel = '';

    public float $selectedItemPendingQuantity = 0;

    public string $selectedItemFormattedPendingQuantity = '';

    public float $quantity = 0;

    public bool $received = false;

    public function mount(int $bagId, string $bagCode, string $campaignName = ''): void
    {
        $this->bagId = $bagId;
        $this->bagCode = $bagCode;
        $this->campaignName = $campaignName;
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->with('campaign')
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }

    public function openModal(): void
    {
        if (! $this->itemsLoaded) {
            $this->loadItems();
        }

        $this->modal = true;
    }

    public function closeModal(): void
    {
        $this->modal = false;
    }

    public function openAddModal(int $item): void
    {
        $selectedItem = $this->findLoadedItem($item);

        abort_if(! $selectedItem, 404);

        $this->resetAddForm();

        $this->selectedItemId = $selectedItem['id'];
        $this->selectedItemName = $selectedItem['name'];
        $this->selectedItemUnitLabel = $selectedItem['unit_label'];
        $this->selectedItemPendingQuantity = $selectedItem['pending_quantity'];
        $this->selectedItemFormattedPendingQuantity = $selectedItem['formatted_pending_quantity'];
        // $this->quantity = $this->selectedItemPendingQuantity;
        $this->addModal = true;
    }

    public function closeAddModal(): void
    {
        $this->resetAddForm();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $item = $this->findSelectedItem();

        $this->bag->items()->create([
            'campaign_item_id' => $item->id,
            'quantity' => $validated['quantity'],
            'status' => $this->received
                ? BagItemStatusEnum::RECEIVED
                : BagItemStatusEnum::CONFIRMED,
        ]);

        $this->refreshItemQuantities($item);
        $this->removeLoadedItem($item->id);
        $this->resetAddForm();

        $this->toast()->success('Item adicionado à sacola com sucesso.')->send();
        $this->dispatch("bag-item-added.{$this->bagId}");
        $this->dispatch("item-created.{$item->campaign_id}");
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'numeric',
                'min:0.1',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'quantity' => 'quantidade',
        ];
    }

    private function loadItems(): void
    {
        $this->itemsByCategory = $this->availableItems()
            ->groupBy(fn (CampaignItem $item): string => $item->category->value)
            ->map(fn (Collection $items): array => $items
                ->map(fn (CampaignItem $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'pending_quantity' => $this->pendingQuantity($item),
                    'formatted_pending_quantity' => $this->formatQuantity($this->pendingQuantity($item)),
                    'unit_label' => $item->unit->label(),
                    'unit_abbreviation' => $item->unit->abbreviation(),
                ])
                ->values()
                ->all())
            ->all();

        $this->itemsLoaded = true;
    }

    private function availableItems(): Collection
    {
        return CampaignItem::query()
            ->where('campaign_id', $this->bag->campaign_id)
            ->whereColumn('bagged_quantity', '<', 'required_quantity')
            ->whereDoesntHave('bagItems', fn ($query) => $query->where('bag_id', $this->bagId))
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    private function findSelectedItem(): CampaignItem
    {
        return CampaignItem::query()
            ->where('campaign_id', $this->bag->campaign_id)
            ->whereDoesntHave('bagItems', fn ($query) => $query->where('bag_id', $this->bagId))
            ->findOrFail($this->selectedItemId);
    }

    private function refreshItemQuantities(CampaignItem $item): void
    {
        $baggedQuantity = BagItem::query()
            ->where('campaign_item_id', $item->id)
            ->whereIn('status', [
                BagItemStatusEnum::PENDING->value,
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = BagItem::query()
            ->where('campaign_item_id', $item->id)
            ->whereIn('status', [
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $item->update([
            'bagged_quantity' => $baggedQuantity,
            'received_quantity' => $receivedQuantity,
        ]);
    }

    private function removeLoadedItem(int $item): void
    {
        foreach ($this->itemsByCategory as $category => $items) {
            $this->itemsByCategory[$category] = array_values(
                array_filter($items, fn (array $loadedItem): bool => $loadedItem['id'] !== $item),
            );

            if ($this->itemsByCategory[$category] === []) {
                unset($this->itemsByCategory[$category]);
            }
        }
    }

    private function findLoadedItem(int $item): ?array
    {
        foreach ($this->itemsByCategory as $items) {
            $selectedItem = collect($items)->firstWhere('id', $item);

            if ($selectedItem) {
                return $selectedItem;
            }
        }

        return null;
    }

    private function resetAddForm(): void
    {
        $this->reset([
            'addModal',
            'selectedItemId',
            'selectedItemName',
            'selectedItemUnitLabel',
            'selectedItemPendingQuantity',
            'selectedItemFormattedPendingQuantity',
            'quantity',
            'received',
        ]);

        $this->resetValidation();
    }

    private function pendingQuantity(CampaignItem $item): float
    {
        return (float) $item->required_quantity - (float) $item->bagged_quantity;
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
};
