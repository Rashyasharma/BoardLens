@extends('layouts.app')

@section('title', 'CBSE Insights — Dashboard')
@section('page-title', 'CBSE Insights Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner -->
    <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 rounded-3xl p-8 overflow-hidden shadow-lg shadow-amber-950/20">
        <div class="absolute top-0 right-0 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
        <div class="relative z-10 max-w-xl">
            <h2 class="text-3xl font-bold text-white tracking-tight sm:text-4xl">CBSE Insights</h2>
            <p class="mt-2 text-slate-300 text-lg leading-relaxed">
                Track Class 10 and Class 12 board qualifications, subjects, year-wise results, and analyze student progress.
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
            <a href="{{ route('cbse.academic-years.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition">Academic Years</span>
                    <span class="p-2 bg-amber-50 text-amber-650 rounded-xl group-hover:bg-amber-100 transition">📅</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ \App\Models\Cbse\CbseAcademicYear::count() }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Active sessions ({{ $totalStudents }} total students)</p>
                </div>
            </a>

            <!-- Qualifications -->
            <a href="{{ route('cbse.qualifications.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition">Qualifications</span>
                    <span class="p-2 bg-amber-50 text-amber-650 rounded-xl group-hover:bg-amber-100 transition">📋</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalQualifications }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Active class qualifications</p>
                </div>
            </a>

            <!-- Subjects -->
            <a href="{{ route('cbse.subjects.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-amber-600 transition">Subjects</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition">📚</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalSubjects }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Syllabi listed in catalog</p>
                </div>
            </a>

            <!-- Average Pass Rate -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Average Pass Rate</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">🎯</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $avgPassRate }}%</h3>
                    <p class="text-xxs text-slate-500 mt-1">Overall percentage passed</p>
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
            <!-- Total Results -->
            <a href="{{ route('cbse.results.index') }}" class="block bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-amber-650 transition">Subject Entries</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition">📝</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $totalResults }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Total subject results recorded</p>
                </div>
            </a>

            <!-- Top Grades -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Top Grades (A1/A2)</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">🏆</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $topGradesPercent }}%</h3>
                    <p class="text-xxs text-slate-500 mt-1">Percentage of top scoring outcomes</p>
                </div>
            </div>

            <!-- Fails -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-rose-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Failed (E1/E2)</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">⚠️</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-3xl font-black text-rose-600 tracking-tight">{{ $failCount }}</h3>
                    <p class="text-xxs text-slate-500 mt-1">Students failed in exams</p>
                </div>
            </div>

            <!-- Compartments & Absents -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between border-amber-100/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Absent / Compartment</span>
                    <span class="p-2 bg-slate-50 text-slate-600 rounded-xl">🔍</span>
                </div>
                <div class="mt-4">
                    <h3 class="text-xl font-bold text-slate-800 tracking-tight">{{ $absentCount }} Abs / {{ $compartmentCount }} Comp</h3>
                    <p class="text-xxs text-slate-500 mt-1">Absentee and compartment counts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category 3: Yearly Stats -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-150">
            <h4 class="font-bold text-slate-850">CBSE Yearly Performance Summary</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                        <th class="px-6 py-3">Academic Year</th>
                        <th class="px-6 py-3">Total Subject Entries</th>
                        <th class="px-6 py-3">Average Percentage</th>
                        <th class="px-6 py-3">Pass Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($yearlyStats as $stat)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $stat->name }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $stat->total_entries }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $stat->avg_percent }}%</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stat->pass_rate >= 80 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $stat->pass_rate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-450 italic">No CBSE result records seeded or uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
