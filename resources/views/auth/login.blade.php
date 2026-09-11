<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f4f7f4]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CIEBoard Lens</title>
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
            background-color: #f6faf6;
            background-image: url("data:image/svg+xml,%3Csvg width='800' height='800' viewBox='0 0 800 800' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M-100 -100 Q 100 200 400 400 T 900 900' stroke='%23e8f0e8' stroke-width='40' fill='none' /%3E%3Cpath d='M800 -100 Q 600 200 400 400 T -100 900' stroke='%23e8f0e8' stroke-width='20' fill='none' /%3E%3Cpath d='M0 400 Q 200 300 400 400 T 800 400' stroke='%23e8f0e8' stroke-width='15' fill='none' /%3E%3C/svg%3E");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
        .login-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #eaeaea;
        }
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md z-10 text-center">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center justify-center gap-2">
            CIEBoard Lens 
            <svg class="h-8 w-8 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <polyline points="11 8 11 11 14 14"></polyline>
            </svg>
        </h2>
        <p class="mt-2 text-sm text-slate-600 font-medium">
            Result Analysis Portal
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10">
        <div class="login-card py-8 px-4 sm:px-10">
            
            <!-- Error Banner -->
            @if ($errors->any())
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-2.5">
                    <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <ul class="text-xs text-rose-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('login.store') }}" method="POST">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700">
                        School Centre Code (Username)
                    </label>
                    <div class="mt-1">
                        <input id="username" name="username" type="text" autocomplete="username" required value="{{ old('username') }}" placeholder="e.g., IN016"
                               class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-blue-800 focus:border-blue-800 sm:text-sm bg-slate-100/50">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Password
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"
                               class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-blue-800 focus:border-blue-800 sm:text-sm bg-slate-100/50">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-[#103c73] hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-800 transition duration-150 ease-in-out mt-4">
                        Sign In
                    </button>
                </div>
                
                <div class="text-center mt-4">
                    <a href="#" class="text-xs text-slate-500 hover:text-slate-700 underline underline-offset-2">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
