<div class="grid lg:grid-cols-2 gap-4 md:gap-6">

    <div class="space-y-3">
        <h2 class="text font-semibold text-gray-700 dark:text-gray-300">Dados da capanha</h2>
        <div class="p-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-500">
            <x-inline-info label="Nome da campanha" :value="$campaign->name" />
            <x-inline-info label="Organização" :value="$campaign->organization ?? 'Não informada'" />
            <x-inline-info label="Grupo/pastoral" :value="$campaign->groupo ?? 'Não informado'" />
            <x-inline-info label="Descrição" :value="$campaign->description ?? 'Nenhuma descrição disponível'" />
        </div>
    </div>

    <div class="space-y-3">
        <h2 class="text font-semibold text-gray-700 dark:text-gray-300">Detalhes da capanha</h2>
        <div class="p-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-500">
            <x-inline-info label="Prazo de confirmação" :value="$campaign->confirmation_deadline->format('d/m/Y')" />
            <x-inline-info label="Prazo de entrega" :value="$campaign->delivery_deadline->format('d/m/Y')" />
            <x-inline-info label="Criada em" :value="$campaign->created_at->format('d/m/Y H:i')" />
            <x-inline-info label="Última atualização" :value="$campaign->updated_at->format('d/m/Y H:i')" />
        </div>
    </div>
</div>