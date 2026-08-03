<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\BagItem;
use App\Models\CampaignItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class extends Component
{
    use Interactions;

    #[Locked]
    public ?int $bagId = null;

    #[Locked]
    public ?int $campaignId = null;

    public bool $hasReceivedItems = false;

    #[On('open-delete-bag')]
    public function open(int $bag): void
    {
        $selectedBag = $this->findBag($bag);

        $this->bagId = $selectedBag->id;
        $this->campaignId = $selectedBag->campaign_id;
        $this->hasReceivedItems = $selectedBag->items->contains(
            fn (BagItem $item): bool => $item->status === BagItemStatusEnum::RECEIVED
        );

        $description = $this->hasReceivedItems
            ? 'Esta sacola possui itens recebidos. Excluir poderá gerar inconsistência entre as quantidades recebidas fisicamente e as registradas na plataforma. Esta ação não poderá ser desfeita.'
            : 'Esta ação não poderá ser desfeita.';

        $this->dialog()
            ->question('Excluir sacola?', $description)
            ->confirm('Excluir', 'delete')
            ->cancel('Cancelar')
            ->send();
    }

    public function delete(): void
    {
        $bag = $this->findBag($this->bagId);
        $campaignId = $bag->campaign_id;
        $affectedCampaignItems = $this->affectedCampaignItems($bag);

        DB::transaction(function () use ($bag, $affectedCampaignItems): void {
            $bag->items()->delete();
            $bag->delete();

            $affectedCampaignItems->each(function (int $campaignItem): void {
                $this->refreshItemQuantities($campaignItem);
            });
        });

        $this->reset(['bagId', 'campaignId', 'hasReceivedItems']);

        $this->toast()->success('Sacola excluída com sucesso.')->flash(true)->send();
        $this->dispatch("bag-deleted.{$campaignId}");
    }

    private function findBag(?int $bag): Bag
    {
        return Bag::query()
            ->with([
                'items' => fn ($query) => $query->whereIn('status', [
                    BagItemStatusEnum::CONFIRMED->value,
                    BagItemStatusEnum::RECEIVED->value,
                ]),
            ])
            ->whereKey($bag)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
    }

    /**
     * @return Collection<int, int>
     */
    private function affectedCampaignItems(Bag $bag): Collection
    {
        return $bag->items()
            ->whereIn('status', [
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
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
?>

<x-dialog />
