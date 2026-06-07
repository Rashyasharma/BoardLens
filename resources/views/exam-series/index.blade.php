@extends('layouts.app')

@section('title', 'Exam Series')
@section('page-title', 'Exam Series')

@section('content')
<div class="space-y-10 max-w-7xl mx-auto">

    {{-- Filter & Actions Bar --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-fade-in">
        <form method="GET" action="{{ route('exam-series.index') }}" class="w-full sm:max-w-xs">
            <div>
                <label class="block text-xs font-bold text-slate-400 mb-1.5 tracking-wider uppercase">Filter by Year</label>
                <select name="year" onchange="this.form.submit()" 
                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition cursor-pointer font-bold text-slate-700">
                    <option value="">All Years</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div class="flex items-end">
            <a href="{{ route('exam-series.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-550 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-600/20 hover:scale-[1.01] transition duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create Series
            </a>
        </div>
    </div>

    {{-- Series Grouped by Year --}}
    @if($seriesGrouped->isEmpty())
        <div class="bg-white border border-slate-200 rounded-3xl p-16 text-center shadow-sm animate-fade-in">
            <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">📅</div>
            <h3 class="text-lg font-bold text-slate-700">No Exam Series Found</h3>
            <p class="text-sm text-slate-400 mt-1 font-medium">There are no active or configured exam series matching your criteria.</p>
            <a href="{{ route('exam-series.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-555 border border-indigo-100 text-indigo-700 text-sm font-bold rounded-xl hover:bg-indigo-100 transition">
                + Create Series
            </a>
        </div>
    @else
        @foreach($seriesGrouped as $year => $list)
            <div class="space-y-4 animate-fade-in">
                <!-- Year Section Header -->
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $year }}</h2>
                    <div class="h-px bg-slate-200 flex-1"></div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $list->count() }} {{ Str::plural('Series', $list->count()) }}</span>
                </div>

                <!-- 3-Column Grid for Series Tiles -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($list as $s)
                        @php
                            // Color theme based on month
                            $theme = [
                                'March'    => ['border' => 'border-emerald-200', 'bg' => 'bg-emerald-50/20', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-50 border-emerald-100 text-emerald-700', 'grad' => 'from-emerald-500/5 to-transparent'],
                                'June'     => ['border' => 'border-indigo-200', 'bg' => 'bg-indigo-50/20', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-50 border-indigo-100 text-indigo-700', 'grad' => 'from-indigo-500/5 to-transparent'],
                                'November' => ['border' => 'border-amber-200', 'bg' => 'bg-amber-50/20', 'text' => 'text-amber-700', 'badge' => 'bg-amber-50 border-amber-100 text-amber-700', 'grad' => 'from-amber-500/5 to-transparent']
                            ][$s['month']] ?? ['border' => 'border-slate-200', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'badge' => 'bg-slate-100 border-slate-200 text-slate-500', 'grad' => 'from-slate-500/5 to-transparent'];
                        @endphp
                        
                        <div class="bg-white border {{ $theme['border'] }} rounded-3xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 overflow-hidden flex flex-col group">
                            {{-- Card Header --}}
                            <div class="px-6 pt-6 pb-4 bg-gradient-to-b {{ $theme['grad'] }} border-b border-slate-100 flex items-start justify-between gap-3">
                                <div class="space-y-1.5">
                                    <h3 class="text-xl font-bold text-slate-800 leading-tight group-hover:text-indigo-900 transition">{{ $s['label'] }}</h3>
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded border inline-block {{ $theme['badge'] }}">
                                        {{ $s['series_code'] }}
                                    </span>
                                </div>
                                <span class="shrink-0 text-xs font-bold px-2.5 py-0.5 rounded-full border
                                    {{ $s['is_active'] ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-500' }}">
                                    {{ $s['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            {{-- Stats Grid (Candidates, Subjects, Avg PUM, Pass Rate) --}}
                            <div class="p-6 bg-slate-50/30 border-b border-slate-100/50 grid grid-cols-2 gap-4">
                                <!-- Candidates -->
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">👥</span>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Candidates</span>
                                        <span class="text-sm font-black text-slate-800">{{ $s['candidates'] }}</span>
                                    </div>
                                </div>

                                <!-- Subjects Enrolled -->
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📚</span>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Subjects</span>
                                        <span class="text-sm font-black text-slate-800">{{ $s['subjects_count'] }}</span>
                                    </div>
                                </div>

                                <!-- Average PUM -->
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📈</span>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg PUM</span>
                                        <span class="text-sm font-black text-slate-800">{{ $s['avg_pum'] }}</span>
                                    </div>
                                </div>

                                <!-- Pass Rate -->
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">✅</span>
                                    <div>
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pass Rate</span>
                                        <span class="text-sm font-black text-slate-800">{{ $s['pass_rate'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions Footer --}}
                            <div class="mt-auto bg-slate-50/20 px-6 py-4 flex items-center justify-between gap-2 border-t border-slate-100/80">
                                <a href="{{ route('student-entries.show', $s['id']) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-150 text-slate-700 hover:text-indigo-700 text-xs font-bold rounded-lg transition duration-150 shadow-xs">
                                    👥 Manage Entries
                                </a>
                                
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('exam-series.edit', $s['id']) }}"
                                       class="text-xs font-bold text-slate-400 hover:text-slate-700 transition">Edit</a>
                                    <span class="text-slate-200 text-xs">|</span>
                                    <form action="{{ route('exam-series.destroy', $s['id']) }}" method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Are you sure you want to delete this series? All candidate registration records associated with this series will be deleted.')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-rose-500 hover:text-rose-700 transition cursor-pointer">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection
