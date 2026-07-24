<?php

use App\Enums\PromiseItemStatusEnum;
use App\Models\Item;
use App\Models\Promise;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    public int $itemId;

    public ?Item $item = null;

    public ?int $campaign_id = null;

    public string $donor_name = '';

    public string $donor_whatsapp = '';

    #[Locked]
    public string $item_name = '';

    public int $promised_quantity = 0;

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

        $this->item = Item::query()->findOrFail($this->itemId);
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
        $validated = $this->validate();
        $validated['donor_whatsapp'] = blank($validated['donor_whatsapp'])
            ? null
            : $validated['donor_whatsapp'];

        $item = Item::query()->findOrFail($this->itemId);

        $promise = $this->findOrCreatePromise($validated, $item);

        $promise->items()->create([
            'item_id' => $item->id,
            'promised_quantity' => $validated['promised_quantity'],
            'status' => $this->received
                ? PromiseItemStatusEnum::RECEIVED
                : PromiseItemStatusEnum::PROMISED,
        ]);

        $this->refreshItemQuantities($item);
        $this->resetForm();

        $this->toast()->success('Promessa adicionada com sucesso.')->send();
        $this->dispatch("promise-added.{$item->campaign_id}");
        $this->dispatch("item-created.{$item->campaign_id}");
    }

    public function rules(): array
    {
        return [
            'donor_name' => [
                'required',
                'string',
                'max:255',
            ],
            'donor_whatsapp' => [
                'nullable',
                'string',
                'max:20',
            ],
            'promised_quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'donor_name' => 'nome do doador',
            'donor_whatsapp' => 'WhatsApp do doador',
            'promised_quantity' => 'quantidade prometida',
        ];
    }

    /**
     * @param  array{donor_name: string, donor_whatsapp: ?string, promised_quantity: int}  $validated
     */
    private function findOrCreatePromise(array $validated, Item $item): Promise
    {
        if ($validated['donor_whatsapp']) {
            $promise = Promise::query()
                ->where('campaign_id', $item->campaign_id)
                ->where('donor_whatsapp', $validated['donor_whatsapp'])
                ->first();

            if ($promise) {
                $promise->update([
                    'confirmed_at' => now(),
                ]);

                return $promise;
            }
        }

        return Promise::query()->create([
            'campaign_id' => $item->campaign_id,
            'donor_name' => $validated['donor_name'],
            'donor_whatsapp' => $validated['donor_whatsapp'],
            'confirmed_at' => now(),
        ]);
    }

    private function refreshItemQuantities(Item $item): void
    {
        $promisedQuantity = $item->promisses()
            ->whereIn('status', [
                PromiseItemStatusEnum::PROMISED->value,
                PromiseItemStatusEnum::RECEIVED->value,
                PromiseItemStatusEnum::DELIVERED->value,
            ])
            ->sum('promised_quantity');

        $receivedQuantity = $item->promisses()
            ->whereIn('status', [
                PromiseItemStatusEnum::RECEIVED->value,
                PromiseItemStatusEnum::DELIVERED->value,
            ])
            ->sum('promised_quantity');

        $item->update([
            'promised_quantity' => $promisedQuantity,
            'received_quantity' => $receivedQuantity,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'item',
            'campaign_id',
            'donor_name',
            'donor_whatsapp',
            'item_name',
            'promised_quantity',
            'received',
            'modal',
        ]);

        $this->resetValidation();
    }

    /*
        # INSTRUÇÕES
        - As informações sempre estão vazias e devem ser preenchidas quando o modal for aberto.
        - Ao fechar o modal, limpe as informações (variáveis).
        - Ao salvar, caso o $donor_whatsapp esteja preenchido, verifique se já existe um registro em promisses com o mesmo $donor_whatsapp e $campaign_id, caso exista, apenas faça o vínculo, caso contrário, crie um novo registro.
        - O campo confirmed_at em promisses deve ser preenchido com a data atual.
        - Se $received for true, o campo status em promise_items deve ser preenchido com 'received', caso contrário, 'promised'.
        - Após o salvamento, limpe os campos, feche o modal, exiba uma mensagem de sucesso com o x-toast e dispare um evento para atualizar a lista de promessas no item-promisses.
    */
};
