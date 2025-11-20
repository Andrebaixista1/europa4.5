<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Luminaris AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 h-screen flex items-center justify-center">

    <div class="w-full max-w-md p-8 bg-white rounded-2xl border border-slate-200 shadow-2xl">
        <div class="text-center mb-8">
            <img src="/logo.png" alt="Luminaris AI" class="h-16 w-auto mx-auto mb-4">
            <h1 class="text-2xl font-bold mb-2">Acesso Restrito</h1>
            <p class="text-slate-500 text-sm">Entre com suas credenciais de administrador</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Usuário</label>
                <input type="text" name="username" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 text-slate-900 placeholder-slate-400" placeholder="admin">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Senha</label>
                <input type="password" name="password" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 text-slate-900 placeholder-slate-400" placeholder="••••••">
            </div>
            <button type="submit" class="w-full py-3 bg-yellow-400 hover:bg-yellow-500 text-slate-900 rounded-lg font-bold transition-colors shadow-lg shadow-yellow-400/20">
                Entrar no Sistema
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-slate-500 hover:text-yellow-600 transition-colors">← Voltar para o site</a>
        </div>
    </div>

    <!-- Loader Overlay -->
    <div id="loader" class="fixed inset-0 bg-white/90 backdrop-blur-sm z-50 hidden flex-col items-center justify-center">
        <div class="relative w-24 h-24 mb-4">
            <div class="absolute inset-0 border-4 border-slate-200 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-yellow-400 rounded-full border-t-transparent animate-spin"></div>
            <img src="/logo.png" alt="Logo" class="absolute inset-0 w-12 h-12 m-auto animate-pulse">
        </div>
        <h2 class="text-xl font-bold text-slate-800 animate-pulse">Autenticando...</h2>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const loader = document.getElementById('loader');
            loader.classList.remove('hidden');
            loader.classList.add('flex');
            // Form will submit naturally after this
        });
    </script>

</body>
</html>
