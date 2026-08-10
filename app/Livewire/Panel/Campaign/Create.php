<?php

namespace App\Livewire\Panel\Campaign;

use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Create extends Component
{
    use Interactions;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $institution = null;

    public ?string $group = null;

    public ?string $confirmation_deadline = null;

    public ?string $delivery_deadline = null;

    public bool $is_active = true;

    public bool $modal = false;

    public function render(): View
    {
        return view('livewire.panel.campaign.create');
    }

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
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation_deadline.after_or_equal' => 'O campo data limite de confirmação deve ser uma data posterior ou igual a hoje.',
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'confirmation_deadline' => 'data limite de confirmação',
            'delivery_deadline' => 'data limite de entrega',
        ];
    }

    #[On('open-campaign-create')]
    public function openModal(): void
    {
        $this->resetValidation();

        $this->modal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $campaign = Campaign::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);
        $this->reset();

        $this->toast()
            ->success('Campanha criada com sucesso!')
            ->flash()
            ->send();

        $this->redirectRoute('panel.campaigns.show', [$campaign]);
    }
}
