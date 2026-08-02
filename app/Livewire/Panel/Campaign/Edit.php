<?php

namespace App\Livewire\Panel\Campaign;

use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Edit extends Component
{
    use Interactions;

    #[Locked]
    public string $campaignId;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $institution = null;

    public ?string $group = null;

    public ?string $confirmation_deadline = null;

    public ?string $delivery_deadline = null;

    public bool $is_active = true;

    public bool $modal = false;

    public function mount(Campaign|int|string $campaign): void
    {
        $this->campaignId = $campaign instanceof Campaign
            ? (string) $campaign->getKey()
            : (string) $campaign;
    }

    public function render(): View
    {
        return view('livewire.panel.campaign.edit');
    }

    #[Computed]
    public function campaign(): Campaign
    {
        return Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'institution' => [
                'nullable',
                'string',
                'max:255',
            ],
            'group' => [
                'nullable',
                'string',
                'max:255',
            ],
            'confirmation_deadline' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'delivery_deadline' => [
                'required',
                'date',
                'after_or_equal:confirmation_deadline',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation_deadline.after_or_equal' => 'O campo data limite de confirmação deve ser uma data posterior ou igual a hoje.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'confirmation_deadline' => 'data limite de confirmação',
            'delivery_deadline' => 'data limite de entrega',
        ];
    }

    #[On('open-campaign-edit.{campaignId}')]
    public function openModal(): void
    {
        $this->fillForm();
        $this->resetValidation();

        $this->modal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->campaign->update($validated);

        unset($this->campaign);

        $this->modal = false;

        $this->dispatch('updated');
        $this->dispatch("campaign-updated.{$this->campaignId}");

        $this->toast()
            ->success('Campanha atualizada com sucesso!')
            ->send();
    }

    private function fillForm(): void
    {
        $campaign = $this->campaign;

        $this->name = $campaign->name;
        $this->description = $campaign->description;
        $this->institution = $campaign->institution;
        $this->group = $campaign->group;
        $this->confirmation_deadline = $campaign->confirmation_deadline?->toDateString();
        $this->delivery_deadline = $campaign->delivery_deadline?->toDateString();
        $this->is_active = $campaign->is_active;
    }
}
