<?php

use App\Enums\BagItemStatusEnum;
use App\Models\Bag;
use App\Models\BagItem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public string $bagId;

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
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }

    #[Computed]
    public function bagItems(): Collection
    {
        return BagItem::query()
            ->with('item')
            ->whereBelongsTo($this->bag)
            ->latest()
            ->get();
    }

    public function statusLabel(BagItem $bagItem): string
    {
        return match ($bagItem->status) {
            BagItemStatusEnum::PENDING => 'Pendente',
            BagItemStatusEnum::CONFIRMED => 'Confirmada',
            BagItemStatusEnum::RECEIVED => 'Recebida',
            BagItemStatusEnum::CANCELED => 'Cancelada',
        };
    }

    public function statusColor(BagItem $bagItem): string
    {
        return match ($bagItem->status) {
            BagItemStatusEnum::PENDING => 'yellow',
            BagItemStatusEnum::CONFIRMED => 'cyan',
            BagItemStatusEnum::RECEIVED => 'green',
            BagItemStatusEnum::CANCELED => 'red',
        };
    }
};
?>

<x-card>
    @if ($this->bagItems->isEmpty())
        <x-alert color="secondary" light icon="archive-box" title="Esta sacola ainda não possui itens">
            Adicione o primeiro item para acompanhar a confirmação e o recebimento da doação.
        </x-alert>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-3 font-semibold">Item</th>
                        <th class="px-3 py-3 font-semibold">Quantidade</th>
                        <th class="px-3 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($this->bagItems as $bagItem)
                        <tr wire:key="bag-item-{{ $bagItem->id }}">
                            <td class="px-3 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $bagItem->item->name }}</p>
                                @if ($bagItem->item->complement)
                                    <p class="text-gray-500 dark:text-gray-400">{{ $bagItem->item->complement }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                {{ number_format((float) $bagItem->quantity, 1, ',', '.') }} {{ $bagItem->item->unit->abbreviation() }}
                            </td>
                            <td class="px-3 py-3">
                                <x-badge :text="$this->statusLabel($bagItem)" :color="$this->statusColor($bagItem)" light />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
