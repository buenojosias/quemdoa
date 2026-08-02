<?php

use App\Models\Campaign;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $campaignId;

    public string $name = '';

    public ?string $institution = null;

    public ?string $group = null;

    public ?string $description = null;

    public string $confirmationDeadline = '';

    public string $deliveryDeadline = '';

    public string $createdAt = '';

    public string $updatedAt = '';

    public function mount(Campaign $campaign): void
    {
        $this->campaignId = (string) $campaign->getKey();

        $this->fillCampaignInfo($campaign);
    }

    #[On('campaign-updated.{campaignId}')]
    public function refreshCampaign(): void
    {
        $campaign = Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);

        $this->fillCampaignInfo($campaign);
    }

    private function fillCampaignInfo(Campaign $campaign): void
    {
        $this->name = $campaign->name;
        $this->institution = $campaign->institution;
        $this->group = $campaign->group;
        $this->description = $campaign->description;
        $this->confirmationDeadline = $campaign->confirmation_deadline->format('d/m/Y');
        $this->deliveryDeadline = $campaign->delivery_deadline->format('d/m/Y');
        $this->createdAt = $campaign->created_at->format('d/m/Y H:i');
        $this->updatedAt = $campaign->updated_at->format('d/m/Y H:i');
    }
};
