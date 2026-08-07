<x-app-layout>
    @php
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
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Olá, {{ strtok(auth()->user()->name, ' ') }}! Veja o resumo das suas campanhas.</p>
        </div>

        @if (
            auth()->user()->whatsapp_verified_at === null
                && session('dashboard_whatsapp_alert_dismissed') !== true
                && request()->cookie('dashboard_whatsapp_alert_dismissed') !== '1'
        )
            <livewire:panel.dashboard.whatsapp-alert />
        @endif

        {{-- @island() --}}
            <livewire:panel.dashboard.stats-bar />
        {{-- @endisland --}}

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.95fr)]">
            <livewire:panel.dashboard.active-campaigns />
            {{-- <div class="space-y-6">
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
            </div> --}}
        </div>
    </div>
</x-app-layout>
