
<div>
    <x-button text="Adicionar item" icon="plus" wire:click="$toggle('modal')" />

    <x-modal title="Adicionar item à sacola {{ $this->bag->code }}" wire="modal" center>
        <x-alert color="secondary" light icon="information-circle" title="Seleção de item">
            A inclusão de novos itens nesta sacola será conectada ao catálogo da campanha {{ $this->bag->campaign->name }}.
        </x-alert>
    </x-modal>
</div>
