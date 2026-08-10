<?php

use App\Models\Campaign;
use App\Models\CampaignInfo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    #[Locked]
    public int $campaignId;

    public bool $modal = false;

    public ?string $title = null;

    public ?string $content = null;

    public function mount(int $campaignId): void
    {
        $this->campaignId = $campaignId;
    }

    public function openModal(): void
    {
        $this->resetForm();

        $this->modal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $campaign = Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);

        $campaign->infos()->create([
            ...$validated,
            'order' => ((int) $campaign->infos()->max('order')) + 1,
        ]);

        unset($this->infos);

        $this->resetForm();

        $this->toast()->success('Informação adicionada com sucesso!')->send();
    }

    #[Computed]
    public function infos(): Collection
    {
        $infos = CampaignInfo::query()
            ->where('campaign_id', $this->campaignId)
            ->orderBy('order')
            ->oldest()
            ->get();

        return $infos;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'content' => [
                'required',
                'string',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'title' => 'título',
            'content' => 'informação',
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'modal',
            'title',
            'content',
        ]);

        $this->resetValidation();
    }
};
