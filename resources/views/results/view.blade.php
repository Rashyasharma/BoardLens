@extends('layouts.app')

@section('title', 'View Results')
@section('page-title', 'Subject Results Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Filter Subject Results</h3>
        <form method="GET" action="{{ route('results.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Year -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Year</label>
                <select name="year" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">All Years</option>
                    @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Month -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Month</label>
                <select name="month" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">All Months</option>
                    <option value="March" {{ request('month') == 'March' ? 'selected' : '' }}>March</option>
                    <option value="June" {{ request('month') == 'June' ? 'selected' : '' }}>June</option>
                    <option value="November" {{ request('month') == 'November' ? 'selected' : '' }}>November</option>
                </select>
            </div>

            <!-- Subject -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-sans">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->subject_name }} ({{ $sub->subject_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="button" id="filterBtn" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-755 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition">
                    Filter
                </button>
                <a href="{{ route('results.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl border border-slate-200 text-center transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800">Results Records</h3>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total count: {{ $results->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-6 py-3">Candidate</th>
                        <th class="px-6 py-3">Qualification</th>
                        <th class="px-6 py-3">Subject & Series</th>
                        <th class="px-6 py-3">Uploaded Result</th>
                        <th class="px-6 py-3">Calculated Marks</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse($results as $res) <!-- data-row -->
                        <tr class="hover:bg-slate-50/50 transition duration-150" data-year="{{ $res->series->year ?? '' }}" data-month="{{ $res->series->month ?? '' }}" data-subject-id="{{ $res->subject_id }}" data-qualification="{{ $res->enrollment->qualification->qualification_type }}">
                            <!-- Candidate -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    <a href="{{ route('students.show', $res->enrollment->candidate->id) }}" class="hover:text-indigo-600 transition">
                                        {{ $res->enrollment->candidate->candidate_name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400">{{ $res->enrollment->candidate->candidate_number }}</div>
                            </td>
                            <!-- Qualification -->
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-extrabold rounded-lg">
                                    {{ $res->enrollment->qualification->qualification_type }}
                                </span>
                            </td>
                            <!-- Subject & Series -->
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">
                                    <a href="{{ route('results.index', ['subject_id' => $res->subject_id]) }}" class="hover:text-indigo-650 transition">
                                        {{ $res->subject->subject_name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-medium">{{ $res->series->series_name }}</div>
                            </td>
                            <!-- Uploaded Result -->
                            <td class="px-6 py-4">
                                @php
                                    $normalizedGrade = strtolower($res->grade);
                                    if (in_array($normalizedGrade, ['a*', 'a*a*', 'a', 'aa'])) {
                                        $gradeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                    } elseif (in_array($normalizedGrade, ['b', 'bb', 'c', 'cc'])) {
                                        $gradeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                    } elseif (in_array($normalizedGrade, ['d', 'dd', 'e', 'ee'])) {
                                        $gradeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                    } elseif (in_array($normalizedGrade, ['u', 'uu'])) {
                                        $gradeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                    } else {
                                        $gradeClass = 'bg-slate-50 text-slate-600 border border-slate-200';
                                    }
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center {{ in_array($res->grade, ['a', 'b', 'c', 'd', 'e']) ? 'px-2.5 h-7 rounded-lg' : 'w-7 h-7 rounded-full' }} {{ $gradeClass }} text-xs font-extrabold whitespace-nowrap">
                                        {{ in_array($res->grade, ['a', 'b', 'c', 'd', 'e']) ? $res->grade . ' (AS Level)' : $res->grade }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">PUM: {{ $res->pum }}%</span>
                                </div>
                            </td>
                            <!-- Calculated Marks -->
                            <td class="px-6 py-4">
                                @if($res->status === 'component_marks_added' || $res->status === 'complete')
                                    <div class="font-bold text-slate-800">{{ round($res->total_obtained_marks) }}/{{ $res->total_marks }}</div>
                                    <div class="text-xs text-slate-400">{{ round($res->overall_percentage) }}% (Uniform: {{ round($res->calculated_uniform_mark) }}%)</div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No component marks</span>
                                @endif
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                @if($res->status === 'component_marks_added' || $res->status === 'complete')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-100 text-emerald-800">
                                        Complete
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 border border-amber-100 text-amber-800">
                                        Pending Components
                                    </span>
                                @endif
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('results.show', $res->id) }}" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-900 hover:underline">
                                    View
                                </a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('results.edit-components', $res->id) }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 hover:underline">
                                    Edit Components
                                </a>
                                <span class="text-slate-300">|</span>
                                <form action="{{ route('results.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this result record? This will permanently remove their marks and grade.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">
                                        Delete Entry
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No subject result records found. Try adjusting your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Dynamic Filtering Script -->
        <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const yearSelect = document.querySelector('select[name="year"]');
                    const monthSelect = document.querySelector('select[name="month"]');
                    const subjectSelect = document.querySelector('select[name="subject_id"]');
                    const rows = document.querySelectorAll('tbody tr[data-year]');
                    const filterBtn = document.getElementById('filterBtn');

                    function filterRows() {
                        const year = yearSelect.value;
                        const month = monthSelect.value;
                        const subjectId = subjectSelect.value;
                        console.log('Filtering rows:', {year, month, subjectId});
                        rows.forEach(row => {
                            const rowYear = row.dataset.year || '';
                            const rowMonth = row.dataset.month || '';
                            const rowSubject = row.dataset.subjectId || '';
                            const matchYear = !year || rowYear === year;
                            const matchMonth = !month || rowMonth === month;
                            const matchSubject = !subjectId || rowSubject === subjectId;
                            if (matchYear && matchMonth && matchSubject) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }

                    // Initial filter on page load
                    filterRows();

                    // Apply filter on dropdown changes
                    [yearSelect, monthSelect, subjectSelect].forEach(select => {
                        select.addEventListener('change', filterRows);
                    });

                    // Prevent form submission and use button to trigger filter (optional)
                    if (filterBtn) {
                        filterBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            filterRows();
                        });
                    }
                });
        </script>

        @if($results->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
