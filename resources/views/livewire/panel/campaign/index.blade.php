<div>
    <div class="header">
        <h1>Minhas campanhas</h1>
        <div>
            <livewire:panel.campaign.create />
        </div>
    </div>

    @if ($status == '' && $this->campaigns->isEmpty())
        <div class="mb-4">
            <x-alert color="secondary" light icon="heart" title="Você ainda não tem campanhas">
                Que tal criar sua primeira campanha? Clique no botão acima para começar!
            </x-alert>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-select.native label="Status"
                    wire:model.live="status"
                    :options="$this->statusOptions()"
                    select="label:label|value:value" />
            </div>

            <x-table :$headers :rows="$this->campaigns" loading>
                @interact('column_name', $row)
                    <a href="{{ route('panel.campaigns.show', $row) }}" class="font-medium text-gray-700 dark:text-gray-100">
                        {{ $row->name }}
                        @if ($row->group)
                            <span class="w-full flex text-sm font-normal text-gray-500 dark:text-gray-400">
                                {{ $row->group }}
                            </span>
                        @elseif ($row->institution)
                            <span class="w-full flex text-sm font-normal text-gray-500 dark:text-gray-400">
                                {{ $row->institution }}
                            </span>
                        @endif
                    </a>
                @endinteract

                @interact('column_confirmation_deadline', $row)
                    {{ $row->confirmation_deadline->format('d/m/Y') }}
                @endinteract

                @interact('column_delivery_deadline', $row)
                    {{ $row->delivery_deadline->format('d/m/Y') }}
                @endinteract

                @interact('column_bags_count', $row)
                    <a href="{{ route('panel.campaigns.show', ['campaign' => $row, 'tab' => 'bags']) }}" class="flex items-center gap-1 font-medium text-gray-700 dark:text-gray-100">
                        <x-icon name="shopping-bag" class="w-4 h-4" outline />
                        {{ $row->bags_count }}
                    </a>
                @endinteract

                @interact('column_is_active', $row)
                    <x-badge
                        :text="$row->is_active ? 'Ativa' : 'Inativa'"
                        :color="$row->is_active ? 'green' : 'neutral'"
                        light />
                @endinteract
            </x-table>
            <div>
                {{ $this->campaigns->links() }}
            </div>
        </div>
    @endif
</div>
