<?php

use App\Models\Campaign;
use App\Models\CampaignInfo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

new class () extends Component {
    use Interactions;

    #[Locked]
    public int $campaignId;

    public bool $modal = false;

    public bool $editModal = false;

    #[Locked]
    public ?int $editingInfoId = null;

    #[Locked]
    public ?int $deletingInfoId = null;

    public ?string $title = null;

    public ?string $content = null;

    public ?string $editTitle = null;

    public ?string $editContent = null;

    public function mount(int $campaignId): void
    {
        $this->campaignId = $campaignId;
    }

    public function openModal(): void
    {
        $this->resetForm();

        $this->modal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function openEditModal(int $info): void
    {
        $campaignInfo = $this->findInfo($info);

        $this->resetEditForm();

        $this->editingInfoId = $campaignInfo->id;
        $this->editTitle = $campaignInfo->title;
        $this->editContent = $campaignInfo->content;
        $this->editModal = true;
    }

    public function closeEditModal(): void
    {
        $this->resetEditForm();
    }

    public function askToDelete(int $info): void
    {
        $campaignInfo = $this->findInfo($info);

        $this->deletingInfoId = $campaignInfo->id;

        $this->dialog()
            ->question('Excluir informação?', 'Esta ação não poderá ser desfeita.')
            ->confirm('Excluir', 'delete')
            ->cancel('Cancelar')
            ->send();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $campaign = Campaign::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->campaignId);

        $campaign->infos()->create([
            ...$validated,
            'order' => ((int) $campaign->infos()->max('order')) + 1,
        ]);

        unset($this->infos);

        $this->resetForm();

        $this->toast()->success('Informação adicionada com sucesso!')->send();
    }

    public function update(): void
    {
        $validated = $this->validate($this->editRules());

        $this->findInfo($this->editingInfoId)->update([
            'title' => $validated['editTitle'],
            'content' => $validated['editContent'],
        ]);

        unset($this->infos);

        $this->resetEditForm();

        $this->toast()->success('Informação atualizada com sucesso!')->send();
    }

    public function delete(): void
    {
        $this->findInfo($this->deletingInfoId)->delete();

        unset($this->infos);

        $this->reset('deletingInfoId');

        $this->toast()->success('Informação excluída com sucesso!')->send();
    }

    #[Computed]
    public function infos(): Collection
    {
        $infos = CampaignInfo::query()
            ->where('campaign_id', $this->campaignId)
            ->orderBy('order')
            ->oldest()
            ->get();

        return $infos;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'content' => [
                'required',
                'string',
            ],
        ];
    }

    public function editRules(): array
    {
        return [
            'editTitle' => [
                'required',
                'string',
                'max:255',
            ],
            'editContent' => [
                'required',
                'string',
            ],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'title' => 'título',
            'content' => 'informação',
            'editTitle' => 'título',
            'editContent' => 'informação',
        ];
    }

    private function findInfo(?int $info): CampaignInfo
    {
        return CampaignInfo::query()
            ->where('campaign_id', $this->campaignId)
            ->whereHas('campaign', fn ($query) => $query->where('user_id', auth()->id()))
            ->findOrFail($info);
    }

    private function resetForm(): void
    {
        $this->reset([
            'modal',
            'title',
            'content',
        ]);

        $this->resetValidation();
    }

    private function resetEditForm(): void
    {
        $this->reset([
            'editModal',
            'editingInfoId',
            'editTitle',
            'editContent',
        ]);

        $this->resetValidation();
    }
};
