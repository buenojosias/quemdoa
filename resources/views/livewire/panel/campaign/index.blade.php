<div>
    <div class="header">
        <h1>Minhas campanhas</h1>
        <div>
            <livewire:panel.campaign.create />
        </div>
    </div>

    @if ($status == '' && $this->campaigns->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white px-6 py-10 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:px-8 lg:px-12 lg:py-14">
            <div class="mx-auto flex max-w-5xl flex-col items-center text-center">
                <img
                    src="{{ asset('assets/images/empty-illustration.webp') }}"
                    alt=""
                    class="h-auto w-full max-w-md"
                    aria-hidden="true">

                <h2 class="mt-8 text-2xl font-bold text-primary-900 dark:text-gray-100 sm:text-3xl">
                    Você ainda não criou nenhuma campanha
                </h2>
                <p class="mt-3 max-w-xl text-base leading-7 text-gray-600 dark:text-gray-300">
                    Crie sua primeira campanha e comece a organizar doações de forma simples, prática e transparente.
                </p>

                <div class="mt-9 grid w-full max-w-4xl grid-cols-1 gap-6 text-left lg:grid-cols-3">
                    <div class="flex items-start gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-600 dark:bg-secondary-900 dark:text-secondary-200">
                            <x-icon name="shopping-bag" class="h-6 w-6" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-primary-900 dark:text-gray-100">Organize doações</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">Monte sua lista de itens e receba doações de forma organizada.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-200">
                            <x-icon name="users" class="h-6 w-6" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-primary-900 dark:text-gray-100">Compartilhe facilmente</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">Envie o link da sua campanha e alcance mais doadores.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-200">
                            <x-icon name="chart-bar" class="h-6 w-6" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-primary-900 dark:text-gray-100">Acompanhe tudo</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">Veja o que já foi doado e o que ainda falta.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 w-full max-w-4xl border-t border-gray-200 pt-8 dark:border-gray-700">
                    <div class="flex flex-col items-center justify-center gap-4">
                        <x-button text="Criar minha primeira campanha" icon="plus" wire:click="$dispatchTo('panel.campaign.create', 'open-campaign-create')" class="w-full sm:w-auto" />

                        {{-- <a href="{{ route('welcome') }}#como-funciona" class="inline-flex items-center gap-2 text-sm font-semibold text-secondary-600 transition hover:text-secondary-700 dark:text-secondary-300 dark:hover:text-secondary-200">
                            <x-icon name="play-circle" class="h-5 w-5" />
                            Saiba como funciona
                        </a> --}}
                    </div>
                </div>
            </div>
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
