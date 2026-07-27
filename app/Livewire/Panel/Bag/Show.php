<?php

namespace App\Livewire\Panel\Bag;

use App\Models\Bag;
use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public string $campaignId;

    #[Locked]
    public string $bagId;

    public function mount(Campaign|int|string $campaign, Bag|int|string $bag): void
    {
        $this->campaignId = $campaign instanceof Campaign
            ? (string) $campaign->getKey()
            : (string) $campaign;

        $this->bagId = $bag instanceof Bag
            ? (string) $bag->getKey()
            : (string) $bag;
    }

    #[Computed]
    public function campaign(): Campaign
    {
        return Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->whereBelongsTo($this->campaign)
            ->findOrFail($this->bagId);
    }

    public function render(): View
    {
        return view('livewire.panel.bag.show');
    }
}
