<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    #[Computed]
    public function activeCampaignsCount(): int
    {
        return Campaign::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->count();
    }

    #[Computed]
    public function registeredBagsCount(): int
    {
        return Bag::query()
            ->whereHas('campaign', fn ($query) => $query
                ->where('user_id', Auth::id())
                ->where('is_active', true))
            ->count();
    }

    #[Computed]
    public function pendingBagsCount(): int
    {
        return Bag::query()
            ->whereHas('campaign', fn ($query) => $query
                ->where('user_id', Auth::id())
                ->where('is_active', true))
            ->whereHas('items', fn ($query) => $query->where('status', BagItemStatusEnum::PENDING->value))
            ->count();
    }

    #[Computed]
    public function receivedBagsCount(): int
    {
        return Bag::query()
            ->whereHas('campaign', fn ($query) => $query->where('user_id', Auth::id()))
            ->whereHas('items', fn ($query) => $query->where('status', BagItemStatusEnum::RECEIVED->value))
            ->count();
    }
};
?>

<div class="relative">
    <div
        wire:loading.flex
        class="absolute inset-0 z-10 hidden items-center justify-center rounded-lg bg-white/70 backdrop-blur-sm dark:bg-slate-950/60">
        <div class="flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            <x-icon name="arrow-path" class="h-4 w-4 animate-spin" />
            Atualizando estatísticas
        </div>
    </div>

    <div wire:loading.class="opacity-50" class="grid grid-cols-1 gap-5 transition-opacity sm:grid-cols-2 xl:grid-cols-4">
        <x-custom-stats
            title="Campanhas ativas"
            icon="flag"
            :number="$this->activeCampaignsCount"
            color="cyan"
            description="Suas campanhas que estão ativas"
            link_label="Ver todas"
            :link_url="route('panel.campaigns.index')" />

        <x-custom-stats
            title="Sacolas cadastradas"
            icon="gift"
            :number="$this->registeredBagsCount"
            color="amber"
            description="Sacolas cadastradas em suas campanhas ativas"
            link_label="Ver sacolas"
            :link_url="route('panel.campaigns.index')" />

        <x-custom-stats
            title="Sacolas a confirmar"
            icon="check-circle"
            :number="$this->pendingBagsCount"
            color="emerald"
            description="Sacolas que estão pendentes de confirmação"
            link_label="Ver pendentes"
            :link_url="route('panel.campaigns.index')" />

        <x-custom-stats
            title="Sacolas recebidas"
            icon="cube"
            :number="$this->receivedBagsCount"
            color="blue"
            description="Sacolas recebidas em todas as suas campanhas"
            link_label="Ver recebidas"
            :link_url="route('panel.campaigns.index')" />
    </div>
</div>
