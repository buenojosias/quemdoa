<div class="space-y-6">
    <x-campaign.header :campaign="$campaign" route="public" />
    <div class="flex flex-col md:flex-row justify-between md:items-center p-6 bg-primary-200/10 shadow-sm rounded-lg gap-2">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-primary-400 rounded-full">
                <x-icon name="shopping-bag" class="h-6 w-6 text-white" />
            </div>
            <div>
                <span class="font-medium text-primary-700">Sua doação faz a diferença!</span>
                <p class="text-sm text-medium text-gray-800">
                    Veja os itens abaixo e escolha o que deseja levar. <br class="inline lg:hidden">
                    Juntos faremos um evento incrível!
                </p>
            </div>
        </div>
        <div class="flex gap-2 pl-16 md:pl-0 items-center text-sm text-gray-700">
            <x-icon name="heart" outline class="h-5 w-5 text-primary-600" />
            Gratidão pela sua generosidade!
        </div>
    </div>
</div>
