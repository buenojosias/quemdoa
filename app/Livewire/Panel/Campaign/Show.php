<?php

namespace App\Livewire\Panel\Campaign;

use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    #[Url]
    public string $tab = 'info';
    
    #[Locked]
    public string $campaignId;

    public function mount(Campaign|int|string $campaign): void
    {
        $this->campaignId = $campaign instanceof Campaign
            ? (string) $campaign->getKey()
            : (string) $campaign;
    }

    #[Computed]
    public function campaign(): Campaign
    {
        return Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);
    }

    public function render(): View
    {
        return view('livewire.panel.campaign.show');
    }
}
