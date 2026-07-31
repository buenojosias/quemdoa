<?php

namespace App\Livewire\Public\Campaign;

use App\Enums\CategoryEnum;
use App\Models\Campaign;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public Campaign $campaign;

    #[Locked]
    public string $campaignId;

    public array $bagItemIds = [];

    /**
     * @var array<int, array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}>
     */
    public array $bagItems = [];

    public bool $bagSlide = false;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->campaignId = (string) $campaign->id;
    }

    public function render(): View
    {
        return view('livewire.public.campaign.show')
            ->layout('layouts.public');
    }

    /**
     * @return array<int, array{name: string, illustration: string, items: array<int, array{id: int, name: string, complement: ?string, required_quantity: string, promised_quantity: string, pending_quantity: string, pending_quantity_label: string, unit_abbreviation: string, unit_label: string, progress: int, delivery_date: ?string, note: ?string, is_complete: bool, is_added: bool, button_text: string, button_disabled: bool}>}>
     */
    #[Computed]
    public function itemsByCategory(): array
    {
        $items = $this->campaignItems()
            ->groupBy(fn (CampaignItem $item): string => $item->category->value);

        return collect(CategoryEnum::cases())
            ->filter(fn (CategoryEnum $category): bool => $items->has($category->value))
            ->map(fn (CategoryEnum $category): array => [
                'name' => $category->value,
                'illustration' => $category->illustration(),
                'items' => $items
                    ->get($category->value)
                    ->map(fn (CampaignItem $item): array => $this->formatItem($item))
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, CampaignItem>
     */
    private function campaignItems(): Collection
    {
        return $this->campaign
            ->items()
            ->orderBy('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{id: int, name: string, complement: ?string, required_quantity: string, promised_quantity: string, pending_quantity: string, pending_quantity_label: string, unit_abbreviation: string, unit_label: string, progress: int, delivery_date: ?string, note: ?string, is_complete: bool, is_added: bool, button_text: string, button_disabled: bool}
     */
    private function formatItem(CampaignItem $item): array
    {
        $requiredQuantity = (float) $item->required_quantity;
        $promisedQuantity = min((float) $item->bagged_quantity, $requiredQuantity);
        $pendingQuantity = max($requiredQuantity - $promisedQuantity, 0);
        $isAdded = in_array((int) $item->id, $this->bagItemIds, true);
        $isComplete = $pendingQuantity <= 0;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'complement' => $item->complement,
            'required_quantity' => $this->formatQuantity($requiredQuantity),
            'promised_quantity' => $this->formatQuantity($promisedQuantity),
            'pending_quantity' => $this->formatQuantity($pendingQuantity),
            'pending_quantity_label' => $this->pendingQuantityLabel($pendingQuantity, $item->unit->label()),
            'unit_abbreviation' => $item->unit->abbreviation(),
            'unit_label' => $item->unit->label(),
            'progress' => $requiredQuantity > 0 ? (int) min(($promisedQuantity / $requiredQuantity) * 100, 100) : 0,
            'delivery_date' => $item->delivery_date?->translatedFormat('d \d\e F \d\e Y'),
            'note' => $item->note,
            'is_complete' => $isComplete,
            'is_added' => $isAdded,
            'button_text' => $isAdded ? 'Na sacola' : ($isComplete ? 'Completo' : 'Vou levar'),
            'button_disabled' => $isAdded || $isComplete,
        ];
    }

    /**
     * @param  array{id: int, name: string, complement: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}  $bagItem
     */
    #[On('public-campaign-item-added.{campaignId}')]
    public function markItemAsAdded(int $item, array $bagItem): void
    {
        if (! in_array($item, $this->bagItemIds, true)) {
            $this->bagItemIds[] = $item;
        }

        if ($this->findBagItemIndex($item) === null) {
            $this->bagItems[] = $this->normalizeBagItem($bagItem);
        }

        $this->bagSlide = true;

        unset($this->itemsByCategory);
    }

    #[On('public-campaign-item-removed.{campaignId}')]
    public function markItemAsRemoved(int $item): void
    {
        $this->bagItemIds = array_values(
            array_filter($this->bagItemIds, fn (int $bagItem): bool => $bagItem !== $item),
        );
        $this->bagItems = array_values(
            array_filter($this->bagItems, fn (array $bagItem): bool => $bagItem['id'] !== $item),
        );

        unset($this->itemsByCategory);
    }

    #[On('public-campaign-bag-item-quantity-updated.{campaignId}')]
    public function updateBagItemQuantity(int $item, float $quantity): void
    {
        $index = $this->findBagItemIndex($item);

        if ($index === null) {
            return;
        }

        $quantity = min(
            max($quantity, 0.1),
            $this->bagItems[$index]['pendingBaggedQuantity'],
        );

        $this->bagItems[$index]['quantity'] = $quantity;
        $this->bagItems[$index]['formattedQuantity'] = $this->formatQuantity($quantity);
    }

    #[On('open-bag-slide')]
    public function openBag(): void
    {
        $this->dispatch("open-public-campaign-bag.{$this->campaignId}");
    }

    /**
     * @param  array{id: int, name: string, complement: ?string, quantity: float|int|string, formattedQuantity?: string, pendingBaggedQuantity: float|int|string, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}  $bagItem
     * @return array{id: int, name: string, complement: ?string, quantity: float, formattedQuantity: string, pendingBaggedQuantity: float, unitAbbreviation: string, unitLabel: string, deliveryDate: ?string}
     */
    private function normalizeBagItem(array $bagItem): array
    {
        $quantity = (float) $bagItem['quantity'];

        return [
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
    }

    private function findBagItemIndex(int $item): ?int
    {
        foreach ($this->bagItems as $index => $bagItem) {
            if ($bagItem['id'] === $item) {
                return $index;
            }
        }

        return null;
    }

    private function pendingQuantityLabel(float $quantity, string $unit): string
    {
        if ($quantity <= 0) {
            return 'Meta atingida';
        }

        $verb = $quantity > 1 ? 'Faltam' : 'Falta';

        return $verb.' '.$this->formatQuantity($quantity).' '.$unit;
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }

}
