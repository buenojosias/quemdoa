<?php

use App\Enums\BagItemStatusEnum;
use App\Livewire\Panel\Bag\Show as BagShow;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    #[Locked]
    public ?int $bagId = null;

    #[Locked]
    public ?int $campaignId = null;

    public string $bagCode = '';

    public int $itemsCount = 0;

    #[On('open-set-bag-received')]
    public function open(int $bag): void
    {
        $selectedBag = $this->findBag($bag);

        $this->bagId = $selectedBag->id;
        $this->campaignId = $selectedBag->campaign_id;
        $this->bagCode = $selectedBag->code;
        $this->itemsCount = $selectedBag->items_count;

        $this->dialog()
            ->question(
                'Marcar sacola como recebida?',
                "Todos os {$this->itemsCount} itens da sacola {$this->bagCode} serão marcados como recebidos com as quantidades definidas.",
            )
            ->confirm('Marcar como recebida', 'save')
            ->cancel('Cancelar')
            ->send();
    }

    public function save(): void
    {
        $campaignId = null;
        $bagId = null;
        $affectedCampaignItems = collect();

        DB::transaction(function () use (&$campaignId, &$bagId, &$affectedCampaignItems): void {
            $bag = $this->findBagForReceiving($this->bagId);
            $campaignId = $bag->campaign_id;
            $bagId = $bag->id;
            $affectedCampaignItems = $this->affectedCampaignItems($bag);

            $bag->items()->update([
                'status' => BagItemStatusEnum::RECEIVED->value,
            ]);

            $bag->update([
                'confirmed_at' => $bag->confirmed_at ?? now(),
                'confirmed_by' => $bag->confirmed_by ?? 'organizer',
                'received_at' => now(),
            ]);

            $affectedCampaignItems->each(function (int $campaignItem): void {
                $this->refreshItemQuantities($campaignItem);
            });
        });

        $this->reset(['bagId', 'campaignId', 'bagCode', 'itemsCount']);

        $this->toast()->success('Sacola marcada como recebida com sucesso.')->send();
        $this->dispatch("bag-item-received.{$bagId}")->to('panel.tables.bag-items');
        $this->dispatch("bag-item-received.{$bagId}")->to(BagShow::class);
        $this->dispatch("campaign-bag-status-updated.{$campaignId}", bag: $bagId);
        $this->dispatch("item-created.{$campaignId}");

        $affectedCampaignItems->each(function (int $campaignItem) use ($campaignId): void {
            $this->dispatch("campaign-bag-item-received.{$campaignId}", item: $campaignItem);
            $this->dispatch(
                "campaign-bag-item-quantity-updated.{$campaignId}",
                item: $campaignItem,
                status: BagItemStatusEnum::RECEIVED->value,
            );
        });
    }

    private function findBag(?int $bag): Bag
    {
        return Bag::query()
            ->withCount('items')
            ->whereKey($bag)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    private function findBagForReceiving(?int $bag): Bag
    {
        return Bag::query()
            ->with('items')
            ->whereKey($bag)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @return Collection<int, int>
     */
    private function affectedCampaignItems(Bag $bag): Collection
    {
        return $bag->items
            ->pluck('campaign_item_id')
            ->unique()
            ->values();
    }

    private function refreshItemQuantities(int $campaignItem): void
    {
        $baggedQuantity = BagItem::query()
            ->where('campaign_item_id', $campaignItem)
            ->whereIn('status', [
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = BagItem::query()
            ->where('campaign_item_id', $campaignItem)
            ->where('status', BagItemStatusEnum::RECEIVED->value)
            ->sum('quantity');

        CampaignItem::query()
            ->whereKey($campaignItem)
            ->update([
                'bagged_quantity' => $baggedQuantity,
                'received_quantity' => $receivedQuantity,
            ]);
    }
};
