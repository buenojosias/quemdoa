<x-app-layout>
    @php
        $stats = [
            [
                'title' => 'Campanhas ativas',
                'number' => '3',
                'icon' => 'flag',
                'classes' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-300',
                'link' => 'Ver todas',
            ],
            [
                'title' => 'Itens no total',
                'number' => '48',
                'icon' => 'gift',
                'classes' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
                'link' => 'Ver itens',
            ],
            [
                'title' => 'Doações prometidas',
                'number' => '124',
                'icon' => 'check-circle',
                'classes' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
                'link' => 'Ver doações',
            ],
            [
                'title' => 'Doações recebidas',
                'number' => '76',
                'icon' => 'cube',
                'classes' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300',
                'link' => 'Ver recebidas',
            ],
        ];

        $campaigns = [
            [
                'name' => 'Jantar da Padroeira 2025',
                'date' => 'Entrega até 20/06/2025',
                'progress' => 70,
                'status' => 'Ativa',
                'statusColor' => 'green',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=160&q=80',
            ],
            [
                'name' => 'Campanha do Agasalho',
                'date' => 'Entrega até 30/06/2025',
                'progress' => 45,
                'status' => 'Ativa',
                'statusColor' => 'green',
                'image' => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?auto=format&fit=crop&w=160&q=80',
            ],
            [
                'name' => 'Cesta Básica - Comunidade',
                'date' => 'Entrega até 10/05/2025',
                'progress' => 100,
                'status' => 'Finalizada',
                'statusColor' => 'blue',
                'image' => 'https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=160&q=80',
            ],
            [
                'name' => 'Páscoa Solidária',
                'date' => 'Entrega até 18/04/2025',
                'progress' => 80,
                'status' => 'Encerrada',
                'statusColor' => 'gray',
                'image' => 'https://images.unsplash.com/photo-1521967906867-14ec9d64bee8?auto=format&fit=crop&w=160&q=80',
            ],
        ];

        $deadlines = [
            ['day' => '20', 'month' => 'JUN', 'name' => 'Jantar da Padroeira 2025', 'time' => 'Entrega em 5 dias', 'status' => 'Em breve', 'color' => 'yellow'],
            ['day' => '30', 'month' => 'JUN', 'name' => 'Campanha do Agasalho', 'time' => 'Entrega em 15 dias', 'status' => 'Em breve', 'color' => 'yellow'],
            ['day' => '10', 'month' => 'MAI', 'name' => 'Cesta Básica - Comunidade', 'time' => 'Entrega em 25 dias', 'status' => 'No prazo', 'color' => 'green'],
        ];

        $activities = [
            ['icon' => 'home', 'text' => 'Maria Silva prometeu 2 un. de Refrigerante 2L na campanha Jantar da Padroeira 2025', 'time' => 'Há 5 minutos'],
            ['icon' => 'clipboard-document-check', 'text' => 'João Pereira entregou 5 kg de Arroz na campanha Cesta Básica - Comunidade', 'time' => 'Há 1 hora'],
            ['icon' => 'flag', 'text' => 'Ana Costa prometeu 3 un. de Guardanapo na campanha Jantar da Padroeira 2025', 'time' => 'Há 2 horas'],
        ];
    @endphp

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Olá, João! Veja o resumo das suas campanhas.</p>
        </div>

        <x-card class="border border-amber-300 bg-amber-50/70 shadow-sm dark:border-amber-600/60 dark:bg-amber-950/20">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                        <x-icon name="phone" class="h-7 w-7" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Adicione e confirme seu WhatsApp</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                            Assim você confirma doações com mais segurança e recebe avisos importantes sobre suas campanhas.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:shrink-0">
                    <x-button text="Adicionar WhatsApp" color="primary" outline />
                    <button type="button" class="rounded-md p-2 text-slate-500 transition hover:bg-white/70 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Fechar aviso">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <x-card class="shadow-sm">
                    <div class="flex items-center gap-5">
                        <div @class(['flex h-14 w-14 shrink-0 items-center justify-center rounded-full', $stat['classes']])>
                            <x-icon :name="$stat['icon']" class="h-7 w-7" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $stat['title'] }}</p>
                            <p class="mt-1 text-3xl font-bold text-slate-950 dark:text-white">{{ $stat['number'] }}</p>
                        </div>
                    </div>
                    <a href="#" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-teal-700 hover:text-teal-800 dark:text-teal-300 dark:hover:text-teal-200">
                        {{ $stat['link'] }}
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </x-card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.95fr)]">
            <x-card>
                <x-slot:header>
                    <h2 class="text-medium font-semibold text-slate-900 dark:text-white">Campanhas recentes</h2>
                    <x-button :href="route('campaigns.index')" text="Ver todas as campanhas" color="primary" flat sm />
                </x-slot:header>

                <div class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach ($campaigns as $campaign)
                        <div class="grid grid-cols-[72px_minmax(0,1fr)] gap-4 py-4 first:pt-0 last:pb-0 lg:grid-cols-[72px_minmax(0,1fr)_96px_28px] lg:items-center">
                            <img
                                src="{{ $campaign['image'] }}"
                                alt="{{ $campaign['name'] }}"
                                class="h-16 w-16 rounded-md object-cover"
                            >
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $campaign['name'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $campaign['date'] }}</p>
                                <div class="mt-3 items-center gap-3 w-full">
                                    <x-progress :percent="$campaign['progress']" color="primary" sm class="w-full" />
                                    {{-- <span class="w-10 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $campaign['progress'] }}%</span> --}}
                                </div>
                            </div>
                            <div class="col-start-2 lg:col-start-auto">
                                <x-badge :text="$campaign['status']" :color="$campaign['statusColor']" light round="md" />
                            </div>
                            <button type="button" class="col-start-2 justify-self-end rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 lg:col-start-auto" aria-label="Mais opções para {{ $campaign['name'] }}">
                                <x-icon name="ellipsis-vertical" class="h-5 w-5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <div class="space-y-6">
                <x-card>
                    <x-slot:header>
                        <h2 class="text-medium font-semibold text-slate-900 dark:text-white">Próximos vencimentos</h2>
                        <x-icon name="calendar-days" class="h-5 w-5 text-slate-500 dark:text-slate-300" />
                    </x-slot:header>

                    <div class="space-y-4">
                        @foreach ($deadlines as $deadline)
                            <div class="grid grid-cols-[52px_minmax(0,1fr)_auto] items-center gap-4">
                                <div class="rounded-md bg-teal-50 px-3 py-2 text-center dark:bg-teal-500/10">
                                    <p class="text-lg font-bold leading-5 text-teal-700 dark:text-teal-300">{{ $deadline['day'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-teal-700 dark:text-teal-300">{{ $deadline['month'] }}</p>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $deadline['name'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $deadline['time'] }}</p>
                                </div>
                                <x-badge :text="$deadline['status']" :color="$deadline['color']" light round="md" />
                            </div>
                        @endforeach
                    </div>
                </x-card>

                <x-card>
                    <x-slot:header>
                        <h2 class="text-medium font-semibold text-slate-900 dark:text-white">Atividade recente</h2>
                    </x-slot:header>

                    <div class="space-y-4">
                        @foreach ($activities as $activity)
                            <div class="grid grid-cols-[44px_minmax(0,1fr)_88px] items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">
                                    <x-icon :name="$activity['icon']" class="h-5 w-5" />
                                </div>
                                <p class="text-sm font-medium leading-5 text-slate-800 dark:text-slate-200">{{ $activity['text'] }}</p>
                                <p class="text-right text-sm text-slate-500 dark:text-slate-400">{{ $activity['time'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
