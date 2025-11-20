<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
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
    </style>
</head>
<body class="bg-white text-slate-900 overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full bg-white/80 backdrop-blur-md z-50 border-b border-slate-100">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="p-8 rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-6 text-2xl">🚀</div>
                <h3 class="text-xl font-bold mb-3 text-slate-900">Automação Total</h3>
                <p class="text-slate-600">Integre CRM, WhatsApp e e-mail em fluxos de trabalho contínuos que rodam 24/7.</p>
            </div>
            <div class="p-8 rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6 text-2xl">🧠</div>
                <h3 class="text-xl font-bold mb-3 text-slate-900">IA Generativa</h3>
                <p class="text-slate-600">Use o poder do GPT-4 para analisar dados, gerar relatórios e atender clientes.</p>
            </div>
            <div class="p-8 rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition-transform">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-6 text-2xl">🔒</div>
                <h3 class="text-xl font-bold mb-3 text-slate-900">Segurança Enterprise</h3>
                <p class="text-slate-600">Seus dados protegidos com criptografia de ponta a ponta e conformidade total.</p>
            </div>
        </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_var(--tw-gradient-stops))] from-yellow-50 via-white to-white"></div>
        <div class="max-w-4xl mx-auto px-6 relative z-10">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">Vamos Conversar?</h2>
                <p class="text-lg text-slate-600">Entre em contato para transformarmos seu negócio com automação inteligente.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 md:p-12">
                <form id="whatsappForm" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nome</label>
                            <input type="text" id="name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-yellow-400 focus:border-transparent outline-none transition-all" placeholder="Seu nome">
                        </div>
                        <div>
                            <label for="company" class="block text-sm font-medium text-slate-700 mb-2">Empresa (Opcional)</label>
                            <input type="text" id="company" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-yellow-400 focus:border-transparent outline-none transition-all" placeholder="Nome da sua empresa">
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-700 mb-2">Mensagem</label>
                        <textarea id="message" rows="4" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-yellow-400 focus:border-transparent outline-none transition-all" placeholder="Como podemos ajudar?"></textarea>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-green-500/20 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            Enviar Mensagem
                        </button>
                        
                        <a href="https://wa.me/5511980733602?text=Ol%C3%A1%20Andr%C3%A9%20tudo%20bem%20?%20Queria%20fazer%20um%20or%C3%A7amento" target="_blank" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 px-8 rounded-xl shadow-lg shadow-slate-900/20 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 text-center">
                            <span>Orçamento Rápido</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('whatsappForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const company = document.getElementById('company').value;
            const message = document.getElementById('message').value;
            
            let text = `*Novo Contato via Site*\n\n`;
            text += `*Nome:* ${name}\n`;
            if(company) text += `*Empresa:* ${company}\n`;
            text += `*Mensagem:* ${message}`;
            
            const encodedText = encodeURIComponent(text);
            window.open(`https://wa.me/5511980733602?text=${encodedText}`, '_blank');
        });
    </script>

    <!-- Footer -->
    <footer class="bg-slate-50 py-12 text-center text-slate-500 text-sm">
        &copy; 2024 Luminaris AI. Todos os direitos reservados.
    </footer>

</body>
</html>
