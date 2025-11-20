<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Luminaris AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-slate-200 hidden md:flex flex-col">
        <div class="p-6 border-b border-slate-100 flex items-center gap-2">
            <img src="/logo.png" alt="Logo" class="h-8 w-auto">
            <div class="text-xl font-bold">Luminaris<span class="text-yellow-500">AI</span></div>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="block px-4 py-3 bg-yellow-50 text-yellow-700 rounded-lg font-medium border border-yellow-100">Dashboard</a>
            <a href="#" class="block px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Automações (n8n)</a>
            <a href="#" class="block px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Banco de Dados</a>
            <a href="#" class="block px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">Configurações</a>
        </nav>
        <div class="p-4 border-t border-slate-100">
            <a href="/logout" class="block px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg text-sm transition-colors">Sair do Sistema</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800">Visão Geral</h1>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-900">Administrador</div>
                    <div class="text-xs text-slate-500">admin@luminaris.ai</div>
                </div>
                <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-slate-900 shadow-md">A</div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-slate-500 text-sm mb-2">Automações Ativas</div>
                <div class="text-3xl font-bold text-emerald-600">24</div>
            </div>
            <div class="p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-slate-500 text-sm mb-2">Requisições API (Hoje)</div>
                <div class="text-3xl font-bold text-blue-600">14.2k</div>
            </div>
            <div class="p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="text-slate-500 text-sm mb-2">Custo Estimado</div>
                <div class="text-3xl font-bold text-purple-600">R$ 124,00</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-xl font-bold mb-4 text-slate-800">Atividade Recente</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <div>
                            <div class="font-medium text-slate-900">Sync CRM -> SQL Server</div>
                            <div class="text-xs text-slate-500">Executado com sucesso</div>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">Há 2 min</div>
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <div>
                            <div class="font-medium text-slate-900">Geração de Relatório GPT-4</div>
                            <div class="text-xs text-slate-500">Executado com sucesso</div>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">Há 15 min</div>
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                        <div>
                            <div class="font-medium text-slate-900">Backup Noturno</div>
                            <div class="text-xs text-slate-500">Falha na conexão</div>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500">Há 4h</div>
                </div>
            </div>
        </div>

    </main>

</body>
</html>
