@extends('layouts.app')

@section('title', 'Results Details — ' . $subject->subject_name)
@section('page-title', 'Results Details')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Breadcrumbs & Back link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <a href="{{ route('results.index') }}" class="hover:text-indigo-600 transition">Results Hub</a>
            <span>›</span>
            <span class="text-slate-600">{{ $subject->qualification->type_display }}</span>
            <span>›</span>
            <span class="text-slate-600 font-bold">{{ $subject->subject_name }}</span>
        </div>
        <a href="{{ route('results.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 transition font-sans">
            ← Back to Results Hub
        </a>
    </div>

    <!-- Header Strip -->
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
        <div>
            <div class="flex items-center gap-2 text-xxs font-extrabold px-2 py-0.5 rounded-lg border bg-indigo-50 border-indigo-100 text-indigo-700 w-fit mb-1.5">
                {{ $subject->qualification->type_display }}
            </div>
            <h2 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                {{ $subject->subject_name }}
                <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">{{ $subject->subject_code }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">
                Exam Series: 
                <span class="text-slate-600 font-bold">
                    @switch($series->month)
                        @case('March') February/March {{ $series->year }} @break
                        @case('June') May/June {{ $series->year }} @break
                        @case('November') October/November {{ $series->year }} @break
                        @default {{ $series->month }} {{ $series->year }}
                    @endswitch
                </span>
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('manual-results.show', [$series->id, $subject->id]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-650 hover:bg-indigo-750 text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Manage / Upload Marks
            </a>
        </div>
    </div>

    <!-- Candidates Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-150 overflow-hidden animate-fade-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Candidate Results</h3>
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total candidates: {{ $results->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-6 py-3">Candidate</th>
                        <th class="px-6 py-3">Uploaded Grade & PUM</th>
                        <th class="px-6 py-3">Calculated Marks</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse($results as $res)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <!-- Candidate -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    <a href="{{ route('analysis.student-wise', ['candidate_number' => $res->enrollment->candidate->candidate_number]) }}" class="hover:text-indigo-650 transition">
                                        {{ $res->enrollment->candidate->candidate_name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $res->enrollment->candidate->candidate_number }}</div>
                            </td>
                            <!-- Uploaded Result -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center {{ in_array($res->grade, ['a', 'b', 'c', 'd', 'e']) ? 'px-2.5 h-7 rounded-lg' : 'w-7 h-7 rounded-full' }} bg-slate-900 text-white text-xs font-extrabold whitespace-nowrap">
                                        {{ in_array($res->grade, ['a', 'b', 'c', 'd', 'e']) ? $res->grade . ' (AS Level)' : $res->grade }}
                                    </span>
                                    <span class="text-xs text-slate-500 font-semibold">PUM: {{ $res->pum }}%</span>
                                </div>
                            </td>
                            <!-- Calculated Marks -->
                            <td class="px-6 py-4">
                                @if($res->status === 'component_marks_added' || $res->status === 'complete')
                                    <div class="font-bold text-slate-800">{{ round($res->total_obtained_marks) }}/{{ $res->total_marks }}</div>
                                    <div class="text-xs text-slate-450">{{ round($res->overall_percentage) }}% (Uniform: {{ round($res->calculated_uniform_mark) }}%)</div>
                                @else
                                    <span class="text-xs text-slate-400 italic font-medium">No component marks</span>
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
                            <td class="px-6 py-4 text-right space-x-2 shrink-0 whitespace-nowrap">
                                @if($res->status === 'pending_components')
                                    <a href="{{ route('uploads.components') }}?series_id={{ $res->series_id }}&subject_id={{ $res->subject_id }}" class="inline-flex items-center text-xs font-bold text-amber-600 hover:text-amber-800 hover:underline">
                                        Upload Components
                                    </a>
                                    <span class="text-slate-200">|</span>
                                @endif
                                <a href="{{ route('manual-results.show', [$series->id, $subject->id]) }}#row-{{ $res->enrollment_id }}" class="inline-flex items-center text-xs font-bold text-teal-650 hover:text-teal-850 hover:underline">
                                    Edit Marks
                                </a>
                                <span class="text-slate-200">|</span>
                                <a href="{{ route('results.show', $res->id) }}" class="inline-flex items-center text-xs font-bold text-indigo-650 hover:text-indigo-900 hover:underline">
                                    View Details
                                </a>
                                <span class="text-slate-200">|</span>
                                <form action="{{ route('results.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this candidate\'s results? This will permanently remove their marks and grade.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-650 hover:text-rose-850 hover:underline">
                                        Delete Entry
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                No candidates results found for this subject.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
