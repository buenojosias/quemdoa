@props([
    'item' => null,
    'unit' => null,
])

<div class="flex justify-between items-center gap-4 p-4 rounded-lg bg-gray-200/50 dark:bg-gray-800/40 shadow text-sm">
    <div class="flex-1">
        <p class="font-semibold">Código: {{ $item->bag->code }}</p>
        Participante: {{ $item->bag->participant_name }}<br>
        Quantidade: {{ $item->formatted_quantity }} {{ $unit }}
    </div>
    <div>
        <x-badge :text="$this->statusLabel($item)" :color="$this->statusColor($item)" light />    
    </div>
    <x-dropdown icon="ellipsis-horizontal">
        <x-slot:header>
            <p class="text-sm text-center">Ações</p>
        </x-slot:header>
        @if ($item->status->value === 'pending')
            <x-dropdown.items text="Confirmar" wire:click="confirm({{ $item->id }})" />
        @endif
        @if (in_array($item->status->value, ['pending', 'confirmed']))
            <x-dropdown.items text="Confirmar recebimento" wire:click="$dispatch('open-set-item-received', { bagItem: {{ $item->id }} })" />
        @endif
        <x-dropdown.items text="Alterar quantidade" wire:click="$dispatch('open-change-item-quantity', { bagItem: {{ $item->id }} })" />
        <a href="{{ route('panel.campaigns.bags.show', [$item->bag->campaign_id, $item->bag]) }}">
            <x-dropdown.items text="Ver sacola completa" />
        </a>
        <x-dropdown.items text="Excluir" wire:click="askToDelete({{ $item->id }})" separator />
    </x-dropdown>

    {{-- @dump($item) --}}
</div>
