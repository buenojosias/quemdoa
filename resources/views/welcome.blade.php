<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QuemLeva</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-white selection:bg-teal-100 selection:text-teal-900">

    <!-- Header -->
    <header class="w-full px-6 md:px-14 py-6 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img src="{{ asset('assets/images/logomarca.png') }}" alt="QuemLeva" class="h-10 sm:h-12">
        </div>
        <nav class="hidden md:flex items-center gap-8">
            <a href="#" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">Recursos</a>
            <a href="#" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">Como funciona</a>
        </nav>
        <div class="flex items-center gap-6">
            <a href="{{ route('login') }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700 transition hidden sm:block">Entrar</a>
            <a href="{{ route('register') }}" class="bg-teal-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-teal-700 transition shadow-sm">Criar campanha</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="w-full my-6 flex flex-col sm:flex-row sm:items-center gap-6 overflow-hidden">
        <div class="flex-1 pl-6 pr-6 md:pl-14 space-y-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold uppercase tracking-wider">
                Organize doações com facilidade
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-[1.1] tracking-tight">
                Menos bagunça no grupo, <span class="text-teal-600">mais união</span> na missão.
            </h1>
            <p class="text-lg md:text-xl text-slate-600 max-w-lg leading-relaxed">
                O QuemLeva é a plataforma que ajuda igrejas e pastorais a organizarem doações para eventos e campanhas de forma simples e transparente.
            </p>
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-2">
                <a href="{{ route('panel.dashboard') }}" class="w-full sm:w-auto bg-teal-600 text-white px-8 py-4 rounded-full font-semibold hover:bg-teal-700 transition flex items-center justify-center gap-2 shadow-lg shadow-teal-600/20">
                    Criar minha campanha
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="#" class="w-full sm:w-auto px-8 py-4 rounded-full font-semibold text-teal-700 hover:bg-teal-50 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Ver como funciona
                </a>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Totalmente gratuito.
            </div>
        </div>
        
        <div class="w-full sm:w-4/12 pl-8 sm:pl-0 flex justify-end">
            <img src="{{ asset('assets/images/lp-preview.png') }}" alt="Preview da plataforma QuemLeva" class="">
        </div>
    </section>

    <!-- Social Proof Banner -->
    <section class="bg-slate-50 py-10 border-y border-slate-100">
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-center gap-5">
            <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-teal-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="text-center md:text-left">
                <h3 class="text-base font-bold text-slate-800">Mais organização. Mais transparência. Mais comunhão.</h3>
                <p class="text-sm text-slate-500 font-medium">Centenas de grupos já estão usando o QuemLeva para fazer acontecer.</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="w-full px-6 md:px-14 py-18 text-center">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-16 tracking-tight">Tudo que você precisa para organizar doações</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-100 transition duration-300">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <h4 class="font-bold text-slate-900 mb-2 text-sm">Crie sua campanha</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">Liste os itens necessários e as quantidades de forma rápida e simples.</p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-100 transition duration-300">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </div>
                <h4 class="font-bold text-slate-900 mb-2 text-sm">Compartilhe o link</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">Envie no grupo do WhatsApp e todos verão o que falta em tempo real.</p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-100 transition duration-300">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                </div>
                <h4 class="font-bold text-slate-900 mb-2 text-sm">Doação com segurança</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">A pessoa escolhe o que vai doar e confirma via código no WhatsApp.</p>
            </div>
            <!-- Feature 4 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-100 transition duration-300">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h4 class="font-bold text-slate-900 mb-2 text-sm">Acompanhe tudo</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">Veja quem vai doar, o que falta e o que já foi entregue.</p>
            </div>
            <!-- Feature 5 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-teal-100 transition duration-300">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="font-bold text-slate-900 mb-2 text-sm">Marque como entregue</h4>
                <p class="text-xs text-slate-500 leading-relaxed font-medium">Quando o item chegar, marque como recebido e mantenha tudo atualizado.</p>
            </div>
        </div>
    </section>

    <!-- How it works (Timeline) -->
    <section class="w-full px-6 md:px-14 py-8 bg-white relative">
        <h2 class="text-3xl font-extrabold text-slate-900 text-center mb-16 tracking-tight">Como funciona</h2>
        
        <div class="flex flex-col md:flex-row items-start justify-between relative max-w-6xl mx-auto">
            <!-- Connecting Line (Desktop) -->
            <div class="hidden md:block absolute top-4 left-[10%] right-[10%] h-[2px] border-t-2 border-dashed border-slate-200"></div>
            
            <!-- Step 1 -->
            <div class="relative z-10 flex flex-col items-center text-center w-full lg:w-1/5 mb-12 lg:mb-0">
                <div class="w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mb-4 shadow-md shadow-teal-200 text-sm">1</div>
                <div class="w-16 h-16 bg-white border border-slate-100 rounded-2xl flex items-center justify-center shadow-sm mb-5 text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <h4 class="font-bold text-teal-700 text-sm mb-2">Crie a campanha</h4>
                <p class="text-xs text-slate-500 px-2 lg:px-4 leading-relaxed font-medium">Informe os detalhes do evento e cadastre os itens que você precisa.</p>
            </div>
            
            <!-- Step 2 -->
             <div class="relative z-10 flex flex-col items-center text-center w-full lg:w-1/5 mb-12 lg:mb-0">
                <div class="w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mb-4 shadow-md shadow-teal-200 text-sm">2</div>
                <div class="w-16 h-16 bg-white border border-slate-100 rounded-2xl flex items-center justify-center shadow-sm mb-5 text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                </div>
                <h4 class="font-bold text-teal-700 text-sm mb-2">Compartilhe o link</h4>
                <p class="text-xs text-slate-500 px-2 lg:px-4 leading-relaxed font-medium">Envie o link da campanha no grupo do WhatsApp ou em outros canais.</p>
            </div>

            <!-- Step 3 -->
             <div class="relative z-10 flex flex-col items-center text-center w-full lg:w-1/5 mb-12 lg:mb-0">
                <div class="w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mb-4 shadow-md shadow-teal-200 text-sm">3</div>
                <div class="w-16 h-16 bg-white border border-slate-100 rounded-2xl flex items-center justify-center shadow-sm mb-5 text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h4 class="font-bold text-teal-700 text-sm mb-2">Pessoas escolhem o que vão doar</h4>
                <p class="text-xs text-slate-500 px-2 lg:px-4 leading-relaxed font-medium">Elas selecionam os itens e confirmam com um código via WhatsApp.</p>
            </div>

            <!-- Step 4 -->
            <div class="relative z-10 flex flex-col items-center text-center w-full lg:w-1/5 mb-12 lg:mb-0">
                <div class="w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mb-4 shadow-md shadow-teal-200 text-sm">4</div>
                <div class="w-16 h-16 bg-white border border-slate-100 rounded-2xl flex items-center justify-center shadow-sm mb-5 text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h4 class="font-bold text-teal-700 text-sm mb-2">Você recebe as doações</h4>
                <p class="text-xs text-slate-500 px-2 lg:px-4 leading-relaxed font-medium">Acompanhe tudo em tempo real e saiba exatamente o que falta.</p>
            </div>

            <!-- Step 5 -->
            <div class="relative z-10 flex flex-col items-center text-center w-full lg:w-1/5">
                <div class="w-8 h-8 bg-teal-600 text-white rounded-full flex items-center justify-center font-bold mb-4 shadow-md shadow-teal-200 text-sm">5</div>
                <div class="w-16 h-16 bg-white border border-slate-100 rounded-2xl flex items-center justify-center shadow-sm mb-5 text-teal-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h4 class="font-bold text-teal-700 text-sm mb-2">Marque como entregue</h4>
                <p class="text-xs text-slate-500 px-2 lg:px-4 leading-relaxed font-medium">Quando receber, marque e mantenha tudo organizado até o dia do evento.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="w-full px-6 md:px-14 py-12 text-center">
        <div class="max-w-4xl mx-auto flex flex-col items-center relative">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold mb-8 uppercase tracking-wider">
                Chega de planilha e perguntas no grupo!
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-6 relative tracking-tight">
                Crie sua campanha agora e <span class="text-teal-600">facilite sua organização.</span>
                <!-- Accents -->
                <svg class="absolute -right-8 md:-right-16 -top-4 md:top-0 w-10 h-10 text-yellow-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </h2>
            <p class="text-lg text-slate-600 mb-10 max-w-xl font-medium">
                É rápido, gratuito e vai transformar a forma como sua comunidade se organiza para fazer o bem.
            </p>
            <div class="flex flex-col items-center gap-5">
                <a href="{{ route('panel.dashboard') }}" class="bg-teal-600 text-white px-10 py-4 rounded-full font-bold hover:bg-teal-700 transition flex items-center gap-2 shadow-xl shadow-teal-600/20 text-lg">
                    Criar minha campanha
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Ambiente seguro e confiável
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-50 pt-16 pb-4 border-t border-slate-200">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-8 mb-12">
            <!-- Column 1: Logo & Info -->
            <div class="md:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-1 mb-4">
                    <img src="{{ asset('assets/images/logomarca.png') }}" alt="QuemLeva" class="h-10 md:h-12">
                </div>
                <p class="text-sm text-slate-500 mb-6 font-medium max-w-xs leading-relaxed">A plataforma que conecta pessoas para fazer o bem, com organização e transparência.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <a href="#" class="hover:text-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
                    </a>
                    <a href="#" class="hover:text-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path></svg>
                    </a>
                    <a href="#" class="hover:text-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path></svg>
                    </a>
                </div>
            </div>
            
            <!-- Column 2: Links -->
            <div class="lg:col-span-1">
                <h4 class="font-bold text-slate-900 mb-5 text-sm uppercase tracking-wider">Navegue</h4>
                <ul class="space-y-3.5 text-sm text-slate-500 font-medium">
                    <li><a href="#" class="hover:text-teal-600 transition">Recursos</a></li>
                    <li><a href="#" class="hover:text-teal-600 transition">Como funciona</a></li>
                    {{-- <li><a href="#" class="hover:text-teal-600 transition">Dúvidas</a></li> --}}
                    <li><a href="#" class="hover:text-teal-600 transition">Contato</a></li>
                </ul>
            </div>
            
            <!-- Column 3: Links -->
            <div class="lg:col-span-1">
                <h4 class="font-bold text-slate-900 mb-5 text-sm uppercase tracking-wider">Para organizadores</h4>
                <ul class="space-y-3.5 text-sm text-slate-500 font-medium">
                    <li><a href="{{ route('panel.campaigns.index') }}" class="hover:text-teal-600 transition">Criar campanha</a></li>
                    {{-- <li><a href="#" class="hover:text-teal-600 transition">Gerenciar campanhas</a></li> --}}
                    {{-- <li><a href="#" class="hover:text-teal-600 transition">Modelos de campanhas</a></li> --}}
                    {{-- <li><a href="#" class="hover:text-teal-600 transition">Dicas e boas práticas</a></li> --}}
                </ul>
            </div>
            
            <!-- Column 4: Newsletter -->
            {{-- <div class="lg:col-span-1">
                <h4 class="font-bold text-slate-900 mb-5 text-sm uppercase tracking-wider">Receba dicas e novidades</h4>
                <form class="flex gap-2">
                    <input type="email" placeholder="Insira seu melhor e-mail" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-teal-600 focus:ring-1 focus:ring-teal-600 font-medium text-slate-900 placeholder:text-slate-400">
                    <button type="submit" class="bg-teal-600 text-white rounded-lg px-4 py-2.5 hover:bg-teal-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
                <p class="text-xs text-slate-400 mt-4 leading-relaxed font-medium">Ao se cadastrar, você concorda com nossos Termos de Uso e Política de Privacidade.</p>
            </div> --}}
        </div>
        
        <div class="container mx-auto px-6 pt-4 border-t border-slate-200 text-center text-xs text-slate-400 font-medium">
            © {{ date('Y') }} QuemLeva. Todos os direitos reservados.
        </div>
    </footer>

</body>
</html>
