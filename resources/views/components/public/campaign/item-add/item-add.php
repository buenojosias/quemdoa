<?php

use App\Models\CampaignItem;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    #[Locked]
    public string $campaignId;

    #[Locked]
    public ?int $itemId = null;

    public string $itemName = '';

    public ?string $itemComplement = null;

    public int|float|string|null $quantity = 1;

    public float $pendingBaggedQuantity = 0;

    public string $formattedPendingBaggedQuantity = '';

    public string $unitAbbreviation = '';

    public string $unitLabel = '';

    public ?string $deliveryDate = null;

    public ?string $note = null;

    public bool $modal = false;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    #[On('open-public-campaign-item-add.{campaignId}')]
    public function open(int $item): void
    {
        $selectedItem = $this->findAvailableItem($item);
        $pendingQuantity = $this->pendingQuantity($selectedItem);

        $this->itemId = $selectedItem->id;
        $this->itemName = $selectedItem->name;
        $this->itemComplement = $selectedItem->complement;
        $this->pendingBaggedQuantity = $pendingQuantity;
        $this->formattedPendingBaggedQuantity = $this->formatQuantity($pendingQuantity);
        $this->quantity = min(1, $pendingQuantity);
        $this->unitAbbreviation = $selectedItem->unit->abbreviation();
        $this->unitLabel = $selectedItem->unit->label();
        $this->deliveryDate = $selectedItem->delivery_date?->translatedFormat('d \d\e F \d\e Y');
        $this->note = $selectedItem->note;
        $this->modal = true;
    }

    #[On('public-campaign-item-add-save')]
    public function addToBag(int|float|string|null $quantity = null): void
    {
        if ($quantity !== null) {
            $this->quantity = str_replace(',', '.', (string) $quantity);
        }

        $validated = $this->validate();
        $selectedItem = $this->findAvailableItem($this->itemId);
        $quantity = (float) $validated['quantity'];

        $this->dispatch(
            "public-campaign-item-added.{$this->campaignId}",
            item: $selectedItem->id,
            bagItem: [
                'id' => $selectedItem->id,
                'name' => $selectedItem->name,
                'complement' => $selectedItem->complement,
                'quantity' => $quantity,
                'formattedQuantity' => $this->formatQuantity($quantity),
                'pendingBaggedQuantity' => $this->pendingQuantity($selectedItem),
                'unitAbbreviation' => $selectedItem->unit->abbreviation(),
                'unitLabel' => $selectedItem->unit->label(),
                'deliveryDate' => $selectedItem->delivery_date?->translatedFormat('d \d\e F \d\e Y'),
                'note' => $selectedItem->note,
            ],
        );
        // $this->dispatch("open-public-campaign-bag.{$this->campaignId}");

        $this->resetForm();
        $this->toast()->success('Item adicionado à sacola.')->send();
    }

    #[On('public-campaign-item-add-modal-closed')]
    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'numeric',
                'min:0.1',
                'max:'.$this->pendingBaggedQuantity+1,
            ],
        ];
    }

    #[On('public-campaign-item-add-quantity-updated')]
    public function normalizeQuantity(int|float|string|null $quantity = null): void
    {
        if ($quantity === null || $quantity === '') {
            $this->quantity = $quantity;

            return;
        }

        $normalizedQuantity = (float) str_replace(',', '.', (string) $quantity);

        $this->quantity = min($normalizedQuantity, $this->pendingBaggedQuantity);
    }

    public function validationAttributes(): array
    {
        return [
            'quantity' => 'quantidade',
        ];
    }

    private function findAvailableItem(?int $item): CampaignItem
    {
        return CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->whereColumn('bagged_quantity', '<', 'required_quantity')
            ->findOrFail($item);
    }

    private function pendingQuantity(CampaignItem $item): float
    {
        return max((float) $item->required_quantity - (float) $item->bagged_quantity, 0);
    }

    private function resetForm(): void
    {
        $this->reset([
            'modal',
            'itemId',
            'itemName',
            'itemComplement',
            'quantity',
            'pendingBaggedQuantity',
            'formattedPendingBaggedQuantity',
            'unitAbbreviation',
            'unitLabel',
            'deliveryDate',
            'note',
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
