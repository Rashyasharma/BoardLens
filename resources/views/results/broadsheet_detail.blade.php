@extends('layouts.app')

@section('title', 'Broadsheet View')
@section('page-title', 'Broadsheet Detail')

@section('content')
<div class="space-y-5 max-w-full">
    
    {{-- Header Options Bar --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-150 flex flex-wrap items-center justify-between gap-4 print:hidden">
        {{-- Left side: Breadcrumb & Title --}}
        <div>
            <a href="{{ route('results.broadsheet') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                ← Back to All Broadsheets
            </a>
            <h2 class="text-base font-bold text-slate-800 font-display mt-1">Detailed Broadsheet</h2>
        </div>

        {{-- Actions: Export to Excel & Print --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('results.broadsheet.export', [$series->id, $qualification->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition flex items-center gap-1.5">
                📥 Export to Excel
            </a>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition">
                🖨️ Print Broadsheet
            </button>
        </div>
    </div>

    {{-- Broadsheet Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-150 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 print:border-b-2 print:border-black flex flex-wrap items-center justify-between gap-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm flex-1">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Centre No.</span>
                    <div class="font-extrabold text-slate-850">{{ auth()->user()->school->centre_number ?? 'IN016' }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Centre Name</span>
                    <div class="font-extrabold text-slate-850">{{ auth()->user()->school->name ?? 'Lucky International School' }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Qualification</span>
                    <div class="font-extrabold text-slate-850">{{ $qualification->type_display }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Session</span>
                    <div class="font-extrabold text-slate-850">
                        @switch($series->month)
                            @case('March') Feb/Mar {{ $series->year }} @break
                            @case('June') May/Jun {{ $series->year }} @break
                            @case('November') Oct/Nov {{ $series->year }} @break
                            @default {{ $series->month }} {{ $series->year }}
                        @endswitch
                    </div>
                </div>
            </div>

            {{-- 3-Way Display Toggle: Grade / PUM / Grade(PUM) --}}
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 shrink-0 print:hidden" id="view-mode-toggle-container">
                <button type="button" onclick="setViewMode('grade')" id="btn-view-grade" class="px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white shadow-sm text-slate-800">
                    Grade
                </button>
                <button type="button" onclick="setViewMode('pum')" id="btn-view-pum" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-850">
                    PUM
                </button>
                <button type="button" onclick="setViewMode('combined')" id="btn-view-combined" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-850">
                    Grade(PUM)
                </button>
            </div>
        </div>

        {{-- Broadsheet Table --}}
        <div class="overflow-auto max-h-[70vh] border-b border-slate-200" id="broadsheet-scroll-container">
            <table class="border-collapse text-sm w-full" id="broadsheet-table" style="table-layout: fixed;">
                <colgroup>
                    <col style="width: 90px;">
                    <col style="width: 220px;">
                    @foreach($subjects as $sub)
                        <col style="width: 50px;">
                    @endforeach
                </colgroup>
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="bg-white px-3 py-3 text-left border-r border-slate-200 align-bottom sticky left-0 z-20" style="min-width:90px;">
                            <span class="text-xs font-bold text-slate-650 uppercase tracking-wider">Cand. No</span>
                        </th>
                        <th class="bg-white px-3 py-3 text-left border-r border-slate-200 align-bottom sticky left-[90px] z-20" style="min-width:220px;">
                            <span class="text-xs font-bold text-slate-650 uppercase tracking-wider">Candidate Name</span>
                        </th>
                        @foreach($subjects as $sub)
                            <th class="bg-white px-1 py-3 text-center border-l border-slate-100 align-bottom cursor-pointer hover:bg-slate-50 transition"
                                style="height: 10rem; min-width:50px;"
                                id="header-subj-{{ $sub->id }}"
                                onclick="toggleColumnHighlight('{{ $sub->id }}')">
                                <div class="broadsheet-subject-header">
                                    <span class="text-[10px] font-extrabold text-slate-650 uppercase tracking-wide" title="{{ $sub->subject_name }}">{{ $sub->subject_name }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($candidates as $cand)
                        <tr class="hover:bg-emerald-50/10 transition duration-700" id="row-cand-{{ $cand['candidate_no'] }}">
                            <td class="bg-white border-r border-slate-150 px-3 py-2 cursor-pointer sticky left-0 z-10 font-mono text-xs font-bold text-slate-700 whitespace-nowrap" style="min-width:90px;" onclick="toggleRowHighlight('{{ $cand['candidate_no'] }}')">
                                {{ $cand['candidate_no'] }}
                            </td>
                            <td class="bg-white border-r border-slate-150 px-3 py-2 cursor-pointer sticky left-[90px] z-10 text-xs font-bold text-slate-800 uppercase whitespace-nowrap" style="min-width:220px;" onclick="toggleRowHighlight('{{ $cand['candidate_no'] }}')">
                                {{ $cand['candidate_name'] }}
                            </td>
                            @foreach($subjects as $sub)
                                @php
                                    $grade = $cand['grades'][$sub->id] ?? null;
                                    $pum = $cand['pums'][$sub->id] ?? null;
                                    $displayGrade = '';
                                    if ($grade !== null) {
                                        $displayGrade = in_array($grade, ['a', 'b', 'c', 'd', 'e']) ? $grade . '^' : $grade;
                                    }
                                    $displayPum = ($pum !== null) ? $pum : '';
                                    
                                    // Highlighting rule: treat U grade PUM as 0, highlight yellow if PUM is < 33
                                    $isLowPum = ($pum !== null && $pum !== '' && is_numeric($pum) && (float)$pum < 33);
                                @endphp
                                <td class="px-1 py-2 text-center border-l border-slate-100 broadsheet-cell col-subj-{{ $sub->id }} {{ $isLowPum ? 'bg-amber-100/75 print:bg-amber-100 font-bold' : '' }}"
                                    data-grade="{{ $displayGrade }}"
                                    data-pum="{{ $displayPum }}">
                                    @if($grade !== null)
                                        <span class="cell-value text-xs font-extrabold {{ in_array($grade, ['A*', 'A*A*', 'A', 'AA', 'B', 'BB', 'C', 'CC', 'a', 'b', 'c']) ? 'text-slate-800' : (in_array($grade, ['D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG']) ? 'text-slate-650' : (in_array($grade, ['U', 'UU', 'u']) ? 'text-rose-600' : 'text-indigo-650')) }}">
                                            {{ $displayGrade }}
                                        </span>
                                    @else
                                        <span class="cell-value text-xs font-extrabold text-slate-300"></span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>

                {{-- Footers --}}
                @if($presentGrades->isNotEmpty())
                    <tfoot class="border-t-2 border-slate-300 bg-slate-50/50 print:bg-white text-xs font-bold text-slate-700" id="broadsheet-tfoot-grade">
                        @foreach($presentGrades as $g)
                            <tr class="hover:bg-slate-100/50 transition border-b border-slate-150">
                                <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                                    Grade {{ in_array($g, ['a', 'b', 'c', 'd', 'e']) ? $g . ' (AS Level)' : $g }}
                                </td>
                                @foreach($subjects as $sub)
                                    <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-slate-800 col-subj-{{ $sub->id }}">
                                        {{ $statsMap[$sub->id][$g] ?: '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-indigo-50/30 text-indigo-750 font-black border-t border-slate-200">
                            <td colspan="2" class="sticky left-0 z-10 bg-indigo-50/40 border-r border-slate-200 px-4 py-2.5 text-right text-indigo-805">
                                Total Candidates (Sat)
                            </td>
                            @foreach($subjects as $sub)
                                <td class="px-1 py-2.5 text-center border-l border-slate-100 font-black text-indigo-700 col-subj-{{ $sub->id }}">
                                    {{ $statsMap[$sub->id]['total_sat'] ?: '0' }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
                
                @if(isset($pumStats) && count($pumStats) > 0)
                    <tfoot class="border-t-2 border-slate-300 bg-slate-50/50 print:bg-white text-xs font-bold text-slate-700 hidden" id="broadsheet-tfoot-pum">
                        <tr class="hover:bg-slate-100/50 transition border-b border-slate-150">
                            <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                                Highest PUM
                            </td>
                            @foreach($subjects as $sub)
                                <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-slate-800 col-subj-{{ $sub->id }}">
                                    {{ $pumStats[$sub->id]['highest'] }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="hover:bg-slate-100/50 transition border-b border-slate-150">
                            <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                                Lowest PUM
                            </td>
                            @foreach($subjects as $sub)
                                <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-slate-800 col-subj-{{ $sub->id }}">
                                    {{ $pumStats[$sub->id]['lowest'] }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-indigo-50/30 text-indigo-750 font-black border-t border-slate-200">
                            <td colspan="2" class="sticky left-0 z-10 bg-indigo-50/40 border-r border-slate-200 px-4 py-2.5 text-right text-indigo-805">
                                Average PUM
                            </td>
                            @foreach($subjects as $sub)
                                <td class="px-1 py-2.5 text-center border-l border-slate-100 font-black text-indigo-700 col-subj-{{ $sub->id }}">
                                    {{ $pumStats[$sub->id]['average'] }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Footer summary --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between text-xs text-slate-500 font-semibold print:hidden">
            <span>{{ $candidates->count() }} candidates &bull; {{ $subjects->count() }} subjects</span>
        </div>
    </div>
</div>

<script>
    let currentViewMode = 'grade';

    // ── Grade / PUM / Combined Toggle ──────────────────────────────────
    function setViewMode(mode) {
        if (mode === currentViewMode) return;
        currentViewMode = mode;

        const gradeBtn    = document.getElementById('btn-view-grade');
        const pumBtn      = document.getElementById('btn-view-pum');
        const combinedBtn = document.getElementById('btn-view-combined');
        if (!gradeBtn || !pumBtn || !combinedBtn) return;

        const activeClass   = 'px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white shadow-sm text-slate-850';
        const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-850';

        gradeBtn.className    = mode === 'grade' ? activeClass : inactiveClass;
        pumBtn.className      = mode === 'pum' ? activeClass : inactiveClass;
        combinedBtn.className = mode === 'combined' ? activeClass : inactiveClass;

        // Toggle footer sections
        const gradeFoot = document.getElementById('broadsheet-tfoot-grade');
        const pumFoot   = document.getElementById('broadsheet-tfoot-pum');
        if (gradeFoot) gradeFoot.classList.toggle('hidden', mode === 'pum');
        if (pumFoot)   pumFoot.classList.toggle('hidden',   mode !== 'pum');

        // Update cells
        document.querySelectorAll('.broadsheet-cell').forEach(cell => {
            const span = cell.querySelector('.cell-value');
            if (!span) return;

            const grade = cell.dataset.grade || '';
            const pum   = cell.dataset.pum   || '';
            
            let val = '';
            if (mode === 'grade') {
                val = grade;
            } else if (mode === 'pum') {
                val = pum;
            } else if (mode === 'combined') {
                if (grade !== '' && pum !== '' && pum !== 'N/A') {
                    val = `${grade}(${pum})`;
                } else {
                    val = grade;
                }
            }
            span.textContent = val;

            // Colour styling logic
            if (val === '' || val === 'N/A') {
                span.className = 'cell-value text-xs font-extrabold text-slate-300';
            } else if (val === '0' || val === '0.0') {
                span.className = 'cell-value text-xs font-extrabold text-rose-600';
            } else if (mode === 'grade' || mode === 'combined') {
                const clean = grade.endsWith('^') ? grade.slice(0, -1) : grade;
                const colour = ['A*','A','B','C','a','b','c'].includes(clean) ? 'text-slate-800'
                             : ['D','E','d','e'].includes(clean) ? 'text-slate-650'
                             : (clean === 'U' || clean === 'u') ? 'text-rose-600'
                             : 'text-indigo-650';
                span.className = 'cell-value text-xs font-extrabold ' + colour;
            } else {
                span.className = 'cell-value text-xs font-extrabold text-indigo-650';
            }
        });
    }

    // ── Row / Column highlight ──────────────────────────────────────────
    function toggleRowHighlight(candNo) {
        const row = document.getElementById('row-cand-' + candNo);
        if (row) row.classList.toggle('row-highlighted');
    }

    function toggleColumnHighlight(subjectId) {
        const header = document.getElementById('header-subj-' + subjectId);
        if (!header) return;
        header.classList.toggle('col-highlighted');
        header.classList.toggle('bg-indigo-50/50');
        document.querySelectorAll('.col-subj-' + subjectId).forEach(cell => {
            cell.classList.toggle('col-highlighted');
        });
    }
</script>

<style>
    /* Table scrolling and Sticky columns/headers styling */
    #broadsheet-table { border-collapse: collapse; }
    
    #broadsheet-table thead th {
        position: sticky;
        top: 0;
        z-index: 15;
        background-color: #ffffff;
    }
    /* Set higher z-index for double sticky corner cells */
    #broadsheet-table thead th:first-child {
        z-index: 25;
        position: sticky;
        left: 0;
    }
    #broadsheet-table thead th:nth-child(2) {
        z-index: 25;
        position: sticky;
        left: 90px;
    }

    /* Column/Row highlighting styles - premium green */
    .row-highlighted td { background-color: #d1fae5 !important; }
    .col-highlighted { background-color: #ecfdf5 !important; }
    .row-highlighted.col-highlighted { background-color: #a7f3d0 !important; }

    /* Vertical Subject Header Text */
    .broadsheet-subject-header {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 9rem;
        padding: 0.25rem 0;
        margin: 0 auto;
    }

    /* Sticky columns shadow/styling */
    #broadsheet-table td.sticky,
    #broadsheet-table td[class*="sticky"] { background-color: #ffffff !important; }
    #broadsheet-table tr:hover td.sticky,
    #broadsheet-table tr:hover td[class*="sticky"] { background-color: #f8fafc !important; }

    @media print {
        .print\:hidden { display: none !important; }
        .sticky { position: static !important; }
        #broadsheet-table thead th { position: static !important; }
        table { font-size: 8pt; width: 100%; table-layout: auto !important; }
        th, td { padding: 4px 6px !important; border: 1px solid #e2e8f0 !important; }
        .broadsheet-subject-header { height: 7rem; }
    }
</style>
@endsection
