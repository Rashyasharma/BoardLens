@extends('layouts.app')

@section('title')
    {{ $candidate->candidate_name }} - Cambridge Exam Portal
@endsection

@section('page-title', 'Student Profile Analysis')

@section('content')
<div class="space-y-8">
    <!-- Go Back Link -->
    <div>
        <a href="{{ route('students.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition duration-150">
            &larr; Back to Students Catalog
        </a>
    </div>

    <!-- Candidate Profile Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4.5">
            <div class="h-16 w-16 bg-slate-900 text-white rounded-2xl flex items-center justify-center font-bold text-2xl tracking-wide shrink-0">
                {{ substr($candidate->candidate_name, 0, 2) }}
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $candidate->candidate_name }}</h2>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-slate-500 font-medium">
                    <span class="font-mono">No: {{ $candidate->candidate_number }}</span>
                    <span class="text-slate-300">&bull;</span>
                    <span>School: {{ $candidate->school->school_name }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-6 text-sm text-slate-600">
            <div class="flex flex-col bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Date of Birth</span>
                <span class="font-semibold text-slate-700 mt-0.5">{{ $candidate->date_of_birth ? $candidate->date_of_birth->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="flex flex-col bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Gender</span>
                <span class="font-semibold text-slate-700 mt-0.5">{{ $candidate->gender === 'F' ? 'Female' : ($candidate->gender === 'M' ? 'Male' : 'Other') }}</span>
            </div>
            <div class="flex flex-col bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Status</span>
                <span class="font-semibold text-emerald-600 mt-0.5 capitalize">{{ $candidate->status }}</span>
            </div>
            <a href="{{ route('students.edit', $candidate->id) }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition duration-150 self-end md:self-center shadow-sm">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Exam Results & Component Breakdowns -->
    <div class="space-y-6">
        <h3 class="text-xl font-bold text-slate-800 tracking-tight">Academic Performance Records</h3>
        
        @forelse($enrollments as $enrollment)
            @if(!$enrollment->subject)
                @continue
            @endif
            <!-- Subject Result Block -->
            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded font-mono">
                                {{ $enrollment->subject->subject_code }}
                            </span>
                            <h4 class="text-lg font-bold text-slate-800">{{ $enrollment->subject->subject_name }}</h4>
                        </div>
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-1">
                            Series: {{ $enrollment->series->series_name ?? 'N/A' }} &bull; Qualification: {{ $enrollment->qualification->qualification_name ?? 'N/A' }}
                        </p>
                    </div>

                    @if($enrollment->subjectResult)
                        <!-- Grade Badge & Pass Status -->
                        <div class="flex items-center gap-4.5">
                            <div class="flex flex-col items-end">
                                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Overall Grade</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @php
                                        $g = $enrollment->subjectResult->grade;
                                        $color = 'bg-slate-100 text-slate-800';
                                        if (in_array($g, ['A*', 'A*A*', 'A', 'AA', 'a'])) $color = 'bg-purple-100 text-purple-800';
                                        elseif (in_array($g, ['B', 'BB', 'b', 'c', 'CC', 'C'])) $color = 'bg-emerald-100 text-emerald-800';
                                        elseif (in_array($g, ['D', 'DD', 'd', 'e', 'EE', 'E'])) $color = 'bg-amber-100 text-amber-800';
                                        elseif (in_array($g, ['F', 'FF', 'G', 'GG'])) $color = 'bg-orange-100 text-orange-800';
                                        elseif (in_array($g, ['U', 'UU', 'u'])) $color = 'bg-rose-100 text-rose-800';
                                        elseif ($g === 'Q') $color = 'bg-sky-100 text-sky-800';
                                        elseif ($g === 'X') $color = 'bg-gray-100 text-gray-500';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-base font-black {{ $color }} font-mono whitespace-nowrap">
                                        @if($g === 'Q')
                                            Q (Pending)
                                        @elseif($g === 'X')
                                            X (No Result)
                                        @elseif($enrollment->qualification->qualification_type === 'AS_A_LEVEL' && in_array($g, ['a', 'b', 'c', 'd', 'e']))
                                            {{ $g }} (AS Level)
                                        @else
                                            {{ $g }}
                                        @endif
                                    </span>
                                    
                                    @if($g === 'Q')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Pending
                                        </span>
                                    @elseif($g === 'X')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-50 text-gray-500 border border-gray-200">
                                            No Result
                                        </span>
                                    @elseif($enrollment->subjectResult->is_passed)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Passed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Failed
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="h-10 w-px bg-slate-200"></div>

                            <div class="flex flex-col items-end">
                                <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">PUM Score</span>
                                <span class="text-lg font-black text-slate-800 mt-0.5 font-mono">
                                    @if($enrollment->subjectResult->pum !== null && $enrollment->subjectResult->pum > 0)
                                        {{ round($enrollment->subjectResult->pum, 1) }}%
                                    @elseif($enrollment->subjectResult->overall_percentage !== null && $enrollment->subjectResult->overall_percentage > 0)
                                        {{ round($enrollment->subjectResult->overall_percentage, 1) }}%
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                            Calculation Pending
                        </span>
                    @endif
                </div>

                <!-- Component Marks Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 bg-slate-50">
                                <th class="py-3 px-6">Component Code</th>
                                <th class="py-3 px-6">Component Name</th>
                                <th class="py-3 px-6 text-center">Marks Obtained</th>
                                <th class="py-3 px-6 text-center">Scaling Factor</th>
                                <th class="py-3 px-6 text-center">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($enrollment->componentMarks as $mark)
                                <tr>
                                    <td class="py-4 px-6 font-mono font-bold text-slate-500">
                                        {{ $mark->component->component_code }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-800 font-medium">
                                        {{ $mark->component->component_name }}
                                    </td>
                                    <td class="py-4 px-6 text-center font-mono font-semibold text-slate-700">
                                        {{ round($mark->obtained_marks, 1) }} / {{ $mark->total_marks }}
                                    </td>
                                    <td class="py-4 px-6 text-center text-slate-500 font-bold">
                                        {{ $mark->component->scaling_factor }}/10
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-center gap-3">
                                            <span class="font-mono font-semibold text-slate-700">{{ round($mark->percentage, 1) }}%</span>
                                            <div class="w-24 bg-slate-100 h-2 rounded-full overflow-hidden shrink-0 hidden sm:block">
                                                <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $mark->percentage }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-6 text-center text-slate-400">
                                        No component marks loaded for this enrollment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-3xl p-8 text-center text-slate-400 shadow-sm">
                This candidate is not enrolled in any examinations.
            </div>
        @endforelse
    </div>
</div>
@endsection
