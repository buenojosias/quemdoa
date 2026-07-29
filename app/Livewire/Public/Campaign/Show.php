<?php

namespace App\Livewire\Public\Campaign;

use App\Enums\CategoryEnum;
use App\Models\Campaign;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function render(): View
    {
        return view('livewire.public.campaign.show')
            ->layout('layouts.public');
    }

    /**
     * @return array<int, array{name: string, illustration: string, items: array<int, array{id: int, name: string, complement: ?string, required_quantity: string, promised_quantity: string, pending_quantity: string, pending_quantity_label: string, unit_abbreviation: string, unit_label: string, progress: int, delivery_date: ?string, note: ?string, is_complete: bool}>}>
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
     * @return array{id: int, name: string, complement: ?string, required_quantity: string, promised_quantity: string, pending_quantity: string, pending_quantity_label: string, unit_abbreviation: string, unit_label: string, progress: int, delivery_date: ?string, note: ?string, is_complete: bool}
     */
    private function formatItem(CampaignItem $item): array
    {
        $requiredQuantity = (float) $item->required_quantity;
        $promisedQuantity = min((float) $item->bagged_quantity, $requiredQuantity);
        $pendingQuantity = max($requiredQuantity - $promisedQuantity, 0);

        return [
            'id' => $item->id,
            'name' => $item->name,
            'complement' => $item->complement,
            'required_quantity' => $this->formatQuantity($requiredQuantity),
            'promised_quantity' => $this->formatQuantity($promisedQuantity),
            'pending_quantity' => $this->formatQuantity($pendingQuantity),
            'pending_quantity_label' => $this->pendingQuantityLabel($pendingQuantity, $item->unit->abbreviation()),
            'unit_abbreviation' => $item->unit->abbreviation(),
            'unit_label' => $item->unit->label(),
            'progress' => $requiredQuantity > 0 ? (int) min(($promisedQuantity / $requiredQuantity) * 100, 100) : 0,
            'delivery_date' => $item->delivery_date?->translatedFormat('d \d\e F \d\e Y'),
            'note' => $item->note,
            'is_complete' => $pendingQuantity <= 0,
        ];
    }

    private function pendingQuantityLabel(float $quantity, string $unit): string
    {
        return $this->formatQuantity($quantity).' '.$unit.' pendente';
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return (string) (int) $quantity;
        }

        return number_format($quantity, 1, ',', '');
    }
}
