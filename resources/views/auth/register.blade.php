<x-guest-layout wide>
    <div class="min-h-screen bg-white text-primary-950">
        <div class="grid min-h-screen lg:grid-cols-2">
            <section class="flex items-center justify-center px-6 py-10 sm:px-10 lg:px-16 xl:px-24">
                <div class="w-full max-w-lg">
                    <div class="mb-10 flex justify-center">
                        <img
                            src="{{ asset('assets/images/logomarca.png') }}"
                            alt="QuemDoa"
                            class="h-auto w-full max-w-60"
                        >
                    </div>

                    <div class="mb-8 text-center">
                        <h1 class="text-2xl font-bold leading-tight text-primary-900 sm:text-2xl">
                            Crie sua conta no QuemDoa
                        </h1>

                        <p class="mt-2 leading-7 text-slate-600">
                            Organize campanhas, acompanhe doações e facilite a missão da sua comunidade.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-7">
                        @csrf

                        <div class="space-y-5">
                            <x-input
                                label="Nome"
                                icon="user"
                                name="name"
                                :value="old('name')"
                                placeholder="Seu nome"
                                required
                                autofocus
                                autocomplete="name"
                            />

                            <x-input
                                label="E-mail"
                                icon="envelope"
                                type="email"
                                name="email"
                                :value="old('email')"
                                placeholder="seu@email.com"
                                required
                                autocomplete="username"
                            />

                            <x-password
                                label="Senha"
                                name="password"
                                placeholder="Crie sua senha"
                                required
                                autocomplete="new-password"
                            />

                            <x-password
                                label="Confirmar senha"
                                name="password_confirmation"
                                placeholder="Confirme sua senha"
                                required
                                autocomplete="new-password"
                            />
                        </div>

                        <x-button type="submit" lg block>
                            <x-slot:left>
                                <x-icon name="user-plus" class="h-5 w-5" />
                            </x-slot:left>
                            Criar conta
                        </x-button>

                        <div class="flex items-center gap-5">
                            <span class="h-px flex-1 bg-slate-200"></span>
                            <span class="text-sm font-medium text-slate-500">ou continue com</span>
                            <span class="h-px flex-1 bg-slate-200"></span>
                        </div>

                        <a
                            href="{{ route('auth.google.redirect') }}"
                            class="flex h-14 w-full items-center justify-center gap-3 rounded-md border border-slate-200 bg-white px-5 text-base font-bold text-primary-950 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:ring-offset-2"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M21.8 12.2c0-.7-.1-1.3-.2-1.9H12v3.6h5.5c-.2 1.2-.9 2.2-2 2.9v2.4h3.1c1.8-1.7 2.8-4.1 2.8-7z" />
                                <path fill="#34A853" d="M12 22c2.7 0 4.9-.9 6.6-2.4l-3.1-2.4c-.9.6-2 .9-3.5.9-2.6 0-4.8-1.8-5.6-4.1H3.2v2.5C4.9 19.8 8.2 22 12 22z" />
                                <path fill="#FBBC05" d="M6.4 14c-.2-.6-.3-1.3-.3-2s.1-1.4.3-2V7.5H3.2C2.4 8.9 2 10.4 2 12s.4 3.1 1.2 4.5z" />
                                <path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.8-2.8C16.9 3 14.7 2 12 2 8.2 2 4.9 4.2 3.2 7.5L6.4 10c.8-2.3 3-4.1 5.6-4.1z" />
                            </svg>

                            Criar conta com Google
                        </a>

                        @error('google')
                            <p class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="text-center text-sm text-slate-600">
                            Já tem uma conta?

                            <a
                                href="{{ route('login') }}"
                                class="font-bold text-secondary-700 transition hover:text-primary-700"
                            >
                                Entrar
                            </a>
                        </p>
                    </form>

                </div>
            </section>

            <section class="hidden overflow-hidden bg-secondary-100/80 px-10 py-10 lg:flex lg:items-center lg:justify-center xl:px-20">
                <div class="relative w-full max-w-2xl">
                    <div class="absolute left-1/2 top-4 h-48 w-64 -translate-x-1/2 rounded-full bg-secondary-100"></div>

                    <div class="relative">
                        <div class="mb-10 flex justify-center">
                            <img
                                src="{{ asset('assets/images/illustration-register.png') }}"
                                alt="Cadastro de doações"
                                class="h-auto w-64"
                            >
                        </div>

                        <div class="mx-auto max-w-xl text-center">
                            <h2 class="text-3xl font-extrabold leading-tight text-primary-900 xl:text-3xl">
                                Comece com tudo no lugar.
                                <span class="mt-1 block text-secondary-700">Mais clareza. Mais colaboração.</span>
                            </h2>

                            <p class="mt-5 text-base leading-7 text-slate-600">
                                Cadastre sua conta para criar campanhas, listar necessidades e acompanhar cada doação com transparência.
                            </p>
                        </div>

                        <div class="mx-auto mt-12 grid max-w-xl gap-6">
                            <div class="flex items-center gap-5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-700">
                                    <x-icon name="clipboard-document-check" class="h-7 w-7" />
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-primary-950">Cadastro simples</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Crie seu acesso e comece a organizar campanhas em poucos passos.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-700">
                                    <x-icon name="users" class="h-7 w-7" />
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-primary-950">Gestão colaborativa</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Acompanhe participantes, itens e entregas em um só lugar.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-neutral-100 text-neutral-500">
                                    <x-icon name="heart" class="h-7 w-7" />
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-primary-950">Doações com propósito</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Mostre o que falta e ajude cada pessoa a contribuir melhor.</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-700">
                                    <x-icon name="shield-check" class="h-7 w-7" />
                                </div>

                                <div>
                                    <h3 class="text-lg font-bold text-primary-950">Confiança desde o início</h3>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Organize informações essenciais para manter todos alinhados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>
