<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <script>
            (function () {
                const theme = localStorage.getItem('lumia-theme');
                const resolved = theme === 'dark' || theme === 'light'
                    ? theme
                    : 'dark';

                document.documentElement.classList.add(resolved === 'dark' ? 'theme-dark' : 'theme-light');
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $isEmbedded = request()->boolean('embedded');
        @endphp
        <div class="min-h-screen bg-gray-100 flex flex-col">
            @unless($isEmbedded)
                @include('layouts.navigation')
            @endunless

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            @unless($isEmbedded)
                <footer class="border-t border-gray-200 bg-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-center text-xs sm:text-sm text-gray-500">
                        &copy; 2025-2026 Nova Europa 4. Todos os direitos reservados. Criado e Desenvolvido por Andr&eacute; Felipe
                    </div>
                </footer>
            @endunless
        </div>
    </body>
</html>
