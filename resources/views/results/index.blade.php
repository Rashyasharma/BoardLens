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
        
        <!-- Series Search Filter -->
        <form method="GET" action="{{ route('results.index') }}" class="w-full md:w-auto flex items-center gap-3">
            <div class="relative w-full md:w-64">
                <select name="series_id" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-xs font-bold text-slate-700 appearance-none">
                    <option value="">-- Search &amp; Filter Exam Series --</option>
                    @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ $selectedSeriesId == $s->id ? 'selected' : '' }}>
                            {{ $s->series_name }} ({{ $s->series_code }})
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                    <span class="text-[10px]">▼</span>
                </div>
            </div>
            @if($selectedSeriesId)
                <a href="{{ route('results.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-655 text-xs font-bold rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if($seriesGroups->isEmpty())
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">📁</div>
            <p class="text-slate-500 text-sm font-semibold">No results matching search filters or uploaded in the system.</p>
            <p class="text-xs text-slate-405 mt-1 font-medium">Upload statements or component marks first to populate the Results Hub.</p>
        </div>
    @else
        <!-- LEVEL 1: Series Tiles Grid -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Exam Series Tiles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($seriesGroups as $idx => $sGroup)
                    <a href="{{ route('results.series-details', $sGroup['series_id']) }}" 
                       class="series-tile bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-200 flex flex-col justify-between space-y-4 hover:border-indigo-400 group relative overflow-hidden">
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-black text-slate-800 tracking-tight group-hover:text-indigo-900 transition">
                                    {{ $sGroup['series_name'] }}
                                </h4>
                                <span class="text-xxs text-slate-400 font-mono font-bold">{{ $sGroup['month'] }} {{ $sGroup['year'] }}</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach($sGroup['qualifications'] as $qual)
                                    <div class="flex items-center justify-between text-xs border-b border-slate-50 pb-1.5 last:border-0 last:pb-0">
                                        <span class="font-extrabold text-slate-600 bg-slate-50 border border-slate-200/60 px-2 py-0.5 rounded text-[10px]">
                                            {{ $qual['qualification_name'] }}
                                        </span>
                                        <div class="flex items-center gap-2 text-slate-500 font-medium">
                                            <span>{{ $qual['subject_count'] }} subjects</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="font-bold text-indigo-650">{{ $qual['average_pum'] }}% PUM</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-[10px] text-indigo-600 uppercase font-extrabold tracking-wider">
                            <span>Open Series Overview</span>
                            <span class="text-xs group-hover:translate-x-1 transition-transform">→</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
