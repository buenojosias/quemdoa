
<div>
    <x-button text="Adicionar item" icon="plus" wire:click="$toggle('modal')" />

    <x-modal title="Adicionar item à sacola {{ $bagCode }}" wire="modal" center>
        <x-alert color="secondary" light icon="information-circle" title="Seleção de item">
            A inclusão de novos itens nesta sacola será conectada ao catálogo da campanha {{ $campaignName }}.
        </x-alert>
    </x-modal>
</div>
