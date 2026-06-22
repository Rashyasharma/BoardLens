@extends('layouts.app')

@section('title', 'Results Hub')
@section('page-title', 'Results Hub')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto py-2">
    <!-- Header Summary & Search Filter -->
    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 animate-fade-in">
        <div class="flex gap-4 items-center">
            <span class="text-3xl">📊</span>
            <div>
                <h4 class="text-slate-800 font-black text-base tracking-tight">Results Center</h4>
                <p class="text-slate-500 text-xs mt-0.5 leading-relaxed font-semibold">
                    Browse exam results. Click a series tile to view subjects and qualification groups registered for that series.
                </p>
            </div>
        </div>
        
        <!-- Dynamic Search Filters -->
        <form method="GET" action="{{ route('results.index') }}" class="w-full md:w-auto flex flex-col sm:flex-row items-center gap-3">
            {{-- Year Dropdown --}}
            <div class="relative w-full sm:w-36">
                <select name="year" id="filter-year" onchange="filterSeriesByYear(); this.form.submit()" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-xs font-bold text-slate-700 appearance-none">
                    <option value="">-- All Years --</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                    <span class="text-[10px]">▼</span>
                </div>
            </div>

            {{-- Series Dropdown --}}
            <div class="relative w-full sm:w-60">
                <select name="series_id" id="filter-series" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-xs font-bold text-slate-700 appearance-none">
                    <option value="" data-year="">-- All Series --</option>
                    @foreach($series as $s)
                        <option value="{{ $s->id }}" data-year="{{ $s->year }}" {{ $selectedSeriesId == $s->id ? 'selected' : '' }}>
                            {{ $s->series_name }} ({{ $s->series_code }})
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                    <span class="text-[10px]">▼</span>
                </div>
            </div>

            @if($selectedSeriesId || $selectedYear)
                <a href="{{ route('results.index') }}" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-655 text-xs text-center font-bold rounded-xl transition border border-slate-200">
                    Reset
                </a>
            @endif
        </form>

        <script>
            function filterSeriesByYear() {
                const yearSelect = document.getElementById('filter-year');
                const seriesSelect = document.getElementById('filter-series');
                if (!yearSelect || !seriesSelect) return;
                
                const selectedYear = yearSelect.value;
                let hasActiveMatch = false;

                Array.from(seriesSelect.options).forEach(opt => {
                    const optYear = opt.getAttribute('data-year');
                    if (!selectedYear || !optYear || optYear === selectedYear) {
                        opt.style.display = '';
                        opt.disabled = false;
                        if (opt.selected && opt.value) {
                            hasActiveMatch = true;
                        }
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                        if (opt.selected) {
                            opt.selected = false;
                        }
                    }
                });

                if (selectedYear && !hasActiveMatch) {
                    seriesSelect.value = '';
                }
            }

            document.addEventListener('DOMContentLoaded', filterSeriesByYear);
        </script>
    </div>

    @if($seriesGroups->isEmpty())
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">📁</div>
            <p class="text-slate-500 text-sm font-semibold">No results matching search filters or uploaded in the system.</p>
            <p class="text-xs text-slate-405 mt-1 font-medium">Upload statements or component marks first to populate the Results Hub.</p>
        </div>
    @else
        <!-- LEVEL 1: Series Tiles Grid -->
        <div class="space-y-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Exam Series Tiles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="series-tiles-grid">
                @foreach($seriesGroups as $idx => $sGroup)
                    <a href="{{ route('results.series-details', $sGroup['series_id']) }}" 
                       class="series-tile group relative block rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:border-transparent hover:shadow-2xl hover:shadow-indigo-500/10 overflow-hidden">
                        
                        {{-- Top decorative gradient stripe --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
 
                        {{-- Hover background accent glow --}}
                        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-50/30 via-slate-50/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
 
                        <div class="space-y-4">
                            {{-- Header: Session & Date --}}
                            <div class="flex items-center justify-between">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-50 text-slate-500 transition-colors duration-300 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-lg group-hover:shadow-indigo-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-xxs text-slate-400 font-mono font-bold">{{ $sGroup['month'] }} {{ $sGroup['year'] }}</span>
                            </div>
 
                            <div>
                                <h4 class="text-xl font-black text-slate-800 tracking-tight group-hover:text-indigo-950 transition-colors duration-200">
                                    {{ $sGroup['series_name'] }}
                                </h4>
                            </div>
 
                            {{-- Qualifications List Container --}}
                            <div class="space-y-3 pt-2">
                                @foreach($sGroup['qualifications'] as $qual)
                                    @php
                                        $isIgcse = strtoupper($qual['qualification_id']) === 'IGCSE' || stripos($qual['qualification_name'], 'IGCSE') !== false;
                                        $tagColor = $isIgcse ? 'bg-indigo-50 text-indigo-750 border-indigo-100' : 'bg-purple-50 text-purple-750 border-purple-100';
                                    @endphp
                                    <div class="rounded-2xl bg-slate-50/60 p-3 border border-slate-100/40 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-extrabold px-2 py-0.5 rounded-md text-[9px] uppercase tracking-wider border {{ $tagColor }}">
                                                {{ $qual['qualification_name'] }}
                                            </span>
                                            <span class="font-bold text-xs text-indigo-700">{{ $qual['average_pum'] }}% PUM</span>
                                        </div>
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold">
                                            <span class="flex items-center gap-1">
                                                👥 {{ $qual['total_candidates'] }} Candidates
                                            </span>
                                            <span class="text-slate-300">|</span>
                                            <span class="flex items-center gap-1">
                                                📚 {{ $qual['subject_count'] }} Subjects
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
 
                        {{-- Footer Action Link --}}
                        <div class="mt-5 pt-4 border-t border-slate-100/80 flex justify-between items-center text-[10px] text-indigo-600 uppercase font-extrabold tracking-wider">
                            <span>Open Series Overview</span>
                            <span class="text-xs group-hover:translate-x-1.5 transition-transform duration-200 font-bold">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
