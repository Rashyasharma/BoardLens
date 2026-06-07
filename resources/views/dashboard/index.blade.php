@extends('layouts.app')

@section('title', 'Dashboard — Cambridge Insights')
@section('page-title', 'Cambridge Insights')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-8 overflow-hidden shadow-lg shadow-indigo-950/20">
        <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
        <div class="relative z-10 max-w-xl">
            <h2 class="text-3xl font-bold text-white tracking-tight sm:text-4xl">Cambridge Insights</h2>
            <p class="mt-2 text-slate-300 text-lg leading-relaxed">
                Upload marks, track qualifications, and visualize grade distributions for Cambridge IGCSE & A Level examinations.
            </p>
        </div>
    </div>

    <!-- Category 1: Core Database Metrics -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            📦 Core Database Metrics
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <!-- Candidates -->
            <a href="{{ route('students.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-indigo-650 transition">Candidates</span>
                    <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl group-hover:bg-indigo-100 transition">👥</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalStudents }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Enrolled candidate profiles</p>
                </div>
            </a>

            <!-- Exam Series -->
            <a href="{{ route('exam-series.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-emerald-650 transition">Exam Series</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-100 transition">📅</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $activeSeries }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Series with registered students</p>
                </div>
            </a>

            <!-- Subjects -->
            <a href="{{ route('subjects.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-amber-650 transition">Subjects</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition">📚</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalSubjects }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Syllabi listed in catalog</p>
                </div>
            </a>

            <!-- School Center -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">School Center</span>
                    <span class="p-2 bg-slate-50 text-slate-600 rounded-xl">🏫</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-base font-extrabold text-slate-800 truncate" title="{{ $schoolName }}">{{ $schoolName }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Active center name</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category 2: Academic Results Analytics -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            📈 Academic Results Analytics
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <!-- Avg Pass Rate -->
            <a href="{{ route('analytics.yearly') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-purple-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-purple-650 transition">Average Pass Rate</span>
                    <span class="p-2 bg-purple-50 text-purple-600 rounded-xl group-hover:bg-purple-100 transition">🎯</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $avgPassRate }}%</h3>
                    <div class="w-full bg-slate-100 h-1.5 rounded-full mt-2.5 overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full" style="width: {{ $avgPassRate }}%"></div>
                    </div>
                </div>
            </a>

            <!-- Total Subject Entries -->
            <a href="{{ route('results.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-indigo-650 transition">Subject Entries</span>
                    <span class="p-2 bg-indigo-50 text-indigo-650 rounded-xl group-hover:bg-indigo-100 transition">📝</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalResults }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Total subject results recorded</p>
                </div>
            </a>

            <!-- Top Grades Percentage -->
            <a href="{{ route('analytics.yearly') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-emerald-650 transition">Top Grades (A*/A)</span>
                    <span class="p-2 bg-emerald-50 text-emerald-650 rounded-xl group-hover:bg-emerald-100 transition">🏆</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $topGradesPercent }}%</h3>
                    <p class="text-xxs text-slate-500 mt-1">Percentage of A* & A grade outcomes</p>
                </div>
            </a>

            <!-- Academic Fails -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-rose-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-rose-500">Ungraded (U) Fails</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">⚠️</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-rose-600 tracking-tight">{{ $failCount }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Candidates receiving U grade results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category 3: Component Portfolios -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            🧩 Component Portfolios
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- Total Component Marks -->
            <a href="{{ route('uploads.components') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-slate-700 transition">Component Marks</span>
                    <span class="p-2 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-slate-100 transition">📊</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalComponentMarks }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Individual component marks recorded</p>
                </div>
            </a>

            <!-- Completed Portfolios -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-emerald-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-emerald-600">Completed Portfolios</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">✅</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-emerald-600 tracking-tight">{{ $completedPortfolios }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Subject results with all components uploaded</p>
                </div>
            </div>

            <!-- Incomplete Portfolios -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-amber-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-amber-600">Incomplete Portfolios</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">⏳</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-amber-600 tracking-tight">{{ $incompletePortfolios }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Subject results missing component marks</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category 4: Platform Activity & Audits -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            🛡️ Platform Activity & Audits
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <!-- AI Provisional Imports -->
            <a href="{{ route('uploads.ai_importer') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-violet-600 transition">AI Upload Logs</span>
                    <span class="p-2 bg-violet-50 text-violet-600 rounded-xl group-hover:bg-violet-100 transition">🤖</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $aiUploads }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">AI-assisted broadsheet imports processed</p>
                </div>
            </a>

            <!-- Traditional Excel Imports -->
            <a href="{{ route('uploads.history') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-slate-750 transition">Excel/CSV Imports</span>
                    <span class="p-2 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-slate-100 transition">📂</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $excelUploads }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Traditional Excel/CSV results uploads</p>
                </div>
            </a>

            <!-- Flagged Results -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-amber-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-amber-500">Flagged Results (Q / X)</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl font-bold">🔍</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $flaggedResults }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Pending (Q) or No Result (X) grades</p>
                </div>
            </div>
        </div>
    </div>

            <div class="relative z-10 max-w-xl">
                <h2 class="text-3xl font-bold text-white tracking-tight sm:text-4xl">CBSE Insights</h2>
                <p class="mt-2 text-orange-200 text-lg leading-relaxed">
                    Analytics and performance tracking for CBSE Board examinations. This module is coming soon.
                </p>
            </div>
        </div>

        <!-- Coming Soon Placeholder -->
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-20 h-20 bg-amber-50 border-2 border-amber-200 rounded-3xl flex items-center justify-center text-4xl mb-6 shadow-sm">
                📘
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-3">CBSE Insights Coming Soon</h3>
            <p class="text-slate-500 text-base max-w-md leading-relaxed">
                We're building a comprehensive analytics suite for CBSE Board results. Stay tuned — this module will be available shortly.
            </p>
            <div class="mt-8 flex items-center gap-2 text-sm font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-5 py-2.5 rounded-full">
                <span>🚧</span>
                <span>Under Development</span>
            </div>
        </div>
    </div>

</div>

<script>
function switchTab(tab) {
    // Panel visibility
    document.getElementById('panel-cambridge').classList.toggle('hidden', tab !== 'cambridge');
    document.getElementById('panel-cbse').classList.toggle('hidden', tab !== 'cbse');

    // Tab button styles
    const cambridgeBtn = document.getElementById('tab-cambridge');
    const cbseBtn = document.getElementById('tab-cbse');

    if (tab === 'cambridge') {
        cambridgeBtn.className = 'tab-btn relative flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 bg-white text-indigo-700 shadow-md border border-indigo-100';
        cbseBtn.className = 'tab-btn flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50';
    } else {
        cbseBtn.className = 'tab-btn relative flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 bg-white text-amber-700 shadow-md border border-amber-100';
        cambridgeBtn.className = 'tab-btn flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50';
    }
}
</script>

@endsection
