<x-guest-layout>
    <div class="space-y-6 text-primary-950">
        <div class="flex justify-center">
            <img
                src="{{ asset('assets/images/logo.webp') }}"
                alt="QuemDoa"
                class="h-auto w-full max-w-48"
            >
        </div>

        <div class="space-y-2 text-center">
            <h1 class="text-xl font-bold text-primary-900">
                URI de redirecionamento do Google
            </h1>

            <p class="text-sm leading-6 text-slate-600">
                Cadastre exatamente esta URL no cliente OAuth 2.0 do Google Cloud Console.
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="break-all font-mono text-sm font-semibold text-slate-800">
                {{ $redirectUri }}
            </p>
        </div>

        <a
            href="{{ route('login') }}"
            class="flex h-11 w-full items-center justify-center rounded-md bg-primary-900 px-4 text-sm font-bold text-white transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2"
        >
            Voltar para login
        </a>
    </div>
</x-guest-layout>
