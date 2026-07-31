@php
    $campaign = request()->route('campaign');
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <section class="relative overflow-hidden rounded-xl border border-gray-200 bg-white px-6 py-8 text-center shadow-sm sm:px-10 lg:px-16 dark:border-gray-700 dark:bg-gray-900">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <span class="absolute left-[28%] top-20 h-2 w-2 rounded-full bg-amber-300"></span>
            <span class="absolute right-[30%] top-16 h-3 w-3 rounded-full bg-emerald-300"></span>
            <span class="absolute left-[20%] top-[43%] h-4 w-4 rotate-12 rounded-sm bg-primary-300/70"></span>
            <span class="absolute right-[17%] top-[42%] h-4 w-4 rounded-full bg-amber-300"></span>
            <span class="absolute left-[12%] top-[47%] h-3 w-3 rounded-full bg-primary-600"></span>
            <span class="absolute right-[13%] top-[52%] h-2 w-2 rounded-full bg-primary-500"></span>
            <x-icon name="heart" outline class="absolute left-[31%] top-16 h-7 w-7 rotate-12 text-primary-300" />
            <x-icon name="heart" class="absolute right-[34%] top-10 h-5 w-5 rotate-12 text-primary-700" />
            <x-icon name="heart" class="absolute left-[36%] top-24 h-4 w-4 -rotate-12 text-primary-600" />
            <x-icon name="heart" class="absolute right-[25%] top-20 h-5 w-5 -rotate-12 text-amber-300" />
            <x-icon name="heart" class="absolute right-[22%] top-[46%] h-5 w-5 rotate-12 text-primary-300" />
        </div>

        <div class="relative">
            <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-secondary-200 text-primary-700 shadow-[0_0_45px_rgba(20,184,166,0.15)]">
                <x-icon name="check" class="h-16 w-16 stroke-[3]" />
            </div>

            <div class="mx-auto mt-5 max-w-2xl">
                <h1 class="text-3xl font-bold tracking-normal text-gray-900 sm:text-4xl dark:text-gray-50">
                    Sacola cadastrada!
                </h1>
                <p class="mt-4 text-xl font-semibold text-primary-700 dark:text-primary-300">
                    Muito obrigado pela sua generosidade!
                </p>
                <p class="mt-4 text-lg leading-6 text-gray-600 dark:text-gray-300">
                    Sua sacola foi cadastrada e está aguardando confirmação.<br>
                    Avise o organizador e informe o código <strong class="text-gray-800">QL4827</strong>.
                </p>
                <p class="mt-4 text-center leading-6 text-gray-600 dark:text-gray-300">
                    Se preferir, clique no botão abaixo para copiar o texto pronto.
                </p>
                <x-clipboard :text="
'Olá! Cadastrei uma sacola na campanha Jantar da Comunidade. 
Nome: Maria Ferreira 
Código da sacola: QL-4827 
Pode confirmar para mim?'
                "/>
            </div>

            <div class="mx-auto mt-5 max-w-2xl">
                <h1 class="text-3xl font-bold tracking-normal text-gray-900 sm:text-4xl dark:text-gray-50">
                    Sacola confirmada!
                </h1>
                <p class="mt-4 text-xl font-semibold text-primary-700 dark:text-primary-300">
                    Muito obrigado pela sua generosidade!
                </p>
                <p class="mt-4 text-lg leading-6 text-gray-600 dark:text-gray-300">
                    Sua sacola foi confirmada com sucesso e fará<br class="hidden sm:block">
                    toda a diferença para quem mais precisa.
                </p>
            </div>

            <img
                src="{{ asset('assets/images/illustration-finish.png') }}"
                alt=""
                class="mx-auto mt-9 w-full max-w-xl object-contain"
            >

            <a
                href="{{ route('welcome') }}"
                wire:navigate
                class="mx-auto mt-8 flex w-full md:w-2/3 lg:w-1/2 items-center justify-center gap-4 rounded-lg bg-primary-700 px-6 py-3 text-xl font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            >
                <x-icon name="home" outline class="h-8 w-8 shrink-0" />
                <span>Voltar para a página inicial</span>
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-amber-200/70 bg-amber-50/60 px-6 py-7 shadow-sm sm:px-10 dark:border-amber-900/60 dark:bg-amber-950/20">
        <div class="grid items-center gap-6 md:grid-cols-[240px_1fr] lg:gap-10">
            <img
                src="{{ asset('assets/images/illustration-finish-cta.png') }}"
                alt=""
                class="mx-auto w-56 object-contain md:w-full"
            >

            <div class="text-center md:text-left">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    Que tal criar uma campanha?
                </h2>
                <p class="mt-3 max-w-2xl text-lg leading-6 text-gray-600 dark:text-gray-300">
                    Você pode criar sua própria campanha de doação gratuitamente e contar com a ajuda de outras pessoas incríveis como você!
                </p>
                <a
                    href="{{ route('register') }}"
                    class="mt-6 inline-flex w-full items-center justify-center gap-4 rounded-lg border border-primary-700 bg-white px-6 py-3 text-lg font-semibold text-primary-700 transition hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:w-auto dark:bg-gray-900 dark:text-primary-300 dark:hover:bg-gray-800 dark:focus:ring-offset-gray-900"
                >
                    <x-icon name="plus" class="h-7 w-7 shrink-0" />
                    <span>Criar minha campanha</span>
                </a>
            </div>
        </div>
    </section>
</div>
