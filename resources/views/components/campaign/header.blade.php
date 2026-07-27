@props([
    'campaign',
    'route' => null,
    'campaignUrl' => route('campaigns.show', $campaign)
])

<x-card class="flex flex-col sm:flex-row justify-between items-center gap-4">
    <div class="w-full sm:w-3/5 space-y-4">
        <div>
            <h3 class="text-xs font-semibold">Campanha</h3>
            <p class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{ $this->campaign->name }}</p>
            <p class="text-sm text-gray-500 my-3">{{ $this->campaign->description }}</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="w-full lg:w-1/2 flex gap-3">
                <div class="pt-0.5">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
                <div class="text-gray-600 dark:text-gray-300 font-medium">
                    <p class="text-sm">Prazo para confirmação</p>
                    <p class="text-md">{{ $this->campaign->confirmation_deadline->translatedFormat('d \d\e F \d\e Y') }}</p>
                </div>
            </div>
            <div class="w-full lg:w-1/2 flex gap-3">
                <div class="pt-0.5">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
                <div class="text-gray-600 dark:text-gray-300 font-medium">
                    <p class="text-sm">Prazo para entrega</p>
                    <p class="text-md">{{ $this->campaign->delivery_deadline->translatedFormat('d \d\e F \d\e Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full sm:w-2/5 bg-gray-100 dark:bg-gray-600 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4">
        <h4 class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Compartilhe esta campanha</h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">Envie este link para o grupo de sua pastoral ou comunidade.</p>
        <x-clipboard :text="$campaignUrl" />
    </div>
    @if(in_array($route, ['show', 'bags']))
        <x-slot:footer>
            <div class="flex items-center justify-between gap-4">
                <x-link
                    :text="$route === 'show' ? 'Listar sacolas' : 'Listar itens'"
                    :href="route($route === 'show' ? 'campaigns.bags' : 'campaigns.show', $this->campaign)"
                    sm />
                <x-dropdown text="Mais ações" position="bottom-end">
                    <x-dropdown.items text="Editar campanha" />
                    <x-dropdown.items text="Desativar campanha" />
                    <x-dropdown.items text="Excluir campanha" separator />
                </x-dropdown>
            </div>
        </x-slot>
    @endif
</x-card>