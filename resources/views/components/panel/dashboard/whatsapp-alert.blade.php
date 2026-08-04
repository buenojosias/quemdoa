<?php

use Livewire\Component;

new class extends Component
{
    /* INSTRUÇÕES
    - Este componente só será exibido no componente pai se o usuário não tiver um WhatsApp confirmado (whatsapp_verified_at) e se não tiver armazenado em seção ou cookie a decisão fechar.
    - O botão "x-mark" deve fechar o alerta e armazenar em seção ou cookie esta decisão durante 5 dias ou se o usuário fizer login novamente.
    - O botão "Adicionar WhatsApp" deve abrir um modal para o usuário adicionar seu número de WhatsApp.
    - Se o campo whatsapp estiver preenchido, deve exibir um botão para reenviar o código de confirmação e outro para o usuário alterar o número.
    - Se o campo whatsapp estiver vazio, deve exibir um campo x-input com x-mask para o usuário informar seu número de WhatsApp e um botão para enviar o código de confirmação.
    - Após o envio do código, deve ser exibido um campo x-pin com atributo smart.
    - Após confirmar o código, deve disparar um evento para o componente pai remover este componente e também excluir a decisão armazenada.
    - Ao fazer login, também deve ser removida a decisão de remover, caso esteja armazenada em session ou cookie.
    */
};
?>

<div class="p-4 border rounded-lg border-amber-400 bg-amber-200/40 shadow-sm dark:border-amber-500/60 dark:bg-amber-600/40">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-200/50 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
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
            <x-button text="Adicionar WhatsApp" color="amber" outline />
            <x-button icon="x-mark" color="dark" flat />
        </div>
    </div>
</div>