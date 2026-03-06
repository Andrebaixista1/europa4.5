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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col bg-gray-100 relative">
            <button type="button" data-theme-toggle onclick="window.toggleTheme()" class="theme-toggle-btn absolute top-4 right-4 inline-flex items-center justify-center w-10 h-10 border border-gray-300 rounded-md bg-white text-gray-700 hover:bg-gray-50 transition" aria-label="Ativar tema escuro" title="Tema escuro">
                <svg data-icon-sun class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2.5M12 19.5V22M4.93 4.93l1.77 1.77M17.3 17.3l1.77 1.77M2 12h2.5M19.5 12H22M4.93 19.07l1.77-1.77M17.3 6.7l1.77-1.77"></path>
                </svg>
                <svg data-icon-moon class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"></path>
                </svg>
            </button>

            <div class="flex-1 w-full flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
                <div class="flex flex-col items-center">
                    <a href="/" class="flex flex-col items-center">
                        <x-application-logo class="w-28 h-28" />
                        <span class="mt-3 text-lg font-semibold text-slate-800 dark:text-slate-100">Europa 4.5</span>
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>

            <footer class="w-full border-t border-gray-200 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-center text-xs sm:text-sm text-gray-500">
                    &copy; 2025-2026 Nova Europa 4. Todos os direitos reservados. Criado e Desenvolvido por Andr&eacute; Felipe
                </div>
            </footer>
        </div>
    </body>
</html>
