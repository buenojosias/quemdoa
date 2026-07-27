<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <tallstackui:script />
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">

        <div @class([
            'min-h-screen bg-gray-100',
            'flex flex-col items-center pt-6 sm:justify-center sm:pt-0' => ! $wide,
        ])>
            <div @class([
                'w-full overflow-hidden',
                'mt-6 px-6 py-4 bg-white shadow-md sm:max-w-md sm:rounded-lg' => ! $wide,
            ])>
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
