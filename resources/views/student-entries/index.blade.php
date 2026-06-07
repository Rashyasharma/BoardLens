@extends('layouts.app')

@section('title', 'Student Entries')
@section('page-title', 'Student Entries')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    {{-- Intro card --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-5 flex items-start gap-4">
        <div class="w-10 h-10 shrink-0 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-xl">👥</div>
        <div>
            <h2 class="text-sm font-black text-slate-800">Student Entries</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Select an exam series below to enroll or manage candidates for each subject.
                Only active series are shown.
            </p>
        </div>
    </div>

    {{-- Filter & Actions --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-150 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form method="GET" action="{{ route('student-entries.index') }}" class="w-full sm:max-w-xs">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Filter by Year</label>
                <select name="year" onchange="this.form.submit()"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Years</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div>
            <a href="{{ route('student-entries.add-candidates') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0" />
                </svg>
                Upload / Add Candidates
            </a>
        </div>
    </div>

    {{-- Series Grid --}}
    @if($allSeries->isEmpty())
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">📅</div>
            <p class="text-slate-500 text-sm font-semibold">No active exam series found.</p>
            <a href="{{ route('exam-series.create') }}" class="mt-3 inline-block text-indigo-600 text-xs font-bold hover:underline">
                → Create an exam series first
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($allSeries as $s)
                <div class="bg-white border border-slate-150 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-200 flex flex-col overflow-hidden
                    {{ isset($selectedSeries) && $selectedSeries['id'] === $s['id'] ? 'ring-2 ring-indigo-400' : '' }}">
                    <div class="px-5 pt-5 pb-3">
                        <h3 class="text-base font-black text-slate-800">{{ $s['label'] }}</h3>
                        <p class="text-xxs font-mono text-slate-400 mt-1.5 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded inline-block">{{ $s['code'] }}</p>
                    </div>
                    <div class="mt-auto border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('student-entries.show', $s['id']) }}"
                           class="inline-flex items-center gap-1.5 w-full justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            Manage Entries →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
