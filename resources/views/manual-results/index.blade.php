@extends('layouts.app')

@section('title', 'Upload Marks')
@section('page-title', 'Upload Marks')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- Intro --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-5 flex items-start gap-4">
        <div class="w-10 h-10 shrink-0 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-xl">📝</div>
        <div>
            <h2 class="text-sm font-black text-slate-800">Upload Marks</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Select a year, session, qualification, and subject to open the results entry grid.
                You can enter Grade, PUM, and component marks for each enrolled candidate.
            </p>
        </div>
    </div>

    {{-- Cascading selector card --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Step 1 — Select Exam Series & Subject</h3>
        </div>
        <div class="px-6 py-6 space-y-5">

            {{-- Row 1: Year + Qualification --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Year</label>
                    <select id="sel-year" onchange="onYearChange()"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">— Select Year —</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Qualification</label>
                    <select id="sel-qual" onchange="onQualChange()"
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">— Select Qualification —</option>
                        @foreach($qualifications as $q)
                            <option value="{{ $q->id }}" {{ $selectedQual == $q->id ? 'selected' : '' }}>
                                {{ $q->type_display }} — {{ $q->qualification_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2: Month (session) --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Session</label>
                <div class="grid grid-cols-3 gap-3" id="month-buttons">
                    @foreach([
                        'March'    => 'February / March',
                        'June'     => 'May / June',
                        'November' => 'October / November',
                    ] as $val => $lbl)
                        <button type="button"
                            id="month-btn-{{ $val }}"
                            onclick="selectMonth('{{ $val }}')"
                            class="month-btn border-2 border-slate-200 rounded-xl py-3 px-2 text-xs font-bold text-slate-500 hover:border-slate-300 transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed"
                            {{ !$selectedYear || !$selectedQual ? 'disabled' : '' }}>
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
                <input type="hidden" id="sel-month" value="{{ $selectedMonth }}" />
            </div>

            {{-- Row 3: Subject --}}
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Subject</label>
                <select id="sel-subject"
                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:opacity-50"
                    {{ !$selectedQual ? 'disabled' : '' }}>
                    <option value="">— Select Subject —</option>
                    @foreach($subjectOptions as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubject == $sub->id ? 'selected' : '' }}>
                            {{ $sub->subject_code }} — {{ $sub->subject_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Open button --}}
            <div class="pt-2">
                <button type="button" id="open-btn" onclick="openEntryGrid()"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition">
                    Open Entry Grid →
                </button>
            </div>
        </div>
    </div>

    {{-- Quick tip --}}
    <div class="text-center text-xs text-slate-400 font-semibold">
        💡 Make sure candidates are enrolled via
        <a href="{{ route('exam-series.index') }}" class="text-indigo-500 hover:underline">Exam Series</a>
        before entering results.
    </div>

</div>

<script>
    const monthsApiBase = "{{ route('api.manual-results.months') }}";
    const seriesApiBase = "/api/manual-results/subjects/";

    let seriesMap = {}; // month -> series_id for selected year+qual

    // Pre-fill seriesMap if we have selectedSeries data
    @if($selectedSeries)
        seriesMap['{{ $selectedSeries->month }}'] = '{{ $selectedSeries->id }}';
        highlightMonth('{{ $selectedMonth }}');
    @endif

    function onYearChange() {
        const month = document.getElementById('sel-month').value;
        document.getElementById('sel-month').value = '';
        document.querySelectorAll('.month-btn').forEach(b => {
            b.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-700');
            b.classList.add('border-slate-200', 'text-slate-500');
        });
        loadAvailableMonths();
    }

    function onQualChange() {
        document.getElementById('sel-month').value = '';
        document.querySelectorAll('.month-btn').forEach(b => {
            b.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-700');
            b.classList.add('border-slate-200', 'text-slate-500');
        });
        // Reload subjects for qual
        const qualId = document.getElementById('sel-qual').value;
        const subjectSel = document.getElementById('sel-subject');
        subjectSel.innerHTML = '<option value="">— Select Subject —</option>';
        if (qualId) {
            subjectSel.disabled = false;
            // Fetch subjects for this qual via hidden API
            fetch('/api/subjects/' + qualId)
                .then(r => r.json())
                .then(subjects => {
                    subjects.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.subject_code + ' — ' + s.subject_name;
                        subjectSel.appendChild(opt);
                    });
                });
        } else {
            subjectSel.disabled = true;
        }
        loadAvailableMonths();
    }

    function loadAvailableMonths() {
        const year  = document.getElementById('sel-year').value;
        const qual  = document.getElementById('sel-qual').value;

        const btns = document.querySelectorAll('.month-btn');

        if (!year || !qual) {
            btns.forEach(b => b.disabled = true);
            return;
        }

        fetch(monthsApiBase + '?year=' + year + '&qual=' + qual)
            .then(r => r.json())
            .then(availableMonths => {
                seriesMap = {};
                btns.forEach(btn => {
                    const btnMonth = btn.id.replace('month-btn-', '');
                    if (availableMonths.includes(btnMonth)) {
                        btn.disabled = false;
                    } else {
                        btn.disabled = true;
                        btn.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-700');
                        btn.classList.add('border-slate-200', 'text-slate-500');
                    }
                });

                // Also fetch series_ids for the mapping
                return fetch('/api/series?qualification_id=' + qual + '&year=' + year);
            })
            .then(r => r && r.json())
            .then(series => {
                if (!series) return;
                series.forEach(s => { seriesMap[s.month] = s.id; });
            })
            .catch(() => btns.forEach(b => b.disabled = false));
    }

    function selectMonth(month) {
        document.getElementById('sel-month').value = month;
        highlightMonth(month);
    }

    function highlightMonth(month) {
        document.querySelectorAll('.month-btn').forEach(b => {
            b.classList.remove('border-indigo-500', 'bg-indigo-50', 'text-indigo-700');
            b.classList.add('border-slate-200', 'text-slate-500');
        });
        const active = document.getElementById('month-btn-' + month);
        if (active) {
            active.classList.add('border-indigo-500', 'bg-indigo-50', 'text-indigo-700');
            active.classList.remove('border-slate-200', 'text-slate-500');
        }
    }

    function openEntryGrid() {
        const year    = document.getElementById('sel-year').value;
        const qual    = document.getElementById('sel-qual').value;
        const month   = document.getElementById('sel-month').value;
        const subject = document.getElementById('sel-subject').value;

        if (!year || !qual || !month || !subject) {
            alert('Please select Year, Qualification, Session, and Subject before continuing.');
            return;
        }

        const seriesId = seriesMap[month];
        if (!seriesId) {
            alert('No exam series found for the selected combination. Please create the series first.');
            return;
        }

        window.location.href = '/manual-results/' + seriesId + '/' + subject;
    }

    // On page load, apply month highlight if previously selected
    document.addEventListener('DOMContentLoaded', () => {
        const month = document.getElementById('sel-month').value;
        if (month) highlightMonth(month);

        // Enable/disable month buttons based on pre-filled state
        const year = document.getElementById('sel-year').value;
        const qual = document.getElementById('sel-qual').value;
        if (year && qual) loadAvailableMonths();
    });
</script>
@endsection
