@extends('layouts.app')

@section('title', 'Manage Entries — ' . $academicYear->name)
@section('page-title', 'Academic Years')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">

    {{-- Breadcrumbs & Back link --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <a href="{{ route('cbse.academic-years.index') }}" class="hover:text-amber-600 transition">Academic Years</a>
            <span>›</span>
            <span class="text-slate-600">Manage LOC ({{ $academicYear->name }})</span>
        </div>
        <a href="{{ route('cbse.academic-years.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 transition">
            ← Back to Academic Years
        </a>
    </div>

    {{-- Header Strip --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
        <div>
            <h2 class="text-lg font-black text-slate-800">
                Manage Class 10 & 12 LOC
                <span class="text-slate-400 font-semibold">·</span>
                {{ $academicYear->name }}
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Register students and select their subjects for this academic year.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="text-xxs font-extrabold px-2.5 py-1 rounded-lg border {{ $academicYear->is_active ? 'bg-emerald-50 border-emerald-150 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-500' }}">
                {{ $academicYear->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>

    {{-- Qualification Tabs Header --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-1.5 flex gap-2">
        <button type="button" id="tab-class10-btn" onclick="switchQualTab('class10')" class="flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 bg-amber-600 text-white shadow-sm flex items-center justify-center gap-2">
            🎓 Class 10 LOC
            <span id="class10-count-badge" class="bg-white/20 text-white px-2 py-0.5 rounded-lg text-[10px] font-black">{{ $class10Students->count() }}</span>
        </button>
        <button type="button" id="tab-class12-btn" onclick="switchQualTab('class12')" class="flex-1 py-3 px-4 rounded-xl text-xs font-black transition duration-200 text-slate-600 hover:bg-slate-50 flex items-center justify-center gap-2">
            ⚡ Class 12 LOC
            <span id="class12-count-badge" class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-lg text-[10px] font-black">{{ $class12Students->count() }}</span>
        </button>
    </div>

    @php
        $colors = [
            'bg-blue-50/20', 'bg-emerald-50/20', 'bg-amber-50/20', 'bg-pink-50/20',
            'bg-purple-50/20', 'bg-cyan-50/20', 'bg-rose-50/20', 'bg-teal-50/20'
        ];
    @endphp

    {{-- ========================================== --}}
    {{-- CLASS 10 PANEL --}}
    {{-- ========================================== --}}
    <div id="panel-class10" class="space-y-4 animate-fade-in">
        {{-- Collapsible Quick Registration Form --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <button type="button" onclick="toggleAddForm('class10')" class="w-full px-6 py-4 flex items-center justify-between text-sm font-black text-slate-800 hover:bg-slate-50 transition">
                <span>👤 Create New Class 10 Student</span>
                <span id="add-icon-class10" class="text-xs text-amber-600 font-bold">＋ Expand Form</span>
            </button>
            <div id="add-form-class10" class="hidden border-t border-slate-100 bg-slate-50/30 p-6">
                <form method="POST" action="{{ route('cbse.student-entries.add-student', $academicYear->id) }}" class="space-y-4 max-w-4xl">
                    @csrf
                    <input type="hidden" name="qualification_type" value="CLASS_10" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Roll Number / Admission Number</label>
                            <input type="text" name="admission_number" required placeholder="e.g. 12345678" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Student Name</label>
                            <input type="text" name="student_name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                            Create Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Students List / Grid --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-sm font-black text-slate-800 shrink-0">Class 10 LOC</h3>
                <div class="flex flex-wrap items-center gap-3 w-full justify-end">
                    <button type="button" id="edit-btn-class10" onclick="toggleEditMode('class10')" 
                        class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        ✏️ Edit Entries
                    </button>
                    <button type="button" id="save-btn-class10" onclick="saveBulkChanges('class10')" 
                        class="hidden px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        💾 Save Changes
                    </button>
                    <button type="button" id="cancel-btn-class10" onclick="cancelEditMode('class10')" 
                        class="hidden px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black rounded-xl shadow-sm transition">
                        Cancel
                    </button>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <input type="text" id="search-class10" onkeyup="filterTable('class10')" placeholder="Search students..." class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 max-w-xs font-bold text-slate-700" />
                </div>
            </div>

            @if($class10Students->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No Class 10 students available.</p>
                </div>
            @else
                <div class="overflow-x-auto flex-1 relative">
                    <table class="w-full min-w-max text-left border-separate border-spacing-0" id="table-class10">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky bg-slate-50 z-20 border-r border-b border-slate-200 align-bottom w-32 min-w-[8rem]" style="left: 0px;">Roll No</th>
                                <th class="px-6 py-3 sticky bg-slate-50 z-20 border-r border-b border-slate-200 min-w-[14rem] align-bottom" style="left: 128px;">Name</th>
                                @foreach($class10Subjects as $idx => $sub)
                                    @php $color = $colors[$idx % count($colors)]; @endphp
                                    <th class="px-1 py-3 text-center min-w-[2.5rem] border-r border-b border-slate-200 align-bottom {{ $color }}" style="height: 10rem;">
                                        <div class="subject-header-vertical" style="writing-mode: vertical-rl; transform: rotate(180deg); margin: 0 auto;">
                                            <span class="text-[10px] font-black text-slate-600 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($class10Students as $student)
                                <tr class="hover:bg-amber-50/50 transition duration-100">
                                    <td class="px-4 py-3 sticky bg-white z-10 border-r border-b border-slate-100 w-32 min-w-[8rem]" style="left: 0px;">
                                        <input type="text" class="w-full px-2 py-1 text-xs border border-slate-200 rounded focus:ring-2 focus:ring-amber-500 outline-none font-bold text-slate-800" value="{{ $studentRollNumbers[$student->id] ?? '' }}" placeholder="Roll No" onblur="updateRollNumber(this, '{{ $student->id }}')" />
                                    </td>
                                    <td class="px-6 py-3 sticky bg-white z-10 border-r border-b border-slate-100 font-bold text-slate-800 min-w-[14rem]" style="left: 128px;">{{ $student->student_name }}</td>
                                    @foreach($class10Subjects as $idx => $sub)
                                        @php
                                            $enrolled = isset($studentSubjectsMap[$student->id]) && in_array($sub->id, $studentSubjectsMap[$student->id]);
                                        @endphp
                                        <td class="text-center border-r border-b border-slate-100 {{ $colors[$idx % count($colors)] }}">
                                            <div class="w-full h-full min-h-[3rem] flex items-center justify-center">
                                                <input type="checkbox" 
                                                       class="subject-toggle-checkbox rounded text-amber-600 focus:ring-amber-500 border-slate-300 shadow-sm transition-transform duration-200 cursor-pointer" 
                                                       {{ $enrolled ? 'checked' : '' }} 
                                                       data-student-id="{{ $student->id }}"
                                                       data-subject-id="{{ $sub->id }}"
                                                       data-qualification-id="{{ $class10Qual->id }}"
                                                       data-initial-checked="{{ $enrolled ? 'true' : 'false' }}"
                                                       disabled />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- CLASS 12 PANEL --}}
    {{-- ========================================== --}}
    <div id="panel-class12" class="space-y-4 hidden animate-fade-in">
        {{-- Collapsible Quick Registration Form --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <button type="button" onclick="toggleAddForm('class12')" class="w-full px-6 py-4 flex items-center justify-between text-sm font-black text-slate-800 hover:bg-slate-50 transition">
                <span>👤 Create New Class 12 Student</span>
                <span id="add-icon-class12" class="text-xs text-amber-600 font-bold">＋ Expand Form</span>
            </button>
            <div id="add-form-class12" class="hidden border-t border-slate-100 bg-slate-50/30 p-6">
                <form method="POST" action="{{ route('cbse.student-entries.add-student', $academicYear->id) }}" class="space-y-4 max-w-4xl">
                    @csrf
                    <input type="hidden" name="qualification_type" value="CLASS_12" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Roll Number / Admission Number</label>
                            <input type="text" name="admission_number" required placeholder="e.g. 12345678" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-xxs font-black text-slate-400 uppercase mb-1">Student Name</label>
                            <input type="text" name="student_name" required placeholder="e.g. Jane Smith" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500/20 focus:outline-none" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                            Create Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Students List / Grid --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-sm font-black text-slate-800 shrink-0">Class 12 LOC</h3>
                <div class="flex flex-wrap items-center gap-3 w-full justify-end">
                    <button type="button" id="edit-btn-class12" onclick="toggleEditMode('class12')" 
                        class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        ✏️ Edit Entries
                    </button>
                    <button type="button" id="save-btn-class12" onclick="saveBulkChanges('class12')" 
                        class="hidden px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                        💾 Save Changes
                    </button>
                    <button type="button" id="cancel-btn-class12" onclick="cancelEditMode('class12')" 
                        class="hidden px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black rounded-xl shadow-sm transition">
                        Cancel
                    </button>
                    <div class="h-4 w-px bg-slate-200 hidden md:block"></div>
                    <input type="text" id="search-class12" onkeyup="filterTable('class12')" placeholder="Search students..." class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 max-w-xs font-bold text-slate-700" />
                </div>
            </div>

            @if($class12Students->isEmpty())
                <div class="p-16 text-center my-auto">
                    <span class="text-3xl block mb-2">🤷‍♂️</span>
                    <p class="text-xs text-slate-400 font-semibold">No Class 12 students available.</p>
                </div>
            @else
                <div class="overflow-x-auto flex-1 relative">
                    <table class="w-full min-w-max text-left border-separate border-spacing-0" id="table-class12">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-3 sticky bg-slate-50 z-20 border-r border-b border-slate-200 align-bottom w-32 min-w-[8rem]" style="left: 0px;">Roll No</th>
                                <th class="px-6 py-3 sticky bg-slate-50 z-20 border-r border-b border-slate-200 min-w-[14rem] align-bottom" style="left: 128px;">Name</th>
                                @foreach($class12Subjects as $idx => $sub)
                                    @php $color = $colors[$idx % count($colors)]; @endphp
                                    <th class="px-1 py-3 text-center min-w-[2.5rem] border-r border-b border-slate-200 align-bottom {{ $color }}" style="height: 10rem;">
                                        <div class="subject-header-vertical" style="writing-mode: vertical-rl; transform: rotate(180deg); margin: 0 auto;">
                                            <span class="text-[10px] font-black text-slate-600 uppercase" title="{{ $sub->subject_name }}">{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                            @foreach($class12Students as $student)
                                <tr class="hover:bg-amber-50/50 transition duration-100">
                                    <td class="px-4 py-3 sticky bg-white z-10 border-r border-b border-slate-100 w-32 min-w-[8rem]" style="left: 0px;">
                                        <input type="text" class="w-full px-2 py-1 text-xs border border-slate-200 rounded focus:ring-2 focus:ring-amber-500 outline-none font-bold text-slate-800" value="{{ $studentRollNumbers[$student->id] ?? '' }}" placeholder="Roll No" onblur="updateRollNumber(this, '{{ $student->id }}')" />
                                    </td>
                                    <td class="px-6 py-3 sticky bg-white z-10 border-r border-b border-slate-100 font-bold text-slate-800 min-w-[14rem]" style="left: 128px;">{{ $student->student_name }}</td>
                                    @foreach($class12Subjects as $idx => $sub)
                                        @php
                                            $enrolled = isset($studentSubjectsMap[$student->id]) && in_array($sub->id, $studentSubjectsMap[$student->id]);
                                        @endphp
                                        <td class="text-center border-r border-b border-slate-100 {{ $colors[$idx % count($colors)] }}">
                                            <div class="w-full h-full min-h-[3rem] flex items-center justify-center">
                                                <input type="checkbox" 
                                                       class="subject-toggle-checkbox rounded text-amber-600 focus:ring-amber-500 border-slate-300 shadow-sm transition-transform duration-200 cursor-pointer" 
                                                       {{ $enrolled ? 'checked' : '' }} 
                                                       data-student-id="{{ $student->id }}"
                                                       data-subject-id="{{ $sub->id }}"
                                                       data-qualification-id="{{ $class12Qual->id }}"
                                                       data-initial-checked="{{ $enrolled ? 'true' : 'false' }}"
                                                       disabled />
                                            </div>
                                        </td>
                                    @endforeach
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
    function switchQualTab(tab) {
        if(tab === 'class10') {
            document.getElementById('panel-class10').classList.remove('hidden');
            document.getElementById('panel-class12').classList.add('hidden');
            document.getElementById('tab-class10-btn').classList.replace('text-slate-600', 'text-white');
            document.getElementById('tab-class10-btn').classList.replace('hover:bg-slate-50', 'bg-amber-600');
            document.getElementById('tab-class12-btn').classList.replace('text-white', 'text-slate-600');
            document.getElementById('tab-class12-btn').classList.replace('bg-amber-600', 'hover:bg-slate-50');
            document.getElementById('class10-count-badge').classList.replace('bg-slate-100', 'bg-white/20');
            document.getElementById('class10-count-badge').classList.replace('text-slate-500', 'text-white');
            document.getElementById('class12-count-badge').classList.replace('bg-white/20', 'bg-slate-100');
            document.getElementById('class12-count-badge').classList.replace('text-white', 'text-slate-500');
        } else {
            document.getElementById('panel-class12').classList.remove('hidden');
            document.getElementById('panel-class10').classList.add('hidden');
            document.getElementById('tab-class12-btn').classList.replace('text-slate-600', 'text-white');
            document.getElementById('tab-class12-btn').classList.replace('hover:bg-slate-50', 'bg-amber-600');
            document.getElementById('tab-class10-btn').classList.replace('text-white', 'text-slate-600');
            document.getElementById('tab-class10-btn').classList.replace('bg-amber-600', 'hover:bg-slate-50');
            document.getElementById('class12-count-badge').classList.replace('bg-slate-100', 'bg-white/20');
            document.getElementById('class12-count-badge').classList.replace('text-slate-500', 'text-white');
            document.getElementById('class10-count-badge').classList.replace('bg-white/20', 'bg-slate-100');
            document.getElementById('class10-count-badge').classList.replace('text-white', 'text-slate-500');
        }
    }

    function toggleAddForm(tab) {
        const form = document.getElementById('add-form-' + tab);
        const icon = document.getElementById('add-icon-' + tab);
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            icon.innerText = '－ Collapse Form';
        } else {
            form.classList.add('hidden');
            icon.innerText = '＋ Expand Form';
        }
    }

    function filterTable(tab) {
        let input = document.getElementById('search-' + tab).value.toLowerCase();
        let rows = document.querySelectorAll('#table-' + tab + ' tbody tr');
        rows.forEach(row => {
            let rollInput = row.cells[0].querySelector('input');
            let num = rollInput ? rollInput.value.toLowerCase() : '';
            let name = row.cells[1].innerText.toLowerCase();
            if (num.includes(input) || name.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
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
        let qualId = null;

        checkboxes.forEach(cb => {
            const currentVal = cb.checked;
            const initialVal = cb.dataset.initialChecked === 'true';

            if (!qualId) qualId = cb.dataset.qualificationId;

            if (currentVal !== initialVal) {
                entries.push({
                    student_id: cb.dataset.studentId,
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
            const response = await fetch("{{ route('cbse.student-entries.bulk-update', $academicYear->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
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

        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        }, 10);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    function updateRollNumber(inputEl, studentId) {
        const rollNumber = inputEl.value;

        fetch("{{ route('cbse.student-entries.update-roll-number', $academicYear->id) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                student_id: studentId,
                roll_number: rollNumber
            })
        }).then(res => {
            if (!res.ok) {
                alert("Failed to update roll number. Note: Roll numbers are saved to subject enrollments, so the student must be enrolled in at least one subject first.");
            } else {
                inputEl.classList.add('bg-emerald-50', 'border-emerald-200');
                setTimeout(() => inputEl.classList.remove('bg-emerald-50', 'border-emerald-200'), 1000);
            }
        }).catch(err => {
            alert("Network error.");
        });
    }
</script>

<style>
    .subject-header-vertical {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        margin: 0 auto;
    }
</style>
@endsection
