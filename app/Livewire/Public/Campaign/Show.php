<?php

namespace App\Livewire\Public\Campaign;

use App\Models\Campaign;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function render()
    {
        return view('livewire.public.campaign.show');
    }
}
