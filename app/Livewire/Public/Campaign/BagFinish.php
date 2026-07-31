<?php

namespace App\Livewire\Public\Campaign;

use Livewire\Component;

class BagFinish extends Component
{
    public function render()
    {
        return view('livewire.public.campaign.bag-finish')
            ->layout('layouts.public');
    }
}
