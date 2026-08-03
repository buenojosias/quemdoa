<?php

use App\Enums\BagItemStatusEnum;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class () extends Component {
    public bool $slide = false;

    #[Locked]
    public string $campaignId;

    public ?int $itemId = null;

    public string $category = '';

    public string $categoryIllustration = 'others.png';

    public string $name = '';

    public ?string $complement = null;

    public string $unitLabel = '';

    public string $unitAbbreviation = '';

    public string $requiredQuantity = '';

    public string $baggedQuantity = '';

    public string $receivedQuantity = '';

    public string $pendingQuantity = '';

    public int $baggedPercent = 0;

    public int $receivedPercent = 0;

    public int $pendingPercent = 0;

    public ?string $deliveryDate = null;

    public ?string $note = null;

    public string $statusLabel = '';

    public string $statusColor = 'blue';

    public int $pendingBagsCount = 0;

    public int $confirmedBagsCount = 0;

    public int $receivedBagsCount = 0;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    #[On('open-item-details.{campaignId}')]
    public function open(int $item): void
    {
        $selectedItem = CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->findOrFail($item);

        $requiredQuantity = (float) $selectedItem->required_quantity;
        $baggedQuantity = (float) $selectedItem->bagged_quantity;
        $receivedQuantity = (float) $selectedItem->received_quantity;
        $pendingQuantity = max($requiredQuantity - $baggedQuantity, 0);

        $this->itemId = $selectedItem->id;
        $this->category = $selectedItem->category->value;
        $this->categoryIllustration = $selectedItem->category->illustration();
        $this->name = $selectedItem->name;
        $this->complement = $selectedItem->complement;
        $this->unitLabel = $selectedItem->unit->label();
        $this->unitAbbreviation = $selectedItem->unit->abbreviation();
        $this->requiredQuantity = $this->formatQuantity($requiredQuantity);
        $this->baggedQuantity = $this->formatQuantity($baggedQuantity);
        $this->receivedQuantity = $this->formatQuantity($receivedQuantity);
        $this->pendingQuantity = $this->formatQuantity($pendingQuantity);
        $this->baggedPercent = $this->progressPercent($baggedQuantity, $requiredQuantity);
        $this->receivedPercent = $this->progressPercent($receivedQuantity, $requiredQuantity);
        $this->pendingPercent = $this->progressPercent($pendingQuantity, $requiredQuantity);
        $this->deliveryDate = $selectedItem->delivery_date?->format('d/m/Y');
        $this->note = $selectedItem->note;
        $this->statusLabel = $baggedQuantity >= $requiredQuantity ? 'Meta atingida' : 'Coletando';
        $this->statusColor = $baggedQuantity >= $requiredQuantity ? 'green' : 'blue';

        $bagStatusCounts = $this->bagStatusCounts($selectedItem->id);
        $this->pendingBagsCount = $bagStatusCounts[BagItemStatusEnum::PENDING->value] ?? 0;
        $this->confirmedBagsCount = $bagStatusCounts[BagItemStatusEnum::CONFIRMED->value] ?? 0;
        $this->receivedBagsCount = $bagStatusCounts[BagItemStatusEnum::RECEIVED->value] ?? 0;

        $this->slide = true;
    }

    /**
     * @return Collection<string, int>
     */
    private function bagStatusCounts(int $item): Collection
    {
        return BagItem::query()
            ->where('campaign_item_id', $item)
            ->whereHas('bag', fn ($query) => $query->where('campaign_id', $this->campaignId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    private function progressPercent(float $quantity, float $requiredQuantity): int
    {
        if ($requiredQuantity <= 0) {
            return 0;
        }

        return (int) min(($quantity / $requiredQuantity) * 100, 100);
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
};
