<?php

use App\Models\Bag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $bagId;

    public bool $modal = false;

    public function mount(Bag|int|string $bag): void
    {
        $this->bagId = $bag instanceof Bag
            ? (string) $bag->getKey()
            : (string) $bag;
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->with('campaign')
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }
};
