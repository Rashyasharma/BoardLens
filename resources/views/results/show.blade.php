@extends('layouts.app')

@section('title', 'Result Details')
@section('page-title', 'Student Subject Result Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Back Button -->
    <div class="flex justify-between items-center">
        <a href="{{ route('results.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 text-sm font-semibold transition">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Results Dashboard
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('results.edit-components', $result->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition">
                Edit Component Marks
            </a>
            <form action="{{ route('results.destroy', $result->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this result record? This will permanently remove their marks and grade.')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition">
                    Delete Entry
                </button>
            </form>
        </div>
    </div>

    <!-- Candidate and Subject Header -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3">
            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-extrabold rounded-lg uppercase tracking-wide">
                {{ $result->enrollment->qualification->qualification_name }}
            </span>
            <h2 class="text-2xl font-extrabold text-slate-800">{{ $result->enrollment->candidate->candidate_name }}</h2>
            <div class="text-sm text-slate-500 space-y-1">
                <p><strong>Candidate Number:</strong> {{ $result->enrollment->candidate->candidate_number }}</p>
                <p><strong>School:</strong> {{ $result->enrollment->candidate->school->school_name }}</p>
            </div>
        </div>
        <div class="md:border-l md:border-slate-100 md:pl-6 space-y-3">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Subject & Series</span>
            <h3 class="text-xl font-bold text-slate-800">{{ $result->subject->subject_name }} ({{ $result->subject->subject_code }})</h3>
            <p class="text-sm text-slate-500"><strong>Exam Series:</strong> {{ $result->series->series_name }} ({{ $result->series->series_code }})</p>
        </div>
    </div>

    <!-- Result Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between h-32 relative overflow-hidden">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Uploaded Grade</span>
            <span class="text-5xl font-black mt-2">{{ ($result->enrollment->qualification->qualification_type === 'AS_A_LEVEL' && in_array($result->grade, ['a', 'b', 'c', 'd', 'e'])) ? $result->grade . ' (AS Level)' : $result->grade }}</span>
            <div class="absolute -right-4 -bottom-6 opacity-10">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between h-32">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Percentage Uniform Mark (PUM)</span>
            <span class="text-4xl font-extrabold text-slate-800 mt-2">{{ $result->pum }}%</span>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between h-32">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Calculated Totals</span>
            @if($result->status === 'component_marks_added' || $result->status === 'complete')
                <span class="text-3xl font-extrabold text-slate-800 mt-2">
                    {{ round($result->total_obtained_marks) }}<span class="text-slate-400 text-lg">/{{ $result->total_marks }}</span>
                </span>
                <span class="text-xs text-slate-400 mt-1 font-semibold">{{ round($result->overall_percentage) }}% Overall Percentage</span>
            @else
                <span class="text-sm font-semibold text-amber-600 mt-2">Pending Component Upload</span>
            @endif
        </div>
    </div>

    <!-- Component Breakdown Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h4 class="text-lg font-bold text-slate-800">Component-wise Marks Breakdown</h4>
            <span class="text-xs text-slate-500 font-semibold">
                {{ $result->componentMarks->count() }} of {{ $result->expected_component_count }} Uploaded
            </span>
        </div>
        <div class="overflow-x-auto">
            @php
                $marksKeyed = $result->componentMarks->keyBy('component_id');
            @endphp
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-6 py-3">Component Label & Code</th>
                        <th class="px-6 py-3">Component Name</th>
                        <th class="px-6 py-3">Total Marks</th>
                        <th class="px-6 py-3">Obtained Marks</th>
                        <th class="px-6 py-3">Percentage</th>
                        <th class="px-6 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse($result->expected_components as $comp)
                        @php
                            $mark = $marksKeyed->get($comp->id);
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded border border-indigo-100">
                                    {{ $comp->component_label }} ({{ $comp->component_code }})
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $comp->component_name }}</td>
                            <td class="px-6 py-4">{{ $comp->total_marks }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                @if($mark)
                                    {{ round($mark->obtained_marks, 1) }}
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold">
                                @if($mark)
                                    <span class="text-emerald-600">{{ round($mark->percentage, 1) }}%</span>
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($mark)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Uploaded
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Pending Upload
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                No components defined for this subject.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
