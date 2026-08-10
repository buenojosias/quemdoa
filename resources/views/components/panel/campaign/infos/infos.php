<?php

use App\Models\Campaign;
use App\Models\CampaignInfo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $campaignId;
    
    public function mount(int $campaignId): void
    {
        $this->campaignId = $campaignId;
    }

    #[Computed]
    public function infos(): Collection
    {
        $infos = CampaignInfo::query()
            ->where('campaign_id', $this->campaignId)
            ->get();

        return $infos;
    }
};