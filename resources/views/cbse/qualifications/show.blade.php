@extends('layouts.app')

@section('title', $qualification->qualification_name)
@section('page-title', $qualification->qualification_name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.qualifications.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back
        </a>
        <h2 class="text-lg font-bold text-slate-800">Syllabus details for {{ $qualification->qualification_name }}</h2>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-150 bg-slate-50 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Subject Catalog</span>
            <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-full">Official CBSE Codes</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-150 text-xs uppercase">
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">Subject Name</th>
                        <th class="px-6 py-3">Theory Marks</th>
                        <th class="px-6 py-3">Practical / IA Marks</th>
                        <th class="px-6 py-3">Practical Type</th>
                        <th class="px-6 py-3">Total Max Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qualification->subjects as $subject)
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $subject->subject_code }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $subject->subject_name }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $subject->theory_marks }}</td>
                            <td class="px-6 py-4 text-slate-650">{{ $subject->practical_marks }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                    {{ $subject->practical_type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ $subject->total_marks }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450 italic">No subjects seeded for this qualification yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
