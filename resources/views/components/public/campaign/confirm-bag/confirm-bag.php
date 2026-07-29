<?php

use Livewire\Component;

new class extends Component
{
    /* INSTRUÇÕES
        - Ao clicar no botão "Concluir", no componente "bag", deve fechar o slide "bag" e abrir o modal "Confirmar sacola" deste componente
        - Ao abrir o "Confirmar sacola", deve exibir a quantidade total de itens
        - Se o visitante escolher o method 'whatsapp', este campo será obrigatório e deverá ser validado no front-end e no back-end
        - Se o visitante alternar o method de 'whatsapp' para 'organizer', o campo whatsapp deve ser limpo e não será mais obrigatório
        - No método submit, se o WhatsApp estiver preenchido, deverá remover os caracteres não numéricos e validar se o número é válido
        - No momento do salvamento, deverá gerar um código com o GenerateBagCodeService e recuperar os itens da lista para salvar na tabela bag_items
        - Ainda no submit, se o method for 'whatsapp', deverá gerar um confirmation_code de 6 dígitos e ser enviado para o WhatsApp do participante, e abrir um segundo modal, ainda menor com um componente x-pin, com atributos smart e numbers
        - Se o method for 'organizer', assim que houver o salvamento correto, deverá redirecionar para a rota welcome. Caso contrário, o redirecionamento só será feito após a digitação correta pin.
    */
};