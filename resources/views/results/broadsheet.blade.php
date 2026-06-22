@extends('layouts.app')

@section('title', 'Broadsheet View')
@section('page-title', 'Broadsheet View')

@section('content')
<div class="space-y-5 max-w-full">

    {{-- Filter Card --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 font-display">Select Series & Qualification</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Qualification dropdown for filtering --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Qualification</label>
                <select id="filter-qualification" onchange="filterTiles()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-semibold text-slate-700">
                    <option value="">All Qualifications</option>
                    @foreach($qualifications as $q)
                        <option value="{{ $q->qualification_type }}">{{ $q->type_display }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Year --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Year</label>
                <select id="filter-year" onchange="filterTiles()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-semibold text-slate-700">
                    <option value="">All Years</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Session/Month --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Session</label>
                <select id="filter-month" onchange="filterTiles()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-semibold text-slate-700">
                    <option value="">All Sessions</option>
                    <option value="March">February/March</option>
                    <option value="June">May/June</option>
                    <option value="November">October/November</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Series-wise Stats Tiles --}}
    @if(isset($seriesStats) && $seriesStats->isNotEmpty())
    <div id="series-tiles-container">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
            Available Broadsheets
            <span id="tiles-count-badge" class="ml-2 text-indigo-500"></span>
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="series-tiles-grid">
            @foreach($seriesStats as $sStat)
                @php
                    $isIgcse = strtoupper($sStat['qualification_type']) === 'IGCSE';
                    $themeClass = $isIgcse 
                        ? 'hover:border-indigo-400/50 hover:shadow-indigo-500/10' 
                        : 'hover:border-purple-400/50 hover:shadow-purple-500/10';
                    $focusClass = $isIgcse ? 'focus:ring-indigo-500' : 'focus:ring-purple-500';
                    $accentBg = $isIgcse ? 'bg-indigo-50 text-indigo-750' : 'bg-purple-50 text-purple-750';
                    $iconTheme = $isIgcse 
                        ? 'bg-indigo-500 text-white shadow-indigo-200' 
                        : 'bg-purple-500 text-white shadow-purple-200';
                    $hoverBlob = $isIgcse 
                        ? 'from-indigo-50/40 via-blue-50/10 to-transparent' 
                        : 'from-purple-50/40 via-fuchsia-50/10 to-transparent';
                @endphp
                <a href="{{ route('results.broadsheet.detail', [$sStat['series_id'], $sStat['qualification_id']]) }}"
                   target="_blank"
                   data-qualification="{{ $sStat['qualification_type'] }}"
                   data-year="{{ $sStat['year'] }}"
                   data-month="{{ $sStat['month'] }}"
                   class="series-tile group relative block rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-2 hover:shadow-xl {{ $themeClass }} {{ $focusClass }} focus:outline-none focus:ring-2 focus:ring-offset-2 overflow-hidden">
                    
                    {{-- Hover background gradient blob --}}
                    <div class="absolute inset-0 -z-10 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-100 {{ $hoverBlob }}"></div>

                    {{-- Top Section: Icon & Badge --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl shadow-sm transition-all duration-300 {{ $iconTheme }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider border {{ $isIgcse ? 'bg-indigo-50/80 text-indigo-750 border-indigo-100' : 'bg-purple-50/80 text-purple-750 border-purple-100' }}">
                            {{ $sStat['qualification_name'] }}
                        </span>
                    </div>

                    {{-- Header: Month & Year --}}
                    <div class="mb-4">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            @switch($sStat['month'])
                                @case('March') Feb / Mar @break
                                @case('June') May / June @break
                                @case('November') Oct / Nov @break
                                @default {{ $sStat['month'] }}
                            @endswitch
                        </span>
                        <h4 class="text-3xl font-black text-slate-800 mt-0.5 font-display tracking-tight group-hover:text-slate-900 transition-colors duration-200">{{ $sStat['year'] }}</h4>
                    </div>

                    {{-- Statistics Dashboard List (Vertical rows to prevent any overflow) --}}
                    <div class="space-y-3 py-3.5 border-t border-b border-slate-100/80 mb-4 text-xs font-semibold text-slate-650">
                        {{-- Candidates --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Candidates</span>
                            </div>
                            <span class="font-extrabold text-sm text-slate-800">{{ $sStat['candidate_count'] }}</span>
                        </div>
                        
                        {{-- Entries --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Entries</span>
                            </div>
                            <span class="font-extrabold text-sm text-slate-800">{{ $sStat['entries_count'] ?? 0 }}</span>
                        </div>

                        {{-- Subjects --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Subjects</span>
                            </div>
                            <span class="font-extrabold text-sm text-slate-800">{{ $sStat['subject_count'] ?? 0 }}</span>
                        </div>
                    </div>

                    {{-- Bottom Performance Footer --}}
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <div class="flex items-center space-x-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-[8px] text-slate-400 font-bold uppercase tracking-wider leading-none mb-0.5">Avg PUM</span>
                                <span class="font-extrabold text-xs text-slate-700">{{ $sStat['average_pum'] ?? '—' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="text-right">
                                <span class="block text-[8px] text-slate-400 font-bold uppercase tracking-wider leading-none mb-0.5">A*–C Rate</span>
                                <span class="font-extrabold text-xs text-emerald-600">{{ $sStat['pass_rate'] }}%</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <p id="no-tiles-msg" class="hidden text-center text-sm text-slate-400 font-semibold py-12">No exam series match the selected filters.</p>
    </div>
    @else
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">📋</div>
            <p class="text-slate-500 text-sm font-semibold">No results uploaded yet. Upload a result sheet to get started.</p>
        </div>
    @endif
</div>

<script>
    // ── Client-side tile filtering ──────────────────────────────────────
    function filterTiles() {
        const qualVal  = document.getElementById('filter-qualification')?.value || '';
        const yearVal  = document.getElementById('filter-year')?.value  || '';
        const monthVal = document.getElementById('filter-month')?.value || '';
        const tiles    = document.querySelectorAll('.series-tile');
        const noMsg    = document.getElementById('no-tiles-msg');
        let visible = 0;

        tiles.forEach(tile => {
            const tileQual  = tile.dataset.qualification;
            const tileYear  = tile.dataset.year;
            const tileMonth = tile.dataset.month;

            const matchQual  = !qualVal  || tileQual  === qualVal;
            const matchYear  = !yearVal  || tileYear  === yearVal;
            const matchMonth = !monthVal || tileMonth === monthVal;

            if (matchQual && matchYear && matchMonth) {
                tile.style.display = '';
                visible++;
            } else {
                tile.style.display = 'none';
            }
        });

        if (noMsg) {
            noMsg.classList.toggle('hidden', visible > 0);
        }

        const badge = document.getElementById('tiles-count-badge');
        if (badge) {
            badge.textContent = visible < tiles.length ? `(${visible} of ${tiles.length})` : '';
        }
    }

    // Run tile filter on load in case dropdowns already have values
    document.addEventListener('DOMContentLoaded', filterTiles);
</script>
@endsection
