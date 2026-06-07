@extends('layouts.app')

@section('title', 'Trends Analysis')
@section('page-title', 'Year-over-Year Performance Trends')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Filter Panel -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 font-sans">Filter Year Range</h3>
        <form method="GET" action="{{ route('analysis.trends') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Subject -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>{{ $sub->subject_name }} ({{ $sub->subject_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Year Range -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Year Range</label>
                <select name="year_range" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="2022-2026" {{ $selectedYearRange == '2022-2026' ? 'selected' : '' }}>2022 - 2026 (Recent 5 Years)</option>
                    <option value="2018-2026" {{ $selectedYearRange == '2018-2026' ? 'selected' : '' }}>2018 - 2026 (All Available)</option>
                    <option value="2018-2022" {{ $selectedYearRange == '2018-2022' ? 'selected' : '' }}>2018 - 2022 (Historical)</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2 sm:col-span-2 md:col-span-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                    Analyze Trends
                </button>
                <a href="{{ route('analysis.trends') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl border border-slate-200 text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if(count($trends) > 0)
        <!-- Chart & Grid Container -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Line Chart Card -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Performance Trajectory</h3>
                <div class="h-80 relative">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Year-on-Year Summary</h3>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-6 py-3">Year</th>
                                <th class="px-6 py-3">Candidates</th>
                                <th class="px-6 py-3">Avg PUM</th>
                                <th class="px-6 py-3 text-right">Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                            @foreach(collect($trends)->sortKeys() as $year => $data)
                                <tr class="hover:bg-slate-50/50 transition font-medium">
                                    <td class="px-6 py-4 font-bold text-slate-800">{{ $year }}</td>
                                    <td class="px-6 py-4">{{ $data['total_students'] }}</td>
                                    <td class="px-6 py-4 font-bold text-indigo-650">{{ number_format($data['avg_pum'], 1) }}%</td>
                                    <td class="px-6 py-4 text-right font-bold text-emerald-600">{{ number_format($data['pass_rate'], 1) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ctx = document.getElementById('trendsChart').getContext('2d');
                const trends = @json($trends);

                // Sort keys chronologically
                const sortedYears = Object.keys(trends).sort();
                const avgPums = sortedYears.map(yr => trends[yr].avg_pum);
                const passRates = sortedYears.map(yr => trends[yr].pass_rate);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: sortedYears,
                        datasets: [
                            {
                                label: 'Average PUM (%)',
                                data: avgPums,
                                borderColor: '#4f46e5',
                                backgroundColor: 'transparent',
                                borderWidth: 3.5,
                                pointBackgroundColor: '#4f46e5',
                                pointBorderColor: '#fff',
                                pointRadius: 5,
                                tension: 0.25,
                                yAxisID: 'yPum'
                            },
                            {
                                label: 'Pass Rate (%)',
                                data: passRates,
                                borderColor: '#10b981',
                                backgroundColor: 'transparent',
                                borderWidth: 3.5,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#fff',
                                pointRadius: 5,
                                tension: 0.25,
                                yAxisID: 'yPass'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: { size: 11, weight: 'bold' }
                                }
                            }
                        },
                        scales: {
                            yPum: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                min: 0,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Average PUM',
                                    font: { weight: 'bold', size: 10 }
                                },
                                grid: {
                                    color: '#f1f5f9'
                                }
                            },
                            yPass: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                min: 0,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Pass Rate (%)',
                                    font: { weight: 'bold', size: 10 }
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: { size: 11, weight: 'bold' }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @else
        <!-- No Trends Empty State -->
        <div class="text-center py-20 bg-white rounded-2xl border border-slate-150 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">No Historical Trends Available</h3>
            <p class="text-sm text-slate-450 max-w-sm mx-auto">No candidate results datasets are uploaded across consecutive years to plot trend lines. Try uploading additional files.</p>
        </div>
    @endif
</div>
@endsection
