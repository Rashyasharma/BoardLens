<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIEBoard Lens</title>
    <meta name="description" content="CIE Board Lens — Academic insights platform for Cambridge examinations.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS / Vite -->
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: 'Inter', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #f6faf6;
            background-image: url("data:image/svg+xml,%3Csvg width='800' height='800' viewBox='0 0 800 800' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M-100 -100 Q 100 200 400 400 T 900 900' stroke='%23e8f0e8' stroke-width='40' fill='none' /%3E%3Cpath d='M800 -100 Q 600 200 400 400 T -100 900' stroke='%23e8f0e8' stroke-width='20' fill='none' /%3E%3Cpath d='M0 400 Q 200 300 400 400 T 800 400' stroke='%23e8f0e8' stroke-width='15' fill='none' /%3E%3C/svg%3E");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }
        .page {
            position: relative;
            z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            gap: 2rem;
        }
    </style>
</head>
<body>

<div class="page text-center">

    <div class="flex flex-col items-center gap-4">
        <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight flex items-center justify-center gap-3">
            CIEBoard Lens 
            <svg class="h-10 w-10 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <polyline points="11 8 11 11 14 14"></polyline>
            </svg>
        </h2>
        <p class="text-lg md:text-xl text-slate-600 max-w-2xl">
            The premier academic insights platform for Cambridge examinations. Analyse grade distributions, component marks, and student journeys.
        </p>
    </div>

    <div class="mt-6 flex gap-4">
        @auth
            <a href="{{ route('dashboard') }}" class="px-8 py-3 rounded-md shadow-sm text-base font-semibold text-white bg-[#103c73] hover:bg-blue-900 transition-colors">
                Go to Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="px-8 py-3 rounded-md shadow-sm text-base font-semibold text-white bg-[#103c73] hover:bg-blue-900 transition-colors">
                Sign In to Portal
            </a>
        @endauth
    </div>

    <p class="absolute bottom-6 text-sm text-slate-500 font-medium tracking-wide">
        CIEBoard Lens · Academic Performance Intelligence
    </p>

</div>

</body>
</html>
