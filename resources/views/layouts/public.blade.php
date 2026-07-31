<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="tallstackui_darkTheme()">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'QuemLeva') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <tallstackui:script />
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased"
        x-cloak
        {{-- x-data="{ name: @js(auth()->user()->name) }" --}}
        x-on:name-updated.window="name = $event.detail.name"
        x-bind:class="{ 'dark bg-gray-800': darkTheme, 'bg-gray-100': !darkTheme }">
        <div class="dark:bg-dark-700 dark:border-dark-600 sticky top-0 z-40 flex h-18 shrink-0 items-center gap-x-4 border-b border-gray-300/10 bg-white shadow-sm">
            <div class="container mx-auto flex justify-between px-6 lg:px-16">
                <div><img src="{{ asset('/assets/images/logomarca.png') }}" class="h-12" /></div>
            </div>
        </div>
        <div class="my-8 container mx-auto px-6 lg:px-16">
            {{ $slot }}
        </div>
        @if (@$footer)
            <div class="sticky bottom-0 z-40 py-2 bg-white shadow">
                <div class="container mx-auto px-6 lg:px-16">
                    {{ $footer }}
                </div>
            </div>
        @endif

        <x-toast />
        @livewireScripts
    </body>
</html>
