<?php

use App\Models\Campaign;
use Livewire\Component;

new class extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }
};