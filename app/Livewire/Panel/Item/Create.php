<?php

namespace App\Livewire\Panel\Item;

use App\Enums\CategoryEnum;
use App\Enums\UnitEnum;
use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Create extends Component
{
    use Interactions;

    #[Locked]
    public string $campaignId;

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
        return view('livewire.panel.item.create');
    }

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

    public function save(): void
    {
        $validated = $this->validate();

        $campaign = Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);

        $campaign->items()->create($validated);

        $this->reset([
            'name',
            'complement',
            'unit',
            'required_quantity',
            'delivery_date',
            'note',
        ]);

        $this->toast()->success('Item adicionado com sucesso!')->send();

        $this->dispatch("item-created.{$this->campaignId}");
    }

    private function campaignDeliveryDeadline(): Carbon
    {
        return Campaign::query()
            ->findOrFail($this->campaignId)
            ->delivery_deadline;
    }
}
