@extends('layouts.app')

@section('title', 'Marks Sheet — ' . $subject->subject_name)
@section('page-title', 'Marks Sheet')

@section('content')
<div class="space-y-5 max-w-full">

    {{-- Header strip --}}
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-1">
                <a href="{{ route('results.index') }}" class="hover:text-indigo-600 transition font-bold">Results Hub</a>
                <span>›</span>
                <span class="text-slate-650">{{ $subject->qualification->type_display }} — {{ $series->month }} {{ $series->year }}</span>
                <span>›</span>
                <span class="text-slate-650">{{ $subject->subject_name }}</span>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-base font-black text-slate-800">{{ $subject->subject_name }}</h2>
                <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">{{ $subject->subject_code }}</span>
                <span class="text-xxs font-extrabold px-2 py-0.5 rounded-lg border bg-indigo-50 border-indigo-100 text-indigo-700">
                    {{ $subject->qualification->type_display }}
                </span>
                @switch($series->month)
                    @case('March') <span class="text-xs font-bold text-slate-500">Feb/Mar {{ $series->year }}</span> @break
                    @case('June') <span class="text-xs font-bold text-slate-500">May/Jun {{ $series->year }}</span> @break
                    @case('November') <span class="text-xs font-bold text-slate-500">Oct/Nov {{ $series->year }}</span> @break
                @endswitch
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-xs text-slate-500 font-semibold" id="progress-indicator">
                <span id="saved-count">0</span> / {{ count($rows) }} saved
            </span>
            
            {{-- Edit Button --}}
            <button type="button" onclick="enableEditing()"
                id="edit-mode-btn"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-indigo-50 text-indigo-650 hover:text-indigo-700 text-xs font-bold rounded-xl shadow-sm transition border border-slate-200">
                ✏️ Edit Sheet
            </button>

            {{-- Save & Lock Button --}}
            <button type="button" onclick="saveAndLock()"
                id="save-all-btn"
                class="hidden inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save & Lock
            </button>
        </div>
    </div>

    {{-- Component legend --}}
    @if($components->isNotEmpty())
        <div class="bg-slate-50 border border-slate-150 rounded-xl px-5 py-3 flex flex-wrap gap-x-6 gap-y-1.5">
            @foreach($components as $comp)
                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                    <span class="font-mono font-bold text-slate-700 bg-white border border-slate-200 px-1.5 py-0.5 rounded">{{ $comp->component_code }}</span>
                    <span class="font-medium">{{ $comp->component_name }}</span>
                    <span class="text-slate-400">(max {{ $comp->total_marks }})</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- No candidates --}}
    @if($rows->isEmpty())
        <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
            <div class="text-4xl mb-3">👤</div>
            <p class="text-slate-500 text-sm font-semibold">No candidates enrolled for this subject in this series.</p>
            <a href="{{ route('student-entries.show', $series->id) }}" class="mt-3 inline-block text-indigo-600 text-xs font-bold hover:underline">
                → Go to Student Entries to enroll candidates
            </a>
        </div>
    @else
        {{-- Scrollable table --}}
        <div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm" id="results-table">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-150 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            {{-- Sticky columns --}}
                            <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left border-r border-slate-150 min-w-[7rem]">Cand. No.</th>
                            <th class="sticky left-28 z-10 bg-slate-50 px-4 py-3 text-left border-r border-slate-150 min-w-[11rem]">Name</th>
                            {{-- Functional columns --}}
                            <th class="px-3 py-3 text-center min-w-[7rem]">Grade</th>
                            <th class="px-3 py-3 text-center min-w-[7rem]">PUM</th>
                            {{-- Dynamic component columns --}}
                            @foreach($components as $comp)
                                <th class="px-3 py-3 text-center min-w-[8rem] border-l border-slate-100 bg-slate-50/80">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="font-mono text-indigo-600 font-extrabold">{{ $comp->component_code }}</div>
                                        <div class="text-xxs font-semibold text-slate-400 normal-case">/ {{ $comp->total_marks }}</div>
                                        <label class="inline-flex items-center gap-1 cursor-pointer mt-1 text-[10px] font-semibold text-indigo-500 hover:text-indigo-700">
                                            <input type="checkbox"
                                                class="header-applicable-toggle w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                data-component="{{ $comp->id }}"
                                                checked
                                                disabled
                                                onchange="toggleColumnApplicable('{{ $comp->id }}', this.checked)" />
                                            All
                                        </label>
                                    </div>
                                </th>
                            @endforeach
                            {{-- Total --}}
                            <th class="px-3 py-3 text-center min-w-[6rem] border-l border-slate-200 bg-indigo-50/30 text-indigo-700">Total</th>
                            {{-- Action --}}
                            <th class="px-4 py-3 text-center min-w-[5rem]">Save</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rows as $row)
                            <tr class="hover:bg-slate-50/30 transition duration-100 result-row"
                                id="row-{{ $row['enrollment_id'] }}"
                                data-enrollment="{{ $row['enrollment_id'] }}">

                                {{-- Sticky: Cand No. --}}
                                <td class="sticky left-0 z-[5] bg-white border-r border-slate-100 px-4 py-3">
                                    <span class="font-mono text-xs font-bold text-slate-600">{{ $row['candidate_no'] }}</span>
                                </td>

                                {{-- Sticky: Name --}}
                                <td class="sticky left-28 z-[5] bg-white border-r border-slate-100 px-4 py-3">
                                    <span class="font-semibold text-slate-700 text-xs">{{ $row['candidate_name'] }}</span>
                                </td>

                                {{-- Grade --}}
                                <td class="px-3 py-3 text-center">
                                    <select class="grade-input px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 w-full max-w-[8.5rem] disabled:opacity-80 disabled:cursor-not-allowed"
                                        data-enrollment="{{ $row['enrollment_id'] }}"
                                        disabled
                                        onchange="recalcTotal('{{ $row['enrollment_id'] }}')">
                                        <option value="">—</option>
                                        @foreach($grades as $g)
                                            @php
                                                $displayGrade = in_array($g, ['a', 'b', 'c', 'd', 'e']) ? $g . ' (AS Level)' : $g;
                                            @endphp
                                            <option value="{{ $g }}" {{ $row['grade'] === $g ? 'selected' : '' }}>{{ $displayGrade }}</option>
                                        @endforeach
                                    </select>
                                </td>

                                {{-- PUM --}}
                                <td class="px-3 py-3 text-center">
                                    <input type="number" step="0.01" min="0" max="100"
                                        class="pum-input px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-center focus:outline-none focus:ring-2 focus:ring-indigo-500/20 w-full max-w-[5.5rem] disabled:opacity-80 disabled:cursor-not-allowed"
                                        value="{{ $row['pum'] }}"
                                        placeholder="0–100"
                                        disabled
                                        data-enrollment="{{ $row['enrollment_id'] }}" />
                                </td>

                                {{-- Component marks --}}
                                @foreach($row['components'] as $compRow)
                                    <td class="px-3 py-3 text-center border-l border-slate-100"
                                        id="comp-cell-{{ $row['enrollment_id'] }}-{{ $compRow['component_id'] }}">
                                        <div class="flex flex-col items-center gap-1.5">
                                            {{-- Applicable checkbox --}}
                                            <input type="checkbox"
                                                class="applicable-toggle w-3.5 h-3.5 rounded border-slate-300 text-indigo-650 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer disabled:opacity-80 disabled:cursor-not-allowed"
                                                data-enrollment="{{ $row['enrollment_id'] }}"
                                                data-component="{{ $compRow['component_id'] }}"
                                                title="Is applicable?"
                                                disabled
                                                {{ $compRow['applicable'] ? 'checked' : '' }}
                                                onchange="onApplicableToggle(this)" />
                                            {{-- Marks input --}}
                                            <input type="number" step="0.5" min="0" max="{{ $compRow['max_marks'] }}"
                                                class="comp-marks-input px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-center focus:outline-none focus:ring-2 focus:ring-indigo-500/20 w-full max-w-[5rem] disabled:opacity-80 disabled:cursor-not-allowed
                                                    {{ !$compRow['applicable'] ? 'opacity-30 pointer-events-none' : '' }}"
                                                value="{{ $compRow['obtained'] ?? '' }}"
                                                placeholder="{{ $compRow['max_marks'] }}"
                                                data-enrollment="{{ $row['enrollment_id'] }}"
                                                data-component="{{ $compRow['component_id'] }}"
                                                data-max="{{ $compRow['max_marks'] }}"
                                                disabled
                                                id="comp-input-{{ $row['enrollment_id'] }}-{{ $compRow['component_id'] }}"
                                                {{ !$compRow['applicable'] ? 'disabled' : '' }}
                                                oninput="recalcTotal('{{ $row['enrollment_id'] }}')" />
                                        </div>
                                    </td>
                                @endforeach

                                {{-- Total --}}
                                <td class="px-3 py-3 text-center border-l border-slate-200 bg-indigo-50/20"
                                    id="total-cell-{{ $row['enrollment_id'] }}">
                                    @php
                                        $initTotal = collect($row['components'])
                                            ->where('applicable', true)
                                            ->sum(fn($c) => (float)($c['obtained'] ?? 0));
                                        $initMax = collect($row['components'])
                                            ->where('applicable', true)
                                            ->sum('max_marks');
                                    @endphp
                                    <div class="text-xs font-black text-indigo-700" id="total-obtained-{{ $row['enrollment_id'] }}">
                                        {{ $initTotal > 0 ? number_format($initTotal, 1) : '—' }}
                                    </div>
                                    <div class="text-xxs text-slate-400 font-semibold" id="total-max-{{ $row['enrollment_id'] }}">
                                        {{ $initMax > 0 ? '/ '.$initMax : '' }}
                                    </div>
                                </td>

                                {{-- Save button --}}
                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                        disabled
                                        onclick="saveRow('{{ $row['enrollment_id'] }}', '{{ $series->id }}', '{{ $subject->id }}')"
                                        id="save-row-btn-{{ $row['enrollment_id'] }}"
                                        class="save-row-btn px-3 py-1.5 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-xs font-bold text-slate-650 hover:text-indigo-700 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        Save
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer summary --}}
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/30 flex items-center justify-between text-xs text-slate-500 font-semibold">
                <span id="footer-status">{{ count($rows) }} candidates listed</span>
                
                {{-- Edit Trigger for Footer --}}
                <button type="button" onclick="enableEditing()" id="footer-edit-btn"
                    class="px-4 py-1.5 bg-indigo-50 text-indigo-750 border border-indigo-150 text-xs font-bold rounded-xl transition">
                    Edit Sheet
                </button>

                <button type="button" onclick="saveAndLock()" id="footer-save-btn"
                    class="hidden px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                    Save & Lock
                </button>
            </div>
        </div>
    @endif

