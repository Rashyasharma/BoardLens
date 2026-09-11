@extends('layouts.app')

@section('title', 'Component Marks Analysis')
@section('page-title', 'Component-wise Marks Analysis')

@section('content')
<div class="space-y-5 max-w-full px-6 mx-auto py-2">

    {{-- ══ TOP FILTERS ══ --}}
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm animate-fade-in">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            </div>
            <div>
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Filter by Qualification & Subject</h3>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Select filters to narrow down, or browse all subjects below</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase mb-1.5 tracking-wider">Qualification</label>
                <select id="qual-filter" onchange="applyFilters()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs font-bold text-slate-700">
                    <option value="">All Qualifications</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual['id'] }}">{{ $qual['qualification_name'] }} ({{ $qual['qualification_type'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-extrabold text-slate-400 uppercase mb-1.5 tracking-wider">Subject</label>
                <select id="subj-filter" onchange="applyFilters()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs font-bold text-slate-700">
                    <option value="">All Subjects</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="clearFilters()" class="w-full px-4 py-2.5 text-xs font-bold text-slate-500 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    {{-- ══ BREADCRUMB NAV ══ --}}
    <div id="breadcrumb-nav" class="hidden items-center gap-2 text-xs font-bold text-slate-500">
        <button onclick="resetToSubjectGrid()" class="hover:text-indigo-600 transition">All Subjects</button>
        <span id="bc-subject-sep" class="hidden text-slate-300">›</span>
        <span id="bc-subject-name" class="hidden text-slate-700"></span>
        <span id="bc-range-sep" class="hidden text-slate-300">›</span>
        <span id="bc-range-name" class="hidden text-slate-700"></span>
    </div>

    {{-- ══ VIEW 1: SUBJECT TILES GRID ══ --}}
    <div id="view-subjects" class="">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">All Subjects</h4>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Click a subject to explore its component sets and statistics</p>
            </div>
            <span id="subjects-count" class="text-xxs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-lg">0 Subjects</span>
        </div>
        <div id="subjects-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            {{-- Dynamically filled --}}
        </div>
        <div id="subjects-empty" class="hidden text-center py-16 text-slate-400 text-xs italic">No subjects found.</div>
    </div>

    {{-- ══ VIEW 2: YEAR RANGE TILES ══ --}}
    <div id="view-ranges" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 id="ranges-title" class="text-xs font-black text-slate-800 uppercase tracking-wider">Component Sets</h4>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Select a year range to view component statistics</p>
            </div>
            <button onclick="resetToSubjectGrid()" class="flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-slate-500 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Back to Subjects
            </button>
        </div>
        <div id="ranges-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Dynamically filled --}}
        </div>
    </div>

    {{-- ══ VIEW 3: COMPONENT STATISTICS PANEL ══ --}}
    <div id="view-stats" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 id="stats-title" class="text-xs font-black text-slate-800 uppercase tracking-wider">Component Statistics</h4>
                <p id="stats-subtitle" class="text-[10px] text-slate-400 font-medium mt-0.5"></p>
            </div>
            <button id="stats-back-btn" onclick="goBackToRanges()" class="flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-slate-500 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Back to Year Ranges
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left: Component List --}}
            <div class="lg:col-span-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Components in Set</span>
                    <span id="comp-count-badge" class="text-xxs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">0 Papers</span>
                </div>
                <div id="comp-list" class="space-y-2.5">
                    {{-- Dynamically filled --}}
                </div>
            </div>

            {{-- Right: Details --}}
            <div class="lg:col-span-8 space-y-5">

                {{-- Overview chart placeholder --}}
                <div id="comp-overview-card" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">All Components – Average % Comparison</h4>
                    <div class="relative h-72">
                        <canvas id="allComponentsChart"></canvas>
                    </div>
                    <p class="text-center text-xs text-slate-400 italic pt-1">Click a component on the left to see its detailed performance breakdown →</p>
                </div>

                {{-- Specific component detail --}}
                <div id="comp-detail-card" class="hidden space-y-5">

                    {{-- Header stats row --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                        <div class="flex justify-between items-start gap-4">
                            <div class="space-y-2 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span id="cd-code" class="px-2.5 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-700 font-extrabold rounded text-[10px] uppercase tracking-wider font-mono">P1</span>
                                    <span id="cd-years" class="px-2.5 py-0.5 bg-slate-50 border border-slate-100 text-slate-500 font-extrabold rounded text-[10px] uppercase tracking-wider">All Years</span>
                                    <span id="cd-total-marks-badge" class="px-2.5 py-0.5 bg-slate-50 border border-slate-100 text-slate-500 font-bold rounded text-[10px]">— Marks</span>
                                </div>
                                <h3 id="cd-name" class="text-base font-black text-slate-800 tracking-tight">Component Name</h3>
                                <p id="cd-subname" class="text-[11px] text-slate-400 font-bold -mt-1"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Avg %</span>
                                <span id="cd-avg" class="text-base font-black text-slate-800 mt-0.5 block">—</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Median</span>
                                <span id="cd-median" class="text-base font-black text-slate-800 mt-0.5 block">—</span>
                            </div>
                            <div class="bg-emerald-50/50 rounded-xl p-3 border border-emerald-100/70">
                                <span class="block text-[10px] font-black text-emerald-600/80 uppercase tracking-wider">Highest</span>
                                <span id="cd-highest" class="text-base font-black text-emerald-700 mt-0.5 block">—</span>
                            </div>
                            <div class="bg-rose-50/50 rounded-xl p-3 border border-rose-100/70">
                                <span class="block text-[10px] font-black text-rose-600/80 uppercase tracking-wider">Lowest</span>
                                <span id="cd-lowest" class="text-base font-black text-rose-700 mt-0.5 block">—</span>
                            </div>
                        </div>
                        <div class="pt-2 flex items-center gap-2 text-xs text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            <span>Candidates Sat: <strong id="cd-candidates" class="font-mono text-slate-700">0</strong></span>
                        </div>
                    </div>

                    {{-- Trend chart --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Yearly Performance Trend</h4>
                        <div class="relative h-60">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    {{-- Series high/low performers --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Series-wise Breakdown</h4>
                        <div id="series-performers-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {{-- Dynamically filled --}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══ DATA INJECTION ══ --}}
<script>
    const qualificationData = @json($qualifications);
    const componentStats    = @json($componentAnalysis);
    const trendsData        = @json($componentTrends);

    // --- State ---
    let activeSubject   = null; // { id, subject_name, subject_code, components, qual_id, qual_name, qual_type }
    let activeSetKey    = null; // 'YYYY-YYYY' or 'default'
    let trendChart      = null;
    let overviewChart   = null;

    // ── Initialise: render subject tiles ──────────────────────────────────────
    function init() {
        renderSubjectTiles(null, null);
        populateSubjectFilter();
    }

    function populateSubjectFilter() {
        // already injected via PHP; do nothing (subjects in qual filter)
    }

    // ── Filters ───────────────────────────────────────────────────────────────
    function applyFilters() {
        const qualId   = document.getElementById('qual-filter').value;
        const subjId   = document.getElementById('subj-filter').value;

        // Update subject dropdown options based on qual
        const subjSel = document.getElementById('subj-filter');
        subjSel.innerHTML = '<option value="">All Subjects</option>';
        if (qualId) {
            const qual = qualificationData.find(q => q.id == qualId);
            if (qual) {
                qual.subjects.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id;
                    o.textContent = `${s.subject_name} (${s.subject_code})`;
                    subjSel.appendChild(o);
                });
            }
        } else {
            // All subjects across all qualifications
            qualificationData.forEach(qual => {
                qual.subjects.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id;
                    o.textContent = `${s.subject_name} (${s.subject_code})`;
                    subjSel.appendChild(o);
                });
            });
        }
        // Restore selected value if exists
        if (subjId) subjSel.value = subjId;

        resetToSubjectGrid();
        renderSubjectTiles(qualId || null, subjSel.value || null);
    }

    function clearFilters() {
        document.getElementById('qual-filter').value = '';
        document.getElementById('subj-filter').innerHTML = '<option value="">All Subjects</option>';
        // Populate with all subjects
        qualificationData.forEach(qual => {
            qual.subjects.forEach(s => {
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = `${s.subject_name} (${s.subject_code})`;
                document.getElementById('subj-filter').appendChild(o);
            });
        });
        resetToSubjectGrid();
        renderSubjectTiles(null, null);
    }

    // ── View helpers ──────────────────────────────────────────────────────────
    function showView(name) {
        ['view-subjects', 'view-ranges', 'view-stats'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });
        document.getElementById('view-' + name).classList.remove('hidden');

        // breadcrumb
        const bc = document.getElementById('breadcrumb-nav');
        if (name === 'subjects') {
            bc.classList.add('hidden');
        } else {
            bc.classList.remove('hidden');
            bc.classList.add('flex');
            if (name === 'ranges' || name === 'stats') {
                document.getElementById('bc-subject-sep').classList.remove('hidden');
                document.getElementById('bc-subject-name').classList.remove('hidden');
                document.getElementById('bc-subject-name').textContent = activeSubject ? `${activeSubject.subject_name} (${activeSubject.subject_code})` : '';
            }
            if (name === 'stats') {
                document.getElementById('bc-range-sep').classList.remove('hidden');
                document.getElementById('bc-range-name').classList.remove('hidden');
            } else {
                document.getElementById('bc-range-sep').classList.add('hidden');
                document.getElementById('bc-range-name').classList.add('hidden');
            }
        }
    }

    function resetToSubjectGrid() {
        activeSubject = null;
        activeSetKey  = null;
        showView('subjects');
    }

    function goBackToRanges() {
        showView('ranges');
    }

    // ── QUAL TYPE COLORS ──────────────────────────────────────────────────────
    function qualColors(qualType) {
        if (!qualType) return { bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200', accent: '#6366f1', tile: 'bg-slate-50' };
        const t = qualType.toUpperCase();
        if (t.includes('IGCSE')) return { bg: 'bg-cyan-100', text: 'text-cyan-700', border: 'border-cyan-200', accent: '#0891b2', tile: 'bg-cyan-50/40' };
        if (t.includes('AS') || t.includes('A LEVEL') || t.includes('GCE')) return { bg: 'bg-violet-100', text: 'text-violet-700', border: 'border-violet-200', accent: '#7c3aed', tile: 'bg-violet-50/40' };
        return { bg: 'bg-indigo-100', text: 'text-indigo-700', border: 'border-indigo-200', accent: '#4f46e5', tile: 'bg-indigo-50/40' };
    }

    // ── VIEW 1: Render Subject Tiles ──────────────────────────────────────────
    function renderSubjectTiles(filterQualId, filterSubjId) {
        const grid  = document.getElementById('subjects-grid');
        const empty = document.getElementById('subjects-empty');
        const count = document.getElementById('subjects-count');
        grid.innerHTML = '';

        // Gather all subject entries, sorted by subject_name
        let entries = [];
        qualificationData.forEach(qual => {
            qual.subjects.forEach(s => {
                if (filterQualId && qual.id != filterQualId) return;
                if (filterSubjId && s.id != filterSubjId) return;
                entries.push({ ...s, qual_id: qual.id, qual_name: qual.qualification_name, qual_type: qual.qualification_type });
            });
        });
        entries.sort((a, b) => a.subject_name.localeCompare(b.subject_name));

        count.textContent = `${entries.length} Subject${entries.length !== 1 ? 's' : ''}`;

        if (entries.length === 0) {
            empty.classList.remove('hidden');
            return;
        }
        empty.classList.add('hidden');

        entries.forEach(subj => {
            const colors = qualColors(subj.qual_type);
            // Count total components across all sets
            const totalComps = (subj.components || []).length;
            // Count sets
            const sets = groupComponentsBySets(subj.components || []);
            const setsCount = Object.keys(sets).length;

            const tile = document.createElement('button');
            tile.type = 'button';
            tile.className = `${colors.tile} border ${colors.border} rounded-2xl p-4 text-left hover:shadow-md hover:scale-[1.02] transition-all duration-150 group space-y-3`;
            tile.onclick = () => selectSubject(subj);

            tile.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <span class="inline-block px-2 py-0.5 ${colors.bg} ${colors.text} text-[9px] font-black rounded-md uppercase tracking-wider font-mono">${subj.subject_code}</span>
                    <span class="inline-block px-1.5 py-0.5 ${colors.bg} ${colors.text} text-[8px] font-bold rounded uppercase tracking-wider opacity-80">${subj.qual_type || ''}</span>
                </div>
                <div>
                    <h5 class="text-xs font-black text-slate-800 group-hover:text-indigo-900 transition leading-snug" title="${subj.subject_name}">${subj.subject_name}</h5>
                </div>
                <div class="flex items-center gap-2 pt-1 border-t ${colors.border}">
                    <span class="text-[10px] text-slate-500 font-bold">${totalComps} component${totalComps !== 1 ? 's' : ''}</span>
                    <span class="text-slate-300">·</span>
                    <span class="text-[10px] text-slate-500 font-bold">${setsCount} set${setsCount !== 1 ? 's' : ''}</span>
                </div>
            `;
            grid.appendChild(tile);
        });
    }

    // ── VIEW 2: Render Year Range Tiles ───────────────────────────────────────
    function selectSubject(subj) {
        activeSubject = subj;
        document.getElementById('ranges-title').textContent = `${subj.subject_name} (${subj.subject_code}) – Component Sets`;

        const grid = document.getElementById('ranges-grid');
        grid.innerHTML = '';

        const sets = groupComponentsBySets(subj.components || []);
        const colors = qualColors(subj.qual_type);
        const setKeys = Object.keys(sets).sort((a, b) => {
            // Sort: put 'default' last, others by start year desc
            if (a === 'default') return 1;
            if (b === 'default') return -1;
            return parseInt(b.split('-')[0]) - parseInt(a.split('-')[0]);
        });

        setKeys.forEach(setKey => {
            const comps = sets[setKey];
            const first = comps[0];
            const startYear = first.component_set ? first.component_set.start_year : null;
            const endYear   = first.component_set ? first.component_set.end_year   : null;
            const rangeLabel = startYear && endYear ? `${startYear} – ${endYear}`
                             : startYear ? `${startYear} onwards`
                             : 'All Years (Default)';
            const setLabel   = first.component_set ? first.component_set.label : null;

            // Count how many of these components have actual data
            let dataCount = 0;
            comps.forEach(c => {
                const key = c.id;
                if (componentStats[key]) dataCount++;
            });

            const tile = document.createElement('button');
            tile.type = 'button';
            tile.className = `${colors.tile} border ${colors.border} rounded-2xl p-5 text-left hover:shadow-md hover:scale-[1.02] transition-all duration-150 group space-y-3`;
            tile.onclick = () => selectYearRange(setKey, comps);

            tile.innerHTML = `
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg ${colors.bg} flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 ${colors.text}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                    </div>
                    <span class="text-sm font-black text-slate-800 group-hover:text-indigo-900 transition">${rangeLabel}</span>
                </div>
                ${setLabel ? `<p class="text-[10px] text-slate-400 font-bold">${setLabel}</p>` : ''}
                <div class="flex items-center gap-3 pt-2 border-t ${colors.border}">
                    <span class="text-[10px] font-bold text-slate-600">${comps.length} component${comps.length !== 1 ? 's' : ''}</span>
                    ${dataCount > 0 
                        ? `<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">${dataCount} with data</span>`
                        : `<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400">No data yet</span>`}
                </div>
            `;
            grid.appendChild(tile);
        });

        showView('ranges');
    }

    // ── VIEW 3: Statistics Panel ──────────────────────────────────────────────
    function selectYearRange(setKey, comps) {
        activeSetKey = setKey;
        const subj = activeSubject;

        const first = comps[0];
        const startYear = first.component_set ? first.component_set.start_year : null;
        const endYear   = first.component_set ? first.component_set.end_year   : null;
        const rangeLabel = startYear && endYear ? `${startYear} – ${endYear}` : startYear ? `${startYear} onwards` : 'All Years';

        document.getElementById('stats-title').textContent = `${subj.subject_name} – ${rangeLabel}`;
        document.getElementById('stats-subtitle').textContent = `${comps.length} component${comps.length !== 1 ? 's' : ''} · ${subj.qual_type || ''}`;
        document.getElementById('bc-range-name').textContent = rangeLabel;

        // Build component list on left
        const listEl = document.getElementById('comp-list');
        listEl.innerHTML = '';
        document.getElementById('comp-count-badge').textContent = `${comps.length} Paper${comps.length !== 1 ? 's' : ''}`;

        // Reset detail view
        document.getElementById('comp-overview-card').classList.remove('hidden');
        document.getElementById('comp-detail-card').classList.add('hidden');

        // Gather stats for all comps in this set
        const enriched = comps.map(c => {
            const key = c.id;  // component UUID — unique across all sets
            const stats = componentStats[key] || null;
            return {
                id: c.id,       // preserve UUID for trendsData lookup
                code: c.component_code,
                name: c.component_name,
                label: c.component_label || null,
                total_marks: c.total_marks,
                subject_id: subj.id,
                component_set: c.component_set || null,
                has_data: !!stats,
                ...(stats || { candidate_count:0, avg_marks:0, avg_percentage:0, highest:0, lowest:0, median:0 })
            };
        }).sort((a, b) => a.code.localeCompare(b.code, undefined, { numeric: true }));

        enriched.forEach((c, idx) => {
            const displayName = c.label || c.name || `Component ${c.code}`;
            const subText     = (c.label && c.name && c.label !== c.name) ? c.name : '';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.id   = `comp-btn-${idx}`;
            btn.className = 'comp-tile-btn w-full text-left bg-white p-3.5 rounded-2xl border border-slate-200 transition duration-150 shadow-sm hover:shadow hover:border-slate-300 flex justify-between items-start gap-2 group';
            btn.onclick = () => showComponentDetail(c, btn);

            const colors = qualColors(subj.qual_type);
            btn.innerHTML = `
                <div class="space-y-1 min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-block px-1.5 py-0.5 rounded ${colors.bg} border ${colors.border} text-[9px] font-mono font-black ${colors.text}">${c.code}</span>
                        <span class="text-[9px] font-bold text-slate-400 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded">${c.total_marks} Marks</span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 group-hover:text-indigo-900 transition leading-tight" title="${displayName}">${displayName}</p>
                    ${subText ? `<p class="text-[9px] text-slate-400 font-medium">${subText}</p>` : ''}
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[9px] text-slate-400 font-semibold block">Avg</span>
                    <span class="text-xs font-extrabold ${c.has_data ? 'text-indigo-700' : 'text-slate-300'}">${c.has_data ? Math.round(c.avg_percentage) + '%' : 'N/A'}</span>
                </div>
            `;
            listEl.appendChild(btn);
        });

        // Render overview chart
        renderOverviewChart(enriched, subj);

        showView('stats');
    }

    // ── Component Detail ───────────────────────────────────────────────────────
    function showComponentDetail(component, btnEl) {
        // Highlight selected
        document.querySelectorAll('.comp-tile-btn').forEach(b => {
            b.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500/20', 'bg-indigo-50/30');
        });
        if (btnEl) {
            btnEl.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500/20', 'bg-indigo-50/30');
        }

        // Show detail card, hide overview chart
        document.getElementById('comp-overview-card').classList.add('hidden');
        document.getElementById('comp-detail-card').classList.remove('hidden');

        const displayName = component.label || component.name || `Component ${component.code}`;
        const subTitle    = (component.label && component.name && component.label !== component.name) ? component.name : '';

        document.getElementById('cd-code').textContent          = component.code;
        document.getElementById('cd-name').textContent          = displayName;
        document.getElementById('cd-subname').textContent       = subTitle;
        document.getElementById('cd-total-marks-badge').textContent = `${component.total_marks} Marks`;
        document.getElementById('cd-candidates').textContent    = component.candidate_count || 0;

        const key = component.id;  // component UUID
        const trend = trendsData[key];

        // Year range from set data
        const startYear = component.component_set ? component.component_set.start_year : (component.start_year || null);
        const endYear   = component.component_set ? component.component_set.end_year   : (component.end_year || null);
        document.getElementById('cd-years').textContent = (startYear && endYear) ? `${startYear} – ${endYear}` : startYear ? `${startYear}+` : 'All Years';

        if (!component.has_data) {
            document.getElementById('cd-avg').textContent    = 'N/A';
            document.getElementById('cd-median').textContent = 'N/A';
            document.getElementById('cd-highest').textContent = 'N/A';
            document.getElementById('cd-lowest').textContent  = 'N/A';
            document.getElementById('series-performers-grid').innerHTML = '<div class="col-span-full text-center text-slate-400 italic py-8 text-xs">No candidate marks data yet for this component.</div>';
            if (trendChart) { trendChart.destroy(); trendChart = null; }
            return;
        }

        document.getElementById('cd-avg').textContent    = `${Math.round(component.avg_percentage)}%`;
        document.getElementById('cd-median').textContent = `${component.median} / ${component.total_marks}`;

        if (trend) {
            document.getElementById('cd-highest').innerHTML = `
                <span class="block text-emerald-700 font-black">${component.highest}/${component.total_marks}</span>
                <span class="block text-[9px] text-slate-400 font-semibold truncate">${trend.highest_candidate || ''}</span>
            `;
            document.getElementById('cd-lowest').innerHTML = `
                <span class="block text-rose-700 font-black">${component.lowest}/${component.total_marks}</span>
                <span class="block text-[9px] text-slate-400 font-semibold truncate">${trend.lowest_candidate || ''}</span>
            `;
        }

        // Render trend chart
        renderTrendChart(component, trend);

        // Series performer tiles
        const grid = document.getElementById('series-performers-grid');
        grid.innerHTML = '';
        if (trend && trend.series_trends && trend.series_trends.length > 0) {
            trend.series_trends.forEach(row => {
                const tile = document.createElement('div');
                tile.className = 'bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between';
                tile.innerHTML = `
                    <div class="flex justify-between items-start mb-3 border-b border-slate-100 pb-2">
                        <span class="font-extrabold text-slate-800 text-sm">${row.series}</span>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-black px-2 py-0.5 rounded-md">${row.avg_pct}% Avg</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-emerald-50/60 rounded-xl p-2 border border-emerald-100">
                            <span class="block text-[9px] font-black text-emerald-600/80 uppercase tracking-wider mb-0.5">Highest</span>
                            <span class="block font-extrabold text-emerald-700 text-sm leading-none">${Math.round(row.max_score)}/${component.total_marks}</span>
                            <span class="block text-[9px] text-slate-400 font-semibold mt-1 truncate" title="${row.max_candidate}">${row.max_candidate}</span>
                        </div>
                        <div class="bg-rose-50/60 rounded-xl p-2 border border-rose-100">
                            <span class="block text-[9px] font-black text-rose-600/80 uppercase tracking-wider mb-0.5">Lowest</span>
                            <span class="block font-extrabold text-rose-700 text-sm leading-none">${Math.round(row.min_score)}/${component.total_marks}</span>
                            <span class="block text-[9px] text-slate-400 font-semibold mt-1 truncate" title="${row.min_candidate}">${row.min_candidate}</span>
                        </div>
                    </div>
                `;
                grid.appendChild(tile);
            });
        } else {
            grid.innerHTML = '<div class="col-span-full text-center text-slate-400 italic py-4 text-xs">No series-level data available.</div>';
        }
    }

    // ── Charts ─────────────────────────────────────────────────────────────────
    function renderOverviewChart(enrichedComps, subj) {
        if (overviewChart) { overviewChart.destroy(); overviewChart = null; }

        const withData = enrichedComps.filter(c => c.has_data);
        if (withData.length === 0) {
            document.getElementById('allComponentsChart').getContext('2d').clearRect(0, 0, 1000, 1000);
            return;
        }

        const allSeriesSet = new Set();
        withData.forEach(c => {
            const key = c.id;  // component UUID
            const trend = trendsData[key];
            if (trend && trend.series_trends) trend.series_trends.forEach(s => allSeriesSet.add(s.series));
        });

        const monthOrder = { 'March': 1, 'June': 2, 'November': 3 };
        const seriesLabels = Array.from(allSeriesSet).sort((a, b) => {
            const [mA, yA] = a.split(' '); const [mB, yB] = b.split(' ');
            if (yA !== yB) return yA - yB;
            return (monthOrder[mA] || 0) - (monthOrder[mB] || 0);
        });

        const palette = ['#4f46e5','#0891b2','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#64748b'];
        const datasets = [];

        withData.forEach((c, idx) => {
            const key = c.id;  // component UUID
            const trend = trendsData[key];
            if (!trend || !trend.series_trends) return;
            const dataPoints = seriesLabels.map(lbl => {
                const found = trend.series_trends.find(s => s.series === lbl);
                return found ? found.avg_pct : null;
            });
            const color = palette[idx % palette.length];
            const label = c.label || c.name || `C${c.code}`;
            datasets.push({ label, data: dataPoints, borderColor: color, backgroundColor: color, borderWidth: 2.5, pointRadius: 4, tension: 0.3, spanGaps: true });
        });

        const ctx = document.getElementById('allComponentsChart').getContext('2d');
        overviewChart = new Chart(ctx, {
            type: 'line',
            data: { labels: seriesLabels, datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { size: 10 } } },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { min: 0, max: 100, grid: { color: '#f1f5f9' }, title: { display: true, text: 'Average %', font: { size: 10 } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function renderTrendChart(component, trend) {
        if (trendChart) { trendChart.destroy(); trendChart = null; }
        if (!trend || !trend.series_trends || trend.series_trends.length === 0) return;

        const labels = trend.series_trends.map(t => t.series);
        const datasetData = trend.series_trends.map(t => t.avg_pct);

        const ctx = document.getElementById('trendChart').getContext('2d');
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Average Performance %',
                    data: datasetData,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.06)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, max: 100, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ── Utility: Group components by their component_set ──────────────────────
    function groupComponentsBySets(components) {
        const sets = {};
        components.forEach(c => {
            const startYear = c.component_set ? c.component_set.start_year : null;
            const endYear   = c.component_set ? c.component_set.end_year   : null;
            let key = (startYear && endYear) ? `${startYear}-${endYear}` : (startYear ? `${startYear}-open` : 'default');
            if (!sets[key]) sets[key] = [];
            sets[key].push(c);
        });
        return sets;
    }

    // ── Boot ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        init();
        // Populate subject filter dropdown on load
        clearFilters();
    });
</script>
@endsection
