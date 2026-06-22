@extends('layouts.app')

@section('title', 'CBSE Results Hub')
@section('page-title', 'CBSE Results Hub')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto py-2">
    <!-- Header Summary & Search Filter -->
    <div class="bg-white border border-slate-200 p-6 rounded-3xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 animate-fade-in">
        <div class="flex gap-4 items-center">
            <span class="text-3xl">📊</span>
            <div>
                <h4 class="text-slate-800 font-black text-base tracking-tight text-amber-700">CBSE Results Center</h4>
                <p class="text-slate-500 text-xs mt-0.5 leading-relaxed font-semibold">
                    Browse year-wise CBSE results. Click an Academic Year tile to view subjects and qualification groups.
                </p>
            </div>
        </div>
        
        <!-- Year Search Filter -->
        <form method="GET" action="{{ route('cbse.results.index') }}" class="w-full md:w-auto flex items-center gap-3">
            <div class="relative w-full md:w-64">
                <select name="academic_year_id" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition text-xs font-bold text-slate-700 appearance-none">
                    <option value="">-- Search &amp; Filter Academic Year --</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ $selectedYearId == $y->id ? 'selected' : '' }}>
                            {{ $y->name }}
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                    <span class="text-[10px]">▼</span>
                </div>
            </div>
            @if($selectedYearId)
                <a href="{{ route('cbse.results.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if($yearGroups->isEmpty())
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">📁</div>
            <p class="text-slate-500 text-sm font-semibold">No results matching search filters or entered in the system.</p>
            <p class="text-xs text-slate-400 mt-1 font-medium">Use the "Enter Result" spreadsheet view or Upload CSV to populate CBSE results.</p>
            <div class="mt-4 flex gap-3 justify-center">
                <a href="{{ route('cbse.results.upload') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-750 text-xs font-bold rounded-xl transition">
                    Upload CSV
                </a>
                <a href="{{ route('cbse.results.create') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                    Enter Marks Grid
                </a>
            </div>
        </div>
    @else
        <!-- Series (Year) Tiles Grid -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Academic Year Sessions</h3>
                <div class="flex gap-2">
                    <a href="{{ route('cbse.results.upload') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xxs font-bold transition">
                        Bulk CSV
                    </a>
                    <a href="{{ route('cbse.results.create') }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xxs font-bold transition">
                        + Enter Results
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($yearGroups as $yGroup)
                    <a href="{{ route('cbse.results.year-details', $yGroup['academic_year_id']) }}" 
                       class="series-tile bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-amber-900/5 transition-all duration-300 flex flex-col justify-between hover:border-amber-400 group relative overflow-hidden min-h-[12rem]">
                        
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 font-mono font-bold tracking-widest uppercase">CBSE Session</span>
                                <h4 class="text-2xl font-black text-slate-800 tracking-tight group-hover:text-amber-600 transition mt-1">
                                    {{ $yGroup['year_name'] }}
                                </h4>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl w-10 h-10 flex items-center justify-center group-hover:bg-amber-50 group-hover:border-amber-100 group-hover:-rotate-3 group-hover:scale-110 transition-all duration-300">
                                <span class="text-slate-400 group-hover:text-amber-600">📁</span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-2.5">
                            @foreach($yGroup['qualifications'] as $qual)
                                @php
                                    $shortName = str_replace(['Secondary ', 'Senior ', '(', ')'], '', $qual['qualification_name'] ?? '');
                                    if(empty($shortName)) $shortName = 'Unknown';
                                @endphp
                                <div class="flex items-center justify-between group/row">
                                    <span class="text-xs font-bold text-slate-600 flex items-center gap-2 group-hover/row:text-slate-900 transition-colors">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover/row:bg-amber-400 transition-colors"></span>
                                        {{ $shortName }}
                                    </span>
                                    <div class="flex items-center gap-2.5 text-[10px] font-semibold text-slate-500">
                                        <span class="bg-slate-50 px-2 py-0.5 rounded text-slate-600">{{ $qual['subject_count'] }} Subjects</span>
                                        <span class="text-amber-700 font-black bg-amber-50/50 border border-amber-100 px-2 py-0.5 rounded">
                                            {{ $qual['average_percentage'] ? $qual['average_percentage'].'%' : 'N/A' }} Avg
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
