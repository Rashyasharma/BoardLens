@extends('layouts.app')

@section('title', 'Performance Analytics - Cambridge Exam Portal')
@section('page-title', 'Performance Analytics')

@section('content')
<div class="grid grid-cols-12 gap-8">
    
    <!-- Filters Sidebar Panel -->
    <div class="col-span-12 lg:col-span-3 space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800 tracking-tight mb-4">Dashboard Filters</h3>
            
            <form method="GET" action="{{ route('analytics.yearly') }}" class="space-y-4">
                <!-- Year Select -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-1.5">Calendar Year</label>
                    <select name="year" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Years</option>
                        @for ($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Series Select -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-1.5">Exam Series</label>
                    <select name="series_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Series</option>
                        @foreach($examSeries as $series)
                            <option value="{{ $series->id }}" {{ request('series_id') == $series->id ? 'selected' : '' }}>{{ $series->series_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Subject Select -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 tracking-wider mb-1.5">Subject</label>
                    <select name="subject_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->subject_code }} - {{ $sub->subject_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm rounded-xl transition duration-150 shadow-sm">
                        Apply Filters
                    </button>
                    @if(request()->anyFilled(['year', 'series_id', 'subject_id']))
                        <a href="{{ route('analytics.yearly') }}" class="block text-center mt-2.5 text-xs font-bold text-slate-500 hover:text-slate-700">
                            Reset Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Export Buttons Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h4 class="text-sm font-bold text-slate-800 tracking-tight mb-3">Download Reports</h4>
            <div class="grid grid-cols-2 gap-3">
                <form method="POST" action="{{ route('analytics.export') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="format" value="pdf">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="series_id" value="{{ request('series_id') }}">
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-xl border border-rose-200 transition duration-150 flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        PDF
                    </button>
                </form>

                <form method="POST" action="{{ route('analytics.export') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="format" value="excel">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="series_id" value="{{ request('series_id') }}">
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    <button type="submit" class="w-full py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 transition duration-150 flex items-center justify-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Content -->
    <div class="col-span-12 lg:col-span-9 space-y-6">
        
        <!-- Summary Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400 tracking-wider">Total Candidates</p>
                <p class="text-3xl font-black text-slate-800 mt-1 font-mono">{{ $statisticalSummary['total_students'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400 tracking-wider">Avg. Percentage</p>
                <p class="text-3xl font-black text-indigo-600 mt-1 font-mono">{{ $statisticalSummary['average_percentage'] }}%</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400 tracking-wider">Overall Pass Rate</p>
                <p class="text-3xl font-black text-emerald-600 mt-1 font-mono">{{ $passFailStats['pass_rate'] }}%</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-400 tracking-wider">Highest Score</p>
                <p class="text-3xl font-black text-purple-600 mt-1 font-mono">{{ $statisticalSummary['highest_score'] }}%</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Grade Distribution Bar Chart -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm md:col-span-7">
                <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">Grade Distribution</h3>
                <div class="h-64 relative">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>

            <!-- Pass/Fail Doughnut Chart -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm md:col-span-5">
                <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">Pass/Fail Ratio</h3>
                <div class="h-64 relative">
                    <canvas id="passFailChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Subject Performance breakdown -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h3 class="text-lg font-bold text-slate-800">Subject-wise Analytics Breakdown</h3>
                <p class="text-sm text-slate-500 mt-0.5">Summary metrics aggregated across qualifications</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                            <th class="py-3 px-6">Subject Code</th>
                            <th class="py-3 px-6">Subject Name</th>
                            <th class="py-3 px-6 text-center">Students</th>
                            <th class="py-3 px-6 text-center">Avg Score</th>
                            <th class="py-3 px-6 text-center">Pass Rate</th>
                            <th class="py-3 px-6 text-center">Standard Deviation</th>
                            <th class="py-3 px-6 text-center">Score Range (Min-Max)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($subjectPerformance as $perf)
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-4 px-6 font-mono font-semibold text-slate-500">
                                    {{ $perf->subject->subject_code }}
                                </td>
                                <td class="py-4 px-6 text-slate-800 font-bold">
                                    {{ $perf->subject->subject_name }}
                                </td>
                                <td class="py-4 px-6 text-center text-slate-600 font-medium">
                                    {{ $perf->total_students }}
                                </td>
                                <td class="py-4 px-6 text-center text-indigo-600 font-bold font-mono">
                                    {{ $perf->avg_percentage }}%
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="font-bold text-slate-800 font-mono">{{ $perf->pass_rate }}%</span>
                                        <div class="w-16 bg-slate-100 h-1.5 rounded-full overflow-hidden shrink-0 hidden sm:block">
                                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $perf->pass_rate }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center text-slate-500 font-mono">
                                    &plusmn;{{ $perf->std_dev }}
                                </td>
                                <td class="py-4 px-6 text-center text-slate-500 font-mono">
                                    {{ $perf->min_percentage }}% - {{ $perf->max_percentage }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                    No subject results found matching the filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Grade Distribution Chart (Bar)
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($gradeDistribution)) !!},
                datasets: [{
                    label: 'Candidates',
                    data: {!! json_encode(array_values($gradeDistribution)) !!},
                    backgroundColor: [
                        '#6366f1', // A* (Indigo)
                        '#4f46e5', // A (Dark Indigo)
                        '#10b981', // B (Emerald)
                        '#059669', // C (Dark Emerald)
                        '#f59e0b', // D (Amber)
                        '#d97706', // E (Dark Amber)
                        '#f43f5e'  // U (Rose)
                    ],
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        ticks: { stepSize: 1 } 
                    }
                }
            }
        });

        // Pass/Fail Doughnut Chart
        const passFailCtx = document.getElementById('passFailChart').getContext('2d');
        new Chart(passFailCtx, {
            type: 'doughnut',
            data: {
                labels: ['Passed', 'Failed'],
                datasets: [{
                    data: [{{ $passFailStats['passed'] }}, {{ $passFailStats['failed'] }}],
                    backgroundColor: ['#10b981', '#f43f5e'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { weight: 'bold' }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
