<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cambridge Exam Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS / Vite -->
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @endif

    <script>
        // Fallback check: if no stylesheet or style block is found, load Tailwind CDN
        document.addEventListener('DOMContentLoaded', () => {
            const hasStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).some(el => {
                return el.sheet && el.sheet.cssRules.length > 0;
            });
            if (!hasStyles) {
                const script = document.createElement('script');
                script.src = "https://unpkg.com/@tailwindcss/browser@4";
                document.head.appendChild(script);
            }
        });
    </script>
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-950 relative overflow-hidden">
    
    <!-- Background Accents -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10">
        <div class="flex justify-center items-center gap-2">
            <svg class="h-10 w-10 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="text-2xl font-bold text-white tracking-tight">Cambridge Insights</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-100">
            Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-slate-400">
            Cambridge Exam Portal & Analytics Engine
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10">
        <div class="bg-slate-900 py-8 px-4 shadow-2xl rounded-2xl sm:px-10 border border-slate-800/80">
            
            <!-- Error Banner -->
            @if ($errors->any())
                <div class="mb-4 p-3.5 bg-rose-950/40 border border-rose-800/60 rounded-xl flex items-start gap-2.5">
                    <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <ul class="text-xs text-rose-300 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('login.store') }}" method="POST">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-300">
                        Username
                    </label>
                    <div class="mt-1.5">
                        <input id="username" name="username" type="text" autocomplete="username" required value="{{ old('username') }}" 
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-700 bg-slate-950 text-white rounded-xl shadow-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300">
                        Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               class="appearance-none block w-full px-3.5 py-2.5 border border-slate-700 bg-slate-950 text-white rounded-xl shadow-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-700 rounded bg-slate-950">
                        <label for="remember" class="ml-2 block text-sm text-slate-300">
                            Remember me
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center text-xs text-slate-500">
                Seeded credentials: <span class="text-slate-300 font-mono">admin</span> / <span class="text-slate-300 font-mono">password</span>
            </div>
        </div>
    </div>
</body>
</html>
