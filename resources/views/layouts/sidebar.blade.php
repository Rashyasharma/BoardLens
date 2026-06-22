<!-- Sidebar Layout -->
<div class="hidden md:flex md:flex-col md:w-64 bg-slate-900 text-slate-400 min-h-screen shrink-0 border-r border-slate-800 shadow-sm">
    <!-- Header/Logo -->
    <a href="{{ route('home') }}" class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 gap-2.5 hover:bg-slate-900/50 transition-colors duration-150 group">
        <svg class="h-8 w-8 text-indigo-400 shrink-0 group-hover:scale-110 transition-transform duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="text-lg font-black text-white tracking-wide font-sans group-hover:text-indigo-300 transition-colors duration-150">BoardLens</span>
    </a>

    <!-- Navigation List -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto px-4 py-6">
        <nav class="space-y-1">
            @if(Request::is('cbse') || Request::is('cbse/*'))
                <!-- CBSE Dashboard -->
                <a href="{{ route('cbse.dashboard') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.dashboard') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">🏠</span>
                    CBSE Dashboard
                </a>

                <!-- CBSE Qualifications -->
                <a href="{{ route('cbse.qualifications.index') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.qualifications.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📋</span>
                    Qualifications
                </a>

                <!-- CBSE Subjects -->
                <a href="{{ route('cbse.subjects.index') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.subjects.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📚</span>
                    Subjects
                </a>

                <!-- CBSE Academic Years -->
                <a href="{{ route('cbse.academic-years.index') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.academic-years.*') || Request::routeIs('cbse.student-entries.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📅</span>
                    Academic Years
                </a>

                <!-- CBSE Results -->
                <div>
                    <a href="{{ route('cbse.results.index') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ (Request::routeIs('cbse.results.*') || Request::routeIs('cbse.analysis.broadsheet')) ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                        <span class="mr-3 text-base">📊</span>
                        Results
                    </a>
                    <div class="ml-8 mt-1 space-y-1 {{ (Request::routeIs('cbse.results.*') || Request::routeIs('cbse.analysis.broadsheet')) ? '' : 'hidden' }}">
                        <a href="{{ route('cbse.results.index') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('cbse.results.index') || Request::routeIs('cbse.results.year-details') || Request::routeIs('cbse.results.subject-details') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-amber-300' }} transition">
                            • View Result Records
                        </a>
                        <a href="{{ route('cbse.results.create') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('cbse.results.create') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-amber-300' }} transition">
                            • Enter Result
                        </a>
                        <a href="{{ route('cbse.results.upload') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('cbse.results.upload') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-amber-300' }} transition">
                            • Upload CSV
                        </a>
                        <a href="{{ route('cbse.analysis.broadsheet') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('cbse.analysis.broadsheet') ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-amber-300' }} transition">
                            • Broadsheet
                        </a>
                    </div>
                </div>

                <!-- CBSE Analysis Header -->
                <div class="pt-4 pb-1">
                    <span class="px-4 text-xxs font-extrabold text-slate-500 uppercase tracking-wider">CBSE Analysis</span>
                </div>

                <!-- CBSE Subject Trends -->
                <a href="{{ route('cbse.analysis.subject-wise') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.analysis.subject-wise') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📈</span>
                    Subject Trends
                </a>

                <!-- CBSE Student Journey -->
                <a href="{{ route('cbse.analysis.student-journey') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('cbse.analysis.student-journey') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">🛤️</span>
                    Student Journey
                </a>
            @else
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ Request::is('dashboard') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <svg class="mr-3 h-5 w-5 {{ Request::is('dashboard') ? 'text-indigo-400' : 'text-slate-450 group-hover:text-slate-250' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <!-- Qualifications -->
                <a href="{{ route('qualifications.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('qualifications.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📋</span>
                    Qualifications
                </a>

                <!-- Subjects -->
                <a href="{{ route('subjects.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('subjects.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📚</span>
                    Subjects
                </a>

                <!-- Exam Series -->
                <a href="{{ route('exam-series.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('exam-series.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📅</span>
                    Exam Series
                </a>

                <!-- Results -->
                <div>
                    <a href="{{ route('results.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ (Request::routeIs('results.*') || Request::routeIs('manual-results.*') || Request::routeIs('uploads.ai_importer') || Request::routeIs('uploads.ai_components')) ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                        <span class="mr-3 text-base">📊</span>
                        Results
                    </a>
                    <div class="ml-8 mt-1 space-y-1 {{ (Request::routeIs('results.*') || Request::routeIs('manual-results.*') || Request::routeIs('uploads.ai_importer') || Request::routeIs('uploads.ai_components')) ? '' : 'hidden' }}">
                        <a href="{{ route('manual-results.index') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('manual-results.*') ? 'text-indigo-400 font-bold' : 'text-slate-450 hover:text-indigo-300' }} transition">
                            • Upload Marks
                        </a>
                        <a href="{{ route('results.index') }}" class="block px-4 py-1.5 text-xs font-semibold {{ (Request::is('results') || Request::routeIs('results.index') || Request::routeIs('results.subject-results') || Request::routeIs('results.show')) ? 'text-indigo-400 font-bold' : 'text-slate-450 hover:text-indigo-300' }} transition">
                            • View Result Records
                        </a>
                        <a href="{{ route('results.broadsheet') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('results.broadsheet') ? 'text-indigo-400 font-bold' : 'text-slate-450 hover:text-indigo-300' }} transition">
                            • Broadsheet View
                        </a>
                        <a href="{{ route('uploads.ai_importer') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('uploads.ai_importer') ? 'text-indigo-400 font-bold' : 'text-slate-450 hover:text-indigo-300' }} transition">
                            • AI Results Importer
                        </a>
                        <a href="{{ route('uploads.ai_components') }}" class="block px-4 py-1.5 text-xs font-semibold {{ Request::routeIs('uploads.ai_components') ? 'text-indigo-400 font-bold' : 'text-slate-450 hover:text-indigo-300' }} transition">
                            • AI Component Importer
                        </a>
                    </div>
                </div>

                <!-- Analysis Header -->
                <div class="pt-4 pb-1">
                    <span class="px-4 text-xxs font-extrabold text-slate-500 uppercase tracking-wider">Analyse Results</span>
                </div>

                <!-- Subject Trends -->
                <a href="{{ route('analysis.subject-wise') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('analysis.subject-wise') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📚</span>
                    Subject Trends
                </a>

                <!-- Component Marks -->
                <a href="{{ route('analysis.component-marks') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('analysis.component-marks') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">📄</span>
                    Component Marks
                </a>

                <!-- Student Journey -->
                <a href="{{ route('analysis.student-journey') }}" class="group flex items-center px-4 py-2.5 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('analysis.student-journey') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">🛤️</span>
                    Student Journey
                </a>
            @endif

            <!-- Settings Header -->
            <div class="pt-4 border-t border-slate-800 mt-4">
                <a href="{{ route('settings.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold rounded-xl transition duration-150 {{ Request::routeIs('settings.*') ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/25' : 'hover:bg-slate-800 hover:text-slate-200 text-slate-400 border border-transparent' }}">
                    <span class="mr-3 text-base">⚙️</span>
                    Settings
                </a>
            </div>
        </nav>

    </div>
</div>
