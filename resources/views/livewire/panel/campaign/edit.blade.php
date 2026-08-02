<div>
    <x-modal title="Editar campanha" wire size="md" x-on:open="setTimeout(() => $refs.name.focus(), 250)">
        <form id="campaign-edit-{{ $campaignId }}" wire:submit="save" class="space-y-4">
            <x-input label="Nome da campanha *" x-ref="name" wire:model="name" required />

            <x-input label="Instituição" placeholder="Nome da igreja, associação, etc." hint="Opcional" wire:model="institution" />

            <x-input label="Grupo" placeholder="Nome do grupo ou pastoral" hint="Opcional" wire:model="group" />

            <x-textarea label="Descrição" hint="Opcional" wire:model="description" />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-date label="Prazo de confirmação *"
                        wire:model="confirmation_deadline"
                        name="confirmation_deadline"
                        format="DD/MM/YYYY"
                        :min-date="now()->addDay()"
                        required />

                <x-date label="Prazo de entrega *"
                        wire:model="delivery_deadline"
                        name="delivery_deadline"
                        format="DD/MM/YYYY"
                        :min-date="now()->addDay()"
                        required />
            </div>

            <x-toggle label="Campanha ativa" wire:model="is_active" />
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-button text="Cancelar" color="gray" wire:click="$set('modal', false)" />
                <x-button type="submit" form="campaign-edit-{{ $campaignId }}" text="Salvar alterações" loading="save" />
            </div>
        </x-slot:footer>
    </x-modal>
</div>
