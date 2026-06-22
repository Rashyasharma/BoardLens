@extends('layouts.app')

@section('title', $student->student_name)
@section('page-title', $student->student_name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.students.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back to Students
        </a>
    </div>

    <!-- Student details card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">General Information</h3>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <p><strong class="font-bold text-slate-800">Admission No:</strong> <span class="font-mono">{{ $student->admission_number }}</span></p>
                <p><strong class="font-bold text-slate-800">Gender:</strong> {{ $student->gender_label }}</p>
                <p><strong class="font-bold text-slate-800">Date of Birth:</strong> {{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '—' }}</p>
                <p><strong class="font-bold text-slate-800">Status:</strong> {{ ucfirst($student->status) }}</p>
            </div>
        </div>
        <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Parents & Enrollment</h3>
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                <p><strong class="font-bold text-slate-800">Father's Name:</strong> {{ $student->father_name ?? '—' }}</p>
                <p><strong class="font-bold text-slate-800">Mother's Name:</strong> {{ $student->mother_name ?? '—' }}</p>
                <p><strong class="font-bold text-slate-800">Class Qualification:</strong> {{ $student->qualification_label }}</p>
                <p><strong class="font-bold text-slate-800">Admission Year:</strong> {{ $student->admission_year }}</p>
            </div>
        </div>
    </div>

    <!-- Results list card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-150 bg-slate-50 flex justify-between items-center">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Academic Record (Year-wise Results)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                        <th class="px-6 py-3">Year</th>
                        <th class="px-6 py-3">Roll No.</th>
                        <th class="px-6 py-3">Subject</th>
                        <th class="px-6 py-3">Theory Obtained</th>
                        <th class="px-6 py-3">Practical / IA Obtained</th>
                        <th class="px-6 py-3">Total Obtained</th>
                        <th class="px-6 py-3">Percentage</th>
                        <th class="px-6 py-3">Grade</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($student->results as $result)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $result->academicYear ? $result->academicYear->name : '—' }}</td>
                            <td class="px-6 py-4 font-mono text-slate-650">{{ $result->roll_number ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">{{ $result->subject->subject_name }}</span>
                                <span class="block text-xxs text-slate-400">Code: {{ $result->subject->subject_code }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-650">{{ $result->is_absent ? 'ABS' : $result->theory_obtained . ' / ' . $result->subject->theory_marks }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $result->is_absent ? 'ABS' : $result->practical_obtained . ' / ' . $result->subject->practical_marks }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $result->is_absent ? '—' : $result->total_obtained . ' / ' . $result->total_marks }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $result->is_absent ? '—' : $result->percentage . '%' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $result->grade_badge_color }}-50 text-{{ $result->grade_badge_color }}-700 uppercase">
                                    {{ $result->grade }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($result->is_absent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">Absent</span>
                                @elseif($result->is_passed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">Pass</span>
                                @elseif($result->is_compartment)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Compartment</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700">Fail</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-450 italic">No results recorded for this student yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
