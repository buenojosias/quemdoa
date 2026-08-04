<?php

use Livewire\Component;

new class extends Component
{
    /* INSTRUÇÕES
    - Substitua os valores de exemplo pelos valores reais do sistema.
    - Campanhas ativas: número total de campanhas do usuário que estão com status ativo.
    - Sacolas cadastradas: número total de sacolas das campanhas ativas do usuário.
    - Sacolas a confirmar: número total de sacolas das campanhas ativas do usuário que estão com status pending.
    - Sacolas recebidas: número total de sacolas recebidas em todas as campanhas do usuário, inclusive as não ativas.

    - Adicione efeito de loading neste componente, de acordo com a doc do livewire.
    */
};
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
    
    <x-custom-stats
        title="Campanhas ativas"
        icon="flag"
        number="3"
        color="cyan"
        description="Suas campanhas que estão ativas"
        link_label="Ver todas" />
    
    <x-custom-stats
        title="Sacolas ativas"
        icon="gift"
        number="3"
        color="amber"
        description="Sacolas cadastradas em suas campanhas ativas"
        link_label="Ver itens" />
    
    <x-custom-stats
        title="Sacolas a confirmar"
        icon="check-circle"
        number="3"
        color="emerald"
        description="Sacolas que estão pendentes de confirmação"
        link_label="Ver doações" />
    
    <x-custom-stats
        title="Sacolas recebidas"
        icon="cube"
        number="3"
        color="blue"
        description="Sacolas recebidas em todas as suas campanhas"
        link_label="Ver recebidas" />

</div>
