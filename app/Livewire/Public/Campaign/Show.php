<?php

namespace App\Livewire\Public\Campaign;

use App\Models\Campaign;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function render()
    {
        return view('livewire.public.campaign.show')
            ->layout('layouts.public');
    }

    /*
    INSTRUÇÕES
    Carregar os itens da campanha, incluindo os produtos e suas variações, para exibir na página de detalhes da campanha.
    Agrupar os itens por categoria.
    No blade, para cada item, exibir o nome, complemento (abaixo do nome, quando disponível), quantidade necessária com label, quantidade prometida (com x-progress) e x-button Vou levar (não implante esta função ainda).
    No blade, cada categoria deve estar em um x-accordion individual, com o nome da categoria como título do accordion, e a lista de itens.
    Se a quantidade prometida de cada item for superior à quantidade quantidade necessária, substituir a quantidade prometida pela quantidade necessária.
    */
}
