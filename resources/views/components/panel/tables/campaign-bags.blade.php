<?php

use App\Models\Bag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $quantity = 10;
    public string $status = '';

    #[Locked]
    public string $campaignId;

    public function mount(int|string $campaignId): void
    {
        $this->campaignId = (string) $campaignId;
    }

    #[Computed]
    public function bags()
    {
        $query = Bag::query()
            ->where('campaign_id', $this->campaignId)
            ->withCount('items')
            ->latest();

        if ($this->status === 'pending') {
            $query->whereNull('confirmed_at');
        }

        if ($this->status === 'confirmed') {
            $query->whereNotNull('confirmed_at');
        }

        return $query->paginate($this->quantity);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            [
                'label' => 'Todos',
                'value' => '',
            ],
            [
                'label' => 'Pendente',
                'value' => 'pending',
            ],
            [
                'label' => 'Confirmada',
                'value' => 'confirmed',
            ],
        ];
    }

    public function statusLabel(Bag $bag): string
    {
        return $bag->confirmed_at ? 'Confirmada' : 'Pendente';
    }

    public function statusColor(Bag $bag): string
    {
        return $bag->confirmed_at ? 'green' : 'yellow';
    }

    public function confirmedByLabel(Bag $bag): string
    {
        return match ($bag->confirmed_by) {
            'organizer' => 'Mim',
            'participant' => 'Participante',
            default => '-',
        };
    }

    public function updatedQuantity(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'headers' => [
                ['index' => 'code', 'label' => 'Código'],
                ['index' => 'participant_name', 'label' => 'Participante'],
                ['index' => 'participant_whatsapp', 'label' => 'WhatsApp'],
                ['index' => 'items_count', 'label' => 'Itens'],
                ['index' => 'confirmed_at', 'label' => 'Status'],
                ['index' => 'confirmed_by', 'label' => 'Confirmada por'],
                ['index' => 'actions'],
            ],
            'rows' => $this->bags,
        ];
    }

    #[On('bag-added.{campaignId}')]
    public function refreshBags(): void
    {
        unset($this->bags);
    }
};
?>

<div class="space-y-4">
    <div class="flex justify-between items-center gap-4">
        <x-select.native wire:model.live="quantity" label="Itens por página">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </x-select>

        <x-select.native label="Status"
            wire:model.live="status"
            :options="$this->statusOptions()"
            select="label:label|value:value" />
    </div>

    <x-table :$headers :$rows paginate>
        @interact('column_code', $row)
            <a href="{{ route('panel.campaigns.bags.show', [$this->campaignId, $row->id]) }}" class="font-medium text-gray-700 dark:text-gray-100">
                {{ $row->code }}
            </a>
        @endinteract
        @interact('column_confirmed_at', $row)
            <x-badge :text="$this->statusLabel($row)" :color="$this->statusColor($row)" light />
        @endinteract
        @interact('column_confirmed_by', $row)
            {{ $this->confirmedByLabel($row) }}
        @endinteract
        @interact('column_actions', $row)
            <div class="flex">
                ...
            </div>
        @endinteract
    </x-table>
</div>
