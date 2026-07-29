<?php

use Livewire\Component;

new class extends Component
{
    public $campaignId;

    public ?array $bagItems = [
        [
            'id' => 1,
            'name' => 'Pão de alho',
            'complement' => 'Com queijo',
            'quantity' => 1,
            'pendingBaggedQuantity' => 4,
            'unitAbbreviation' => 'un.',
            'deliveryDate' => '20 jul. 2026',
        ],
        [
            'id' => 2,
            'name' => 'Pão de queijo',
            'complement' => '',
            'quantity' => 2,
            'pendingBaggedQuantity' => 12,
            'unitAbbreviation' => 'un.',
            'deliveryDate' => '',
        ],
    ];

    public bool $slide = true;


    /* INSTRUÇÕES
        - O $bagItems está mockado e deverá ser carregado sem os itens
        - Embora eu tenha colocado o $bagItems neste componente, analise se ele deve ficar aqui ou no componente pai
        - Ao clicar em + ou - em um item da sacola, alterar a quantidade neste componente e disparar evento para atualizar a quantidade do item no outro componente
        - Ao clicar em "Remover" em um item da sacola, disparar evento para remover o item da lista da sacola no outro componente
        - Se for possível, defina a position do slide de acordo com o tamanho da tela (bottom em mobile e right em tablet e desktop)
    */
};