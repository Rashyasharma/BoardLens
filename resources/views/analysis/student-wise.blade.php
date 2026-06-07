@extends('layouts.app')

@section('title', 'Student-wise Analysis')
@section('page-title', 'Student Performance Analysis')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <!-- Search Bar Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Find Candidate Profile</h3>
        <form method="GET" action="{{ route('analysis.student-wise') }}" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input 
                    type="text" 
                    name="candidate_number" 
                    value="{{ request('candidate_number') }}" 
                    placeholder="Search by Candidate Number (e.g. 1234)..." 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-sans"
                />
            </div>
            <div class="flex-1">
                <input 
                    type="text" 
                    name="candidate_name" 
                    value="{{ request('candidate_name') }}" 
                    placeholder="Or search by Candidate Name..." 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-sans"
                />
            </div>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition shrink-0">
                Search Candidate
            </button>
        </form>
    </div>

    @if($student)
        <!-- Student Profile Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Panel: Info Card & Trend -->
            <div class="space-y-6">
                <!-- Profile Summary Card -->
                <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-md relative overflow-hidden">
                    <div class="relative z-10 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-lg">
                                Candidate Profile
                            </span>
                            <span class="text-xs text-slate-400 font-mono">#{{ $student->candidate_number }}</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">{{ $student->candidate_name }}</h2>
                            <p class="text-xs text-slate-400 mt-1">{{ $student->school->school_name }}</p>
                        </div>
                        <div class="pt-4 border-t border-slate-800 grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-slate-400 block">Gender</span>
                                <span class="font-bold text-slate-200">{{ $student->gender ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Date of Birth</span>
                                <span class="font-bold text-slate-200">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d-M-Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trend Chart Card -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">PUM Progression Trend</h3>
                    <div class="h-64 relative">
                        @if(count($trend) > 0)
                            <canvas id="studentTrendChart"></canvas>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 text-sm">
                                Not enough historical data to map trends.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Panel: Results lists grouped by qualification and series -->
            <div class="lg:col-span-2 space-y-6">
                @foreach($resultsByQualification as $qualId => $seriesGroup)
                    @php
                        $qualification = \App\Models\Qualification::find($qualId);
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-150 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-800">
                                {{ $qualification->qualification_name }} ({{ $qualification->qualification_type }})
                            </h3>
                            <span class="text-xxs text-slate-400 font-extrabold uppercase tracking-wider bg-white border border-slate-200 px-2 py-0.5 rounded">
                                Qualification
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($seriesGroup as $seriesId => $results)
                                @php
                                    $series = \App\Models\ExamSeries::find($seriesId);
                                @endphp
                                <div class="p-6 space-y-4">
                                    <h4 class="text-sm font-bold text-indigo-650 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        {{ $series->series_name }} ({{ $series->series_code }})
                                    </h4>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($results as $res)
                                            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <span class="text-xs font-bold text-slate-800 block truncate">{{ $res->subject->subject_name }}</span>
                                                    <span class="text-xxs text-slate-450 font-semibold block font-mono mt-0.5">Code: {{ $res->subject->subject_code }}</span>
                                                </div>
                                                <div class="flex items-center gap-2.5 shrink-0">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-900 text-white font-extrabold text-xs shadow-sm">
                                                        {{ $res->grade }}
                                                    </span>
                                                    <div class="text-right">
                                                        <span class="text-xxs text-slate-400 block font-semibold">PUM</span>
                                                        <span class="text-xs font-black text-slate-800">{{ $res->pum }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(count($trend) > 0)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctx = document.getElementById('studentTrendChart').getContext('2d');
                    const trendData = @json($trend);
                    
                    const years = Object.keys(trendData);
                    const pums = Object.values(trendData);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: years,
                            datasets: [{
                                label: 'Average PUM',
                                data: pums,
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.05)',
                                borderWidth: 3,
                                pointBackgroundColor: '#4f46e5',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#4f46e5',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    padding: 12,
                                    backgroundColor: '#0f172a',
                                    titleFont: { size: 12, weight: 'bold' },
                                    bodyFont: { size: 12 },
                                    displayColors: false
                                }
                            },
                            scales: {
                                y: {
                                    min: 0,
                                    max: 100,
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        font: { size: 10 }
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: { size: 10, weight: 'bold' }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @endif
    @else
        <!-- No Search Triggered State -->
        <div class="text-center py-20 bg-white rounded-2xl border border-slate-150 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Search for a Candidate</h3>
            <p class="text-sm text-slate-450 max-w-sm mx-auto">Enter a candidate number or candidate name in the search bar above to generate their detailed profile report.</p>
        </div>
    @endif
</div>
@endsection
