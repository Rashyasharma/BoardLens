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
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-800">Enrolled IGCSE Candidates</h3>
                <input type="text" id="search-igcse" onkeyup="filterTable('igcse')" placeholder="Search candidates..."
                    class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 max-w-xs font-bold text-slate-700" />
            </div>

            @if($igcseEnrollments->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No candidates currently enrolled in IGCSE for this series.</p>
                </div>
            @else
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse" id="table-igcse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky left-0 bg-slate-50 z-10 border-r border-slate-150 align-bottom">Candidate #</th>
                                <th class="px-6 py-3 sticky left-24 bg-slate-50 z-10 border-r border-slate-150 min-w-[12rem] align-bottom">Name</th>
                                @foreach($igcseSubjects as $idx => $sub)
                                    @php $color = $colors[$idx % count($colors)]; @endphp
                                    <th class="px-1 py-3 text-center min-w-[2.5rem] border-r border-slate-100 align-bottom {{ $color }}" style="height: 10rem;">
                                        <div class="subject-header-vertical">
                                            <span class="text-[10px] font-black text-slate-600 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-right align-bottom">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($igcseEnrollments as $enroll)
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-500 sticky left-0 bg-white z-[5] border-r border-slate-100">{{ $enroll->candidate->candidate_number }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-850 sticky left-24 bg-white z-[5] border-r border-slate-100 uppercase">{{ $enroll->candidate->candidate_name }}</td>
                                    @foreach($igcseSubjects as $idx => $sub)
                                        @php
                                            $color = $colors[$idx % count($colors)];
                                            $isEnrolled = in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? []);
                                        @endphp
                                        <td class="px-1 py-3 text-center border-r border-slate-100 {{ $color }}">
                                            <input type="checkbox"
                                                   class="subject-toggle-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-350 w-4 h-4 cursor-pointer"
                                                   data-candidate-id="{{ $enroll->candidate->id }}"
                                                   data-subject-id="{{ $sub->id }}"
                                                   data-qualification-id="{{ $igcseQual->id }}"
                                                   {{ $isEnrolled ? 'checked' : '' }} />
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
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-800">Enrolled GCE Candidates</h3>
                <input type="text" id="search-gce" onkeyup="filterTable('gce')" placeholder="Search candidates..."
                    class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 max-w-xs font-bold text-slate-700" />
            </div>

            @if($gceEnrollments->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No candidates currently enrolled in GCE AS &amp; A Level for this series.</p>
                </div>
            @else
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse" id="table-gce">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-150 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky left-0 bg-slate-50 z-10 border-r border-slate-150 align-bottom">Candidate #</th>
                                <th class="px-6 py-3 sticky left-24 bg-slate-50 z-10 border-r border-slate-150 min-w-[12rem] align-bottom">Name</th>
                                @foreach($gceSubjects as $idx => $sub)
                                    @php $color = $colors[$idx % count($colors)]; @endphp
                                    <th class="px-1 py-3 text-center min-w-[2.5rem] border-r border-slate-100 align-bottom {{ $color }}" style="height: 10rem;">
                                        <div class="subject-header-vertical">
                                            <span class="text-[10px] font-black text-slate-600 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-6 py-3 text-right align-bottom">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($gceEnrollments as $enroll)
                                <tr class="hover:bg-slate-50/50 transition duration-100">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-500 sticky left-0 bg-white z-[5] border-r border-slate-100">{{ $enroll->candidate->candidate_number }}</td>
                                    <td class="px-6 py-3 font-bold text-slate-850 sticky left-24 bg-white z-[5] border-r border-slate-100 uppercase">{{ $enroll->candidate->candidate_name }}</td>
                                    @foreach($gceSubjects as $idx => $sub)
                                        @php
                                            $color = $colors[$idx % count($colors)];
                                            $isEnrolled = in_array($sub->id, $candidateSubjectsMap[$enroll->candidate->id] ?? []);
                                        @endphp
                                        <td class="px-1 py-3 text-center border-r border-slate-100 {{ $color }}">
                                            <input type="checkbox"
                                                   class="subject-toggle-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-350 w-4 h-4 cursor-pointer"
                                                   data-candidate-id="{{ $enroll->candidate->id }}"
                                                   data-subject-id="{{ $sub->id }}"
                                                   data-qualification-id="{{ $gceQual->id }}"
                                                   {{ $isEnrolled ? 'checked' : '' }} />
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

    document.querySelectorAll('.subject-toggle-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const candId = this.dataset.candidateId;
            const subId = this.dataset.subjectId;
            const qualId = this.dataset.qualificationId;
            const registered = this.checked ? 1 : 0;

            fetch("{{ route('student-entries.toggle-subject', $series->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    candidate_id: candId,
                    subject_id: subId,
                    qualification_id: qualId,
                    registered: registered
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to update registration.');
                    this.checked = !this.checked; // Revert
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network error updating registration.');
                this.checked = !this.checked; // Revert
            });
        });
    });
</script>

<style>
    .subject-header-vertical {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 9rem;
        padding: 0.25rem 0;
    }
    
    tr:hover td.sticky { background-color: rgb(238 242 255 / 0.3) !important; }
    th.sticky { background-color: #f8fafc; }
    .sticky.left-24 { left: 6.01rem; }
</style>
@endsection
