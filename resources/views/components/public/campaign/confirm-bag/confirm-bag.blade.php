<x-modal wire="modalConfirm" title="Confirmar sacola" size="sm" center>
    <form class="space-y-4">
        <x-input label="Seu nome *" />
        <div x-data="{ method: ''}">
            <x-label label="Como você deseja confirmar a sacola?" />
            <div class="grid grid-cols-2 gap-4">
                <x-radio label="WhatsApp" name="method"
                    name="method"
                    value="whatsapp"
                    x-model="method" id="method_whatsapp" />
                <x-radio label="Organizador" name="method"
                    name="method"
                    value="organizer"
                    x-model="method" id="method_organizer" />
            </div>
            <div class="mt-4" x-show="method === 'whatsapp'">
                <x-input label="Número de WhatsApp *" placeholder="(99) 99999-9999" x-mask="(99) 99999-9999" hint="Vamos enviar um código para o seu WhatsApp para garantir que dará tudo certo." />
            </div>
            <div class="mt-4 text-sm text-gray-600" x-show="method === 'organizer'">
                Após finalizar, informe o organizador da campanha para ele confirmar sua sacola.
            </div>
        </div>
    </form>
    <x-slot:footer>
        <x-button text="Confirmar" />
    </x-slot:footer>
</x-modal>