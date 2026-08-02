<?php

namespace App\Livewire\Panel\Campaign;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = '';

    public array $headers = [
        ['index' => 'name', 'label' => 'Nome', 'sortable' => false],
        ['index' => 'confirmation_deadline', 'label' => 'Prazo de confirmação', 'sortable' => false],
        ['index' => 'delivery_deadline', 'label' => 'Prazo de entrega', 'sortable' => false],
        ['index' => 'items_count', 'label' => 'Itens', 'sortable' => false],
        ['index' => 'bags_count', 'label' => 'Sacolas', 'sortable' => false],
        ['index' => 'is_active', 'label' => 'Status', 'sortable' => false],
    ];

    #[Computed]
    public function campaigns(): LengthAwarePaginator
    {
        return auth()->user()
            ->campaigns()
            ->withCount(['items', 'bags'])
            ->when($this->status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate();
    }

    public function statusOptions(): array
    {
        return [
            [
                'label' => 'Todos',
                'value' => '',
            ],
            [
                'label' => 'Ativas',
                'value' => 'active',
            ],
            [
                'label' => 'Inativas',
                'value' => 'inactive',
            ],
        ];
    }

    public function updatedQuantity(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.panel.campaign.index');
    }
}
