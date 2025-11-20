<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luminaris AI - Automação Inteligente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gradient-text {
            background: linear-gradient(to right, #a855f7, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-white text-slate-900 overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <img src="/logo.png" alt="Luminaris AI Logo" class="h-10 w-auto">
                <span class="text-2xl font-bold tracking-tighter text-slate-900">Luminaris<span class="text-yellow-500">AI</span></span>
            </div>
            <a href="/login" class="px-6 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-full transition-all text-sm font-medium shadow-lg shadow-yellow-500/20">
                Área do Cliente
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-yellow-100 via-white to-white"></div>
        
        <div class="relative z-10 text-center px-6 max-w-4xl mx-auto">
            <div class="inline-block mb-4 px-4 py-1.5 rounded-full bg-yellow-100 border border-yellow-200 text-yellow-700 text-sm font-bold animate-pulse">
                ⚡ O Futuro da Automação
            </div>
            <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight text-slate-900">
                Escale seu negócio com <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-500 to-orange-500">Inteligência Artificial</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-600 mb-10 max-w-2xl mx-auto">
                Integramos n8n, GPT, Gemini e SQL Server para criar ecossistemas de automação que trabalham enquanto você dorme.
            </p>
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <a href="#contact" class="px-8 py-4 bg-yellow-400 hover:bg-yellow-500 text-slate-900 rounded-full font-bold shadow-xl shadow-yellow-400/30 transition-all transform hover:-translate-y-1">
                    Começar Agora
                </a>
                <a href="#features" class="px-8 py-4 bg-white border border-slate-200 text-slate-700 rounded-full font-bold hover:bg-slate-50 transition-all">
                    Ver Soluções
                </a>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-16 text-slate-900">Nossas Tecnologias</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 hover:shadow-xl hover:border-yellow-400 transition-all group">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">Automação com n8n</h3>
                    <p class="text-slate-600">Fluxos de trabalho complexos automatizados entre centenas de aplicativos sem esforço manual.</p>
                </div>
                <!-- Card 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 hover:shadow-xl hover:border-yellow-400 transition-all group">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">IA Generativa</h3>
                    <p class="text-slate-600">Implementação de GPT-4 e Gemini para análise de dados, atendimento e criação de conteúdo.</p>
                </div>
                <!-- Card 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-slate-100 hover:shadow-xl hover:border-yellow-400 transition-all group">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900">SQL Server</h3>
                    <p class="text-slate-600">Bancos de dados robustos e seguros para armazenar e processar o core do seu negócio.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-8 text-center text-slate-500 text-sm border-t border-slate-200 bg-white">
        &copy; 2024 Luminaris AI. Todos os direitos reservados.
    </footer>

</body>
</html>
