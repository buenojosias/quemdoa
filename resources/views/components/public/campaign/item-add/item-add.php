<?php

use Livewire\Component;

new class extends Component
{
    public $campaignId;

    public string $itemName = 'Pão de alho';

    public string $itemComplement = 'Pacote com 4 unidades Frimesa';

    public float $quantity = 1;

    public int $pendingBaggedQuantity = 4;

    public string $unitAbbreviation = 'un.';

    public string $note = 'Sem glúten';

    public bool $modal = false;

    /*
    INSTRUÇÕES
        - Carregar o componente apenas com o compaignId
        - Escutar o evento disparado ao clicar em "Vou levar" no componente pai para carregar os dados do item e abrir o modal
        - Ao clicar em "Adicionar à sacola", disparar evento para adicionar o item e a quantidade à lista da sacola no outro componente
        - Ao adicionar o item à sacola, disparar evento para fechar o modal e abrir o slide (componente public.campaign.item-add no componente pai)
        - Ao adicionar o item à sacola, também exibir uma mensagem de sucesso com o componente x-toast
        - Ao clicar em "Cancelar", disparar evento para fechar o modal
        - Ao fechar o modal, resetar os dados do item para o estado inicial
        Observação 1: os dados atuais estão mockados, então faça a devida substituição
        Observação 2: faça os devidos ajustes e adições nas variáveis
    */
};