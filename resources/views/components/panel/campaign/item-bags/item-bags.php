<?php

use App\Enums\BagItemStatusEnum;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    public bool $slide = false;

    #[Locked]
    public string $campaignId;

    public ?int $itemId = null;

    public ?string $itemName = null;

    public float $itemRequiredQuantity = 0;

    public float $itemBaggedQuantity = 0;

    public float $itemReceivedQuantity = 0;

    public string $formattedItemBaggedQuantity = '';

    public string $formattedItemReceivedQuantity = '';

    public ?string $itemUnitLabel = null;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    #[Computed]
    public function bagItems(): Collection
    {
        if (! $this->itemId) {
            return collect();
        }

        return BagItem::query()
            ->with('bag')
            ->where('campaign_item_id', $this->itemId)
            ->whereHas('bag', fn ($query) => $query->where('campaign_id', $this->campaignId))
            ->latest()
            ->get();
    }

    #[On('open-item-bags.{campaignId}')]
    public function open(int $item): void
    {
        $selectedItem = CampaignItem::query()
            ->select(['id', 'name', 'unit', 'required_quantity', 'bagged_quantity', 'received_quantity'])
            ->where('campaign_id', $this->campaignId)
            ->findOrFail($item);

        $this->itemId = $selectedItem->id;
        $this->itemName = $selectedItem->name;
        $this->itemRequiredQuantity = $selectedItem->required_quantity;
        $this->itemBaggedQuantity = $selectedItem->bagged_quantity;
        $this->itemReceivedQuantity = $selectedItem->received_quantity;
        $this->formattedItemBaggedQuantity = $selectedItem->formattedBaggedQuantity;
        $this->formattedItemReceivedQuantity = $selectedItem->formattedReceivedQuantity;
        $this->itemUnitLabel = $selectedItem->unit->label();

        $this->slide = true;

        unset($this->bagItems);
    }

    #[On('bag-added.{campaignId}')]
    public function refreshAfterBagAdded(): void
    {
        if (! $this->itemId) {
            return;
        }

        $selectedItem = CampaignItem::query()
            ->select(['bagged_quantity', 'received_quantity'])
            ->where('campaign_id', $this->campaignId)
            ->findOrFail($this->itemId);

        $this->itemBaggedQuantity = $selectedItem->bagged_quantity;
        $this->itemReceivedQuantity = $selectedItem->received_quantity;

        unset($this->bagItems);
    }

    public function confirm(int $bagItem): void
    {
        $bagItem = $this->findBagItem($bagItem);

        $bagItem->update([
            'status' => BagItemStatusEnum::CONFIRMED,
        ]);

        $bagItem->bag->update([
            'confirmed_at' => $bagItem->bag->confirmed_at ?? now(),
            'confirmed_by' => $bagItem->bag->confirmed_by ?? 'organizer',
        ]);

        $this->refreshItemQuantities();
        $this->toast()->success('Sacola confirmada com sucesso.')->send();
    }

    public function receive(int $bagItem): void
    {
        $bagItem = $this->findBagItem($bagItem);

        $bagItem->update([
            'status' => BagItemStatusEnum::RECEIVED,
        ]);

        $bagItem->bag->update([
            'confirmed_at' => $bagItem->bag->confirmed_at ?? now(),
            'confirmed_by' => $bagItem->bag->confirmed_by ?? 'organizer',
        ]);

        $this->refreshItemQuantities();
        $this->toast()->success('Sacola marcada como recebida.')->send();
    }

    public function askToDelete(int $bagItem): void
    {
        $this->findBagItem($bagItem);

        $this->dialog()
            ->question('Excluir item da sacola?', 'Esta ação não poderá ser desfeita.')
            ->confirm('Excluir', 'delete', $bagItem)
            ->cancel('Cancelar')
            ->send();
    }

    public function delete(int $bagItem): void
    {
        $bagItem = $this->findBagItem($bagItem);
        $bag = $bagItem->bag;

        $bagItem->delete();

        if (! $bag->items()->exists()) {
            $bag->delete();
        }

        $this->refreshItemQuantities();
        $this->toast()->success('Item da sacola excluído com sucesso.')->send();
    }

    public function statusLabel(BagItem $bagItem): string
    {
        return match ($bagItem->status) {
            BagItemStatusEnum::PENDING => 'Pendente',
            BagItemStatusEnum::CONFIRMED => 'Confirmada',
            BagItemStatusEnum::RECEIVED => 'Recebida',
            BagItemStatusEnum::CANCELED => 'Cancelada',
        };
    }

    public function statusColor(BagItem $bagItem): string
    {
        return match ($bagItem->status) {
            BagItemStatusEnum::PENDING => 'yellow',
            BagItemStatusEnum::CONFIRMED => 'cyan',
            BagItemStatusEnum::RECEIVED => 'green',
            BagItemStatusEnum::CANCELED => 'red',
        };
    }

    private function findBagItem(int $bagItem): BagItem
    {
        return BagItem::query()
            ->with('bag')
            ->whereKey($bagItem)
            ->where('campaign_item_id', $this->itemId)
            ->whereHas('bag', fn ($query) => $query->where('campaign_id', $this->campaignId))
            ->firstOrFail();
    }

    private function refreshItemQuantities(): void
    {
        if (! $this->itemId) {
            return;
        }

        $baggedQuantity = BagItem::query()
            ->where('campaign_item_id', $this->itemId)
            ->whereIn('status', [
                BagItemStatusEnum::PENDING->value,
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = BagItem::query()
            ->where('campaign_item_id', $this->itemId)
            ->whereIn('status', [
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->whereKey($this->itemId)
            ->update([
                'bagged_quantity' => $baggedQuantity,
                'received_quantity' => $receivedQuantity,
            ]);

        $this->itemBaggedQuantity = $baggedQuantity;
        $this->itemReceivedQuantity = $receivedQuantity;

        unset($this->bagItems);

        $this->dispatch("item-created.{$this->campaignId}");
    }
};
