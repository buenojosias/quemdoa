<div>
    <x-card class="flex flex-col sm:flex-row justify-between items-center gap-6">
        <div class="w-full sm:w-3/5 space-y-3">
            <div class="flex gap-2">
                <div>
                    <x-icon name="shopping-bag" class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Sacola</p>
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ $this->bag->code }}</p>
                </div>
                <div>
                    <x-badge
                        :text="$this->bag->confirmed_at ? 'Confirmada' : 'Pendente'"
                        :color="$this->bag->confirmed_at ? 'green' : 'yellow'"
                        light />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex gap-2">
                    <div>
                        <x-icon name="user" class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Participante</p>
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ $this->bag->participant_name }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div>
                        <x-icon name="phone" class="w-4 h-4" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">WhatsApp</p>
                        <p class="text-sm text-gray-700 dark:text-gray-200">{{ $this->bag->participant_whatsapp ?? 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <div>
                    <x-icon name="megaphone" class="w-4 h-4" />
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Campanha</p>
                    <p class="text-sm underline decoration-dotted text-gray-700 dark:text-gray-200">
                        <a href="{{ route('panel.campaigns.show', $this->bag->campaign) }}">{{ $this->bag->campaign->name }}</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="w-full sm:w-2/5 bg-gray-100 dark:bg-gray-600 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
            <h4 class="text-gray-600 dark:text-gray-300 text-sm font-semibold">Curadoria</h4>
            <div class="space-y-1 text-sm text-gray-500 dark:text-gray-400">
                <p><span class="font-semibold text-gray-600 dark:text-gray-300">Criada em:</span> {{ $this->bag->created_at->translatedFormat('d M. Y H:i') }}</p>
                <p><span class="font-semibold text-gray-600 dark:text-gray-300">Criada por:</span> {{ $this->bag->user_id && $this->bag->user_id === auth()->id() ? 'Mim' : 'Participante' }}</p>
                @if ($this->bag->confirmed_at)
                    <p><span class="font-semibold text-gray-600 dark:text-gray-300">Confirmada em:</span> {{ $this->bag->confirmed_at->translatedFormat('d M. Y H:i') }}</p>
                    <p><span class="font-semibold text-gray-600 dark:text-gray-300">Confirmada por:</span> {{ $this->bag->confirmed_by === 'organizer' ? 'Mim' : 'Participante' }}</p>
                @endif
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-between gap-4">
                <x-link
                    text="Voltar"
                    href="{{ url()->previous() }}"
                    sm />
                <x-dropdown text="Mais ações" position="bottom-end">
                    <x-dropdown.items text="Confirmar sacola" />
                    <x-dropdown.items text="Excluir sacola" separator />
                </x-dropdown>
            </div>
        </x-slot>
    </x-card>

    <div class="flex justify-between items-center my-6 gap-4">
        <h2 class="text-xl font-semibold dark:text-gray-300">Itens da sacola</h2>
        <livewire:panel.bag.add-item :bag="$this->bag" :key="'bag-add-item-'.$this->bag->id" />
    </div>

    @island('bag-items')
        <livewire:panel.tables.bag-items :bag="$this->bag" :key="'bag-items-'.$this->bag->id" />
    @endisland
</div>
