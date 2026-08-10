<div class="p-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-500">
    @forelse($this->infos as $info)
        <x-inline-info :label="$info->title" :value="$info->content" />
    @empty
        <p class="py-6 text-center text-gray-500">Nenhuma informação adicionada<br>
            <span class="text-sm">Adicione mais informações sanar as dúvidas os participantes da campanha.</span>
        </p>
    @endforelse
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-500 text-right">
        <x-button text="Adicionar informação" outline sm />
    </div>
    <livewire:panel.campaign.infos.add-info :campaignId="$this->campaignId" />
</div>