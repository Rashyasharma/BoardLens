@extends('layouts.app')

@section('title', 'Results Details — ' . $subject->subject_name)
@section('page-title', 'Results Details')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Breadcrumbs & Back link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <a href="{{ route('cbse.results.index') }}" class="hover:text-amber-600 transition">Results Hub</a>
            <span>›</span>
            <a href="{{ route('cbse.results.year-details', $academicYear->id) }}" class="hover:text-amber-650 transition">{{ $academicYear->name }} Overview</a>
            <span>›</span>
            <span class="text-slate-600 font-bold">{{ $subject->subject_name }}</span>
        </div>
        <a href="{{ route('cbse.results.year-details', $academicYear->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-500 hover:text-slate-700 transition font-sans">
            ← Back to Session Overview
        </a>
    </div>

    <!-- Header Strip -->
    <div class="bg-white border border-slate-150 rounded-2xl shadow-sm px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-in">
        <div>
            <div class="flex items-center gap-2 text-xxs font-extrabold px-2 py-0.5 rounded-lg border bg-amber-50 border-amber-100 text-amber-700 w-fit mb-1.5">
                {{ $subject->qualification->qualification_name }}
            </div>
            <h2 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                {{ $subject->subject_name }}
                <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">{{ $subject->subject_code }}</span>
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">
                Academic Year: 
                <span class="text-slate-600 font-bold">{{ $academicYear->name }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <form action="{{ route('cbse.results.destroy-subject', [$academicYear->id, $subject->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete / clear all results of this subject for the academic year? This action is irreversible.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-bold rounded-xl shadow-sm transition">
                    🗑️ Delete / Clear All Marks
                </button>
            </form>

            <a href="{{ route('cbse.results.create') }}?academic_year_id={{ $academicYear->id }}&qualification_id={{ $subject->qualification_id }}&subject_id={{ $subject->id }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                ✏️ Bulk Edit
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in mt-6 mb-6">
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Avg Percentage</p>
            <div class="flex items-end gap-2">
                <span class="text-2xl font-black text-slate-800">{{ $averagePercentage !== null ? number_format($averagePercentage, 1) . '%' : '—' }}</span>
            </div>
            @if($lastYearAverage !== null)
                <p class="text-xs text-slate-500 mt-1 font-medium">Last year avg: {{ number_format($lastYearAverage, 1) }}%</p>
            @endif
        </div>
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Highest Marks</p>
            <div class="flex items-end gap-2">
                <span class="text-2xl font-black text-emerald-600">{{ $highestResult ? $highestResult->total_obtained : '—' }}</span>
            </div>
            @if($highestResult)
                <p class="text-xs text-slate-500 mt-1 font-bold">{{ $highestResult->student->student_name }}</p>
            @endif
        </div>
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Lowest Marks</p>
            <div class="flex items-end gap-2">
                <span class="text-2xl font-black text-rose-600">{{ $lowestResult ? $lowestResult->total_obtained : '—' }}</span>
            </div>
            @if($lowestResult)
                <p class="text-xs text-slate-500 mt-1 font-bold">{{ $lowestResult->student->student_name }}</p>
            @endif
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
                        <th class="px-6 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-amber-600 flex items-center gap-1">
                                Student {!! request('sort_by') === 'name' ? (request('sort_order') === 'asc' ? '↑' : '↓') : '↕' !!}
                            </a>
                        </th>
                        <th class="px-6 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'roll_number', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-amber-600 flex items-center gap-1">
                                Board Roll No. {!! request('sort_by') === 'roll_number' ? (request('sort_order') === 'asc' ? '↑' : '↓') : '↕' !!}
                            </a>
                        </th>
                        <th class="px-6 py-3">Theory Obtained</th>
                        <th class="px-6 py-3">Practical / IA Obtained</th>
                        <th class="px-6 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'marks', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-amber-600 flex items-center gap-1">
                                Total Obtained {!! request('sort_by') === 'marks' ? (request('sort_order') === 'asc' ? '↑' : '↓') : '↕' !!}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-center">Grade</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse($results as $res)
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <!-- Student -->
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">
                                    {{ $res->student->student_name }}
                                </div>
                            </td>
                            <!-- Roll No -->
                            <td class="px-6 py-4 font-mono font-bold text-slate-700">
                                {{ $res->roll_number ?? '—' }}
                            </td>
                            <!-- Theory -->
                            <td class="px-6 py-4">
                                @if($res->is_absent)
                                    <span class="text-xs font-bold text-rose-600">ABSENT</span>
                                @else
                                    {{ $res->theory_obtained !== null ? $res->theory_obtained : '—' }} <span class="text-slate-400">/ {{ $subject->theory_marks }}</span>
                                @endif
                            </td>
                            <!-- Practical -->
                            <td class="px-6 py-4">
                                @if($res->is_absent)
                                    <span class="text-xs font-bold text-rose-600">ABSENT</span>
                                @else
                                    {{ $res->practical_obtained !== null ? $res->practical_obtained : '—' }} <span class="text-slate-400">/ {{ $subject->practical_marks }}</span>
                                @endif
                            </td>
                            <!-- Total obtained -->
                            <td class="px-6 py-4">
                                @if($res->is_absent)
                                    <span class="text-slate-400">—</span>
                                @elseif($res->total_obtained !== null)
                                    <div class="font-bold text-slate-800">{{ $res->total_obtained }} / {{ $res->total_marks }}</div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No marks entered</span>
                                @endif
                            </td>
                            <!-- Grade -->
                            <td class="px-6 py-4 text-center">
                                @if($res->is_absent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">F</span>
                                @elseif($res->grade)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $res->grade_badge_color }}-50 text-{{ $res->grade_badge_color }}-700 uppercase">
                                        {{ $res->grade }}
                                    </span>
                                @else
                                    <span class="text-slate-350">—</span>
                                @endif
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-right shrink-0 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('cbse.results.edit', $res->id) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-slate-100 hover:bg-amber-100 text-slate-600 hover:text-amber-700 text-xs font-bold rounded-lg transition" title="Edit">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('cbse.results.destroy', $res->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this result entry?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-700 text-xs font-bold rounded-lg transition cursor-pointer" title="Delete">
                                            🗑️ Del
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                No candidates registered for this subject in the session.
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
