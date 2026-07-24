<?php

use App\Models\Item;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $itemId;

    public Item $item;

    public int $campaign_id;

    public string $donor_name = '';

    public string $donor_whatsapp = '';

    #[Locked]
    public string $item_name = '';

    public int $promised_quantity = 0;

    public bool $received = false;

    public bool $modal = false;

    public function mount(int $itemId)
    {
        $this->itemId = $itemId;
    }

    #[On('open-add-modal')]
    public function openModal(): void
    {
        $this->item = Item::findOrFail($this->itemId);
        $this->item_name = $this->item->name;
        $this->modal = true;
    }

    #[On('add-modal-closed')]
    public function unsetItem(): void
    {
        unset($this->item);
    }

    public function save(): void
    {
        $this->validate([
            'donor_whatsapp' => 'nullable|string|max:20',
            'promised_quantity' => 'required|integer|min:1',
        ]);
    }

    /*
        # INSTRUÇÕES
        - As informações sempre estão vazias e devem ser preenchidas quando o modal for aberto.
        - Ao fechar o modal, limpe as informações (variáveis).
        - Ao salvar, caso o $donor_whatsapp esteja preenchido, verifique se já existe um registro em promisses com o mesmo $donor_whatsapp e $campaign_id, caso exista, apenas faça o vínculo, caso contrário, crie um novo registro.
        - O campo confirmed_at em promisses deve ser preenchido com a data atual.
        - Se $received for true, o campo status em promise_items deve ser preenchido com 'received', caso contrário, 'promised'.
        - Após o salvamento, limpe os campos, feche o modal, exiba uma mensagem de sucesso com o x-toast e dispare um evento para atualizar a lista de promessas no item-promisses.
    */
};