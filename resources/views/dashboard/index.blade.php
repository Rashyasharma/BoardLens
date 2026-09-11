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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Candidates -->
            <a href="#" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300 group">
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
        </div>
    </div>



    <!-- Category 2: Pending Action Items -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            ⚠️ Pending Action Items
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Flagged Results -->
            <a href="{{ route('results.view', ['grade' => 'QX']) }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group border-amber-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider text-amber-500 group-hover:text-amber-600 transition">Flagged Results (Q / X)</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition font-bold">🔍</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $flaggedResults }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Pending (Q) or No Result (X) grades</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Category 3: Subject Performance -->
    <div class="space-y-3">
        <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center gap-1.5 font-sans">
            📈 Subject Performance (Last 3 Years)
        </h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- GCE AS & A Level Performance -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center justify-between">
                    <span>GCE AS & A Level</span>
                    <span class="text-xs font-normal text-slate-400 bg-slate-100 px-2 py-1 rounded">Top & Bottom 5</span>
                </h4>
                
                <div class="space-y-6">
                    <div>
                        <h5 class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="text-lg">🏆</span> Best Performing</h5>
                        <ul class="space-y-2">
                            @forelse($bestGceSubjects as $stat)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 font-medium truncate pr-2">{{ $stat->subject->subject_name }} <span class="text-xs text-slate-400 ml-1">({{ $stat->subject->subject_code }})</span></span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">{{ $stat->avg_pum }} <span class="text-xs font-medium opacity-70">PUM</span></span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-400 italic">No data available</li>
                            @endforelse
                        </ul>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100">
                        <h5 class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="text-lg">📉</span> Needs Improvement</h5>
                        <ul class="space-y-2">
                            @forelse($worstGceSubjects as $stat)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 font-medium truncate pr-2">{{ $stat->subject->subject_name }} <span class="text-xs text-slate-400 ml-1">({{ $stat->subject->subject_code }})</span></span>
                                    <span class="text-rose-700 font-bold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100">{{ $stat->avg_pum }} <span class="text-xs font-medium opacity-70">PUM</span></span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-400 italic">No data available</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- IGCSE Performance -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h4 class="text-sm font-bold text-slate-700 mb-4 flex items-center justify-between">
                    <span>IGCSE</span>
                    <span class="text-xs font-normal text-slate-400 bg-slate-100 px-2 py-1 rounded">Top & Bottom 5</span>
                </h4>
                
                <div class="space-y-6">
                    <div>
                        <h5 class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="text-lg">🏆</span> Best Performing</h5>
                        <ul class="space-y-2">
                            @forelse($bestIgcseSubjects as $stat)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 font-medium truncate pr-2">{{ $stat->subject->subject_name }} <span class="text-xs text-slate-400 ml-1">({{ $stat->subject->subject_code }})</span></span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">{{ $stat->avg_pum }} <span class="text-xs font-medium opacity-70">PUM</span></span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-400 italic">No data available</li>
                            @endforelse
                        </ul>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-100">
                        <h5 class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="text-lg">📉</span> Needs Improvement</h5>
                        <ul class="space-y-2">
                            @forelse($worstIgcseSubjects as $stat)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="text-slate-600 font-medium truncate pr-2">{{ $stat->subject->subject_name }} <span class="text-xs text-slate-400 ml-1">({{ $stat->subject->subject_code }})</span></span>
                                    <span class="text-rose-700 font-bold bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100">{{ $stat->avg_pum }} <span class="text-xs font-medium opacity-70">PUM</span></span>
                                </li>
                            @empty
                                <li class="text-sm text-slate-400 italic">No data available</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
