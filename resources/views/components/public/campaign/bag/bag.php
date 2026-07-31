<?php

use App\Support\PublicCampaignBagSession;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $campaignId;

    /**
     * @var array<int, array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}>
     */
    public array $bagItems = [];

    public bool $slide = false;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;

        if ($this->bagItems === []) {
            $this->bagItems = PublicCampaignBagSession::get($this->campaignId);
        }
    }

    #[On('open-public-campaign-bag.{campaignId}')]
    public function openSlide(): void
    {
        $this->slide = true;
    }

    /**
     * @param  array{id: int, name: string, complement: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}  $bagItem
     */
    #[On('public-campaign-item-added.{campaignId}')]
    public function addItem(int $item, array $bagItem): void
    {
        if ($this->findItemIndex($item) !== null) {
            return;
        }

        $quantity = (float) $bagItem['quantity'];

        $this->bagItems[] = [
            'id' => (int) $bagItem['id'],
            'name' => $bagItem['name'],
            'complement' => $bagItem['complement'],
            'quantity' => $quantity,
            'formattedQuantity' => $this->formatQuantity($quantity),
            'pendingBaggedQuantity' => (float) $bagItem['pendingBaggedQuantity'],
            'unitAbbreviation' => $bagItem['unitAbbreviation'],
            'unitLabel' => $bagItem['unitLabel'],
            'deliveryDate' => $bagItem['deliveryDate'],
        ];

        $this->persistBagItems();
    }

    #[On('public-campaign-bag-increment.{campaignId}')]
    public function increment(int $item): void
    {
        $index = $this->findItemIndex($item);

        if ($index === null) {
            return;
        }

        $quantity = min(
            $this->bagItems[$index]['quantity'] + 0.5,
            $this->bagItems[$index]['pendingBaggedQuantity'],
        );

        $this->updateItemQuantity($index, $quantity);
    }

    #[On('public-campaign-bag-decrement.{campaignId}')]
    public function decrement(int $item): void
    {
        $index = $this->findItemIndex($item);

        if ($index === null) {
            return;
        }

        $quantity = max($this->bagItems[$index]['quantity'] - 0.5, 0.1);

        $this->updateItemQuantity($index, $quantity);
    }

    #[On('public-campaign-bag-remove.{campaignId}')]
    public function remove(int $item): void
    {
        $this->bagItems = array_values(
            array_filter($this->bagItems, fn (array $bagItem): bool => $bagItem['id'] !== $item),
        );

        $this->persistBagItems();

        $this->dispatch("public-campaign-item-removed.{$this->campaignId}", item: $item);
    }

    #[On('public-campaign-bag-finish.{campaignId}')]
    public function finish(): void
    {
        if ($this->bagItems === []) {
            return;
        }

        $this->slide = false;

        $this->dispatch(
            "open-public-campaign-confirm-bag.{$this->campaignId}",
            bagItems: $this->bagItems,
        );
    }

    #[Computed]
    public function totalItems(): int
    {
        return count($this->bagItems);
    }

    private function updateItemQuantity(int $index, float $quantity): void
    {
        $this->bagItems[$index]['quantity'] = $quantity;
        $this->bagItems[$index]['formattedQuantity'] = $this->formatQuantity($quantity);

        $this->dispatch(
            "public-campaign-bag-item-quantity-updated.{$this->campaignId}",
            item: $this->bagItems[$index]['id'],
            quantity: $quantity,
        );

        $this->persistBagItems();
    }

    private function findItemIndex(int $item): ?int
    {
        foreach ($this->bagItems as $index => $bagItem) {
            if ($bagItem['id'] === $item) {
                return $index;
            }
        }

        return null;
    }

    private function persistBagItems(): void
    {
        PublicCampaignBagSession::put($this->campaignId, $this->bagItems);
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
};
