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
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4" id="series-tiles-grid">
            @foreach($seriesStats as $sStat)
                <a href="{{ route('results.broadsheet.detail', [$sStat['series_id'], $sStat['qualification_id']]) }}"
                   target="_blank"
                   data-qualification="{{ $sStat['qualification_type'] }}"
                   data-year="{{ $sStat['year'] }}"
                   data-month="{{ $sStat['month'] }}"
                   class="series-tile block text-left p-5 rounded-2xl border transition-all duration-200 bg-white border-slate-150 text-slate-800 hover:border-indigo-300 hover:shadow-lg hover:-translate-y-0.5 relative overflow-hidden group">
                    
                    {{-- Qualification Tag --}}
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-full shadow-sm {{ $sStat['qualification_type'] === 'igcse' ? 'bg-indigo-50 text-indigo-750 border border-indigo-100' : 'bg-purple-50 text-purple-750 border border-purple-100' }}">
                            {{ $sStat['qualification_name'] }}
                        </span>
                    </div>

                    <div class="flex flex-col h-full justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                @switch($sStat['month'])
                                    @case('March') Feb/Mar @break
                                    @case('June') May/Jun @break
                                    @case('November') Oct/Nov @break
                                    @default {{ $sStat['month'] }}
                                @endswitch
                            </span>
                            <h4 class="text-2xl font-black text-slate-850 mt-1 font-display group-hover:text-indigo-600 transition">{{ $sStat['year'] }}</h4>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-5 pt-4 border-t border-slate-100 text-xs">
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Students</span>
                                <span class="font-extrabold text-sm text-slate-700">{{ $sStat['candidate_count'] }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Avg PUM</span>
                                <span class="font-extrabold text-sm text-slate-700">{{ $sStat['average_pum'] ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">A*–C</span>
                                <span class="font-extrabold text-sm text-emerald-600">{{ $sStat['pass_rate'] }}%</span>
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
