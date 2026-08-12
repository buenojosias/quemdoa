<?php

use App\Models\Campaign;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Campaign $campaign;

    public $infos = [];

    public bool $modal = false;

    public function mount(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    #[On('open-infos-modal')]
    public function infos()
    {
        $this->infos = $this->campaign->infos()->get()->toArray();
        $this->modal = true;
    }
};
?>

<x-modal title="Informações e instruções" size="md" wire>
    <div class="space-y-3">
        <div class="text-sm">
            <div class="text-gray-700 dark:text-gray-300 font-semibold">Organizador(a)</div>
            <div class="text-gray-500 dark:text-gray-400 font-medium">
                {{ $campaign->group ?? ($campaign->institution ?? $campaign->user->name) }}
            </div>
        @if ($campaign->group && $campaign->institution)
            <div class="text-gray-500 dark:text-gray-400 font-medium">
                ({{ $campaign->institution }})
            </div>
        @endif
        </div>
        
        <div class="text-sm">
            <div class="text-gray-700 dark:text-gray-300 font-semibold">Descrição</div>
            <div class="text-gray-500 dark:text-gray-400 font-medium">{{ $campaign->description }}</div>
        </div>

        @foreach ($infos as $info)
            <div class="text-sm">
                <div class="text-gray-700 dark:text-gray-300 font-semibold">{{ $info['title'] }}</div>
                <div class="text-gray-500 dark:text-gray-400 font-medium">{{ $info['content'] }}</div>
            </div>
        @endforeach
    </div>
</x-modal>