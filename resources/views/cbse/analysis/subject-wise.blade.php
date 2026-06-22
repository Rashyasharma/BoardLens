@extends('layouts.app')

@section('title', 'CBSE Subject Trends')
@section('page-title', 'CBSE Subject Trends')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Select Subject to View Trends</h3>
        <form method="GET" action="{{ route('cbse.analysis.subject-wise') }}" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1.5 flex-1 min-w-[240px]">
                <select name="subject_id" id="subject_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="">Choose a Subject</option>
                    @foreach($subjects as $sub)
                        @php
                            $qualName = $sub->qualification->qualification_name ?? str_replace('CLASS_', 'Class ', $sub->qualification->qualification_type);
                        @endphp
                        <option value="{{ $sub->id }}" {{ $subjectId == $sub->id ? 'selected' : '' }}>
                            [{{ $sub->subject_code }}] {{ $sub->subject_name }} ({{ $qualName }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                Analyze Trends
            </button>
        </form>
    </div>

    @if($selectedSubject)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- YoY Score Trend Chart -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Year-over-Year Average Percentage</h3>
                <div class="h-[300px] relative">
                    <canvas id="yoyChart"></canvas>
                </div>
            </div>

            <!-- YoY Pass Rate Trend Chart -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Year-over-Year Pass Rate</h3>
                <div class="h-[300px] relative">
                    <canvas id="passRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detail table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-150 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">YoY Performance Metrics</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                            <th class="px-6 py-3">Year</th>
                            <th class="px-6 py-3">Total Candidates</th>
                            <th class="px-6 py-3">Average Percentage</th>
                            <th class="px-6 py-3">Pass Rate</th>
                            <th class="px-6 py-3">Grade Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($yearlyStats as $stat)
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $stat->exam_year }}</td>
                                <td class="px-6 py-4 text-slate-650">{{ $stat->total }}</td>
                                <td class="px-6 py-4 text-slate-650">{{ $stat->avg_percentage }}%</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                        {{ $stat->pass_rate }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if(isset($gradeDistribution[$stat->exam_year]))
                                            @foreach($gradeDistribution[$stat->exam_year] as $gr => $cnt)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 font-mono">
                                                    {{ $gr }}: {{ $cnt }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-slate-400 italic">No distribution</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const years = {!! json_encode($yearlyStats->pluck('exam_year')) !!};
                const percentages = {!! json_encode($yearlyStats->pluck('avg_percentage')) !!};
                const passRates = {!! json_encode($yearlyStats->pluck('pass_rate')) !!};

                // YoY Chart
                new Chart(document.getElementById('yoyChart'), {
                    type: 'line',
                    data: {
                        labels: years,
                        datasets: [{
                            label: 'Average % Score',
                            data: percentages,
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { min: 0, max: 100 }
                        }
                    }
                });

                // Pass Rate Chart
                new Chart(document.getElementById('passRateChart'), {
                    type: 'bar',
                    data: {
                        labels: years,
                        datasets: [{
                            label: 'Pass Rate %',
                            data: passRates,
                            backgroundColor: '#059669',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { min: 0, max: 100 }
                        }
                    }
                });
            });
        </script>
    @else
        <!-- Initial Select State -->
        <div class="space-y-8">
            {{-- Subjects of Concern --}}
            @if(isset($subjectsOfConcern) && $subjectsOfConcern->isNotEmpty())
                <div class="space-y-5 bg-rose-50/50 p-6 rounded-3xl border border-rose-100">
                    <div class="flex items-center gap-3 pb-2 border-b border-rose-200">
                        <div class="p-2 bg-rose-100 rounded-xl text-rose-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-rose-800 uppercase tracking-wider">Subjects of Concern</h3>
                            <p class="text-[11px] text-rose-600 font-medium">Subjects showing a downward trend in average percentage compared to the previous year.</p>
                        </div>
                    </div>
                    
                    @foreach($subjectsOfConcern as $qual => $concerns)
                        <div class="space-y-3">
                            <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest pl-1">{{ $qual }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($concerns as $sub)
                                    <a href="{{ route('cbse.analysis.subject-wise', ['subject_id' => $sub->id]) }}" 
                                       class="bg-white p-5 rounded-2xl border border-rose-200 hover:shadow-md hover:border-rose-400 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-44 group relative overflow-hidden">
                                        <div class="absolute top-0 right-0 bg-rose-500 text-white text-[10px] font-black px-2.5 py-1 rounded-bl-xl shadow-sm tracking-wide">
                                            DROP {{ $sub->trend['drop'] }}%
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold text-slate-400 font-mono tracking-wide group-hover:text-rose-600 transition">
                                                    {{ $sub->subject_code }}
                                                </span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-rose-700 transition line-clamp-2 leading-snug">
                                                {{ $sub->subject_name }}
                                            </h4>
                                            
                                            <div class="mt-4 bg-rose-50/80 rounded-xl p-2.5 text-[10px] font-semibold text-rose-800 border border-rose-100">
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="opacity-70">{{ $sub->trend['previous_year'] }}:</span>
                                                    <span>{{ $sub->trend['previous_avg'] }}%</span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="opacity-70">{{ $sub->trend['latest_year'] }}:</span>
                                                    <span class="font-black text-rose-700">{{ $sub->trend['latest_avg'] }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- All Subjects --}}
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider font-sans">All Subjects</h3>
                        <p class="text-xs text-slate-450 mt-0.5">Browse all available subjects to analyze their performance trends.</p>
                    </div>
                    <div class="text-xs text-slate-400 font-medium">
                        Showing <span id="visible-tiles-count" class="font-bold text-slate-700">{{ count($subjects) }}</span> subjects
                    </div>
                </div>

            <div class="space-y-8 mt-4">
                @php
                    $groupedSubjects = $subjects->groupBy(function($sub) {
                        return $sub->qualification->qualification_name ?? str_replace('CLASS_', 'Class ', $sub->qualification->qualification_type);
                    });
                @endphp
                @foreach($groupedSubjects as $qualName => $groupSubjects)
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest pl-1 border-b border-slate-100 pb-2">{{ $qualName }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($groupSubjects as $sub)
                                <a href="{{ route('cbse.analysis.subject-wise', ['subject_id' => $sub->id]) }}" 
                                   class="subject-tile bg-white p-4 rounded-xl border border-slate-200 hover:shadow-md hover:border-amber-300 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-32 group">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-[10px] font-bold text-slate-400 font-mono tracking-wide group-hover:text-amber-600 transition">
                                                {{ $sub->subject_code }}
                                            </span>
                                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-amber-500 transition duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-750 group-hover:text-amber-700 transition line-clamp-2 leading-snug">
                                            {{ $sub->subject_name }}
                                        </h4>
                                    </div>
                                    
                                    {{-- Metrics Badge Strip --}}
                                    <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-500 mt-2">
                                        <span class="flex items-center gap-1 bg-slate-50 border border-slate-150 px-1.5 py-0.5 rounded text-slate-600">
                                            <span>👥</span>
                                            <span>{{ $sub->total_candidates ?? 0 }}</span>
                                        </span>
                                        <span class="flex items-center gap-1 bg-amber-50/50 border border-amber-100 px-1.5 py-0.5 rounded text-amber-700">
                                            <span>🎯</span>
                                            <span><strong class="font-black">{{ $sub->avg_percentage !== null ? round($sub->avg_percentage, 1) . '%' : 'N/A' }}</strong></span>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
        </div>
    @endif
</div>
@endsection
