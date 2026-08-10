<div class="grid lg:grid-cols-2 gap-4 md:gap-6">

    <div class="space-y-3">
        <h2 class="text font-semibold text-gray-700 dark:text-gray-300">Dados da campanha</h2>
        <div class="p-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-500">
            <x-inline-info label="Nome da campanha" :value="$name" />
            <x-inline-info label="Organização" :value="$institution ?? 'Não informada'" />
            <x-inline-info label="Grupo/pastoral" :value="$group ?? 'Não informado'" />
            <x-inline-info label="Descrição" :value="$description ?? 'Nenhuma descrição disponível'" />
        </div>
        <div class="p-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-500">
            <x-inline-info label="Prazo de confirmação" :value="$confirmationDeadline" />
            <x-inline-info label="Prazo de entrega" :value="$deliveryDeadline" />
            <x-inline-info label="Criada em" :value="$createdAt" />
            <x-inline-info label="Última atualização" :value="$updatedAt" />
            <x-inline-info label="Status" :value="$is_active ? 'Ativa' : 'Inativa'" :is_badge="true" :badge_color="$is_active ? 'green' : 'yellow'" />
        </div>
    </div>

    <div class="space-y-3">
        <h2 class="text font-semibold text-gray-700 dark:text-gray-300">Informações da campanha</h2>
        <livewire:panel.campaign.infos :campaignId="$this->campaignId" />
    </div>
</div>
