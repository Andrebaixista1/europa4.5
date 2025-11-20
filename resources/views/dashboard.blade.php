<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Luminaris AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="/logo.png" alt="Logo" class="h-10 w-auto">
                <div>
                    <div class="text-xl font-bold text-slate-900">Luminaris<span class="text-yellow-500">AI</span></div>
                    <div class="text-xs text-slate-500">Painel de Controle</div>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center gap-4">
                
                <!-- Menu Dropdown Button -->
                <div class="relative">
                    <button id="menuButton" class="flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span class="text-sm font-medium">Menu</span>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="menuDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl border border-slate-200 shadow-xl opacity-0 scale-95 transition-all duration-200 ease-out">
                        <div class="p-2">
                            <a href="#" class="flex items-center gap-3 px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg font-medium border border-yellow-100 hover:bg-yellow-100 transition-colors mb-1">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors mb-1">
                                <i data-lucide="bot" class="w-5 h-5"></i>
                                <span>Automações (n8n)</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors mb-1">
                                <i data-lucide="database" class="w-5 h-5"></i>
                                <span>Banco de Dados</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors mb-1">
                                <i data-lucide="settings" class="w-5 h-5"></i>
                                <span>Configurações</span>
                            </a>
                            @if(Auth::user()->isAdmin())
                            <div class="border-t border-slate-200 my-2"></div>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors mb-1">
                                <i data-lucide="users" class="w-5 h-5"></i>
                                <span>Gerenciar Usuários</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                                <span>Gerenciar Empresas</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-900">{{ Auth::user()->nome }}</div>
                    <div class="text-xs text-slate-500">{{ Auth::user()->login }}</div>
                </div>
                <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center font-bold text-white shadow-lg">
                    {{ strtoupper(substr(Auth::user()->nome, 0, 1)) }}
                </div>
                <a href="/logout" class="ml-2 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm transition-colors">
                    Sair
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Olá, {{ Auth::user()->nome }}! 👋</h1>
            <p class="text-slate-600">
                @if(Auth::user()->isAdmin())
                    Você tem acesso total ao sistema. Gerencie todas as empresas e usuários.
                @else
                    Bem-vindo ao painel da {{ Auth::user()->empresa()->first()->nome ?? 'sua empresa' }}.
                @endif
            </p>
        </div>

    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle dropdown menu with smooth animation
        const menuButton = document.getElementById('menuButton');
        const menuDropdown = document.getElementById('menuDropdown');

        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            
            if (menuDropdown.classList.contains('hidden')) {
                // Show menu
                menuDropdown.classList.remove('hidden');
                // Trigger reflow to enable transition
                menuDropdown.offsetHeight;
                menuDropdown.classList.remove('opacity-0', 'scale-95');
                menuDropdown.classList.add('opacity-100', 'scale-100');
            } else {
                // Hide menu
                menuDropdown.classList.remove('opacity-100', 'scale-100');
                menuDropdown.classList.add('opacity-0', 'scale-95');
                // Wait for transition to complete before hiding
                setTimeout(() => {
                    menuDropdown.classList.add('hidden');
                }, 200);
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuButton.contains(e.target) && !menuDropdown.contains(e.target)) {
                if (!menuDropdown.classList.contains('hidden')) {
                    menuDropdown.classList.remove('opacity-100', 'scale-100');
                    menuDropdown.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        menuDropdown.classList.add('hidden');
                    }, 200);
                }
            }
        });
    </script>

</body>
</html>
