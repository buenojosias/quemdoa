<?php

namespace App\Livewire\Panel\Item;

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Campaign;
use App\Models\CampaignItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Edit extends Component
{
    use Interactions;

    #[Locked]
    public string $campaignId;

    #[Locked]
    public ?int $itemId = null;

    public bool $modal = false;

    public ?string $category = null;

    public ?string $name = null;

    public ?string $complement = null;

    public ?string $unit = null;

    public ?float $required_quantity = null;

    public ?string $delivery_date = null;

    public ?string $note = null;

    public function mount(Campaign|int|string $campaignId): void
    {
        $this->campaignId = $campaignId instanceof Campaign
            ? (string) $campaignId->getKey()
            : (string) $campaignId;
    }

    public function render(): View
    {
        return view('livewire.panel.item.edit');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category' => [
                'required',
                Rule::enum(CategoryEnum::class),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'complement' => [
                'nullable',
                'string',
                'max:255',
            ],
            'unit' => [
                'required',
                Rule::enum(UnitEnum::class),
            ],
            'required_quantity' => [
                'required',
                'numeric',
                'min:0.5',
                'max:999.9',
            ],
            'delivery_date' => [
                'nullable',
                'date',
                'after:today',
                'before:'.$this->campaignDeliveryDeadline()->toDateString(),
            ],
            'note' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'required_quantity' => 'quantidade necessária',
            'delivery_date' => 'data limite de entrega',
        ];
    }

    public function minimumDeliveryDate(): Carbon
    {
        return today()->addDay();
    }

    public function maximumDeliveryDate(): Carbon
    {
        return $this->campaignDeliveryDeadline()->copy()->subDay();
    }

    public function categoryOptions(): array
    {
        return [
            [
                'label' => 'Selecione',
                'value' => '',
            ],
            ...array_map(
                fn (CategoryEnum $category): array => [
                    'label' => $category->value,
                    'value' => $category->value,
                ],
                CategoryEnum::cases(),
            ),
        ];
    }

    public function unitOptions(): array
    {
        return [
            [
                'label' => 'Selecione',
                'value' => '',
            ],
            ...array_map(
                fn (UnitEnum $unit): array => [
                    'label' => ucfirst($unit->label()),
                    'value' => $unit->value,
                ],
                UnitEnum::cases(),
            ),
        ];
    }

    #[On('open-item-edit.{campaignId}')]
    public function open(int $item): void
    {
        $campaignItem = $this->findItem($item);

        $this->itemId = $campaignItem->id;
        $this->category = $campaignItem->category->value;
        $this->name = $campaignItem->name;
        $this->complement = $campaignItem->complement;
        $this->unit = $campaignItem->unit->value;
        $this->required_quantity = (float) $campaignItem->required_quantity;
        $this->delivery_date = $campaignItem->delivery_date?->toDateString();
        $this->note = $campaignItem->note;

        $this->resetValidation();

        $this->modal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->findItem($this->itemId)->update($validated);

        $this->resetForm();

        $this->toast()->success('Item atualizado com sucesso!')->send();

        $this->dispatch("item-created.{$this->campaignId}");
        $this->dispatch("item-updated.{$this->campaignId}");
    }

    private function findItem(?int $item): CampaignItem
    {
        return CampaignItem::query()
            ->where('campaign_id', $this->campaignId)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($item);
    }

    private function campaignDeliveryDeadline(): Carbon
    {
        return Campaign::query()
            ->findOrFail($this->campaignId)
            ->delivery_deadline;
    }

    private function resetForm(): void
    {
        $this->reset([
            'modal',
            'itemId',
            'category',
            'name',
            'complement',
            'unit',
            'required_quantity',
            'delivery_date',
            'note',
        ]);

        $this->resetValidation();
    }
}
