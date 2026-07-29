<div>
    <x-modal wire="modalConfirm" title="Confirmar sacola" size="sm" center>
        <form id="public-confirm-bag-form" wire:submit="submit" class="space-y-4">
            <div class="rounded-lg border border-primary-100 bg-primary-50 p-3 text-sm text-primary-800 dark:border-primary-800 dark:bg-primary-950 dark:text-primary-200">
                Você está confirmando {{ $this->totalItems }} {{ $this->totalItems === 1 ? 'item' : 'itens' }} na sacola.
            </div>

            <x-input
                label="Seu nome *"
                wire:model.live.blur="participant_name"
                required />

            <div class="space-y-3">
                <x-label label="Como você deseja confirmar a sacola?" />

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-radio
                        label="WhatsApp"
                        name="method"
                        value="whatsapp"
                        wire:model.live="method"
                        id="method_whatsapp" />
                    <x-radio
                        label="Organizador"
                        name="method"
                        value="organizer"
                        wire:model.live="method"
                        id="method_organizer" />
                </div>

                @if ($method === 'whatsapp')
                    <x-input
                        label="Número de WhatsApp *"
                        wire:model.live.blur="participant_whatsapp"
                        placeholder="(99) 99999-9999"
                        x-mask="(99) 99999-9999"
                        hint="Vamos enviar um código para o seu WhatsApp para garantir que dará tudo certo."
                        required />
                @else
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Após finalizar, informe o organizador da campanha para ele confirmar sua sacola.
                    </div>
                @endif
            </div>
        </form>

        <x-slot:footer>
            <x-button text="Cancelar" color="gray" wire:click="$set('modalConfirm', false)" />
            <x-button type="submit" form="public-confirm-bag-form" text="Confirmar" loading="submit" />
        </x-slot:footer>
    </x-modal>

    <x-modal wire="pinModal" title="Código de confirmação" size="sm" center>
        <form id="public-confirm-bag-pin-form" wire:submit="confirmPin" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Informe o código de 6 dígitos enviado para o WhatsApp.
            </p>

            <x-pin
                label="Código"
                wire:model.live="pin"
                :length="6"
                smart
                numbers />
        </form>

        <x-slot:footer>
            <x-button type="submit" form="public-confirm-bag-pin-form" text="Validar código" loading="confirmPin" />
        </x-slot:footer>
    </x-modal>
</div>
