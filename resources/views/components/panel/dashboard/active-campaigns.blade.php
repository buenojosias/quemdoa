<?php

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /**
     * @return Collection<int, Campaign>
     */
    #[Computed]
    public function campaigns(): Collection
    {
        return Campaign::query()
            ->select(['id', 'user_id', 'name', 'delivery_deadline', 'is_active', 'created_at'])
            ->whereBelongsTo(Auth::user())
            ->withSum('items as required_quantity_sum', 'required_quantity')
            ->withSum('items as bagged_quantity_sum', 'bagged_quantity')
            ->latest('created_at')
            ->latest('id')
            ->limit(4)
            ->get();
    }

    public function progress(Campaign $campaign): int
    {
        $requiredQuantity = (float) $campaign->required_quantity_sum;

        if ($requiredQuantity <= 0) {
            return 0;
        }

        return (int) min(((float) $campaign->bagged_quantity_sum / $requiredQuantity) * 100, 100);
    }

    public function statusLabel(Campaign $campaign): string
    {
        return $campaign->is_active ? 'Ativa' : 'Inativa';
    }

    public function statusColor(Campaign $campaign): string
    {
        return $campaign->is_active ? 'green' : 'neutral';
    }
};
?>

<x-card>
    <x-slot:header>
        <h2 class="text-medium font-semibold text-slate-900 dark:text-white">Campanhas recentes</h2>
        <x-button :href="route('panel.campaigns.index')" text="Ver todas" color="secondary" flat sm />
    </x-slot:header>

    <div class="divide-y divide-slate-200 dark:divide-slate-600">
        @forelse ($this->campaigns as $campaign)
            <div class="flex justify-between items-center gap-4">
                <div class="p-1">
                    <div class="py-0.5 bg-primary-50 dark:bg-slate-900/50 rounded-full">
                        <x-icon name="megaphone" class="w-8 h-8 m-2 text-primary-500" />
                    </div>
                </div>
                <div class="flex-1 flex flex-col gap-2 py-4">
                    <div class="flex justify-between gap-3 items-center">
                        <div class="flex-1">
                            <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Encerra em {{ $campaign->delivery_deadline->diffForHumans() }}</p>
                        </div>
                        <x-badge :text="$this->statusLabel($campaign)" :color="$this->statusColor($campaign)" light round="md" />
                    </div>
                    <x-progress :percent="$this->progress($campaign)" color="primary" sm class="w-full" />
                </div>
                <div class="py-1">
                    <x-button icon="chevron-right" color="dark" flat />
                </div>
            </div>
        @empty
            <p class="py-6 text-sm text-slate-500 dark:text-slate-400">Nenhuma campanha cadastrada.</p>
        @endforelse
    </div>
</x-card>
