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

    public string $currentFormattedQuantity = '';

    public float $quantity = 0;

    #[On('open-change-item-quantity')]
    public function open(int $bagItem): void
    {
        $selectedBagItem = $this->findBagItem($bagItem);

        $this->bagItemId = $selectedBagItem->id;
        $this->bagId = $selectedBagItem->bag_id;
        $this->campaignId = $selectedBagItem->bag->campaign_id;
        $this->campaignItemId = $selectedBagItem->campaign_item_id;
        $this->itemName = $selectedBagItem->item->name;
        $this->itemUnitLabel = $selectedBagItem->item->unit->label();
        $this->currentFormattedQuantity = $this->formatQuantity((float) $selectedBagItem->quantity);
        $this->quantity = (float) $selectedBagItem->quantity;
        $this->modal = true;
    }

    #[On('change-item-quantity-modal-closed')]
    public function closeModal(): void
    {
        $this->resetForm();
    }

    #[On('change-item-quantity-save')]
    public function save(int|float|string|null $quantity = null): void
    {
        if ($quantity !== null) {
            $this->quantity = (float) str_replace(',', '.', (string) $quantity);
        }

        $validated = $this->validate();
        $bagItem = $this->findBagItem($this->bagItemId);

        DB::transaction(function () use ($bagItem, $validated): void {
            $bagItem->update([
                'quantity' => $validated['quantity'],
            ]);

            $this->refreshItemQuantities($bagItem->campaign_item_id);
        });

        $bagId = $bagItem->bag_id;
        $campaignId = $bagItem->bag->campaign_id;
        $campaignItemId = $bagItem->campaign_item_id;

        $this->resetForm();

        $this->toast()->success('Quantidade alterada com sucesso.')->send();
        $this->dispatch("bag-item-quantity-updated.{$bagId}");
        $this->dispatch("campaign-bag-item-quantity-updated.{$campaignId}", item: $campaignItemId);
        $this->dispatch("item-created.{$campaignId}");
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'numeric',
                'min:0.1',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'quantity' => 'quantidade',
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
            'currentFormattedQuantity',
            'quantity',
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
?>

<x-modal title="Alterar quantidade" id="change-item-quantity-modal" wire size="sm" center x-on:close="$dispatch('change-item-quantity-modal-closed')">
    <form
        x-on:submit.prevent="$dispatch('change-item-quantity-save', { quantity: $el.querySelector('[dusk=tallstackui_form_number_input]').value })"
        id="change-item-quantity-form"
        class="space-y-4">
        <div class="rounded-md border border-gray-200 p-3 text-sm dark:border-gray-700">
            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $itemName }}</p>
            <p class="text-gray-500 dark:text-gray-400">
                Quantidade atual: {{ $currentFormattedQuantity }} {{ $itemUnitLabel }}
            </p>
        </div>

        <div wire:key="change-item-quantity-number-{{ $bagItemId ?? 'empty' }}">
            <x-number
                :label="'Nova quantidade (' . $itemUnitLabel . ') *'"
                wire:model="quantity"
                :value="$quantity"
                min="0.1"
                step="0.5"
                centralized />
        </div>
    </form>

    <x-slot:footer>
        <x-button text="Cancelar" color="gray" x-on:click="$tsui.close.modal('change-item-quantity-modal')" />
        <x-button type="submit" form="change-item-quantity-form" text="Salvar" />
    </x-slot:footer>
</x-modal>
