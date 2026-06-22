@extends('layouts.app')

@section('title', 'CBSE Broadsheet')
@section('page-title', 'CBSE Broadsheet')

@section('content')
<div class="space-y-5 max-w-full">

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-150 print:hidden">
        <form method="GET" action="{{ route('cbse.analysis.broadsheet') }}" class="flex flex-wrap items-end gap-4">
            <div class="space-y-1.5 flex-1 min-w-[200px]">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition" required>
                    <option value="">Choose Year</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1.5 flex-1 min-w-[200px]">
                <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Qualification</label>
                <select name="qualification_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition" required>
                    <option value="">Choose Class</option>
                    @foreach($qualifications as $q)
                        <option value="{{ $q->id }}" {{ $qualificationId == $q->id ? 'selected' : '' }}>{{ $q->type_label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition">
                Generate
            </button>
        </form>
    </div>

    @if($candidates->isNotEmpty())
    {{-- Broadsheet Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-150 overflow-hidden">

        {{-- Header with info + toggle --}}
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 print:border-b-2 print:border-black">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm flex-1">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Qualification</span>
                    <div class="font-extrabold text-slate-800">{{ $selectedQualification->type_label ?? '—' }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Session</span>
                    <div class="font-extrabold text-slate-800">{{ $selectedYear->name ?? '—' }}</div>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Candidates</span>
                    <div class="font-extrabold text-slate-800">{{ $candidates->count() }}</div>
                </div>
            </div>

            {{-- 3-Way Display Toggle --}}
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 shrink-0 print:hidden" id="view-mode-toggle">
                <button type="button" onclick="setViewMode('marks')" id="btn-view-marks" class="px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white shadow-sm text-slate-800">
                    Marks
                </button>
                <button type="button" onclick="setViewMode('grade')" id="btn-view-grade" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-800">
                    Grade
                </button>
                <button type="button" onclick="setViewMode('combined')" id="btn-view-combined" class="px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-800">
                    Grade(Marks)
                </button>
            </div>
        </div>

        {{-- Sort Toolbar --}}
        <div class="px-6 py-2.5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center gap-2 print:hidden">
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mr-1">Sort by:</span>
            <button onclick="sortTable('name')" id="sort-name" class="sort-btn active-sort px-3 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 bg-white text-slate-700 hover:border-amber-400 hover:text-amber-700 transition">Name</button>
            <button onclick="sortTable('roll')" id="sort-roll" class="sort-btn px-3 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 bg-white text-slate-700 hover:border-amber-400 hover:text-amber-700 transition">Roll No.</button>
            <button onclick="sortTable('pct')" id="sort-pct" class="sort-btn px-3 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 bg-white text-slate-700 hover:border-amber-400 hover:text-amber-700 transition">Overall %</button>
            <button onclick="sortTable('top5')" id="sort-top5" class="sort-btn px-3 py-1.5 rounded-lg text-[11px] font-bold border border-slate-200 bg-white text-slate-700 hover:border-amber-400 hover:text-amber-700 transition">Top 5 %</button>
        </div>

        {{-- Search & Pagination Toolbar --}}
        <div class="px-6 py-3 border-b border-slate-100 bg-white flex flex-wrap items-center justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <input type="text" id="search-candidate" placeholder="Search candidate or roll no..." class="bg-slate-50 border border-slate-250 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 w-full md:w-56" onkeyup="applyFilters()">
                <input type="text" id="search-subject" placeholder="Search subject..." class="bg-slate-50 border border-slate-250 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 w-full md:w-48" onkeyup="applyFilters()">
            </div>
            <div class="flex items-center gap-4 text-xs font-bold text-slate-600">
                <span id="candidate-count"></span>
            </div>
        </div>

        {{-- Print-only header --}}
        <div class="hidden print:block px-6 py-3 text-center border-b-2 border-black">
            <div class="text-lg font-black uppercase">CBSE Broadsheet — {{ $selectedQualification->type_label ?? '' }}</div>
            <div class="text-sm font-bold text-slate-600">{{ $selectedYear->name ?? '' }} • {{ $candidates->count() }} Candidates • {{ $subjects->count() }} Subjects</div>
        </div>

        {{-- Broadsheet Table --}}
        <div class="overflow-auto" style="max-height: calc(100vh - 100px);" id="broadsheet-scroll-container">
            <table class="text-sm w-full" id="broadsheet-table" style="table-layout: fixed;">
                <colgroup>
                    <col style="width: 90px;">
                    <col style="width: 200px;">
                    @foreach($subjects as $sub)
                        <col style="width: 55px;">
                    @endforeach
                    <col style="width: 80px;">
                    <col style="width: 50px;">
                    <col style="width: 80px;">
                    <col style="width: 50px;">
                </colgroup>
                <thead>
                    <tr>
                        {{-- Sticky corner: Roll No --}}
                        <th class="bg-white px-2 py-3 text-left align-bottom sticky top-0 left-0 z-30 border-b-2 border-r border-slate-200"
                            style="min-width:60px;">
                            <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Roll No</span>
                        </th>
                        {{-- Sticky corner: Name --}}
                        <th class="bg-white px-3 py-3 text-left align-bottom sticky top-0 left-[60px] z-30 border-b-2 border-r border-slate-200"
                            style="min-width:200px;">
                            <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Candidate Name</span>
                        </th>
                        {{-- Subject columns --}}
                        @foreach($subjects as $sub)
                            <th class="bg-white px-1 py-2 text-center align-bottom sticky top-0 z-20 border-b-2 border-l border-slate-100 cursor-pointer hover:bg-amber-50/50 transition"
                                style="height: 14rem; min-width:55px;"
                                id="header-subj-{{ $sub->id }}"
                                onclick="toggleColumnHighlight('{{ $sub->id }}')"
                                title="{{ $sub->subject_name }} ({{ $sub->subject_code }})">
                                <div class="broadsheet-subject-header">
                                    <span class="text-[11px] font-black text-slate-900 tracking-wide leading-tight">
                                        {{ $sub->subject_name }} <span class="text-slate-500 font-bold">({{ $sub->subject_code }})</span>
                                    </span>
                                </div>
                            </th>
                        @endforeach
                        {{-- Total --}}
                        <th class="bg-amber-50 px-1 py-3 text-center align-bottom sticky top-0 z-20 border-b-2 border-l-2 border-slate-300"
                            style="min-width:80px;">
                            <span class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider">Total</span>
                        </th>
                        {{-- % all subjects --}}
                        <th class="bg-amber-50 px-1 py-3 text-center align-bottom sticky top-0 z-20 border-b-2 border-l border-slate-200"
                            style="min-width:50px;">
                            <span class="text-[10px] font-extrabold text-amber-700 uppercase tracking-wider">%</span>
                        </th>
                        {{-- Top 5 Total --}}
                        <th class="bg-violet-50 px-1 py-3 text-center align-bottom sticky top-0 z-20 border-b-2 border-l-2 border-violet-300"
                            style="min-width:80px;">
                            <span class="text-[10px] font-extrabold text-violet-700 uppercase tracking-wider">Top 5</span>
                        </th>
                        {{-- Top 5 % --}}
                        <th class="bg-violet-50 px-1 py-3 text-center align-bottom sticky top-0 z-20 border-b-2 border-l border-violet-200"
                            style="min-width:50px;">
                            <span class="text-[10px] font-extrabold text-violet-700 uppercase tracking-wider">Top 5 %</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($candidates as $cand)
                        @php
                            $rollDisplay = str_replace('CBSE-', '', $cand['roll_number']);
                        @endphp
                        <tr class="hover:bg-amber-50/20 transition duration-500 border-b border-slate-100" id="row-cand-{{ $loop->index }}">
                            {{-- Roll Number (sticky) --}}
                            <td class="bg-white border-r border-slate-150 px-2 py-2 sticky left-0 z-10 font-mono text-[11px] font-bold text-slate-700 whitespace-nowrap cursor-pointer"
                                style="min-width:90px;"
                                onclick="toggleRowHighlight({{ $loop->index }})">
                                {{ $rollDisplay }}
                            </td>
                            {{-- Name (sticky) --}}
                            <td class="bg-white border-r border-slate-150 px-3 py-2 sticky left-[90px] z-10 text-xs font-bold text-slate-800 uppercase whitespace-nowrap cursor-pointer"
                                style="min-width:200px;" onclick="toggleRowHighlight({{ $loop->index }})">
                                {{ $cand['student_name'] }}
                            </td>
                            {{-- Subject marks/grades --}}
                            @foreach($subjects as $sub)
                                @php
                                    $mark  = $cand['marks'][$sub->id] ?? null;
                                    $grade = $cand['grades'][$sub->id] ?? null;
                                    $isFail = ($grade && in_array($grade, ['E1', 'E2']));
                                @endphp
                                <td class="px-1 py-2 text-center border-l border-slate-100 broadsheet-cell col-subj-{{ $sub->id }} {{ $isFail ? 'bg-rose-50' : '' }}"
                                    data-marks="{{ $mark !== null ? $mark : '' }}"
                                    data-grade="{{ $grade ?? '' }}">
                                    @if($mark !== null)
                                        <span class="cell-value text-xs font-extrabold {{ $isFail ? 'text-rose-600' : 'text-slate-800' }}">{{ $mark }}</span>
                                    @else
                                        <span class="cell-value text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                            {{-- Total (scored/max) --}}
                            <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30 font-mono text-xs font-black text-amber-800">
                                {{ $cand['total_obtained'] }}/{{ $cand['total_max'] }}
                            </td>
                            {{-- % all subjects --}}
                            <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30 font-mono text-xs font-black text-amber-800"
                                data-sort-pct="{{ $cand['percentage'] }}">
                                {{ $cand['percentage'] }}%
                            </td>
                            {{-- Top 5 total (scored/max) --}}
                            <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30 font-mono text-xs font-black text-violet-800">
                                {{ $cand['top5_obtained'] }}/{{ $cand['top5_max'] }}
                            </td>
                            {{-- Top 5 % --}}
                            <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30 font-mono text-xs font-black text-violet-800"
                                data-sort-top5="{{ $cand['top5_pct'] }}">
                                {{ $cand['top5_pct'] }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                {{-- Stats Footer: Marks Mode --}}
                <tfoot class="border-t-2 border-slate-300 bg-slate-50/50 text-xs font-bold text-slate-700" id="tfoot-marks">
                    {{-- Highest --}}
                    <tr class="border-b border-slate-150">
                        <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                            Highest
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-emerald-700 col-subj-{{ $sub->id }}">
                                {{ $subjectStats[$sub->id]['max'] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                    {{-- Lowest --}}
                    <tr class="border-b border-slate-150">
                        <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                            Lowest
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-rose-600 col-subj-{{ $sub->id }}">
                                {{ $subjectStats[$sub->id]['min'] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                    {{-- Average --}}
                    <tr class="bg-amber-50/40 border-t border-slate-200">
                        <td colspan="2" class="sticky left-0 z-10 bg-amber-50/60 border-r border-slate-200 px-4 py-2.5 text-right font-extrabold text-amber-800">
                            Average
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2.5 text-center border-l border-slate-100 font-black text-amber-700 col-subj-{{ $sub->id }}">
                                {{ $subjectStats[$sub->id]['avg'] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2.5 text-center border-l-2 border-slate-300 bg-amber-50/40"></td>
                        <td class="px-1 py-2.5 text-center border-l border-slate-200 bg-amber-50/40"></td>
                        <td class="px-1 py-2.5 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2.5 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                    {{-- Average (Last Year) --}}
                    <tr class="bg-amber-50/20 border-t border-slate-150">
                        <td colspan="2" class="sticky left-0 z-10 bg-amber-50/40 border-r border-slate-200 px-4 py-2 text-right font-bold text-amber-700">
                            Average (Last Year)
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-bold text-amber-600 col-subj-{{ $sub->id }}">
                                {{ $historicalStats['last1'][$sub->id] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/20"></td>
                    </tr>
                    {{-- Average (Last 3 Years) --}}
                    <tr class="bg-amber-50/20 border-t border-slate-150">
                        <td colspan="2" class="sticky left-0 z-10 bg-amber-50/40 border-r border-slate-200 px-4 py-2 text-right font-bold text-amber-700">
                            Average (Last 3 Years)
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-bold text-amber-600 col-subj-{{ $sub->id }}">
                                {{ $historicalStats['last3'][$sub->id] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/20"></td>
                    </tr>
                    {{-- Average (Last 5 Years) --}}
                    <tr class="bg-amber-50/20 border-t border-slate-150">
                        <td colspan="2" class="sticky left-0 z-10 bg-amber-50/40 border-r border-slate-200 px-4 py-2 text-right font-bold text-amber-700">
                            Average (Last 5 Years)
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-bold text-amber-600 col-subj-{{ $sub->id }}">
                                {{ $historicalStats['last5'][$sub->id] ?? '—' }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/20"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/20"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/20"></td>
                    </tr>
                    {{-- Total Candidates --}}
                    <tr class="border-t border-slate-200">
                        <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                            Candidates
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-bold text-slate-600 col-subj-{{ $sub->id }}">
                                {{ $subjectStats[$sub->id]['total'] ?? 0 }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                    {{-- Pass % --}}
                    <tr class="border-t border-slate-200">
                        <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                            Pass %
                        </td>
                        @foreach($subjects as $sub)
                            @php
                                $total = $subjectStats[$sub->id]['total'] ?? 0;
                                $passed = $subjectStats[$sub->id]['passed'] ?? 0;
                                $passPercent = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
                            @endphp
                            <td class="px-1 py-2 text-center border-l border-slate-100 font-bold {{ $passPercent >= 80 ? 'text-emerald-700' : ($passPercent >= 50 ? 'text-amber-700' : 'text-rose-600') }} col-subj-{{ $sub->id }}">
                                {{ $passPercent }}%
                            </td>
                        @endforeach
                        <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30"></td>
                        <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                </tfoot>

                {{-- Stats Footer: Grade Mode --}}
                <tfoot class="border-t-2 border-slate-300 bg-slate-50/50 text-xs font-bold text-slate-700 hidden" id="tfoot-grade">
                    @php
                        $allGrades = collect();
                        foreach ($gradeStats as $subId => $dist) {
                            $allGrades = $allGrades->merge(array_keys($dist));
                        }
                        $allGrades = $allGrades->unique()->sort()->values();
                    @endphp
                    @foreach($allGrades as $g)
                        <tr class="border-b border-slate-150">
                            <td colspan="2" class="sticky left-0 z-10 bg-slate-50 border-r border-slate-200 px-4 py-2 text-right font-extrabold text-slate-600">
                                Grade {{ $g }}
                            </td>
                            @foreach($subjects as $sub)
                                <td class="px-1 py-2 text-center border-l border-slate-100 font-black text-slate-800 col-subj-{{ $sub->id }}">
                                    {{ $gradeStats[$sub->id][$g] ?? '—' }}
                                </td>
                            @endforeach
                            <td class="px-1 py-2 text-center border-l-2 border-slate-300 bg-amber-50/30"></td>
                            <td class="px-1 py-2 text-center border-l border-slate-200 bg-amber-50/30"></td>
                            <td class="px-1 py-2 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                            <td class="px-1 py-2 text-center border-l border-violet-200 bg-violet-50/30"></td>
                        </tr>
                    @endforeach
                    {{-- Total Sat row --}}
                    <tr class="bg-amber-50/40 border-t border-slate-200">
                        <td colspan="2" class="sticky left-0 z-10 bg-amber-50/60 border-r border-slate-200 px-4 py-2.5 text-right font-extrabold text-amber-800">
                            Total Candidates
                        </td>
                        @foreach($subjects as $sub)
                            <td class="px-1 py-2.5 text-center border-l border-slate-100 font-black text-amber-700 col-subj-{{ $sub->id }}">
                                {{ $subjectStats[$sub->id]['total'] ?? 0 }}
                            </td>
                        @endforeach
                        <td class="px-1 py-2.5 text-center border-l-2 border-slate-300 bg-amber-50/40"></td>
                        <td class="px-1 py-2.5 text-center border-l border-slate-200 bg-amber-50/40"></td>
                        <td class="px-1 py-2.5 text-center border-l-2 border-violet-300 bg-violet-50/30"></td>
                        <td class="px-1 py-2.5 text-center border-l border-violet-200 bg-violet-50/30"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer summary --}}
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between text-xs text-slate-500 font-semibold print:hidden">
            <span>{{ $candidates->count() }} candidates &bull; {{ $subjects->count() }} subjects</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition">
                    🖨️ Print
                </button>
            </div>
        </div>
    </div>

    @elseif($academicYearId && $qualificationId)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-12 text-center">
            <div class="text-5xl mb-4">📭</div>
            <h3 class="text-lg font-extrabold text-slate-700 mb-2">No Results Found</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">No result data exists for the selected academic year and qualification. Please upload results first or try a different combination.</p>
        </div>
    @else
        {{-- Dashboard: Overview Tiles --}}
        @if(count($dashboardStats) > 0)
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📊</span>
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800 leading-tight">CBSE Results Overview</h2>
                    <p class="text-xs text-slate-500 font-medium">Click on a tile to view its full broadsheet</p>
                </div>
            </div>

            {{-- Group tiles by class --}}
            @php
                $class10 = array_filter($dashboardStats, fn($s) => str_contains(strtolower($s['qual_name']), '10'));
                $class12 = array_filter($dashboardStats, fn($s) => str_contains(strtolower($s['qual_name']), '12'));
                $others  = array_filter($dashboardStats, fn($s) =>
                    !str_contains(strtolower($s['qual_name']), '10') &&
                    !str_contains(strtolower($s['qual_name']), '12')
                );
                $tileGroups = [];
                if (!empty($class12)) $tileGroups[] = ['label' => 'Senior Secondary (Class 12)', 'emoji' => '🎓', 'color' => 'amber', 'tiles' => $class12];
                if (!empty($class10)) $tileGroups[] = ['label' => 'Secondary (Class 10)', 'emoji' => '📚', 'color' => 'sky', 'tiles' => $class10];
                if (!empty($others))  $tileGroups[] = ['label' => 'Other Qualifications', 'emoji' => '📋', 'color' => 'slate', 'tiles' => $others];
            @endphp

            @foreach($tileGroups as $group)
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-base">{{ $group['emoji'] }}</span>
                    <h3 class="text-sm font-extrabold text-slate-600 uppercase tracking-wider">{{ $group['label'] }}</h3>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($group['tiles'] as $stat)
                    @php
                        $avgBg = $stat['avg_percentage'] >= 75 ? 'from-emerald-500 to-teal-600' :
                                ($stat['avg_percentage'] >= 50 ? 'from-amber-500 to-orange-500' : 'from-rose-500 to-red-600');
                        $borderColor = $stat['avg_percentage'] >= 75 ? 'border-emerald-200' :
                                      ($stat['avg_percentage'] >= 50 ? 'border-amber-200' : 'border-rose-200');
                        $bgTint = $stat['avg_percentage'] >= 75 ? 'bg-emerald-50/40' :
                                 ($stat['avg_percentage'] >= 50 ? 'bg-amber-50/40' : 'bg-rose-50/40');
                    @endphp
                    <a href="{{ route('cbse.analysis.broadsheet', ['academic_year_id' => $stat['year_id'], 'qualification_id' => $stat['qual_id']]) }}"
                       class="group block rounded-2xl border {{ $borderColor }} {{ $bgTint }} bg-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 overflow-hidden cursor-pointer">
                        {{-- Colored top bar --}}
                        <div class="h-1.5 bg-gradient-to-r {{ $avgBg }}"></div>

                        <div class="p-5 space-y-4">
                            {{-- Year badge + qual --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-lg font-black text-slate-800 leading-none">{{ $stat['year_name'] }}</div>
                                    <div class="text-[11px] font-semibold text-slate-500 mt-0.5">{{ $stat['qual_name'] }}</div>
                                </div>
                                <span class="shrink-0 text-xs font-bold px-2 py-1 rounded-lg bg-slate-100 text-slate-600">
                                    {{ $stat['student_count'] }} Students
                                </span>
                            </div>

                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-2">
                                <div class="text-center bg-white rounded-xl border border-slate-200 py-2 px-1 shadow-sm">
                                    <div class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wide">Highest</div>
                                    <div class="text-base font-black text-emerald-700 leading-tight">{{ $stat['max_percentage'] }}<span class="text-xs font-bold text-emerald-500">%</span></div>
                                </div>
                                <div class="text-center bg-white rounded-xl border border-slate-200 py-2 px-1 shadow-sm">
                                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wide">Average</div>
                                    <div class="text-base font-black text-slate-700 leading-tight">{{ $stat['avg_percentage'] }}<span class="text-xs font-bold text-slate-400">%</span></div>
                                </div>
                                <div class="text-center bg-white rounded-xl border border-slate-200 py-2 px-1 shadow-sm">
                                    <div class="text-[10px] font-extrabold text-rose-500 uppercase tracking-wide">Lowest</div>
                                    <div class="text-base font-black text-rose-600 leading-tight">{{ $stat['min_percentage'] }}<span class="text-xs font-bold text-rose-400">%</span></div>
                                </div>
                            </div>

                            {{-- View broadsheet hint --}}
                            <div class="flex items-center justify-end gap-1 text-[11px] font-bold text-slate-400 group-hover:text-amber-600 transition">
                                <span>View Broadsheet</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-150 p-12 text-center">
            <div class="text-5xl mb-4">📊</div>
            <h3 class="text-lg font-extrabold text-slate-700 mb-2">CBSE Broadsheet</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">Select an academic year and qualification above to generate a detailed broadsheet view with marks, grades, and subject-wise statistics.</p>
        </div>
        @endif
    @endif
</div>

<script>
    let currentViewMode = 'marks';

    function setViewMode(mode) {
        if (mode === currentViewMode) return;
        currentViewMode = mode;

        const marksBtn    = document.getElementById('btn-view-marks');
        const gradeBtn    = document.getElementById('btn-view-grade');
        const combinedBtn = document.getElementById('btn-view-combined');
        if (!marksBtn || !gradeBtn || !combinedBtn) return;

        const activeClass   = 'px-3 py-1.5 rounded-lg text-xs font-bold transition bg-white shadow-sm text-slate-800';
        const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-800';

        marksBtn.className    = mode === 'marks' ? activeClass : inactiveClass;
        gradeBtn.className    = mode === 'grade' ? activeClass : inactiveClass;
        combinedBtn.className = mode === 'combined' ? activeClass : inactiveClass;

        // Toggle footer sections
        const marksFoot = document.getElementById('tfoot-marks');
        const gradeFoot = document.getElementById('tfoot-grade');
        if (marksFoot) marksFoot.classList.toggle('hidden', mode === 'grade');
        if (gradeFoot) gradeFoot.classList.toggle('hidden', mode !== 'grade');

        // Update cell values
        document.querySelectorAll('.broadsheet-cell').forEach(cell => {
            const span = cell.querySelector('.cell-value');
            if (!span) return;

            const marks = cell.dataset.marks || '';
            const grade = cell.dataset.grade || '';

            let val = '';
            if (mode === 'marks') {
                val = marks;
            } else if (mode === 'grade') {
                val = grade;
            } else if (mode === 'combined') {
                if (grade !== '' && marks !== '') {
                    val = `${grade}(${marks})`;
                } else {
                    val = marks || grade || '';
                }
            }

            if (val === '' || val === null) {
                span.textContent = '—';
                span.className = 'cell-value text-xs text-slate-300';
            } else {
                span.textContent = val;
                // Color by grade
                const isFail = ['E1', 'E2'].includes(grade);
                const isHighGrade = ['A1', 'A2'].includes(grade);
                if (isFail) {
                    span.className = 'cell-value text-xs font-extrabold text-rose-600';
                } else if (isHighGrade) {
                    span.className = 'cell-value text-xs font-extrabold text-emerald-700';
                } else {
                    span.className = 'cell-value text-xs font-extrabold text-slate-800';
                }
            }
        });
    }

    // Row / Column highlighting
    function toggleRowHighlight(idx) {
        const row = document.getElementById('row-cand-' + idx);
        if (row) row.classList.toggle('row-highlighted');
    }

    function toggleColumnHighlight(subjectId) {
        const header = document.getElementById('header-subj-' + subjectId);
        if (!header) return;
        header.classList.toggle('col-highlighted');
        document.querySelectorAll('.col-subj-' + subjectId).forEach(cell => {
            cell.classList.toggle('col-highlighted');
        });
    }

    // ---- Pagination, Searching, Sorting ----
    let currentSort = 'name';
    let sortAsc = { name: true, roll: true, pct: false, top5: false };
    
    let allRows = [];
    let filteredRows = [];

    document.addEventListener("DOMContentLoaded", function() {
        const tbody = document.querySelector('#broadsheet-table tbody');
        if (tbody) {
            allRows = Array.from(tbody.querySelectorAll('tr'));
            applyFilters();
        }
    });

    function applyFilters() {
        const candQuery = document.getElementById('search-candidate').value.toLowerCase();
        
        filteredRows = allRows.filter(row => {
            const roll = row.cells[0]?.textContent.trim().toLowerCase() || '';
            const name = row.cells[1]?.textContent.trim().toLowerCase() || '';
            return roll.includes(candQuery) || name.includes(candQuery);
        });

        sortRowsArray();
        renderTable();
        applySubjectFilter();
    }

    function applySubjectFilter() {
        const subjQuery = document.getElementById('search-subject').value.toLowerCase();
        const headers = document.querySelectorAll('.broadsheet-subject-header');
        
        headers.forEach(header => {
            const th = header.closest('th');
            const name = header.textContent.trim().toLowerCase();
            const subjectId = th.id.replace('header-subj-', '');
            
            const match = name.includes(subjQuery);
            const display = match ? '' : 'none';
            
            th.style.display = display;
            document.querySelectorAll('.col-subj-' + subjectId).forEach(cell => {
                cell.style.display = display;
            });
        });
    }

    function renderTable() {
        const tbody = document.querySelector('#broadsheet-table tbody');
        if (!tbody) return;
        
        // Remove all rows from DOM
        allRows.forEach(row => {
            if (row.parentNode) {
                row.parentNode.removeChild(row);
            }
        });
        
        filteredRows.forEach(row => tbody.appendChild(row));
        
        const counter = document.getElementById('candidate-count');
        if (counter) {
            counter.textContent = filteredRows.length + ' candidate' + (filteredRows.length !== 1 ? 's' : '');
        }
    }

    function sortTable(key) {
        if (currentSort === key) {
            sortAsc[key] = !sortAsc[key];
        } else {
            currentSort = key;
        }

        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.classList.remove('active-sort');
        });
        const activeBtn = document.getElementById('sort-' + key);
        if (activeBtn) activeBtn.classList.add('active-sort');

        sortRowsArray();
        renderTable();
    }

    function sortRowsArray() {
        filteredRows.sort((a, b) => {
            let aVal, bVal;
            const key = currentSort;
            if (key === 'name') {
                aVal = a.cells[1]?.textContent.trim().toLowerCase() ?? '';
                bVal = b.cells[1]?.textContent.trim().toLowerCase() ?? '';
                return sortAsc[key] ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            } else if (key === 'roll') {
                aVal = a.cells[0]?.textContent.trim() ?? '';
                bVal = b.cells[0]?.textContent.trim() ?? '';
                return sortAsc[key] ? aVal.localeCompare(bVal, undefined, {numeric: true}) : bVal.localeCompare(aVal, undefined, {numeric: true});
            } else if (key === 'pct') {
                aVal = parseFloat(a.querySelector('[data-sort-pct]')?.dataset.sortPct ?? 0);
                bVal = parseFloat(b.querySelector('[data-sort-pct]')?.dataset.sortPct ?? 0);
                return sortAsc[key] ? aVal - bVal : bVal - aVal;
            } else if (key === 'top5') {
                aVal = parseFloat(a.querySelector('[data-sort-top5]')?.dataset.sortTop5 ?? 0);
                bVal = parseFloat(b.querySelector('[data-sort-top5]')?.dataset.sortTop5 ?? 0);
                return sortAsc[key] ? aVal - bVal : bVal - aVal;
            }
            return 0;
        });
    }
</script>

<style>
    /* Table layout */
    #broadsheet-table { border-collapse: separate; border-spacing: 0; }

    /* Active Sort Button */
    .sort-btn.active-sort {
        background-color: #fef3c7 !important; /* light amber */
        color: #000000 !important; /* black text for visibility */
        border-color: #f59e0b !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    /* Roll number — no overflow hiding */
    #broadsheet-table tbody td:first-child {
        max-width: 90px;
    }

    #broadsheet-table thead th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
    }
    /* Higher z for sticky corners */
    #broadsheet-table thead th:first-child { z-index: 30; left: 0; }
    #broadsheet-table thead th:nth-child(2) { z-index: 30; left: 60px; }

    /* Sticky body cells background */
    #broadsheet-table tbody td:first-child,
    #broadsheet-table tbody td:nth-child(2) { background-color: #ffffff; }
    #broadsheet-table tbody tr:hover td:first-child,
    #broadsheet-table tbody tr:hover td:nth-child(2) { background-color: #fffbeb !important; }

    /* Sticky tfoot cells */
    #broadsheet-table tfoot td:first-child { background-color: #f8fafc; }

    /* Highlighting */
    .row-highlighted td { background-color: #fef3c7 !important; }
    .col-highlighted { background-color: #fefce8 !important; }
    .row-highlighted td.col-highlighted { background-color: #fde68a !important; }

    /* Vertical Subject Header */
    .broadsheet-subject-header {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: normal; /* allow wrapping for long names */
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 13rem; /* increased height */
        padding: 0.5rem 0;
        margin: 0 auto;
        text-align: left;
    }

    /* Print */
    @media print {
        .print\:hidden { display: none !important; }
        .sticky { position: static !important; }
        #broadsheet-table thead th { position: static !important; }
        table { font-size: 8pt; width: 100%; table-layout: auto !important; }
        th, td { padding: 3px 5px !important; border: 1px solid #cbd5e1 !important; }
        .broadsheet-subject-header { height: 7rem; }
    }
</style>
@endsection
