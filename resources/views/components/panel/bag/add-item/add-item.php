<?php

use App\Models\Bag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class () extends Component {
    #[Locked]
    public string $bagId;

    #[Locked]
    public string $bagCode;

    public bool $modal = false;

    public function mount(int $bagId, string $bagCode): void
    {
        $this->bagId = $bagId;
        $this->bagCode = $bagCode;
    }

    #[Computed]
    public function bag(): Bag
    {
        return Bag::query()
            ->with('campaign')
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($this->bagId);
    }

    /*
    INSTRUÇÕES
    - Este componente deve ser montado e renderizado sem a lista de itens
    - Ao abrir o modal, deve-se carregar a lista de itens da campanha (exceto os itens que já estão na sacola), agrupados por categoria em um componente x-acordion, com cada item exibindo o nome e a quantidade pendente
    - Ao fechar o modal, a lista de itens deve permanecer carregada, para não ser necessária uma nova consulta caso o usuário feche e reabra o modal
    - Ao lado de cada item na lista, deve haver um botão (x-button) "Adicionar" que, ao ser clicado, abre um segundo modal (menor) para informar a quantidade e se foi recebido
    - O segundo modal deve ter um campo de input (x-number) para a quantidade, com validação para garantir que o valor seja um número inteiro ou decimal positivo e um toggle (x-toggle) "Recebido"
    - Ao confirmar a adição, o item deve ser adicionado à sacola com a quantidade especificada, e o segundo modal deve ser fechado.
    - O segundo modal também pode ser fechado ao clicar fora dele ou ao pressionar a tecla "Esc", e deve ser possível abrir o segundo modal novamente para adicionar mais itens.
    - O segundo modal também deve exibir o nome do item e a quantidade pendente
    - Ao adicionar um item, deve-se emitir um evento para atualizar a lista de itens da sacola no componente pai e exibir uma mensagem de sucesso para o usuário
    - Lembre-se de atualizar corretamente os campos bagged_quantity e $received_quantity da tabela campaign_items
    */
};
