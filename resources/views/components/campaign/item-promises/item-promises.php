<?php

use App\Enums\PromiseItemStatusEnum;
use App\Models\Item;
use App\Models\PromiseItem;
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

    public int $itemRequiredQuantity = 0;

    public int $itemPromisedQuantity = 0;

    public int $itemReceivedQuantity = 0;

    public ?string $itemUnitLabel = null;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    #[Computed]
    public function promiseItems(): Collection
    {
        if (! $this->itemId) {
            return collect();
        }

        return PromiseItem::query()
            ->with('promise')
            ->where('item_id', $this->itemId)
            ->whereHas('promise', fn ($query) => $query->where('campaign_id', $this->campaignId))
            ->latest()
            ->get();
    }

    #[On('open-item-promises.{campaignId}')]
    public function open(int $item): void
    {
        $selectedItem = Item::query()
            ->select(['id', 'name', 'unit', 'required_quantity', 'promised_quantity', 'received_quantity'])
            ->where('campaign_id', $this->campaignId)
            ->findOrFail($item);

        $this->itemId = $selectedItem->id;
        $this->itemName = $selectedItem->name;
        $this->itemRequiredQuantity = $selectedItem->required_quantity;
        $this->itemPromisedQuantity = $selectedItem->promised_quantity;
        $this->itemReceivedQuantity = $selectedItem->received_quantity;
        $this->itemUnitLabel = $selectedItem->unit->label();

        $this->slide = true;

        unset($this->promiseItems);
    }

    #[On('promise-added.{campaignId}')]
    public function refreshAfterPromiseAdded(): void
    {
        if (! $this->itemId) {
            return;
        }

        $selectedItem = Item::query()
            ->select(['promised_quantity', 'received_quantity'])
            ->where('campaign_id', $this->campaignId)
            ->findOrFail($this->itemId);

        $this->itemPromisedQuantity = $selectedItem->promised_quantity;
        $this->itemReceivedQuantity = $selectedItem->received_quantity;

        unset($this->promiseItems);
    }

    public function confirm(int $promiseItem): void
    {
        $promiseItem = $this->findPromiseItem($promiseItem);

        $promiseItem->update([
            'status' => PromiseItemStatusEnum::PROMISED,
        ]);

        $promiseItem->promise->update([
            'confirmed_at' => $promiseItem->promise->confirmed_at ?? now(),
        ]);

        $this->refreshItemQuantities();
        $this->toast()->success('Promessa confirmada com sucesso.')->send();
    }

    public function receive(int $promiseItem): void
    {
        $promiseItem = $this->findPromiseItem($promiseItem);

        $promiseItem->update([
            'status' => PromiseItemStatusEnum::RECEIVED,
        ]);

        $promiseItem->promise->update([
            'confirmed_at' => $promiseItem->promise->confirmed_at ?? now(),
        ]);

        $this->refreshItemQuantities();
        $this->toast()->success('Promessa marcada como recebida.')->send();
    }

    public function askToDelete(int $promiseItem): void
    {
        $this->findPromiseItem($promiseItem);

        $this->dialog()
            ->question('Excluir promessa?', 'Esta ação não poderá ser desfeita.')
            ->confirm('Excluir', 'delete', $promiseItem)
            ->cancel('Cancelar')
            ->send();
    }

    public function delete(int $promiseItem): void
    {
        $promiseItem = $this->findPromiseItem($promiseItem);
        $promise = $promiseItem->promise;

        $promiseItem->delete();

        if (! $promise->items()->exists()) {
            $promise->delete();
        }

        $this->refreshItemQuantities();
        $this->toast()->success('Promessa excluída com sucesso.')->send();
    }

    public function statusLabel(PromiseItem $promiseItem): string
    {
        return match ($promiseItem->status) {
            PromiseItemStatusEnum::PENDING => 'Pendente',
            PromiseItemStatusEnum::PROMISED => 'Prometida',
            PromiseItemStatusEnum::RECEIVED, PromiseItemStatusEnum::DELIVERED => 'Recebida',
            PromiseItemStatusEnum::CANCELED => 'Cancelada',
        };
    }

    public function statusColor(PromiseItem $promiseItem): string
    {
        return match ($promiseItem->status) {
            PromiseItemStatusEnum::PENDING => 'yellow',
            PromiseItemStatusEnum::PROMISED => 'cyan',
            PromiseItemStatusEnum::RECEIVED, PromiseItemStatusEnum::DELIVERED => 'green',
            PromiseItemStatusEnum::CANCELED => 'red',
        };
    }

    private function findPromiseItem(int $promiseItem): PromiseItem
    {
        return PromiseItem::query()
            ->with('promise')
            ->whereKey($promiseItem)
            ->where('item_id', $this->itemId)
            ->whereHas('promise', fn ($query) => $query->where('campaign_id', $this->campaignId))
            ->firstOrFail();
    }

    private function refreshItemQuantities(): void
    {
        if (! $this->itemId) {
            return;
        }

        $promisedQuantity = PromiseItem::query()
            ->where('item_id', $this->itemId)
            ->whereIn('status', [
                PromiseItemStatusEnum::PROMISED->value,
                PromiseItemStatusEnum::RECEIVED->value,
                PromiseItemStatusEnum::DELIVERED->value,
            ])
            ->sum('promised_quantity');

        $receivedQuantity = PromiseItem::query()
            ->where('item_id', $this->itemId)
            ->whereIn('status', [
                PromiseItemStatusEnum::RECEIVED->value,
                PromiseItemStatusEnum::DELIVERED->value,
            ])
            ->sum('promised_quantity');

        Item::query()
            ->where('campaign_id', $this->campaignId)
            ->whereKey($this->itemId)
            ->update([
                'promised_quantity' => $promisedQuantity,
                'received_quantity' => $receivedQuantity,
            ]);

        $this->itemPromisedQuantity = $promisedQuantity;
        $this->itemReceivedQuantity = $receivedQuantity;

        unset($this->promiseItems);

        $this->dispatch("item-created.{$this->campaignId}");
    }
};
