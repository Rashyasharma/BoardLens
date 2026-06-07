@extends('layouts.app')

@section('title', 'Exam Series: ' . $series->series_name)
@section('page-title', 'Exam Series details')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-4 animate-fade-in">
    <!-- Header Summary & Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xxs font-extrabold uppercase tracking-wider text-slate-400">
                <a href="{{ route('results.index') }}" class="hover:text-indigo-650 transition">Results Hub</a>
                <span>/</span>
                <span class="text-slate-650">Series Overview</span>
            </div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ $series->series_name }} Results</h2>
            <p class="text-xs text-slate-500 font-semibold font-mono">{{ $series->month }} {{ $series->year }}</p>
        </div>
        <div>
            <a href="{{ route('results.index') }}" class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl shadow-sm transition">
                ← Back to Results Hub
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-lg">
                📚
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Qualifications</span>
                <span class="text-lg font-black text-slate-800">{{ $qualificationsData->count() }} Group(s)</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-lg">
                👥
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Candidates</span>
                <span class="text-lg font-black text-slate-800">{{ $qualificationsData->sum('total_candidates') }} Enrolled</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-2xl bg-violet-50 border border-violet-100 flex items-center justify-center text-lg">
                📈
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Average Series PUM</span>
                <span class="text-lg font-black text-indigo-750">
                    @php
                        $avgSeries = $qualificationsData->avg('average_pum');
                    @endphp
                    {{ $avgSeries ? round($avgSeries, 1) . '%' : 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Qualifications / Subjects Accordion Group -->
    <div class="space-y-8">
        @forelse($qualificationsData as $qual)
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 bg-indigo-50 border border-indigo-150 text-indigo-700 font-extrabold rounded-lg text-xxs tracking-wider uppercase">
                            {{ $qual['qualification_name'] }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-800">Syllabi &amp; Subject Performance</h3>
                    </div>
                    <span class="text-xxs font-black text-slate-400 uppercase">
                        {{ $qual['subject_count'] }} {{ $qual['subject_count'] == 1 ? 'Subject' : 'Subjects' }} Configured
                    </span>
                </div>

                <!-- Subject Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($qual['subjects'] as $subj)
                        @php
                            $titleText = '';
                            if ($subj['pum_uploaded']) {
                                $titleText .= 'Grade PUM uploaded';
                            }
                            if ($subj['components_uploaded']) {
                                $titleText .= ($titleText ? ' & ' : '') . 'Components mark uploaded';
                            }
                        @endphp
                        <a href="{{ route('manual-results.show', [$series->id, $subj['subject_id']]) }}"
                           title="{{ $titleText ?: 'No uploads' }}"
                           class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition duration-200 flex flex-col justify-between space-y-4 group">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-sm font-bold text-slate-800 truncate group-hover:text-indigo-900 transition" title="{{ $subj['subject_name'] }}">
                                        {{ $subj['subject_name'] }}
                                    </h4>
                                    <span class="font-mono text-[10px] font-black text-slate-400 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded">
                                        {{ $subj['subject_code'] }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @if($subj['pum_uploaded'])
                                        <span class="px-2 py-0.5 bg-emerald-50 border border-emerald-150 text-emerald-700 font-extrabold rounded text-[9px] uppercase tracking-wider">
                                            Grade/PUM Uploaded
                                        </span>
                                    @endif
                                    @if($subj['components_uploaded'])
                                        <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-150 text-indigo-750 font-extrabold rounded text-[9px] uppercase tracking-wider">
                                            Components marks uploaded
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Subject Performance Metrics -->
                            <div class="space-y-3 pt-3 border-t border-slate-100">
                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                    <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-wider">Candidates</span>
                                        <span class="text-xs font-extrabold text-slate-800">{{ $subj['candidate_count'] }}</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-slate-450 uppercase tracking-wider">Pass / Fail</span>
                                        <span class="text-[10px] font-extrabold text-slate-800">
                                            <span class="text-emerald-600">{{ $subj['passed_count'] }}</span>/<span class="text-rose-500">{{ $subj['failed_count'] }}</span>
                                        </span>
                                    </div>
                                    <div class="bg-indigo-50/50 rounded-xl p-2 border border-indigo-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-indigo-500 uppercase tracking-wider">Avg PUM</span>
                                        <span class="text-xs font-black text-indigo-750">{{ $subj['average_pum'] }}%</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-[10px] text-indigo-650 font-extrabold tracking-wider uppercase pt-1">
                                    <span>Open Marks Sheet</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
                <p class="text-slate-500 text-sm font-semibold">No registered subjects found for this exam series.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
