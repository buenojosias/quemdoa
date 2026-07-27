<div>
    <x-button text="Criar campanha" wire:click="$toggle('modal')" />

    <x-modal title="Criar campanha" wire size="md" x-on:open="setTimeout(() => $refs.name.focus(), 250)">
        <form id="campaign-create" wire:submit="save" class="space-y-4">
            <x-input label="Nome da campanha *" x-ref="name" wire:model="name" required />

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
        </form>

        <x-slot:footer>
            <x-button type="submit" form="campaign-create" text="Salvar e continuar" />
        </x-slot:footer>
    </x-modal>
</div>
