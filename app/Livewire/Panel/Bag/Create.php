<?php

namespace App\Livewire\Panel\Bag;

use App\Models\Bag;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

class Create extends Component
{
    use Interactions;

    #[Locked]
    public int $campaign_id;

    #[Locked]
    public string $campaign_name = '';

    public string $participant_name = '';

    public string $participant_whatsapp = '';

    public string $code = '';
    
    public function mount(int $campaignId, string $campaignName): void
    {
        $this->campaign_id = (int) $campaignId;
        $this->campaign_name = $campaignName;
    }

    public function render()
    {
        return view('livewire.panel.bag.create');
    }

    #[On('open-create-modal')]
    public function openModal(): void
    {
        $this->resetForm();
    }

    #[On('add-modal-closed')]
    public function closeModal(): void
    {
        $this->resetForm();
    }

    protected $validationAttributes = [
        'participant_name' => 'nome do participante',
        'participant_whatsapp' => 'WhatsApp do participante',
    ];

    public function save(): void
    {
        $this->validate([
            'participant_name' => 'required|string|max:255',
            'participant_whatsapp' => 'nullable|string|max:20',
        ]);

        if (!is_null($this->participant_whatsapp)) {
            $this->participant_whatsapp = preg_replace('/\D/', '', $this->participant_whatsapp);
        }

        $generateBagCodeService = new \App\Services\GenerateBagCodeService();
        $this->code = $generateBagCodeService->generateUniqueCode($this->participant_name, $this->campaign_name);

        $bag = Bag::create([
            'campaign_id' => $this->campaign_id,
            'code' => $this->code,
            'participant_name' => $this->participant_name,
            'participant_whatsapp' => $this->participant_whatsapp,
            'confirmed_by' => 'organizer',
            'confirmed_at' => now(),
        ]);

        $this->resetForm();

        $this->toast()
            ->success('Sacola criada com sucesso!')
            ->flash()
            ->send();

        $this->redirectRoute('panel.campaigns.bags.show', [$this->campaign_id, $bag]);
    }

    public function resetForm(): void
    {
        $this->reset([
            'participant_name',
            'participant_whatsapp',
        ]);

        $this->resetValidation();
    }
}
