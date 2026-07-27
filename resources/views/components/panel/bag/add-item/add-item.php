<?php

use App\Models\Bag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $bagId;

    #[Locked]
    public string $bagCode;

    #[Locked]
    public string $campaignName;

    public bool $modal = false;

    public function mount(Bag|int|string|null $bag = null, int|string|null $bagId = null, ?string $bagCode = null, ?string $campaignName = null): void
    {
        $this->bagId = $bag instanceof Bag
            ? (string) $bag->getKey()
            : (string) ($bag ?? $bagId);

        $this->bagCode = $bagCode ?? ($bag instanceof Bag ? $bag->code : '');
        $this->campaignName = $campaignName ?? ($bag instanceof Bag ? $bag->campaign()->value('name') : '');
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
