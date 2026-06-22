@extends('layouts.app')

@section('title', 'Subject-wise Analysis')
@section('page-title', 'Subject Performance Analysis')

@section('content')
<div class="space-y-6 max-w-none mx-auto px-6 pb-12">
    <!-- Filter Panel -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider font-sans">Filter Parameters</h3>
            <button
                id="open-yearly-trends-btn"
                onclick="openYearlyTrendsPanel()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Yearly Trends
                <span class="px-1.5 py-0.5 bg-white/20 rounded text-[10px] font-extrabold">PUM</span>
            </button>
        </div>
        <form method="GET" action="{{ route('analysis.subject-wise') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <!-- Qualification -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Qualification</label>
                <select id="qualification-select" name="qualification_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-medium">
                    <option value="">All Qualifications</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}" {{ $selectedQualId == $qual->id ? 'selected' : '' }}>{{ $qual->qualification_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Subject</label>
                <select id="subject-select" name="subject_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-medium">
                    <option value="">-- Choose Subject --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" data-qualification="{{ $sub->qualification_id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                            {{ $sub->subject_name }} ({{ $sub->subject_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition h-[42px]">
                    Analyze
                </button>
                <a href="{{ route('analysis.subject-wise') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl border border-slate-200 text-center transition h-[42px]">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($selectedSubjectId && $results->isNotEmpty())
        @php
            $subjectModel = $results->first()->subject;
        @endphp
        <!-- Dashboard Header -->
        <div class="bg-gradient-to-r from-indigo-900 to-slate-900 p-6 rounded-2xl shadow-md text-white">
            <span class="text-xxs font-extrabold bg-indigo-500/30 border border-indigo-500/20 px-2.5 py-1 rounded uppercase tracking-wider">
                {{ $subjectModel->qualification->qualification_name }}
            </span>
            <h2 class="text-2xl font-black mt-3">{{ $subjectModel->subject_name }}</h2>
            <p class="text-xs text-indigo-200 mt-1 font-mono font-medium">Subject Code: {{ $subjectModel->subject_code }}</p>
        </div>

        <!-- Overall Summary KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Candidates -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Subject Entries</span>
                    <span class="text-3xl font-black text-slate-800 mt-1 block">{{ $stats['total_students'] }}</span>
                </div>
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-500 text-xl font-bold">
                    👥
                </div>
            </div>

            <!-- Average PUM -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Overall Avg PUM</span>
                    <span class="text-3xl font-black text-indigo-600 mt-1 block">{{ number_format($stats['avg_pum'], 1) }}%</span>
                </div>
                <div class="w-12 h-12 bg-indigo-50 border border-indigo-100/60 rounded-xl flex items-center justify-center text-indigo-600 text-xl font-bold">
                    📈
                </div>
            </div>

            <!-- Best/Worst Grade Overall -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Best / Worst Grade</span>
                    @php
                        $gradeOrderMap = [
                            'A*' => 1, 'A*A*' => 1, 'A' => 2, 'AA' => 2, 'a' => 2, 'B' => 3, 'BB' => 3, 'b' => 3,
                            'C' => 4, 'CC' => 4, 'c' => 4, 'D' => 5, 'DD' => 5, 'd' => 5, 'E' => 6, 'EE' => 6, 'e' => 6,
                            'F' => 7, 'FF' => 7, 'G' => 8, 'GG' => 8, 'U' => 9, 'UU' => 9, 'X' => 10, 'Q' => 11
                        ];
                    @endphp
                    <span class="text-lg font-black text-slate-800 mt-2 block">
                        <span class="inline-flex items-center justify-center w-7 h-7 bg-emerald-600 text-white rounded-full text-xs font-extrabold mr-1">{{ $results->sortBy(fn($r) => $gradeOrderMap[$r->grade] ?? 99)->first()->grade ?? 'N/A' }}</span>
                        /
                        <span class="inline-flex items-center justify-center w-7 h-7 bg-rose-500 text-white rounded-full text-xs font-extrabold ml-1">{{ $results->sortByDesc(fn($r) => $gradeOrderMap[$r->grade] ?? 99)->first()->grade ?? 'N/A' }}</span>
                    </span>
                </div>
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-500 text-xl font-bold">
                    🎓
                </div>
            </div>

            <!-- Best / Worst PUM Overall -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Best / Worst PUM</span>
                    <span class="text-lg font-black text-slate-800 mt-2 block">
                        <span class="text-emerald-600 font-bold">{{ $stats['highest'] }}%</span> / <span class="text-rose-600 font-bold">{{ $stats['lowest'] }}%</span>
                    </span>
                </div>
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-500 text-xl font-bold">
                    ⚖️
                </div>
            </div>
        </div>

        <!-- Charts (Trend and Grade Count Distribution) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Line Chart: PUM progression trend -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    📈 Avg PUM Trend over Series
                </h3>
                <div class="h-64 relative">
                    <canvas id="pumTrendChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart: Grade Count Distribution -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    🍩 Grade Count Distribution
                </h3>
                <div class="h-64 relative">
                    <canvas id="gradePieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Grade Counts Bar -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                📋 Grade Counts Breakdowns (AS Lowercase vs A-Level/IGCSE Uppercase)
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-12 gap-3 text-center">
                @foreach(['A*', 'A*A*', 'A', 'AA', 'a', 'B', 'BB', 'b', 'C', 'CC', 'c', 'D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG', 'U', 'UU'] as $g)
                    @php
                        $count = $gradeDistribution[$g] ?? 0;
                        $isAS = in_array($g, ['a', 'b', 'c', 'd', 'e']);
                        $bgClass = $isAS ? 'bg-indigo-50/50 border-indigo-100 text-indigo-700' : 'bg-slate-50 border-slate-200 text-slate-800';
                    @endphp
                    <div class="p-3 border rounded-xl {{ $bgClass }} flex flex-col justify-between">
                        <span class="text-[10px] font-bold text-slate-400 block mb-1">
                            {{ $g }} {{ $isAS ? '(AS)' : '' }}
                        </span>
                        <span class="text-lg font-black">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Series-on-Series Performance -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-150 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Series-on-Series Progression</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <th class="px-6 py-3.5">Series Name</th>
                            <th class="px-6 py-3.5 text-center">Entries</th>
                            <th class="px-6 py-3.5 text-center">Pass</th>
                            <th class="px-6 py-3.5 text-center">Fail</th>
                            <th class="px-6 py-3.5 text-center">Pending (Q)</th>
                            <th class="px-6 py-3.5 text-center">No Result (X)</th>
                            <th class="px-6 py-3.5 text-center">Average PUM</th>
                            <th class="px-6 py-3.5 text-center">Best Grade</th>
                            <th class="px-6 py-3.5 text-right">Best PUM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-650 font-medium">
                        @foreach($seriesProgress as $index => $prog)
                            <tr class="hover:bg-indigo-50/40 cursor-pointer transition" onclick="openCandidatesPopup({{ $index }})">
                                <td class="px-6 py-4 font-bold text-slate-800 flex items-center gap-2">
                                    <span>{{ $prog['series_name'] }}</span>
                                    <span class="text-indigo-400 group-hover:text-indigo-600 opacity-0 hover:opacity-100 transition text-[10px] font-semibold">🔍 View Students</span>
                                </td>
                                <td class="px-6 py-4 text-center">{{ $prog['entries'] }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $prog['pass'] }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $prog['fail'] }}</td>
                                <td class="px-6 py-4 text-center text-amber-600 font-bold">{{ $prog['pending_q'] }}</td>
                                <td class="px-6 py-4 text-center text-slate-400 font-bold">{{ $prog['no_result_x'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-650">{{ $prog['avg_pum'] }}%</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-emerald-600 text-white rounded-full text-[10px] font-extrabold shadow-sm">
                                        {{ $prog['best_grade'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-emerald-600 font-bold">
                                    {{ $prog['best_pum'] }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Component Performance Analysis -->
        @if(!empty($componentAnalysis))
            <div class="space-y-6">
                <!-- Highlight Badges for Best/Worst component papers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($bestComponent)
                        <div class="bg-emerald-50 border border-emerald-150 p-4 rounded-xl flex items-center gap-3">
                            <span class="text-2xl">🏆</span>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Best Performing Component</h4>
                                <h3 class="text-sm font-black text-slate-800 mt-0.5">{{ $bestComponent['code'] }} - {{ $bestComponent['name'] }}</h3>
                                <p class="text-xs text-emerald-600 font-bold mt-1">Avg Marks: {{ number_format($bestComponent['avg_percentage'], 1) }}%</p>
                            </div>
                        </div>
                    @endif

                    @if($worstComponent)
                        <div class="bg-rose-50 border border-rose-150 p-4 rounded-xl flex items-center gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h4 class="text-xs font-bold text-rose-800 uppercase tracking-wider">Worst Performing Component</h4>
                                <h3 class="text-sm font-black text-slate-800 mt-0.5">{{ $worstComponent['code'] }} - {{ $worstComponent['name'] }}</h3>
                                <p class="text-xs text-rose-600 font-bold mt-1">Avg Marks: {{ number_format($worstComponent['avg_percentage'], 1) }}%</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Components Grid -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Component Papers Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($componentAnalysis as $uniqueName => $comp)
                            <div class="p-4 bg-slate-50/50 border border-slate-150 rounded-2xl flex flex-col justify-between hover:bg-slate-50 transition">
                                <div>
                                    <span class="px-2 py-0.5 bg-slate-150 text-[10px] font-bold text-slate-600 rounded">
                                        Paper Code: {{ $comp['code'] }}
                                    </span>
                                    <h4 class="text-sm font-extrabold text-slate-800 mt-2 truncate" title="{{ $comp['name'] }}">{{ $comp['name'] }}</h4>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-150 grid grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-slate-400 font-semibold block uppercase text-[9px] tracking-wider">Avg Marks</span>
                                        <span class="font-black text-slate-800 text-sm">
                                            {{ number_format($comp['avg_marks'], 1) }} <span class="text-slate-400 font-normal">/{{ $comp['total_marks'] }}</span>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-semibold block uppercase text-[9px] tracking-wider">Avg Percentage</span>
                                        <span class="font-black text-slate-800 text-sm">{{ number_format($comp['avg_percentage'], 1) }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-semibold block uppercase text-[9px] tracking-wider">Highest Marks</span>
                                        <span class="font-black text-emerald-600 text-sm">{{ $comp['highest'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-semibold block uppercase text-[9px] tracking-wider">Lowest Marks</span>
                                        <span class="font-black text-rose-500 text-sm">{{ $comp['lowest'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Chart scripts -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // 1. Avg PUM Trend Line Chart
                const lineCtx = document.getElementById('pumTrendChart').getContext('2d');
                const seriesProgressData = @json($seriesProgress);

                const lineLabels = seriesProgressData.map(p => p.series_name);
                const linePumVals = seriesProgressData.map(p => p.avg_pum);

                const lineGradient = lineCtx.createLinearGradient(0, 0, 0, 250);
                lineGradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
                lineGradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: lineLabels,
                        datasets: [{
                            label: 'Average PUM',
                            data: linePumVals,
                            borderColor: '#4f46e5',
                            borderWidth: 3,
                            backgroundColor: lineGradient,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#4f46e5',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: { font: { size: 10, weight: 'bold' }, color: '#64748b' },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { font: { size: 10, weight: 'bold' }, color: '#64748b' },
                                grid: { display: false }
                            }
                        }
                    }
                });

                // 2. Grade Distribution Pie Chart
                const pieCtx = document.getElementById('gradePieChart').getContext('2d');
                const distribution = @json($gradeDistribution);

                // Sort grades to make the chart look nice
                const standardGrades = ['A*', 'A*A*', 'A', 'AA', 'a', 'B', 'BB', 'b', 'C', 'CC', 'c', 'D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG', 'U', 'UU'];
                const pieLabels = [];
                const pieCounts = [];

                standardGrades.forEach(g => {
                    if (distribution[g] > 0) {
                        pieLabels.push(g);
                        pieCounts.push(distribution[g]);
                    }
                });

                const colorMap = {
                    'A*': 'rgba(16, 185, 129, 0.85)', // A* (emerald)
                    'A*A*': 'rgba(16, 185, 129, 0.85)',
                    'A': 'rgba(52, 211, 153, 0.85)',  // A (green)
                    'AA': 'rgba(52, 211, 153, 0.85)',
                    'a': 'rgba(5, 150, 105, 0.85)',   // a (dark emerald for AS)
                    'B': 'rgba(79, 70, 229, 0.85)',   // B (indigo)
                    'BB': 'rgba(79, 70, 229, 0.85)',
                    'b': 'rgba(99, 102, 241, 0.85)',  // b (light indigo for AS)
                    'C': 'rgba(129, 140, 248, 0.85)',  // C (indigo-400)
                    'CC': 'rgba(129, 140, 248, 0.85)',
                    'c': 'rgba(165, 180, 252, 0.85)', // c (indigo-300 for AS)
                    'D': 'rgba(245, 158, 11, 0.85)',   // D (amber)
                    'DD': 'rgba(245, 158, 11, 0.85)',
                    'd': 'rgba(251, 191, 36, 0.85)',   // d (yellow-500 for AS)
                    'E': 'rgba(253, 224, 71, 0.85)',   // E (yellow-300)
                    'EE': 'rgba(253, 224, 71, 0.85)',
                    'e': 'rgba(254, 240, 138, 0.85)',  // e (yellow-200 for AS)
                    'F': 'rgba(249, 115, 22, 0.85)',   // F (orange)
                    'FF': 'rgba(249, 115, 22, 0.85)',
                    'G': 'rgba(244, 63, 94, 0.85)',    // G (rose)
                    'GG': 'rgba(244, 63, 94, 0.85)',
                    'U': 'rgba(239, 68, 68, 0.85)',    // U (red)
                    'UU': 'rgba(239, 68, 68, 0.85)'
                };
                const bgColors = pieLabels.map(label => colorMap[label] || 'rgba(100, 116, 139, 0.85)');

                new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieCounts,
                            backgroundColor: bgColors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: { size: 11, weight: 'semibold' },
                                    color: '#475569',
                                    boxWidth: 12
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            });
        </script>

    @elseif($selectedSubjectId)
        <!-- Empty Results Page -->
        <div class="text-center py-20 bg-white rounded-2xl border border-slate-150 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">No Results Data Found</h3>
            <p class="text-sm text-slate-450 max-w-sm mx-auto">We couldn't find any results recorded for this subject.</p>
        </div>
    @else
        <!-- Initial Select State (Beautiful minimal tiles) -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider font-sans">Select a Subject to Analyze</h3>
                    <p class="text-xs text-slate-450 mt-0.5">Choose from the list of available subjects below or use the filters above.</p>
                </div>
                <div class="text-xs text-slate-400 font-medium">
                    Showing <span id="visible-tiles-count" class="font-bold text-slate-700">{{ count($subjects) }}</span> subjects
                </div>
            </div>

            <div id="subject-tiles-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($subjects as $sub)
                    <a href="{{ route('analysis.subject-wise', ['subject_id' => $sub->id, 'qualification_id' => $sub->qualification_id]) }}" 
                       data-qualification="{{ $sub->qualification_id }}" 
                       class="subject-tile bg-white p-5 rounded-2xl border border-slate-150 hover:shadow-md hover:border-indigo-300 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between h-44 group">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                @php
                                    $qualName = $sub->qualification->qualification_name;
                                    $isIGCSE = stripos($qualName, 'IGCSE') !== false;
                                    $isALevel = stripos($qualName, 'A-Level') !== false || stripos($qualName, 'A Level') !== false;
                                    $badgeClass = $isIGCSE 
                                        ? 'bg-indigo-50 border-indigo-150 text-indigo-700' 
                                        : ($isALevel ? 'bg-emerald-50 border-emerald-150 text-emerald-700' : 'bg-violet-50 border-violet-150 text-violet-700');
                                @endphp
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border {{ $badgeClass }} tracking-wider">
                                    {{ $qualName }}
                                </span>
                                <span class="text-xxs font-bold text-slate-400 font-mono tracking-wide group-hover:text-indigo-650 transition">
                                    {{ $sub->subject_code }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-650 transition line-clamp-2 leading-snug">
                                {{ $sub->subject_name }}
                            </h4>
                            
                            {{-- Metrics Badge Strip --}}
                            <div class="mt-2.5 flex items-center gap-2.5 text-[9px] font-semibold text-slate-500">
                                <span class="flex items-center gap-1 bg-slate-50/60 border border-slate-150 px-1.5 py-0.5 rounded-md">
                                    <span>👥</span>
                                    <span>{{ $sub->total_candidates ?? 0 }} {{ ($sub->total_candidates ?? 0) === 1 ? 'entry' : 'entries' }}</span>
                                </span>
                                <span class="flex items-center gap-1 bg-indigo-50/40 border border-indigo-100 px-1.5 py-0.5 rounded-md">
                                    <span>🎯</span>
                                    <span>Avg PUM: <strong class="text-indigo-650 font-extrabold">{{ $sub->avg_pum !== null ? round($sub->avg_pum, 1) . '%' : 'N/A' }}</strong></span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 border-t border-slate-50 mt-auto">
                            <span class="text-xs font-semibold text-slate-400 group-hover:text-indigo-600 transition flex items-center gap-1">
                                Analyze Subject
                                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div id="no-subjects-message" class="hidden text-center py-16 bg-white rounded-2xl border border-slate-150 shadow-sm">
                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    📚
                </div>
                <h3 class="text-sm font-bold text-slate-800 mb-0.5">No Subjects Found</h3>
                <p class="text-xs text-slate-450 max-w-xs mx-auto">There are no subjects registered under the selected qualification.</p>
            </div>
        </div>
    @endif
</div>

<!-- Dependent Dropdown & Subject Tiles Filtering Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const qualSelect = document.getElementById('qualification-select');
        const subSelect = document.getElementById('subject-select');
        const tiles = document.querySelectorAll('.subject-tile');
        const visibleTilesCountEl = document.getElementById('visible-tiles-count');
        const noSubjectsMsg = document.getElementById('no-subjects-message');
        const tilesGrid = document.getElementById('subject-tiles-grid');
        
        if (qualSelect && subSelect) {
            // Cache all original options on load
            const originalOptions = Array.from(subSelect.options).map(opt => ({
                value: opt.value,
                text: opt.textContent,
                qualification: opt.getAttribute('data-qualification'),
                selected: opt.selected
            }));

            function filterSubjects() {
                const qualId = qualSelect.value;
                const currentValue = subSelect.value;
                
                // Clear all current options
                subSelect.innerHTML = '';
                
                // Re-add placeholder
                const placeholder = originalOptions.find(opt => opt.value === "");
                if (placeholder) {
                    const el = document.createElement('option');
                    el.value = placeholder.value;
                    el.textContent = placeholder.text;
                    subSelect.appendChild(el);
                }
                
                let selectedIndexValid = false;
                
                // Re-add valid options
                originalOptions.forEach(opt => {
                    if (opt.value === "") return;
                    if (!qualId || opt.qualification === qualId) {
                        const el = document.createElement('option');
                        el.value = opt.value;
                        el.textContent = opt.text;
                        el.setAttribute('data-qualification', opt.qualification);
                        
                        if (currentValue === opt.value) {
                            el.selected = true;
                            selectedIndexValid = true;
                        }
                        
                        subSelect.appendChild(el);
                    }
                });
                
                // Reset select value if currently selected subject is filtered out
                if (currentValue !== "" && !selectedIndexValid && qualId !== "") {
                    subSelect.value = "";
                }

                // Filter subject tiles if they exist
                if (tiles.length > 0) {
                    let visibleTilesCount = 0;
                    tiles.forEach(tile => {
                        const tileQual = tile.getAttribute('data-qualification');
                        if (!qualId || tileQual === qualId) {
                            tile.style.display = 'flex';
                            visibleTilesCount++;
                        } else {
                            tile.style.display = 'none';
                        }
                    });

                    if (visibleTilesCountEl) {
                        visibleTilesCountEl.textContent = visibleTilesCount;
                    }

                    if (visibleTilesCount === 0) {
                        if (tilesGrid) tilesGrid.classList.add('hidden');
                        if (noSubjectsMsg) noSubjectsMsg.classList.remove('hidden');
                    } else {
                        if (tilesGrid) tilesGrid.classList.remove('hidden');
                        if (noSubjectsMsg) noSubjectsMsg.classList.add('hidden');
                    }
                }
            }

            qualSelect.addEventListener('change', filterSubjects);
            
            // Run initially
            filterSubjects();
        }
    });
</script>

<!-- Candidates Popup Modal -->
<div id="candidates-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeCandidatesPopup()"></div>

    <!-- Modal wrapper -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100 flex flex-col max-h-[85vh]">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800" id="modal-series-name">Series Candidates</h3>
                    <p class="text-xs text-slate-400 mt-0.5" id="modal-series-info">Candidate list and performance</p>
                </div>
                <button type="button" onclick="closeCandidatesPopup()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Table content -->
            <div class="overflow-y-auto p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-150 text-slate-500 text-xxs font-bold uppercase tracking-wider">
                            <th class="pb-3 pr-4">Cand. No</th>
                            <th class="pb-3 px-4">Candidate Name</th>
                            <th class="pb-3 px-4 text-center">Grade</th>
                            <th class="pb-3 pl-4 text-right">PUM</th>
                        </tr>
                    </thead>
                    <tbody id="modal-candidates-tbody" class="divide-y divide-slate-100 text-sm text-slate-650 font-medium">
                        <!-- Dynamic list -->
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-3.5 flex justify-end border-t border-slate-100">
                <button type="button" onclick="closeCandidatesPopup()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const seriesProgressData = @json($seriesProgress ?? []);

        window.openCandidatesPopup = function(index) {
            const data = seriesProgressData[index];
            if (!data) return;

            document.getElementById('modal-series-name').textContent = data.series_name + ' Results';
            document.getElementById('modal-series-info').textContent = `Total Entries: ${data.entries} | Avg PUM: ${data.avg_pum}%`;

            const tbody = document.getElementById('modal-candidates-tbody');
            tbody.innerHTML = '';

            data.candidates.forEach(cand => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50 transition';

                // Color code the grades dynamically
                let gradeBadge = '';
                const gradeUpper = cand.grade.toUpperCase();
                if (['A*', 'A*A*', 'A', 'AA', 'a'].includes(cand.grade)) {
                    gradeBadge = `<span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xxs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-100">${cand.grade}</span>`;
                } else if (['B', 'BB', 'b', 'C', 'CC', 'c'].includes(cand.grade)) {
                    gradeBadge = `<span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xxs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-100">${cand.grade}</span>`;
                } else if (['D', 'DD', 'd', 'E', 'EE', 'e'].includes(cand.grade)) {
                    gradeBadge = `<span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xxs font-extrabold bg-amber-50 text-amber-700 border border-amber-100">${cand.grade}</span>`;
                } else {
                    gradeBadge = `<span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xxs font-extrabold bg-rose-50 text-rose-700 border border-rose-100">${cand.grade}</span>`;
                }

                tr.innerHTML = `
                    <td class="py-3.5 pr-4 font-mono font-bold text-slate-500">${cand.candidate_number}</td>
                    <td class="py-3.5 px-4 font-bold text-slate-800">${cand.candidate_name}</td>
                    <td class="py-3.5 px-4 text-center">${gradeBadge}</td>
                    <td class="py-3.5 pl-4 text-right font-black text-indigo-650">${cand.pum}%</td>
                `;
                tbody.appendChild(tr);
            });

            const modal = document.getElementById('candidates-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        window.closeCandidatesPopup = function() {
            const modal = document.getElementById('candidates-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCandidatesPopup();
            }
        });
    });
</script>

{{-- ======================= YEARLY TRENDS SLIDE-OVER PANEL ======================= --}}
<div id="yearly-trends-overlay" class="fixed inset-0 z-[60] hidden" aria-modal="true" role="dialog">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeYearlyTrendsPanel()"></div>

    <!-- Panel -->
    <div id="yearly-trends-panel" class="absolute inset-y-0 right-0 w-full max-w-5xl bg-white shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-in-out">

        <!-- Panel Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-violet-900 to-indigo-900 text-white flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-black">Yearly PUM Trends</h2>
                    <p class="text-xs text-indigo-200">Subject performance across all exam series</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Year Range Filter -->
                <div class="flex items-center gap-2 bg-white/10 rounded-xl px-3 py-1.5">
                    <label class="text-[10px] text-indigo-200 font-bold uppercase">From</label>
                    <input type="number" id="yt-year-from" value="2020" min="2015" max="2030"
                        class="w-16 bg-transparent text-white text-xs font-bold border-none outline-none text-center"
                        onchange="fetchYearlyTrends()" />
                    <span class="text-indigo-300">–</span>
                    <label class="text-[10px] text-indigo-200 font-bold uppercase">To</label>
                    <input type="number" id="yt-year-to" value="{{ date('Y') }}" min="2015" max="2030"
                        class="w-16 bg-transparent text-white text-xs font-bold border-none outline-none text-center"
                        onchange="fetchYearlyTrends()" />
                </div>
                <button onclick="closeYearlyTrendsPanel()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/15 transition text-indigo-200 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <!-- Qualification Tabs -->
        <div class="flex gap-0 border-b border-slate-100 bg-slate-50 flex-shrink-0 overflow-x-auto" id="yt-tabs"></div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6" id="yt-body">
            <!-- Loading state -->
            <div id="yt-loading" class="flex flex-col items-center justify-center h-64 gap-3">
                <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="text-sm text-slate-400 font-semibold">Loading trends data…</p>
            </div>
            <!-- Content injected by JS -->
            <div id="yt-content" class="hidden space-y-6"></div>
        </div>
    </div>
</div>

<script>
(function() {
    // ─── State ────────────────────────────────────────────────────────────────
    let _ytData       = null;   // raw API response
    let _ytTabIdx     = 0;      // active qualification tab
    let _ytChart      = null;   // Chart.js multi-line instance
    let _panelOpen    = false;
    let _activeSubjects = new Set(); // subject_ids currently plotted

    const PALETTE = [
        '#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6',
        '#ef4444','#14b8a6','#f97316','#a855f7','#0ea5e9','#84cc16',
        '#fb7185','#34d399','#fbbf24','#60a5fa','#c084fc','#4ade80'
    ];

    // ─── Open / Close ─────────────────────────────────────────────────────────
    window.openYearlyTrendsPanel = function() {
        const overlay = document.getElementById('yearly-trends-overlay');
        const panel   = document.getElementById('yearly-trends-panel');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => panel.classList.remove('translate-x-full'), 10);
        _panelOpen = true;
        if (!_ytData) fetchYearlyTrends();
    };

    window.closeYearlyTrendsPanel = function() {
        const overlay = document.getElementById('yearly-trends-overlay');
        const panel   = document.getElementById('yearly-trends-panel');
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
        _panelOpen = false;
    };

    // ─── Fetch ────────────────────────────────────────────────────────────────
    window.fetchYearlyTrends = async function() {
        const yf = document.getElementById('yt-year-from').value;
        const yt = document.getElementById('yt-year-to').value;

        document.getElementById('yt-loading').classList.remove('hidden');
        document.getElementById('yt-content').classList.add('hidden');
        document.getElementById('yt-tabs').innerHTML = '';
        document.getElementById('yt-content').innerHTML = '';
        if (_ytChart) { _ytChart.destroy(); _ytChart = null; }
        _activeSubjects.clear();

        try {
            const res  = await fetch(`/api/analysis/yearly-pum-trends?year_from=${yf}&year_to=${yt}`);
            _ytData    = await res.json();

            document.getElementById('yt-year-from').min = _ytData.min_year;
            document.getElementById('yt-year-from').max = _ytData.max_year;
            document.getElementById('yt-year-to').min   = _ytData.min_year;
            document.getElementById('yt-year-to').max   = _ytData.max_year;

            _ytTabIdx = 0;
            renderTabs();
            renderContent();
        } catch(e) {
            document.getElementById('yt-loading').innerHTML =
                `<div class="text-rose-500 font-semibold text-sm">Failed to load data. Please try again.</div>`;
        }
    };

    // ─── Render Tabs ──────────────────────────────────────────────────────────
    function renderTabs() {
        const container = document.getElementById('yt-tabs');
        container.innerHTML = '';
        (_ytData.qualifications || []).forEach((qual, idx) => {
            const isActive = idx === _ytTabIdx;
            const btn = document.createElement('button');
            btn.id = `yt-tab-${idx}`;
            btn.className = [
                'px-5 py-3 text-xs font-bold tracking-wider uppercase transition-all duration-150 border-b-2 whitespace-nowrap',
                isActive
                    ? 'border-indigo-600 text-indigo-700 bg-white'
                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
            ].join(' ');
            btn.textContent = qual.qualification_name;
            btn.onclick = () => { _ytTabIdx = idx; _activeSubjects.clear(); renderTabs(); renderContent(); };
            container.appendChild(btn);
        });
    }

    // ─── Render Content ───────────────────────────────────────────────────────
    function renderContent() {
        document.getElementById('yt-loading').classList.add('hidden');
        const content = document.getElementById('yt-content');
        content.classList.remove('hidden');
        content.innerHTML = '';
        if (_ytChart) { _ytChart.destroy(); _ytChart = null; }

        const quals = _ytData.qualifications || [];
        if (quals.length === 0) {
            content.innerHTML = `<div class="text-center py-16 text-slate-400 font-semibold">No data found for the selected year range.</div>`;
            return;
        }

        const qual        = quals[_ytTabIdx];
        if (!qual) return;

        const subjects    = qual.subjects || [];
        const seriesLabels = qual.series_labels || [];
        const highest     = qual.highest;
        const lowest      = subjects.length > 0 ? subjects[subjects.length - 1] : null;

        // Pre-activate top 5 subjects by default
        if (_activeSubjects.size === 0) {
            subjects.slice(0, 5).forEach(s => _activeSubjects.add(String(s.subject_id)));
        }

        // Assign a stable color per subject_id
        const colorMap = {};
        subjects.forEach((s, i) => { colorMap[s.subject_id] = PALETTE[i % PALETTE.length]; });

        // ── 1. Highlight Cards ─────────────────────────────────────────────
        const cardsHtml = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl p-5">
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-emerald-100 rounded-full opacity-50"></div>
                <div class="flex items-start gap-3 relative">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white text-lg flex-shrink-0 shadow-md">🏆</div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-wider">Highest Avg PUM</p>
                        <h3 class="text-sm font-black text-slate-800 mt-0.5 truncate" title="${highest ? highest.subject_name : 'N/A'}">${highest ? highest.subject_name : 'N/A'}</h3>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-2xl font-black text-emerald-600">${highest ? highest.overall_avg + '%' : 'N/A'}</span>
                            <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">${highest ? highest.subject_code : ''}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative overflow-hidden bg-gradient-to-br from-rose-50 to-orange-50 border border-rose-200 rounded-2xl p-5">
                <div class="absolute -top-4 -right-4 w-20 h-20 bg-rose-100 rounded-full opacity-50"></div>
                <div class="flex items-start gap-3 relative">
                    <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center text-white text-lg flex-shrink-0 shadow-md">⚠️</div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-rose-700 uppercase tracking-wider">Lowest Avg PUM</p>
                        <h3 class="text-sm font-black text-slate-800 mt-0.5 truncate" title="${lowest ? lowest.subject_name : 'N/A'}">${lowest ? lowest.subject_name : 'N/A'}</h3>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-2xl font-black text-rose-600">${lowest ? (lowest.overall_avg ?? 'N/A') + (lowest.overall_avg != null ? '%' : '') : 'N/A'}</span>
                            <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">${lowest ? lowest.subject_code : ''}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        // ── 2. Subject Toggle Buttons ──────────────────────────────────────
        const toggleBtns = subjects.map((s) => {
            const active = _activeSubjects.has(String(s.subject_id));
            const col    = colorMap[s.subject_id];
            return `<button
                class="yt-subj-btn inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[10px] font-bold border transition-all duration-150 whitespace-nowrap ${
                    active ? 'text-white shadow-sm' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-400'
                }"
                style="${active ? `background:${col};border-color:${col};` : ''}"
                data-subject-id="${s.subject_id}"
                onclick="toggleSubjectLine('${s.subject_id}')"
                title="${s.subject_name} (${s.subject_code}) — Avg: ${s.overall_avg != null ? s.overall_avg+'%' : 'N/A'}">
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:${col}"></span>
                ${s.subject_name}
                <span class="opacity-70 font-mono text-[9px]">${s.subject_code}</span>
            </button>`;
        }).join('');

        const activeCount = _activeSubjects.size;
        const subjectToggleSection = `
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">📈 PUM Trend — Toggle Subjects</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Click subject pills to show/hide on the chart. <span id="yt-active-count" class="font-bold text-indigo-600">${activeCount}</span> active.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="selectAllSubjects()" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">All</button>
                    <button onclick="clearAllSubjects()" class="text-[10px] font-bold text-slate-500 hover:text-slate-700 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg transition">Clear</button>
                </div>
            </div>
            <!-- Subject pill buttons -->
            <div class="flex flex-wrap gap-2 mb-5" id="yt-subj-buttons">
                ${toggleBtns}
            </div>
            <!-- Chart -->
            <div class="h-64 relative" id="yt-chart-wrapper">
                <canvas id="yt-trend-chart-${_ytTabIdx}"></canvas>
            </div>
        </div>`;

        // ── 3. Trajectory / Trend Direction cards ──────────────────────────
        // For each subject with ≥2 series, compare first vs last PUM
        const improving = [], declining = [], stable = [];
        subjects.forEach(s => {
            const vals = seriesLabels
                .map(sl => s.series[sl]?.avg_pum ?? null)
                .filter(v => v !== null);
            if (vals.length < 2) return;
            const diff = vals[vals.length-1] - vals[0];
            if (diff > 3) improving.push({...s, diff: diff.toFixed(1)});
            else if (diff < -3) declining.push({...s, diff: diff.toFixed(1)});
            else stable.push({...s, diff: diff.toFixed(1)});
        });

        const trajectoryCard = (items, label, icon, colorClass, borderClass, badgeClass) => {
            if (items.length === 0) return '';
            return `<div class="${borderClass} rounded-2xl p-4 border">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-base">${icon}</span>
                    <h4 class="text-[10px] font-extrabold ${colorClass} uppercase tracking-wider">${label}</h4>
                    <span class="ml-auto text-[9px] font-black ${badgeClass} px-2 py-0.5 rounded-full">${items.length}</span>
                </div>
                <div class="space-y-1">
                    ${items.slice(0,6).map(s => `
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="font-semibold text-slate-700 truncate max-w-[140px]" title="${s.subject_name}">${s.subject_name}</span>
                        <span class="font-black ${colorClass} ml-2 flex-shrink-0">${parseFloat(s.diff) > 0 ? '+' : ''}${s.diff}%</span>
                    </div>`).join('')}
                    ${items.length > 6 ? `<p class="text-[9px] text-slate-400 font-medium mt-1">+${items.length-6} more</p>` : ''}
                </div>
            </div>`;
        };

        const trajectorySection = (improving.length + declining.length + stable.length) > 0 ? `
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-sm">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">🎯 Subject Trajectories <span class="text-[9px] font-medium text-slate-400 normal-case">first vs last series</span></h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                ${trajectoryCard(improving, 'Improving', '📈', 'text-emerald-700', 'bg-emerald-50 border-emerald-200', 'bg-emerald-100 text-emerald-700')}
                ${trajectoryCard(declining, 'Declining', '📉', 'text-rose-700', 'bg-rose-50 border-rose-200', 'bg-rose-100 text-rose-700')}
                ${trajectoryCard(stable, 'Stable', '➡️', 'text-slate-600', 'bg-slate-50 border-slate-200', 'bg-slate-200 text-slate-600')}
            </div>
        </div>` : '';

        // ── 4. PUM Heat Map Grid ───────────────────────────────────────────
        const heatRowLimit = Math.min(subjects.length, 20);
        const heatRows = subjects.slice(0, heatRowLimit).map(s => {
            const cells = seriesLabels.map(sl => {
                const e = s.series[sl];
                if (!e) return `<td class="px-1.5 py-1.5 text-center"><span class="inline-block w-8 text-[9px] text-slate-200 font-bold">—</span></td>`;
                const pum = e.avg_pum;
                const bg  = pum >= 80 ? '#10b981' : pum >= 70 ? '#34d399' : pum >= 60 ? '#6ee7b7'
                          : pum >= 50 ? '#fbbf24' : pum >= 40 ? '#f97316' : '#ef4444';
                const txt = pum >= 50 ? '#fff' : '#fff';
                return `<td class="px-0.5 py-0.5 text-center">
                    <span class="inline-flex items-center justify-center w-10 h-6 rounded text-[9px] font-black" style="background:${bg};color:${txt}">${pum}%</span>
                </td>`;
            }).join('');
            return `<tr class="border-b border-slate-50 hover:bg-slate-50/50">
                <td class="px-3 py-1.5 text-[10px] font-semibold text-slate-700 whitespace-nowrap max-w-[130px]">
                    <span class="truncate block" title="${s.subject_name} (${s.subject_code})">${s.subject_name}</span>
                    <span class="text-[8px] font-mono text-slate-400">${s.subject_code}</span>
                </td>
                ${cells}
            </tr>`;
        }).join('');

        const heatCols = seriesLabels.map(sl =>
            `<th class="px-0.5 py-2 text-[8px] font-bold text-slate-400 uppercase text-center whitespace-nowrap" style="writing-mode:vertical-rl;transform:rotate(180deg);height:60px;">${sl}</th>`
        ).join('');

        const heatMapSection = seriesLabels.length > 0 ? `
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">🌡️ PUM Heat Map</h3>
                    <p class="text-[9px] text-slate-400 mt-0.5">Green = high PUM · Red = low PUM${subjects.length > 20 ? ' · Top 20 shown' : ''}</p>
                </div>
                <div class="flex items-center gap-1 text-[8px] font-bold">
                    <span class="px-1.5 py-0.5 rounded" style="background:#10b981;color:#fff">≥80</span>
                    <span class="px-1.5 py-0.5 rounded" style="background:#fbbf24;color:#fff">≥50</span>
                    <span class="px-1.5 py-0.5 rounded" style="background:#ef4444;color:#fff">&lt;40</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="text-left border-collapse w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Subject</th>
                            ${heatCols}
                        </tr>
                    </thead>
                    <tbody>${heatRows}</tbody>
                </table>
            </div>
        </div>` : '';

        // ── 5. Full Rankings Table ─────────────────────────────────────────
        const headerCols = seriesLabels.map(s =>
            `<th class="px-3 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">${s}</th>`
        ).join('');

        const tableRows = subjects.map((sub, idx) => {
            const rankBadge = idx === 0
                ? `<span class="inline-flex items-center justify-center w-5 h-5 bg-amber-400 text-white rounded-full text-[9px] font-black">1</span>`
                : idx === subjects.length - 1
                    ? `<span class="inline-flex items-center justify-center w-5 h-5 bg-rose-400 text-white rounded-full text-[9px] font-black">${idx+1}</span>`
                    : `<span class="text-[10px] text-slate-400 font-bold w-5 text-center inline-block">${idx+1}</span>`;

            const seriesCells = seriesLabels.map(sl => {
                const entry = sub.series[sl];
                if (!entry) return `<td class="px-3 py-3 text-center text-[10px] text-slate-300">—</td>`;
                const pum = entry.avg_pum;
                const color = pum >= 70 ? 'text-emerald-600' : pum >= 50 ? 'text-amber-600' : 'text-rose-600';
                const bg    = pum >= 70 ? 'bg-emerald-50' : pum >= 50 ? 'bg-amber-50' : 'bg-rose-50';
                return `<td class="px-3 py-3 text-center">
                    <span class="inline-block px-2 py-0.5 rounded-lg text-[11px] font-bold ${color} ${bg}">${pum}%</span>
                </td>`;
            }).join('');

            const avgColor = (sub.overall_avg ?? 0) >= 70 ? 'text-emerald-700' : (sub.overall_avg ?? 0) >= 50 ? 'text-amber-700' : 'text-rose-700';

            return `<tr class="hover:bg-indigo-50/30 transition-colors border-b border-slate-50">
                <td class="px-4 py-3">${rankBadge}</td>
                <td class="px-2 py-3">
                    <div class="text-xs font-bold text-slate-800">${sub.subject_name}</div>
                    <div class="text-[9px] font-mono text-slate-400 mt-0.5">${sub.subject_code}</div>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="text-sm font-black ${avgColor}">${sub.overall_avg != null ? sub.overall_avg + '%' : 'N/A'}</span>
                </td>
                ${seriesCells}
            </tr>`;
        }).join('');

        const tableSection = `
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">📋 Subject Rankings — Series-wise PUM</h3>
                <span class="text-[10px] text-slate-400">${subjects.length} subjects</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-10">#</th>
                            <th class="px-2 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Subject</th>
                            <th class="px-3 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Overall Avg</th>
                            ${headerCols}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        ${tableRows || '<tr><td colspan="100" class="text-center py-10 text-slate-400">No data</td></tr>'}
                    </tbody>
                </table>
            </div>
        </div>`;

        content.innerHTML = [
            cardsHtml,
            '<div class="mt-1"></div>',
            subjectToggleSection,
            '<div class="mt-1"></div>',
            trajectorySection,
            trajectorySection ? '<div class="mt-1"></div>' : '',
            heatMapSection,
            heatMapSection ? '<div class="mt-1"></div>' : '',
            tableSection
        ].join('');

        // Draw the chart with currently active subjects
        drawChart(subjects, seriesLabels, colorMap);
    }

    // ─── Draw / Redraw Chart ──────────────────────────────────────────────────
    function drawChart(subjects, seriesLabels, colorMap) {
        if (_ytChart) { _ytChart.destroy(); _ytChart = null; }

        // get the tab index from the current DOM (may have changed)
        const qual = (_ytData.qualifications || [])[_ytTabIdx];
        if (!qual) return;

        const canvasEl = document.querySelector('#yt-chart-wrapper canvas');
        if (!canvasEl) return;

        const activeList = (subjects || qual.subjects || []).filter(s => _activeSubjects.has(String(s.subject_id)));

        const datasets = activeList.map(s => {
            const col = colorMap[s.subject_id];
            return {
                label: `${s.subject_name} (${s.subject_code})`,
                data: seriesLabels.map(sl => s.series[sl]?.avg_pum ?? null),
                borderColor: col,
                backgroundColor: col + '15',
                borderWidth: 2.5,
                fill: false,
                tension: 0.35,
                pointBackgroundColor: col,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                spanGaps: true,
            };
        });

        _ytChart = new Chart(canvasEl.getContext('2d'), {
            type: 'line',
            data: { labels: seriesLabels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: false // we have our own pill buttons
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.raw != null ? ctx.raw + '%' : 'N/A'}`
                        }
                    }
                },
                scales: {
                    y: { min: 0, max: 100,
                        ticks: { font: { size: 10, weight: 'bold' }, color: '#64748b', callback: v => v + '%' },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 9, weight: 'bold' }, color: '#64748b', maxRotation: 45 },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ─── Toggle a subject line on/off ─────────────────────────────────────────
    window.toggleSubjectLine = function(subjectId) {
        const sid = String(subjectId);
        if (_activeSubjects.has(sid)) {
            _activeSubjects.delete(sid);
        } else {
            _activeSubjects.add(sid);
        }
        refreshToggleButtons();
        const qual = (_ytData.qualifications || [])[_ytTabIdx];
        if (qual) drawChart(qual.subjects, qual.series_labels, buildColorMap(qual.subjects));
    };

    window.selectAllSubjects = function() {
        const qual = (_ytData.qualifications || [])[_ytTabIdx];
        if (!qual) return;
        qual.subjects.forEach(s => _activeSubjects.add(String(s.subject_id)));
        refreshToggleButtons();
        drawChart(qual.subjects, qual.series_labels, buildColorMap(qual.subjects));
    };

    window.clearAllSubjects = function() {
        _activeSubjects.clear();
        refreshToggleButtons();
        const qual = (_ytData.qualifications || [])[_ytTabIdx];
        if (qual) drawChart(qual.subjects, qual.series_labels, buildColorMap(qual.subjects));
    };

    function buildColorMap(subjects) {
        const m = {};
        subjects.forEach((s, i) => { m[s.subject_id] = PALETTE[i % PALETTE.length]; });
        return m;
    }

    function refreshToggleButtons() {
        const qual = (_ytData.qualifications || [])[_ytTabIdx];
        if (!qual) return;
        const colorMap = buildColorMap(qual.subjects);
        document.querySelectorAll('.yt-subj-btn').forEach(btn => {
            const sid = String(btn.dataset.subjectId);
            const active = _activeSubjects.has(sid);
            const col = colorMap[sid];
            if (active) {
                btn.style.background = col;
                btn.style.borderColor = col;
                btn.classList.remove('bg-white','text-slate-500','border-slate-200','hover:border-slate-400');
                btn.classList.add('text-white','shadow-sm');
            } else {
                btn.style.background = '';
                btn.style.borderColor = '';
                btn.classList.add('bg-white','text-slate-500','border-slate-200','hover:border-slate-400');
                btn.classList.remove('text-white','shadow-sm');
            }
        });
        const countEl = document.getElementById('yt-active-count');
        if (countEl) countEl.textContent = _activeSubjects.size;
    }

    // Close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && _panelOpen) closeYearlyTrendsPanel();
    });
})();
</script>
@endsection
