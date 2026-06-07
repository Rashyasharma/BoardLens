@extends('layouts.app')

@section('title', 'Grade Threshold Analysis')
@section('page-title', 'Grade Thresholds & Boundaries')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Filters -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 font-sans">Filter Thresholds</h3>
        <form method="GET" action="{{ route('analysis.grade-threshold') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <!-- Subject -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>{{ $sub->subject_name }} ({{ $sub->subject_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Year -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Year</label>
                <select name="year" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">All Years</option>
                    @foreach(range(2026, 2018) as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                    Compare
                </button>
                <a href="{{ route('analysis.grade-threshold') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl border border-slate-200 text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($series->isNotEmpty())
        <!-- Grade Threshold Boundaries Card -->
        <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">PUM Boundary Map by Exam Series</h3>
            </div>
            <div class="p-6 space-y-6">
                @foreach($series as $s)
                    @if($s->gradeThresholds->isNotEmpty())
                        <div class="space-y-3">
                            <h4 class="text-sm font-bold text-indigo-650 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                {{ $s->series_name }} ({{ $s->series_code }})
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                                @foreach($s->gradeThresholds->sortBy('minimum_pum') as $thresh)
                                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition text-center space-y-1">
                                        <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-900 text-white rounded-full text-xs font-black">
                                            {{ $thresh->grade }}
                                        </span>
                                        <div class="text-xs font-extrabold text-slate-700 pt-1">
                                            {{ $thresh->minimum_pum }}% - {{ $thresh->maximum_pum ?? 100 }}%
                                        </div>
                                        <span class="text-xxs text-slate-400 font-semibold block uppercase tracking-wide">PUM Bounds</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-4 border-b border-slate-100 last:border-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                            <div>
                                <h4 class="text-sm font-bold text-slate-700">{{ $s->series_name }} ({{ $s->series_code }})</h4>
                                <p class="text-xs text-slate-400 font-medium">No PUM thresholds configured for this series.</p>
                            </div>
                            <span class="text-xxs font-extrabold text-slate-400 uppercase bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">
                                Missing Config
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Grade Frequencies Card -->
        @if(count($thresholdComparison) > 0)
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Candidate Grade Distribution Comparison</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3">Exam Series</th>
                                @foreach(['A*', 'A*A*', 'A', 'AA', 'a', 'B', 'BB', 'b', 'C', 'CC', 'c', 'D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG', 'U', 'UU'] as $g)
                                    <th class="px-6 py-3 text-center">{{ $g }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-655 font-semibold">
                            @foreach($thresholdComparison as $seriesName => $dist)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ $seriesName }}</td>
                                    @foreach(['A*', 'A*A*', 'A', 'AA', 'a', 'B', 'BB', 'b', 'C', 'CC', 'c', 'D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG', 'U', 'UU'] as $g)
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm font-black text-slate-700">{{ $dist[$g] ?? 0 }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @else
        <!-- No Series Found -->
        <div class="text-center py-20 bg-white rounded-2xl border border-slate-150 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">No Exam Series Available</h3>
            <p class="text-sm text-slate-450 max-w-sm mx-auto">No exam series were found matching the filter query.</p>
        </div>
    @endif
</div>
@endsection
