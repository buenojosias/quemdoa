<div class="space-y-6">
    <x-campaign.header :campaign="$campaign" route="public" />
    <div class="flex flex-col md:flex-row justify-between md:items-center p-6 bg-primary-200/10 dark:bg-primary-300/50 shadow-sm rounded-lg gap-2">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-primary-400 rounded-full">
                <x-icon name="shopping-bag" class="h-6 w-6 text-white" />
            </div>
            <div>
                <span class="font-medium text-primary-700 dark:text-primary-900">Sua doação faz a diferença!</span>
                <p class="text-sm text-medium text-gray-800 dark:text-gray-200">
                    Veja os itens abaixo e escolha o que deseja doar. <br class="inline lg:hidden">
                    Juntos faremos um evento incrível!
                </p>
            </div>
        </div>
        <div class="hidden md:flex gap-2 pl-16 md:pl-0 items-center text-sm text-gray-700 dark:text-gray-900">
            <x-icon name="heart" outline class="h-5 w-5 text-primary-600 dark:text-primary-900" />
            Gratidão pela sua generosidade!
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($this->itemsByCategory as $category)
            <div wire:key="public-campaign-category-{{ md5($category['name']) }}">
                <x-accordion>
                    <x-accordion.items
                        :id="'public-campaign-category-'.md5($category['name'])"
                        >
                        <x-slot:trigger>
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary-200">
                                    <img
                                        src="{{ asset('assets/images/category-illustrations/'.$category['illustration']) }}"
                                        alt=""
                                        class="h-9 w-9 object-contain">
                                </span>
                                <span class="flex items-center gap-3 min-w-0 text-left">
                                    <span class="block truncate text-lg font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $category['name'] }}
                                    </span>
                                    <x-badge color="secondary" xs light>{{ count($category['items']) }} {{ count($category['items']) === 1 ? 'item' : 'itens' }}</x-badge>
                                </span>
                            </div>
                        </x-slot:trigger>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($category['items'] as $item)
                                <div
                                    wire:key="public-campaign-item-{{ $item['id'] }}"
                                    x-data="{ expanded: false }"
                                    class="py-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <button
                                            type="button"
                                            x-on:click="expanded = ! expanded"
                                            class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left cursor-pointer">
                                            <span class="min-w-0">
                                                <span class="block truncate font-semibold text-gray-800 dark:text-gray-100">
                                                    {{ $item['name'] }}
                                                    @if ($item['complement'])
                                                        <span class="font-normal text-gray-600 dark:text-gray-300">({{ $item['complement'] }})</span>
                                                    @endif
                                                </span>
                                                <span class="mt-0.5 block text-sm font-medium text-orange-500">
                                                    {{ $item['pending_quantity_label'] }}
                                                </span>
                                            </span>
                                            <x-icon
                                                name="chevron-down"
                                                outline
                                                x-bind:class="{ 'rotate-180': expanded }"
                                                class="h-4 w-4 shrink-0 text-gray-700 transition-transform dark:text-gray-200" />
                                        </button>

                                        @if (!$item['is_added'] && !$item['is_complete'])
                                            <x-button
                                                text="Vou doar"
                                                color="primary"
                                                outline
                                                sm
                                                wire:click="$dispatch('open-public-campaign-item-add.{{ $campaignId }}', { item: {{ $item['id'] }} })" />
                                        @elseif ($item['is_added'])
                                            <x-badge text="Na sacola" color="gray" outline md />
                                        @elseif ($item['is_complete'])
                                            <x-badge text="Completo" color="gray" outline md />
                                        @endif
                                    </div>

                                    <div x-show="expanded" x-collapse x-cloak class="pt-4">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Necessário</p>
                                                    <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100">
                                                        {{ $item['required_quantity'] }} {{ $item['unit_abbreviation'] }}.
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500 dark:text-gray-400">Já prometido</p>
                                                    <p class="mt-1 text-lg font-semibold text-primary-700 dark:text-primary-300">
                                                        {{ $item['promised_quantity'] }} {{ $item['unit_abbreviation'] }}. ({{ $item['progress'] }}%)
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <x-progress :percent="$item['progress']" color="primary" sm />
                                            </div>

                                            @if ($item['delivery_date'])
                                                <div class="mt-4 flex gap-2 text-sm text-gray-700 dark:text-gray-200">
                                                    <x-icon name="calendar" outline class="mt-0.5 h-4 w-4 shrink-0 text-gray-500 dark:text-gray-400" />
                                                    <div>
                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            Entregar até
                                                            <span class="font-medium">{{ $item['delivery_date'] }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($item['note'])
                                                <div class="mt-4 text-sm text-gray-700 dark:text-gray-200">
                                                    <p class="font-medium text-gray-500 dark:text-gray-400">Observações:</p>
                                                    <p class="mt-1">{{ $item['note'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-accordion.items>
                </x-accordion>
            </div>
        @empty
            <x-card>
                <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhum item cadastrado para esta campanha.
                </div>
            </x-card>
        @endforelse
    </div>
    @slot('footer')
        <div class="w-full flex justify-center py-0.5">
            <x-button
                text="Ver sacola"
                icon="shopping-bag"      
                x-on:click="$dispatch('open-bag-slide')"
                {{-- wire:click="openBag" --}}
                class="w-full md:w-1/3" />
        </div>
    @endslot
    
    <livewire:public.campaign.item-add :campaign-id="$campaignId" />
    <livewire:public.campaign.bag
        :campaign-id="$campaignId"
        :bag-items="$bagItems"
        :slide="$bagSlide"
        :key="'public-campaign-bag-'.$campaignId.'-'.count($bagItems).'-'.(int) $bagSlide" />
    <livewire:public.campaign.confirm-bag :campaign-id="$campaignId" />
</div>
