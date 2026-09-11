<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BoardLens')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Tailwind CSS / Vite -->
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @endif

    <script>
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
        /* Light, crisp theme */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background-color: #fafafa !important;
            color: #1a1a1a !important;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar link styles */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            border-radius: 8px;
            transition: all 0.15s;
            text-decoration: none;
        }
        .sidebar-link:hover {
            background-color: #f8fafc;
            color: #334155;
        }
        .sidebar-link.active {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 600;
        }
        .sidebar-icon {
            margin-right: 10px;
            font-size: 14px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-svg {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        .sidebar-sublink {
            display: block;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            transition: color 0.15s;
            text-decoration: none;
        }
        .sidebar-sublink:hover {
            color: #475569;
        }
        .sidebar-sublink.active {
            color: #0f172a;
            font-weight: 600;
        }
    </style>
</head>
<body class="h-full overflow-hidden flex">

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white border-b border-slate-200 z-10">
            <div class="px-6 py-3.5 flex justify-between items-center">
                <h1 class="text-lg font-semibold text-slate-800 tracking-tight">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 relative">
            <!-- Flash Messages -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 rounded-lg flex items-start gap-2">
                    <svg class="h-4 w-4 text-rose-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <ul class="text-sm text-rose-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-start gap-2">
                    <svg class="h-4 w-4 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-emerald-700 font-medium">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