</div>

<script>
    const SAVE_ROW_URL = "{{ route('manual-results.save-row', [$series->id, $subject->id]) }}";
    const CSRF_TOKEN   = "{{ csrf_token() }}";
    let savedRows = new Set();
    let isEditing = false;

    // ── Enable Editing Mode ───────────────────────────────────────────────
    function enableEditing() {
        isEditing = true;
        
        // Header Controls
        document.getElementById('edit-mode-btn').classList.add('hidden');
        document.getElementById('save-all-btn').classList.remove('hidden');

        // Footer Controls
        document.getElementById('footer-edit-btn').classList.add('hidden');
        document.getElementById('footer-save-btn').classList.remove('hidden');

        // Enable global selectors and checkboxes
        document.querySelectorAll('.header-applicable-toggle, .grade-input, .pum-input, .applicable-toggle, .save-row-btn').forEach(el => {
            el.disabled = false;
        });

        // Enable input fields only for active/applicable components
        document.querySelectorAll('.applicable-toggle').forEach(cb => {
            const enrollmentId = cb.dataset.enrollment;
            const compId       = cb.dataset.component;
            const input        = document.getElementById(`comp-input-${enrollmentId}-${compId}`);
            if (cb.checked) {
                input.disabled = false;
                input.classList.remove('opacity-30', 'pointer-events-none');
            }
        });

        // Styling indicators
        document.querySelectorAll('.grade-input, .pum-input, .comp-marks-input').forEach(el => {
            el.classList.remove('bg-slate-50');
            el.classList.add('bg-white');
        });
    }

    // ── Disable / Lock Editing Mode ────────────────────────────────────────
    function disableEditing() {
        isEditing = false;

        // Header Controls
        document.getElementById('edit-mode-btn').classList.remove('hidden');
        document.getElementById('save-all-btn').classList.add('hidden');

        // Footer Controls
        document.getElementById('footer-edit-btn').classList.remove('hidden');
        document.getElementById('footer-save-btn').classList.add('hidden');

        // Disable all selectors and checkboxes
        document.querySelectorAll('.header-applicable-toggle, .grade-input, .pum-input, .applicable-toggle, .comp-marks-input, .save-row-btn').forEach(el => {
            el.disabled = true;
        });

        // Styling indicators
        document.querySelectorAll('.grade-input, .pum-input, .comp-marks-input').forEach(el => {
            el.classList.add('bg-slate-50');
            el.classList.remove('bg-white');
        });
    }

    // ── Save all changes and lock sheet ──────────────────────────────────
    async function saveAndLock() {
        const saveBtn = document.getElementById('save-all-btn');
        const footerBtn = document.getElementById('footer-save-btn');
        
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';
        footerBtn.disabled = true;
        footerBtn.textContent = 'Saving…';

        const rows = document.querySelectorAll('.result-row');
        for (const row of rows) {
            const enrollmentId = row.dataset.enrollment;
            await new Promise(resolve => {
                const btn = document.getElementById('save-row-btn-' + enrollmentId);
                btn.click();
                setTimeout(resolve, 200); // brief delay
            });
        }

        saveBtn.disabled  = false;
        saveBtn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save & Lock`;
        footerBtn.disabled = false;
        footerBtn.textContent = 'Save & Lock';
        
        // Freeze everything again
        disableEditing();
    }

    // ── Calculate total from applicable components ──────────────────────────
    function recalcTotal(enrollmentId) {
        let obtained = 0;
        let maxTotal = 0;

        document.querySelectorAll(`.applicable-toggle[data-enrollment="${enrollmentId}"]:checked`).forEach(cb => {
            const compId = cb.dataset.component;
            const input  = document.getElementById(`comp-input-${enrollmentId}-${compId}`);
            const max    = parseFloat(input?.dataset.max ?? 0);
            const val    = parseFloat(input?.value ?? 0) || 0;
            obtained    += val;
            maxTotal    += max;
        });

        const obtEl = document.getElementById('total-obtained-' + enrollmentId);
        const maxEl = document.getElementById('total-max-' + enrollmentId);

        obtEl.textContent = obtained > 0 ? obtained.toFixed(1) : '—';
        maxEl.textContent = maxTotal > 0 ? '/ ' + maxTotal : '';
    }

    // ── Toggle applicable checkbox ─────────────────────────────────────────
    function onApplicableToggle(checkbox) {
        const enrollmentId = checkbox.dataset.enrollment;
        const compId       = checkbox.dataset.component;
        const input        = document.getElementById(`comp-input-${enrollmentId}-${compId}`);

        if (checkbox.checked) {
            input.disabled = false;
            input.classList.remove('opacity-30', 'pointer-events-none');
            input.classList.remove('bg-slate-50');
            input.classList.add('bg-white');
        } else {
            input.disabled = true;
            input.value    = '';
            input.classList.add('opacity-30', 'pointer-events-none');
            input.classList.add('bg-slate-50');
            input.classList.remove('bg-white');
        }
        recalcTotal(enrollmentId);
    }

    // ── Column-wide applicable bulk toggle ─────────────────────────────────
    function toggleColumnApplicable(componentId, isChecked) {
        document.querySelectorAll(`.applicable-toggle[data-component="${componentId}"]`).forEach(cb => {
            if (cb.checked !== isChecked) {
                cb.checked = isChecked;
                onApplicableToggle(cb);
            }
        });
    }

    // ── Sort Rows: sorted candidate-wise ────
    function sortRows() {
        const tbody = document.querySelector('#results-table tbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('.result-row'));

        rows.sort((a, b) => {
            const candNoA = a.querySelector('td.sticky:first-child span').textContent.trim();
            const candNoB = b.querySelector('td.sticky:first-child span').textContent.trim();

            return candNoA.localeCompare(candNoB, undefined, { numeric: true, sensitivity: 'base' });
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    // ── Save a single row ─────────────────────────────────────────────────
    function saveRow(enrollmentId, seriesId, subjectId) {
        const btn = document.getElementById('save-row-btn-' + enrollmentId);
        btn.disabled = true;
        btn.textContent = '…';

        const gradeEl = document.querySelector(`.grade-input[data-enrollment="${enrollmentId}"]`);
        const pumEl   = document.querySelector(`.pum-input[data-enrollment="${enrollmentId}"]`);

        const componentData = [];
        document.querySelectorAll(`.applicable-toggle[data-enrollment="${enrollmentId}"]`).forEach(cb => {
            const compId  = cb.dataset.component;
            const input   = document.getElementById(`comp-input-${enrollmentId}-${compId}`);
            const val     = input?.value ?? '';
            
            componentData.push({
                component_id: compId,
                applicable:   cb.checked,
                obtained:     parseFloat(val) || 0,
            });
        });

        const payload = {
            enrollment_id: enrollmentId,
            not_opted:     false,
            grade:         gradeEl?.value ?? null,
            pum:           pumEl?.value ?? null,
            components:    componentData,
        };

        fetch(SAVE_ROW_URL, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':  CSRF_TOKEN,
                'Accept':        'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.textContent = '✓ Saved';
                btn.className   = 'px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700 rounded-lg transition';
                savedRows.add(enrollmentId);
                updateSavedCount();
                setTimeout(() => {
                    if (isEditing) {
                        btn.disabled  = false;
                        btn.textContent = 'Save';
                        btn.className = 'px-3 py-1.5 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-xs font-bold text-slate-650 hover:text-indigo-700 rounded-lg transition';
                    } else {
                        btn.disabled  = true;
                        btn.textContent = 'Save';
                        btn.className = 'px-3 py-1.5 bg-white border border-slate-200 text-xs font-bold text-slate-650 rounded-lg transition disabled:opacity-40';
                    }
                }, 2000);
            } else {
                throw new Error(data.message ?? 'Unknown error');
            }
        })
        .catch(err => {
            btn.disabled  = false;
            btn.textContent = 'Retry';
            btn.className = 'px-3 py-1.5 bg-rose-50 border border-rose-200 text-xs font-bold text-rose-600 rounded-lg transition';
            console.error(err);
        });
    }

    function updateSavedCount() {
        document.getElementById('saved-count').textContent = savedRows.size;
    }

    // Init totals and sorting on load
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.result-row').forEach(row => {
            recalcTotal(row.dataset.enrollment);
        });
        
        sortRows();
    });
</script>

<style>
    /* Highlight focused row */
    .result-row:focus-within {
        background-color: rgb(241 245 249 / 0.4) !important;
    }

    /* Ensure sticky columns have proper background on hover */
    .result-row:hover td.sticky { background-color: #f8fafc; }

    /* Custom scrollbar for the table container */
    .overflow-x-auto::-webkit-scrollbar { height: 6px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: #f1f5f9; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Sticky left offsets — needs to match th/td widths */
    .sticky.left-28 { left: 7rem; }
</style>
@endsection
