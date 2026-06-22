@extends('layouts.app')

@section('title', 'Enter CBSE Results')
@section('page-title', 'Enter CBSE Results')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-slate-500 text-sm">Select subject and academic year to load registered candidates and enter marks.</p>
        </div>
        <a href="{{ route('cbse.results.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition self-start sm:self-auto">
            ← Back to Results List
        </a>
    </div>

    <!-- Selection Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('cbse.results.create') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end" id="selectorForm">
            <!-- Academic Year -->
            <div class="space-y-1.5">
                <label for="academic_year_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" required class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                    <option value="">Select Year</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ $selectedYearId == $y->id ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Qualification -->
            <div class="space-y-1.5">
                <label for="qualification_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Qualification</label>
                <select name="qualification_id" id="qualification_id" onchange="document.getElementById('subject_id').value=''; this.form.submit()" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                    <option value="">Select Qualification</option>
                    @foreach($qualifications as $q)
                        <option value="{{ $q->id }}" {{ $selectedQualId == $q->id ? 'selected' : '' }}>{{ $q->qualification_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject -->
            <div class="space-y-1.5">
                <label for="subject_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Subject</label>
                <select name="subject_id" id="subject_id" required class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                            [{{ $sub->subject_code }}] {{ $sub->subject_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                Load Registered Candidates
            </button>
        </form>
    </div>

    @if($selectedYearId && $selectedSubjectId)
        @if($enrolledResults->isEmpty())
            <div class="bg-white border border-slate-200 rounded-2xl p-16 text-center shadow-sm">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-slate-500 text-sm font-semibold">No candidates are registered for this subject in the selected academic year.</p>
                <p class="text-xs text-slate-402 mt-1">Please go to <a href="{{ route('cbse.student-entries.show', $selectedYearId) }}" class="text-amber-600 font-bold hover:underline">Academic Years</a> to register students first.</p>
            </div>
        @else
            <!-- Subject Spec Summary Banner -->
            <div class="bg-slate-800 text-white p-5 rounded-2xl flex flex-wrap justify-between items-center gap-4 shadow-sm">
                <div>
                    <h3 class="text-lg font-black tracking-tight">{{ $selectedSubject->subject_name }} (Code: {{ $selectedSubject->subject_code }})</h3>
                    <p class="text-slate-400 text-xs mt-1">
                        Qualification: <span class="text-white font-bold">{{ $selectedSubject->qualification->qualification_name }}</span>
                        &nbsp;&middot;&nbsp; Theory Max: <span class="text-white font-bold">{{ $selectedSubject->theory_marks }}</span>
                        &nbsp;&middot;&nbsp; Practical/IA Max: <span class="text-white font-bold">{{ $selectedSubject->practical_marks }}</span>
                    </p>
                </div>
                <div class="bg-slate-700/60 border border-slate-600 px-4 py-2.5 rounded-xl text-center">
                    <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider block">Registered Candidates</span>
                    <span class="text-2xl font-black text-amber-400">{{ $enrolledResults->count() }}</span>
                </div>
            </div>

            <!-- Marks Entry Grid -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm" id="marksGridTable">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                                <th class="px-6 py-4 w-[250px]">Student Name</th>
                                <th class="px-6 py-4 w-[160px]">Board Roll No.</th>
                                <th class="px-6 py-4 w-[120px]">Absent</th>
                                <th class="px-6 py-4 w-[160px]">Theory (Max: {{ $selectedSubject->theory_marks }})</th>
                                <th class="px-6 py-4 w-[160px]">Practical/IA (Max: {{ $selectedSubject->practical_marks }})</th>
                                <th class="px-6 py-4 w-[120px]">Total Obtained</th>
                                <th class="px-6 py-4 w-[120px] text-center">Outcome</th>
                                <th class="px-6 py-4 w-[80px] text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrolledResults as $res)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition duration-150" data-result-id="{{ $res->id }}">
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        {{ $res->student->student_name }}
                                        <span class="block text-xxs text-slate-400 font-mono font-normal">Adm: {{ $res->student->admission_number }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" 
                                               value="{{ $res->roll_number }}" 
                                               placeholder="Enter Roll No."
                                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition roll-input">
                                    </td>
                                    <td class="px-6 py-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   value="1" 
                                                   {{ $res->is_absent ? 'checked' : '' }}
                                                   class="h-4.5 w-4.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 absent-checkbox">
                                        </label>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $selectedSubject->theory_marks }}"
                                               value="{{ $res->is_absent ? '' : $res->theory_obtained }}" 
                                               {{ $res->is_absent ? 'disabled' : '' }}
                                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition theory-input">
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $selectedSubject->practical_marks }}"
                                               value="{{ $res->is_absent ? '' : $res->practical_obtained }}" 
                                               {{ $res->is_absent ? 'disabled' : '' }}
                                               class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition practical-input">
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-slate-700 total-cell">
                                        {{ $res->is_absent ? '—' : ($res->total_obtained ?? '0.00') }}
                                        <span class="block text-[10px] font-normal text-slate-400 pct-label">{{ $res->is_absent ? '' : ($res->percentage ? $res->percentage . '%' : '0.00%') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="outcome-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-bold uppercase bg-{{ $res->grade_badge_color ?? 'slate' }}-50 text-{{ $res->grade_badge_color ?? 'slate' }}-700">
                                            {{ $res->grade ?? 'PENDING' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center status-cell">
                                        <span class="status-indicator text-slate-350 text-xs">●</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Auto-save notification -->
            <div class="fixed bottom-6 right-6 bg-slate-800 text-white text-xs font-bold px-4 py-3 rounded-xl shadow-lg border border-slate-700 flex items-center gap-2 transition duration-200 opacity-0 pointer-events-none" id="saveToast">
                <span id="toastSpinner" class="animate-spin text-sm">🔄</span>
                <span id="toastText">Marks saved automatically</span>
            </div>
        @endif
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('marksGridTable');
    if (!table) return;

    const saveUrl = "{{ route('cbse.results.save-row') }}";
    const csrfToken = "{{ csrf_token() }}";
    const toast = document.getElementById('saveToast');
    const toastText = document.getElementById('toastText');
    const toastSpinner = document.getElementById('toastSpinner');

    function showToast(text, isError = false) {
        toastText.innerText = text;
        toast.className = `fixed bottom-6 right-6 text-white text-xs font-bold px-4 py-3 rounded-xl shadow-lg border flex items-center gap-2 transition duration-200 opacity-100 ` + 
            (isError ? 'bg-rose-800 border-rose-700' : 'bg-slate-800 border-slate-700');
        
        if (isError) {
            toastSpinner.style.display = 'none';
            setTimeout(() => toast.classList.add('opacity-0'), 4000);
        } else {
            toastSpinner.style.display = 'inline-block';
            setTimeout(() => toast.classList.add('opacity-0'), 2000);
        }
    }

    function saveRow(row) {
        const resultId = row.getAttribute('data-result-id');
        const rollInput = row.querySelector('.roll-input');
        const absentCheckbox = row.querySelector('.absent-checkbox');
        const theoryInput = row.querySelector('.theory-input');
        const practicalInput = row.querySelector('.practical-input');
        
        const totalCell = row.querySelector('.total-cell');
        const pctLabel = row.querySelector('.pct-label');
        const outcomeBadge = row.querySelector('.outcome-badge');
        const statusIndicator = row.querySelector('.status-indicator');

        // Update UI status to saving
        statusIndicator.innerText = '⏳';
        statusIndicator.className = 'status-indicator text-amber-500 font-bold';

        const payload = {
            result_id: resultId,
            roll_number: rollInput.value,
            is_absent: absentCheckbox.checked ? 1 : 0,
            theory_obtained: theoryInput.value,
            practical_obtained: practicalInput.value,
            _token: csrfToken
        };

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update row outcome
                if (absentCheckbox.checked) {
                    totalCell.innerHTML = '—<span class="block text-[10px] font-normal text-slate-400 pct-label"></span>';
                } else {
                    totalCell.innerHTML = Number(data.total_obtained).toFixed(2) + `<span class="block text-[10px] font-normal text-slate-400 pct-label">${data.percentage}%</span>`;
                }

                outcomeBadge.innerText = data.grade;
                outcomeBadge.className = `outcome-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xxs font-bold uppercase bg-${data.status_color}-50 text-${data.status_color}-700`;

                statusIndicator.innerText = '✓';
                statusIndicator.className = 'status-indicator text-emerald-600 font-black text-sm';
                showToast('Marks saved successfully.');
            } else {
                statusIndicator.innerText = '❌';
                statusIndicator.className = 'status-indicator text-rose-600 font-bold';
                showToast(data.error || 'Failed to save marks.', true);
            }
        })
        .catch(err => {
            statusIndicator.innerText = '❌';
            statusIndicator.className = 'status-indicator text-rose-600 font-bold';
            showToast('Network error saving marks.', true);
        });
    }

    // Attach event listeners
    table.querySelectorAll('tbody tr').forEach(row => {
        const inputs = row.querySelectorAll('input');
        const absentCheckbox = row.querySelector('.absent-checkbox');
        const theoryInput = row.querySelector('.theory-input');
        const practicalInput = row.querySelector('.practical-input');

        inputs.forEach(input => {
            input.addEventListener('change', () => saveRow(row));
        });

        const rollInput = row.querySelector('.roll-input');
        if (rollInput) {
            rollInput.addEventListener('blur', () => saveRow(row));
        }

        absentCheckbox.addEventListener('change', function() {
            if (this.checked) {
                theoryInput.disabled = true;
                practicalInput.disabled = true;
                theoryInput.value = '';
                practicalInput.value = '';
            } else {
                theoryInput.disabled = false;
                practicalInput.disabled = false;
            }
            saveRow(row);
        });
    });
});
</script>
@endsection
