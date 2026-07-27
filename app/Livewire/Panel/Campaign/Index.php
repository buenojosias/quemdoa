<?php

namespace App\Livewire\Panel\Campaign;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Computed]
    public function campaigns()
    {
        return auth()->user()->campaigns()->withCount(['items', 'bags'])->latest()->paginate(10);
    }

    public function render()
    {
        return view('livewire.panel.campaign.index');
    }
}
