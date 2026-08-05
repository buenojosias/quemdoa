<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\CampaignItem;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    public int $itemId;

    public ?CampaignItem $item = null;

    public int|string|null $campaign_id = null;

    public string $participant_name = '';

    public string $participant_whatsapp = '';

    #[Locked]
    public string $item_name = '';

    public float $quantity = 0;

    public bool $received = false;

    public bool $modal = false;

    public function mount(int $itemId): void
    {
        $this->itemId = $itemId;
    }

    #[On('open-add-modal')]
    public function openModal(): void
    {
        $this->resetForm();

        $this->item = CampaignItem::query()->findOrFail($this->itemId);
        $this->campaign_id = $this->item->campaign_id;
        $this->item_name = $this->item->name;
        $this->modal = true;
    }

    #[On('add-modal-closed')]
    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        if (!is_null($this->participant_whatsapp)) {
            $this->participant_whatsapp = preg_replace('/\D/', '', $this->participant_whatsapp);
        }

        $validated = $this->validate();
        $validated['participant_whatsapp'] = blank($validated['participant_whatsapp'])
            ? null
            : $validated['participant_whatsapp'];

        $item = CampaignItem::query()->findOrFail($this->itemId);

        $bag = $this->findOrCreateBag($validated, $item);

        $bag->items()->create([
            'campaign_item_id' => $item->id,
            'quantity' => $validated['quantity'],
            'status' => $this->received
                ? BagItemStatusEnum::RECEIVED
                : BagItemStatusEnum::CONFIRMED,
        ]);

        if ($this->received) {
            $bag->markAsReceivedWhenEveryItemIsReceived();
        }

        $this->refreshItemQuantities($item);
        $this->resetForm();

        $this->toast()->success('Sacola adicionada com sucesso.')->send();
        $this->dispatch("bag-added.{$item->campaign_id}");
        $this->dispatch("item-created.{$item->campaign_id}");
    }

    public function rules(): array
    {
        return [
            'participant_name' => [
                'required',
                'string',
                'max:255',
            ],
            'participant_whatsapp' => [
                'nullable',
                'string',
                'max:20',
            ],
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
            'participant_name' => 'nome do participante',
            'participant_whatsapp' => 'WhatsApp do participante',
            'quantity' => 'quantidade',
        ];
    }

    /**
     * @param  array{participant_name: string, participant_whatsapp: ?string, quantity: float|int|string}  $validated
     */
    private function findOrCreateBag(array $validated, CampaignItem $item): Bag
    {
        if ($validated['participant_whatsapp']) {
            $bag = Bag::query()
                ->where('campaign_id', $item->campaign_id)
                ->where('participant_whatsapp', $validated['participant_whatsapp'])
                ->first();

            if ($bag) {
                $bag->update([
                    'confirmed_at' => now(),
                    'confirmed_by' => 'organizer',
                ]);

                return $bag;
            }
        }

        return Bag::query()->create([
            'campaign_id' => $item->campaign_id,
            'code' => $this->generateBagCode(),
            'participant_name' => $validated['participant_name'],
            'participant_whatsapp' => $validated['participant_whatsapp'],
            'confirmed_by' => 'organizer',
            'confirmed_at' => now(),
        ]);
    }

    private function refreshItemQuantities(CampaignItem $item): void
    {
        $baggedQuantity = $item->bagItems()
            ->whereIn('status', [
                BagItemStatusEnum::PENDING->value,
                BagItemStatusEnum::CONFIRMED->value,
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $receivedQuantity = $item->bagItems()
            ->whereIn('status', [
                BagItemStatusEnum::RECEIVED->value,
            ])
            ->sum('quantity');

        $item->update([
            'bagged_quantity' => $baggedQuantity,
            'received_quantity' => $receivedQuantity,
        ]);
    }

    private function generateBagCode(): string
    {
        $generateBagCodeService = new \App\Services\GenerateBagCodeService();
        $code = $generateBagCodeService->generateUniqueCode($this->participant_name);

        return $code;
    }

    private function resetForm(): void
    {
        $this->reset([
            'item',
            'campaign_id',
            'participant_name',
            'participant_whatsapp',
            'item_name',
            'quantity',
            'received',
            'modal',
        ]);

        $this->resetValidation();
    }

};
