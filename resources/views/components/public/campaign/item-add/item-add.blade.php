<x-modal wire size="sm" title="Vou levar">
    <div class="mb-2">
        <p class="font-semibold">{{ $itemName }}</p>
        <p class="text-sm text-slate-600">{{ $itemComplement }}</p>
    </div>
    <p class="text-sm">Informe a quantidade deste item que você irá doar.</p>
    <form class="mt-2 grid grid-cols-2 gap-4">
        <x-number label="Quantidade" wire:model="quantity" :min="0.1" :max="$pendingBaggedQuantity" step="0.5" centralized />
        <span class="pt-8 text-sm text-slate-600">
            Pendente: {{ $pendingBaggedQuantity }} {{ $unitAbbreviation }}
        </span>        
    </form>
    <p class="mt-4 text-sm text-slate-600">
        {{ $note }}
    </p>
    <x-slot:footer>
        <x-button text="Cancelar" class="w-1/2" color="gray" outline />
        <x-button text="Adicionar à sacola" class="w-1/2" />
    </x-slot:footer>
</x-modal>