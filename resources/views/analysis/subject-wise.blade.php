@extends('layouts.app')

@section('title', 'Subject-wise Analysis')
@section('page-title', 'Subject Performance Analysis')

@section('content')
<div class="space-y-6 max-w-none mx-auto px-6 pb-12">
    <!-- Filter Panel -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 font-sans">Filter Parameters</h3>
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
@endsection
