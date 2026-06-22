@extends('layouts.app')

@section('title', 'Manage Entries — ' . $series->month . ' ' . $series->year)
@section('page-title', 'Exam Series')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    {{-- Breadcrumbs & Back link --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <a href="{{ route('exam-series.index') }}" class="hover:text-indigo-600 transition">Exam Series</a>
            <span>›</span>
            <span class="text-slate-600">Manage Entries ({{ $series->month }} {{ $series->year }})</span>
        </div>
        <a href="{{ route('exam-series.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
            ← Back to Exam Series
        </a>
    </div>

    {{-- Header Strip --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
        <div>
            <h2 class="text-lg font-black text-slate-800">
                Manage Registered Candidates
                <span class="text-slate-400 font-semibold">·</span>
                @switch($series->month)
                    @case('March')    February/March {{ $series->year }} @break
                    @case('June')     May/June {{ $series->year }} @break
                    @case('November') October/November {{ $series->year }} @break
                    @default {{ $series->month }} {{ $series->year }}
                @endswitch
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Register students and select their subjects for this exam series.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-xxs font-mono text-slate-400 bg-slate-100 border border-slate-200 px-2 py-1 rounded">
                {{ $series->series_code }}
            </span>
            <span class="text-xxs font-extrabold px-2.5 py-1 rounded-lg border
                {{ $series->is_active ? 'bg-emerald-50 border-emerald-150 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-500' }}">
                {{ $series->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>

    {{-- Qualification Tabs Header --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm p-1.5 flex gap-2">
        <button type="button" id="tab-igcse-btn" onclick="switchQualTab('igcse')"
            class="flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 bg-indigo-600 text-white shadow-sm flex items-center justify-center gap-2">
            🎓 IGCSE Candidates
            <span id="igcse-count-badge" class="bg-white/20 text-white px-2 py-0.5 rounded-lg text-[10px] font-black">
                {{ $igcseEnrollments->count() }}
            </span>
        </button>
        <button type="button" id="tab-gce-btn" onclick="switchQualTab('gce')"
            class="flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-2">
            ⚡ GCE AS &amp; A Level Candidates
            <span id="gce-count-badge" class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-lg text-[10px] font-black">
                {{ $gceEnrollments->count() }}
            </span>
        </button>
    </div>

    @php
        $colors = [
            'bg-blue-50/20', 'bg-emerald-50/20', 'bg-indigo-50/20', 'bg-pink-50/20',
            'bg-purple-50/20', 'bg-amber-50/20', 'bg-cyan-50/20', 'bg-rose-50/20',
            'bg-teal-50/20', 'bg-orange-50/20', 'bg-violet-50/20', 'bg-fuchsia-50/20'
        ];
    @endphp

    {{-- ========================================== --}}
    {{-- IGCSE PANEL --}}
    {{-- ========================================== --}}
    <div id="panel-igcse" class="space-y-4 animate-fade-in">
        {{-- Collapsible Quick Registration Form --}}
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
            <button type="button" onclick="toggleAddForm('igcse')" class="w-full px-6 py-4 flex items-center justify-between text-sm font-black text-slate-800 hover:bg-slate-50 transition">
                <span>👤 Add Candidate (IGCSE)</span>
                <span id="add-icon-igcse" class="text-xs text-indigo-600 font-bold">＋ Expand Form</span>
            </button>
            <div id="add-form-igcse" class="hidden border-t border-slate-100 bg-slate-50/30 p-6">
                <form method="POST" action="{{ route('student-entries.add-candidate', $series->id) }}" class="space-y-4 max-w-4xl">
                    @csrf
                    <input type="hidden" name="qualification_id" value="{{ $igcseQual->id }}" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Candidate Number</label>
                            <input type="text" name="candidate_number" required placeholder="e.g. 0001" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Candidate Name</label>
                            <input type="text" name="candidate_name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xxs font-black text-slate-400 uppercase mb-1.5">Select Subjects</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 bg-white p-4 border border-slate-150 rounded-xl max-h-48 overflow-y-auto">
                            @foreach($igcseSubjects as $sub)
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-indigo-600 transition">
                                    <input type="checkbox" name="subjects[]" value="{{ $sub->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-350" />
                                    <span>{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                            Register Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Enrolled List --}}
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-sm font-black text-slate-800 shrink-0">Enrolled IGCSE Candidates</h3>
                <div class="flex flex-wrap items-center gap-3 w-full justify-end">
                    <button type="button" id="edit-btn-igcse" onclick="toggleEditMode('igcse')" 
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        ✏️ Edit Entries
                    </button>
                    <button type="button" id="save-btn-igcse" onclick="saveBulkChanges('igcse')" 
                        class="hidden px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        💾 Save Changes
                    </button>
                    <button type="button" id="cancel-btn-igcse" onclick="cancelEditMode('igcse')" 
                        class="hidden px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black rounded-xl shadow-sm transition">
                        Cancel
                    </button>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <input type="text" id="search-igcse" onkeyup="filterTable('igcse')" placeholder="Search candidates..."
                        class="w-40 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
                    <input type="text" id="search-subject-igcse" onkeyup="filterSubjects('igcse')" placeholder="Search subjects..."
                        class="w-40 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
                </div>
            </div>

            @if($igcseEnrollments->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No candidates currently enrolled in IGCSE for this series.</p>
                </div>
            @else
                <div class="overflow-x-auto overflow-y-auto flex-1 max-h-[75vh]">
                    <table class="w-full text-left border-collapse" id="table-igcse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky top-0 left-0 bg-slate-50 z-30 border-r border-slate-150 border-b align-bottom shadow-sm">Candidate #</th>
                                <th class="px-6 py-3 sticky top-0 left-24 bg-slate-50 z-30 border-r border-slate-150 border-b min-w-[12rem] align-bottom shadow-sm">Name</th>
                                @foreach($igcseSubjects as $idx => $sub)
                                    @php 
                                        $columnBg = $idx % 2 === 0 ? 'bg-slate-100/90' : 'bg-slate-50/90';
                                    @endphp
                                    <th class="px-1.5 py-3 text-center min-w-[3rem] border-r border-slate-150 border-b align-bottom sticky top-0 z-20 {{ $columnBg }} subject-col-{{ $sub->id }} shadow-sm" 
                                        data-subject-id="{{ $sub->id }}"
                                        data-subject-search="{{ strtolower($sub->subject_name . ' ' . $sub->subject_code) }}"
                                        style="height: 12rem;">
                                        <div class="subject-header-vertical">
                                            <span class="text-[10px] font-black text-slate-700 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-right align-bottom sticky top-0 z-20 bg-slate-50 border-b border-slate-150 shadow-sm">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($igcseEnrollments as $enroll)
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-500 sticky left-0 bg-white z-[5] border-r border-slate-100">{{ $enroll->candidate->candidate_number }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-850 sticky left-24 bg-white z-[5] border-r border-slate-100 uppercase">{{ $enroll->candidate->candidate_name }}</td>
                                    @foreach($igcseSubjects as $idx => $sub)
                                        @php
                                            $columnBg = $idx % 2 === 0 ? 'bg-slate-50/40' : 'bg-white';
                                            $isEnrolled  = in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? []);
                                            $codesInUse  = $candidateSubjectCodes[$enroll->candidate->id] ?? [];
                                            $hasConflict = !$isEnrolled && in_array($sub->subject_code, $codesInUse);
                                        @endphp
                                        <td class="px-1.5 py-3 text-center border-r border-slate-100 {{ $columnBg }} subject-col-{{ $sub->id }}"
                                            data-subject-id="{{ $sub->id }}"
                                            @if($hasConflict) title="Conflict: already enrolled in another '{{ $sub->subject_code }}' subject" @endif>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <input type="checkbox"
                                                       class="subject-toggle-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-350 w-4 h-4 cursor-pointer"
                                                       data-candidate-id="{{ $enroll->candidate->id }}"
                                                       data-subject-id="{{ $sub->id }}"
                                                       data-qualification-id="{{ $igcseQual->id }}"
                                                       {{ $isEnrolled ? 'checked' : '' }}
                                                       data-initial-checked="{{ $isEnrolled ? 'true' : 'false' }}"
                                                       disabled />
                                                @if($hasConflict)
                                                    <span class="text-amber-500 text-[8px] font-black leading-none" title="Code already used">⚠</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST" action="{{ route('student-entries.unenroll', [$series->id, $enroll->candidate->id]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qualification_id" value="{{ $igcseQual->id }}" />
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 border-t border-slate-150 text-[10px] font-black text-slate-500 uppercase tracking-wider shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                                <td class="px-6 py-3 sticky bottom-0 left-0 bg-slate-50 z-30 border-r border-slate-150 border-t">Total</td>
                                <td class="px-6 py-3 sticky bottom-0 left-24 bg-slate-50 z-30 border-r border-slate-150 border-t min-w-[12rem]"></td>
                                @foreach($igcseSubjects as $idx => $sub)
                                    @php 
                                        $columnBg = $idx % 2 === 0 ? 'bg-slate-100/90' : 'bg-slate-50/90';
                                        $enrolledCount = 0;
                                        foreach($igcseEnrollments as $enroll) {
                                            if (in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? [])) {
                                                $enrolledCount++;
                                            }
                                        }
                                    @endphp
                                    <td class="px-1.5 py-3 text-center sticky bottom-0 z-20 border-r border-slate-150 border-t {{ $columnBg }}">
                                        <span class="text-xs text-indigo-700 font-black">{{ $enrolledCount }}</span>
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 sticky bottom-0 z-20 bg-slate-50 border-t border-slate-150"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- GCE PANEL (HIDDEN BY DEFAULT) --}}
    {{-- ========================================== --}}
    <div id="panel-gce" class="hidden space-y-4 animate-fade-in">
        {{-- Collapsible Quick Registration Form --}}
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
            <button type="button" onclick="toggleAddForm('gce')" class="w-full px-6 py-4 flex items-center justify-between text-sm font-black text-slate-800 hover:bg-slate-50 transition">
                <span>⚡ Add Candidate (GCE AS &amp; A Level)</span>
                <span id="add-icon-gce" class="text-xs text-indigo-600 font-bold">＋ Expand Form</span>
            </button>
            <div id="add-form-gce" class="hidden border-t border-slate-100 bg-slate-50/30 p-6">
                <form method="POST" action="{{ route('student-entries.add-candidate', $series->id) }}" class="space-y-4 max-w-4xl">
                    @csrf
                    <input type="hidden" name="qualification_id" value="{{ $gceQual->id }}" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Candidate Number</label>
                            <input type="text" name="candidate_number" required placeholder="e.g. 0001" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Candidate Name</label>
                            <input type="text" name="candidate_name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xxs font-black text-slate-400 uppercase mb-1.5">Select Subjects</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 bg-white p-4 border border-slate-150 rounded-xl max-h-48 overflow-y-auto">
                            @foreach($gceSubjects as $sub)
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-indigo-600 transition">
                                    <input type="checkbox" name="subjects[]" value="{{ $sub->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-350" />
                                    <span>{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                            Register Candidate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Enrolled List --}}
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-sm font-black text-slate-800 shrink-0">Enrolled GCE Candidates</h3>
                <div class="flex flex-wrap items-center gap-3 w-full justify-end">
                    <button type="button" id="edit-btn-gce" onclick="toggleEditMode('gce')" 
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        ✏️ Edit Entries
                    </button>
                    <button type="button" id="save-btn-gce" onclick="saveBulkChanges('gce')" 
                        class="hidden px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        💾 Save Changes
                    </button>
                    <button type="button" id="cancel-btn-gce" onclick="cancelEditMode('gce')" 
                        class="hidden px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black rounded-xl shadow-sm transition">
                        Cancel
                    </button>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <input type="text" id="search-gce" onkeyup="filterTable('gce')" placeholder="Search candidates..."
                        class="w-40 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
                    <input type="text" id="search-subject-gce" onkeyup="filterSubjects('gce')" placeholder="Search subjects..."
                        class="w-40 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
                </div>
            </div>

            @if($gceEnrollments->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No candidates currently enrolled in GCE AS &amp; A Level for this series.</p>
                </div>
            @else
                <div class="overflow-x-auto overflow-y-auto flex-1 max-h-[75vh]">
                    <table class="w-full text-left border-collapse" id="table-gce">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky top-0 left-0 bg-slate-50 z-30 border-r border-slate-150 border-b align-bottom shadow-sm">Candidate #</th>
                                <th class="px-6 py-3 sticky top-0 left-24 bg-slate-50 z-30 border-r border-slate-150 border-b min-w-[12rem] align-bottom shadow-sm">Name</th>
                                @foreach($gceSubjects as $idx => $sub)
                                    @php 
                                        $columnBg = $idx % 2 === 0 ? 'bg-slate-100/90' : 'bg-slate-50/90';
                                    @endphp
                                    <th class="px-1.5 py-3 text-center min-w-[3rem] border-r border-slate-150 border-b align-bottom sticky top-0 z-20 {{ $columnBg }} subject-col-{{ $sub->id }} shadow-sm" 
                                        data-subject-id="{{ $sub->id }}"
                                        data-subject-search="{{ strtolower($sub->subject_name . ' ' . $sub->subject_code) }}"
                                        style="height: 12rem;">
                                        <div class="subject-header-vertical">
                                            <span class="text-[10px] font-black text-slate-700 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-right align-bottom sticky top-0 z-20 bg-slate-50 border-b border-slate-150 shadow-sm">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($gceEnrollments as $enroll)
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-500 sticky left-0 bg-white z-[5] border-r border-slate-100">{{ $enroll->candidate->candidate_number }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-850 sticky left-24 bg-white z-[5] border-r border-slate-100 uppercase">{{ $enroll->candidate->candidate_name }}</td>
                                    @foreach($gceSubjects as $idx => $sub)
                                        @php
                                            $columnBg = $idx % 2 === 0 ? 'bg-slate-50/40' : 'bg-white';
                                            $isEnrolled  = in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? []);
                                            $codesInUse  = $candidateSubjectCodes[$enroll->candidate->id] ?? [];
                                            $hasConflict = !$isEnrolled && in_array($sub->subject_code, $codesInUse);
                                        @endphp
                                        <td class="px-1.5 py-3 text-center border-r border-slate-100 {{ $columnBg }} subject-col-{{ $sub->id }}"
                                            data-subject-id="{{ $sub->id }}"
                                            @if($hasConflict) title="Conflict: already enrolled in another '{{ $sub->subject_code }}' subject" @endif>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <input type="checkbox"
                                                       class="subject-toggle-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-350 w-4 h-4 cursor-pointer"
                                                       data-candidate-id="{{ $enroll->candidate->id }}"
                                                       data-subject-id="{{ $sub->id }}"
                                                       data-qualification-id="{{ $gceQual->id }}"
                                                       {{ $isEnrolled ? 'checked' : '' }}
                                                       data-initial-checked="{{ $isEnrolled ? 'true' : 'false' }}"
                                                       disabled />
                                                @if($hasConflict)
                                                    <span class="text-amber-500 text-[8px] font-black leading-none" title="Code already used">⚠</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST" action="{{ route('student-entries.unenroll', [$series->id, $enroll->candidate->id]) }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="qualification_id" value="{{ $gceQual->id }}" />
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 border-t border-slate-150 text-[10px] font-black text-slate-500 uppercase tracking-wider shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                                <td class="px-6 py-3 sticky bottom-0 left-0 bg-slate-50 z-30 border-r border-slate-150 border-t">Total</td>
                                <td class="px-6 py-3 sticky bottom-0 left-24 bg-slate-50 z-30 border-r border-slate-150 border-t min-w-[12rem]"></td>
                                @foreach($gceSubjects as $idx => $sub)
                                    @php 
                                        $columnBg = $idx % 2 === 0 ? 'bg-slate-100/90' : 'bg-slate-50/90';
                                        $enrolledCount = 0;
                                        foreach($gceEnrollments as $enroll) {
                                            if (in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? [])) {
                                                $enrolledCount++;
                                            }
                                        }
                                    @endphp
                                    <td class="px-1.5 py-3 text-center sticky bottom-0 z-20 border-r border-slate-150 border-t {{ $columnBg }}">
                                        <span class="text-xs text-indigo-700 font-black">{{ $enrolledCount }}</span>
                                    </td>
                                @endforeach
                                <td class="px-6 py-3 sticky bottom-0 z-20 bg-slate-50 border-t border-slate-150"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    function toggleAddForm(type) {
        const form = document.getElementById('add-form-' + type);
        const icon = document.getElementById('add-icon-' + type);
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            icon.textContent = '－ Collapse Form';
        } else {
            form.classList.add('hidden');
            icon.textContent = '＋ Expand Form';
        }
    }

    function switchQualTab(type) {
        const igcseBtn = document.getElementById('tab-igcse-btn');
        const gceBtn = document.getElementById('tab-gce-btn');
        const igcsePanel = document.getElementById('panel-igcse');
        const gcePanel = document.getElementById('panel-gce');
        
        const countIgcseBadge = document.getElementById('igcse-count-badge');
        const countGceBadge = document.getElementById('gce-count-badge');

        if (type === 'igcse') {
            igcseBtn.className = "flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 bg-indigo-600 text-white shadow-sm flex items-center justify-center gap-2";
            gceBtn.className = "flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-2";
            
            countIgcseBadge.className = "bg-white/20 text-white px-2 py-0.5 rounded-lg text-[10px] font-black";
            countGceBadge.className = "bg-slate-100 text-slate-500 px-2 py-0.5 rounded-lg text-[10px] font-black";

            igcsePanel.classList.remove('hidden');
            gcePanel.classList.add('hidden');
        } else {
            gceBtn.className = "flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 bg-indigo-600 text-white shadow-sm flex items-center justify-center gap-2";
            igcseBtn.className = "flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-2";
            
            countGceBadge.className = "bg-white/20 text-white px-2 py-0.5 rounded-lg text-[10px] font-black";
            countIgcseBadge.className = "bg-slate-100 text-slate-500 px-2 py-0.5 rounded-lg text-[10px] font-black";

            gcePanel.classList.remove('hidden');
            igcsePanel.classList.add('hidden');
        }
    }

    function filterTable(type) {
        const input = document.getElementById('search-' + type);
        const filter = input.value.toLowerCase();
        const table = document.getElementById('table-' + type);
        if (!table) return;
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let found = false;
            const tds = tr[i].getElementsByTagName('td');
            // search only on index 0 and 1 (Cand # and Name) to avoid subject checks matching
            for (let j = 0; j < 2; j++) {
                if (tds[j]) {
                    const textValue = tds[j].textContent || tds[j].innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
        }
    }

    function toggleEditMode(type) {
        const table = document.getElementById('table-' + type);
        if (!table) return;

        // Enable all checkboxes
        table.querySelectorAll('.subject-toggle-checkbox').forEach(cb => {
            cb.removeAttribute('disabled');
        });

        // Toggle buttons visibility
        document.getElementById('edit-btn-' + type).classList.add('hidden');
        document.getElementById('save-btn-' + type).classList.remove('hidden');
        document.getElementById('cancel-btn-' + type).classList.remove('hidden');

        showToast('Edit mode enabled. Make changes and click Save Changes.', 'success');
    }

    function cancelEditMode(type) {
        const table = document.getElementById('table-' + type);
        if (!table) return;

        // Revert checks to initial values and disable
        table.querySelectorAll('.subject-toggle-checkbox').forEach(cb => {
            const initialChecked = cb.dataset.initialChecked === 'true';
            cb.checked = initialChecked;
            cb.setAttribute('disabled', 'true');
        });

        // Toggle buttons visibility
        document.getElementById('edit-btn-' + type).classList.remove('hidden');
        document.getElementById('save-btn-' + type).classList.add('hidden');
        document.getElementById('cancel-btn-' + type).classList.add('hidden');

        showToast('Changes cancelled.', 'error');
    }

    async function saveBulkChanges(type) {
        const table = document.getElementById('table-' + type);
        if (!table) return;

        // Find all modified checkboxes
        const checkboxes = table.querySelectorAll('.subject-toggle-checkbox');
        const entries = [];

        checkboxes.forEach(cb => {
            const currentVal = cb.checked;
            const initialVal = cb.dataset.initialChecked === 'true';

            if (currentVal !== initialVal) {
                entries.push({
                    candidate_id: cb.dataset.candidateId,
                    subject_id: cb.dataset.subjectId,
                    registered: currentVal ? 1 : 0
                });
            }
        });

        if (entries.length === 0) {
            checkboxes.forEach(cb => cb.setAttribute('disabled', 'true'));
            document.getElementById('edit-btn-' + type).classList.remove('hidden');
            document.getElementById('save-btn-' + type).classList.add('hidden');
            document.getElementById('cancel-btn-' + type).classList.add('hidden');
            showToast('No changes were made.', 'error');
            return;
        }

        const confirmSave = confirm(`Are you sure you want to save these student registrations?`);
        if (!confirmSave) return;

        try {
            const qualId = type === 'igcse' 
                ? "{{ $igcseQual->id }}" 
                : "{{ $gceQual->id }}";

            const response = await fetch("{{ route('student-entries.bulk-update', $series->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    qualification_id: qualId,
                    entries: entries
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                checkboxes.forEach(cb => {
                    cb.dataset.initialChecked = cb.checked ? 'true' : 'false';
                    cb.setAttribute('disabled', 'true');
                });

                document.getElementById('edit-btn-' + type).classList.remove('hidden');
                document.getElementById('save-btn-' + type).classList.add('hidden');
                document.getElementById('cancel-btn-' + type).classList.add('hidden');

                showToast('Student registrations successfully updated.', 'success');
            } else {
                showToast(data.message || 'Failed to save changes.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('An error occurred while saving registrations.', 'error');
        }
    }

    // Toast notification system
    function showToast(message, type = 'error') {
        const existing = document.getElementById('entry-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'entry-toast';
        toast.className = `fixed bottom-6 right-6 z-50 max-w-sm px-5 py-3 rounded-2xl shadow-xl text-sm font-semibold flex items-start gap-3 transition-all duration-300 opacity-0 translate-y-2 ${
            type === 'error' ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'
        }`;
        toast.innerHTML = `
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                ${ type === 'error'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>'
                }
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 350);
        }, 5000);
    }

    // Search subjects filter
    function filterSubjects(type) {
        const input = document.getElementById('search-subject-' + type);
        const filter = input.value.toLowerCase().trim();
        const table = document.getElementById('table-' + type);
        if (!table) return;

        const headers = table.querySelectorAll('thead th[data-subject-search]');
        headers.forEach(th => {
            const subjectId = th.dataset.subjectId;
            const searchTerms = th.dataset.subjectSearch;
            const match = searchTerms.includes(filter);

            const cells = table.querySelectorAll(`.subject-col-${subjectId}`);
            cells.forEach(cell => {
                if (match) {
                    cell.style.display = "";
                } else {
                    cell.style.display = "none";
                }
            });
        });
    }

    // Toggle highlight on row click (candidate name/number) and column click (subject header)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tbody tr').forEach(row => {
            row.querySelectorAll('td.sticky').forEach(td => {
                td.addEventListener('click', function(e) {
                    const isHighlighted = row.classList.contains('highlighted-row');
                    document.querySelectorAll('tbody tr').forEach(r => r.classList.remove('highlighted-row'));
                    if (!isHighlighted) {
                        row.classList.add('highlighted-row');
                    }
                    e.stopPropagation();
                });
            });
        });

        document.querySelectorAll('thead th[data-subject-id]').forEach(th => {
            th.addEventListener('click', function(e) {
                const subjectId = this.dataset.subjectId;
                const isHighlighted = this.classList.contains('highlighted-column-cell');

                document.querySelectorAll('.highlighted-column-cell').forEach(cell => {
                    cell.classList.remove('highlighted-column-cell');
                });

                if (!isHighlighted) {
                    const table = this.closest('table');
                    table.querySelectorAll(`.subject-col-${subjectId}`).forEach(cell => {
                        cell.classList.add('highlighted-column-cell');
                    });
                }
                e.stopPropagation();
            });
        });
    });
</script>

<style>
    .subject-header-vertical {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: normal;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 11.5rem;
        padding: 0.5rem 0.1rem;
        text-align: center;
        line-height: 1.25;
    }
    
    tr:hover td.sticky { background-color: #f8fafc !important; }
    th.sticky { background-color: #f8fafc !important; }
    .sticky.left-24 { left: 6.01rem; }

    thead th[data-subject-id], tbody tr td.sticky {
        cursor: pointer;
    }
    
    .highlighted-row {
        background-color: rgb(238 242 255 / 0.8) !important;
    }
    .highlighted-row td.sticky {
        background-color: #eef2ff !important;
    }

    .highlighted-column-cell {
        background-color: rgb(224 231 255 / 0.8) !important;
    }

    /* Full inner and outer table borders */
    table {
        border: 1px solid #cbd5e1 !important;
        border-collapse: collapse !important;
    }
    table th, table td {
        border: 1px solid #cbd5e1 !important;
    }
</style>
@endsection
