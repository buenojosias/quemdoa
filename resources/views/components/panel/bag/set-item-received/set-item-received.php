<?php

use App\Enums\BagItemStatusEnum;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    public bool $modal = false;

    #[Locked]
    public ?int $bagItemId = null;

    #[Locked]
    public ?int $bagId = null;

    #[Locked]
    public ?int $campaignId = null;

    #[Locked]
    public ?int $campaignItemId = null;

    public string $itemName = '';

    public string $itemUnitLabel = '';

    public string $formattedBagItemQuantity = '';

    public float $receivedQuantity = 0;

    #[On('open-set-item-received')]
    public function open(int $bagItem): void
    {
        $selectedBagItem = $this->findBagItem($bagItem);

        $this->bagItemId = $selectedBagItem->id;
        $this->bagId = $selectedBagItem->bag_id;
        $this->campaignId = $selectedBagItem->bag->campaign_id;
        $this->campaignItemId = $selectedBagItem->campaign_item_id;
        $this->itemName = $selectedBagItem->item->name;
        $this->itemUnitLabel = $selectedBagItem->item->unit->label();
        $this->formattedBagItemQuantity = $this->formatQuantity((float) $selectedBagItem->quantity);
        $this->receivedQuantity = (float) $selectedBagItem->quantity;
        $this->modal = true;
    }

    #[On('set-item-received-modal-closed')]
    public function closeModal(): void
    {
        $this->resetForm();
    }

    #[On('set-item-received-save')]
    public function save(int|float|string|null $receivedQuantity = null): void
    {
        if ($receivedQuantity !== null) {
            $this->receivedQuantity = (float) str_replace(',', '.', (string) $receivedQuantity);
        }

        $validated = $this->validate();
        $bagItem = $this->findBagItem($this->bagItemId);

        DB::transaction(function () use ($bagItem, $validated): void {
            $bagItem->update([
                'quantity' => $validated['receivedQuantity'],
                'status' => BagItemStatusEnum::RECEIVED,
            ]);

            $bagItem->bag->update([
                'confirmed_at' => $bagItem->bag->confirmed_at ?? now(),
                'confirmed_by' => $bagItem->bag->confirmed_by ?? 'organizer',
            ]);

            $bagItem->bag->markAsReceivedWhenEveryItemIsReceived();

            $this->refreshItemQuantities($bagItem->campaign_item_id);
        });

        $bagId = $bagItem->bag_id;
        $campaignId = $bagItem->bag->campaign_id;
        $campaignItemId = $bagItem->campaign_item_id;

        $this->resetForm();

        $this->toast()->success('Item marcado como recebido com sucesso.')->send();
        $this->dispatch("bag-item-received.{$bagId}");
        $this->dispatch("campaign-bag-item-received.{$campaignId}", item: $campaignItemId);
        $this->dispatch("item-created.{$campaignId}");
    }

    public function rules(): array
    {
        return [
            'receivedQuantity' => [
                'required',
                'numeric',
                'min:0.1',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'receivedQuantity' => 'quantidade recebida',
        ];
    }

    private function findBagItem(?int $bagItem): BagItem
    {
        return BagItem::query()
            ->with(['bag', 'item'])
            ->whereKey($bagItem)
            ->whereHas('bag.campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    private function refreshItemQuantities(int $campaignItem): void
    {
        $baggedQuantity = BagItem::query()
            ->where('campaign_item_id', $campaignItem)
            ->whereIn('status', [
                BagItemStatusEnum::PENDING->value,
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = BagItem::query()
            ->where('campaign_item_id', $campaignItem)
            ->where('status', BagItemStatusEnum::RECEIVED)
            ->sum('quantity');

        CampaignItem::query()
            ->whereKey($campaignItem)
            ->update([
                'bagged_quantity' => $baggedQuantity,
                'received_quantity' => $receivedQuantity,
            ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'modal',
            'bagItemId',
            'bagId',
            'campaignId',
            'campaignItemId',
            'itemName',
            'itemUnitLabel',
            'formattedBagItemQuantity',
            'receivedQuantity',
        ]);

        $this->resetValidation();
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
};
