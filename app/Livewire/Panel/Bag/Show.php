<?php

namespace App\Livewire\Panel\Bag;

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\Campaign;
use App\Models\CampaignItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Show extends Component
{
    use Interactions;

    #[Locked]
    public string $campaignId;

    #[Locked]
    public string $bagId;

    #[Locked]
    public ?string $bagUpdatedAt = null;

    public function mount(Campaign|int|string $campaign, Bag|int|string $bag): void
    {
        $this->campaignId = $campaign instanceof Campaign
            ? (string) $campaign->getKey()
            : (string) $campaign;

        $this->bagId = $bag instanceof Bag
            ? (string) $bag->getKey()
            : (string) $bag;

        $this->bagUpdatedAt = Bag::query()
            ->whereKey($this->bagId)
            ->where('campaign_id', $this->campaignId)
            ->first()
            ?->updated_at
            ?->toJSON();
    }

    #[Computed]
    public function campaign(): Campaign
    {
        return $this->bag->campaign;
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->with('campaign')
            ->where('campaign_id', $this->campaignId)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }

    public function confirm(): void
    {
        $affectedCampaignItems = collect();

        DB::transaction(function () use (&$affectedCampaignItems): void {
            $bag = $this->findBagForConfirmation();

            $affectedCampaignItems = $bag->items
                ->pluck('campaign_item_id')
                ->unique()
                ->values();

            $bag->update([
                'confirmed_at' => $bag->confirmed_at ?? now(),
                'confirmed_by' => $bag->confirmed_by ?? 'organizer',
                'confirmation_code' => null,
            ]);

            $bag->items()
                ->where('status', BagItemStatusEnum::PENDING->value)
                ->update([
                    'status' => BagItemStatusEnum::CONFIRMED->value,
                ]);

            $affectedCampaignItems->each(function (int $campaignItem): void {
                $this->refreshItemQuantities($campaignItem);
            });
        });

        unset($this->bag, $this->campaign);

        $this->toast()->success('Sacola confirmada com sucesso.')->send();
        $this->dispatchBagStatusUpdated();
        $this->dispatch("bag-confirmed.{$this->bagId}");
        $this->dispatch("campaign-bag-confirmed.{$this->campaignId}");
        $this->dispatch("item-created.{$this->campaignId}");

        $affectedCampaignItems->each(function (int $campaignItem): void {
            $this->dispatch(
                "campaign-bag-item-quantity-updated.{$this->campaignId}",
                item: $campaignItem,
                status: BagItemStatusEnum::CONFIRMED->value,
            );
        });
    }

    #[On('bag-item-added.{bagId}')]
    #[On('bag-item-received.{bagId}')]
    public function refreshAfterBagUpdated(): void
    {
        $bag = $this->freshBag();
        $updatedAt = $bag->updated_at?->toJSON();

        if ($updatedAt === $this->bagUpdatedAt) {
            return;
        }

        $this->bagUpdatedAt = $updatedAt;

        unset($this->bag, $this->campaign);

        $this->dispatchBagStatusUpdated();
    }

    #[On('bag-deleted.{campaignId}')]
    public function redirectAfterBagDeleted(): void
    {
        $this->redirectRoute('panel.campaigns.show', ['campaign' => $this->campaignId, 'tab' => 'bags']);
    }

    public function render(): View
    {
        return view('livewire.panel.bag.show');
    }

    private function findBagForConfirmation(): Bag
    {
        return Bag::query()
            ->with('items')
            ->whereKey($this->bagId)
            ->where('campaign_id', $this->campaignId)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function freshBag(): Bag
    {
        return Bag::query()
            ->whereKey($this->bagId)
            ->where('campaign_id', $this->campaignId)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    private function dispatchBagStatusUpdated(): void
    {
        $bag = $this->freshBag();
        $this->bagUpdatedAt = $bag->updated_at?->toJSON();

        $this->dispatch(
            "bag-status-updated.{$this->bagId}",
            updatedAt: $this->bagUpdatedAt,
        );

        $this->dispatch(
            "campaign-bag-status-updated.{$this->campaignId}",
            bag: (int) $this->bagId,
            updatedAt: $this->bagUpdatedAt,
        );
    }

    private function refreshItemQuantities(int $campaignItem): void
    {
        $item = CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->lockForUpdate()
            ->findOrFail($campaignItem);

        $baggedQuantity = BagItem::query()
            ->where('campaign_item_id', $item->id)
            ->whereIn('status', [
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = BagItem::query()
            ->where('campaign_item_id', $item->id)
            ->where('status', BagItemStatusEnum::RECEIVED->value)
            ->sum('quantity');

        $item->update([
            'bagged_quantity' => $baggedQuantity,
            'received_quantity' => $receivedQuantity,
        ]);
    }
}
