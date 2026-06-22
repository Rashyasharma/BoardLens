@extends('layouts.app')

@section('title', 'Edit CBSE Result')
@section('page-title', 'Edit CBSE Result')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.results.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="mb-6 border-b border-slate-150 pb-4">
            <h3 class="text-lg font-bold text-slate-800">{{ $result->student->student_name }}</h3>
            <p class="text-xs text-slate-500">Subject: [{{ $result->subject->subject_code }}] {{ $result->subject->subject_name }} (Class {{ $result->qualification->qualification_type }}) | Year: {{ $result->academicYear ? $result->academicYear->name : '—' }}</p>
        </div>

        <form method="POST" action="{{ route('cbse.results.update', $result->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Marks Obtained -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="theory_obtained" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Theory Marks Obtained (Max: {{ $result->subject->theory_marks }})</label>
                    <input type="number" name="theory_obtained" id="theory_obtained" step="0.01" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" value="{{ $result->theory_obtained }}" placeholder="e.g. 74.5">
                </div>

                <div class="space-y-1.5">
                    <label for="practical_obtained" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Practical/IA Marks Obtained (Max: {{ $result->subject->practical_marks }})</label>
                    <input type="number" name="practical_obtained" id="practical_obtained" step="0.01" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" value="{{ $result->practical_obtained }}" placeholder="e.g. 18.5">
                </div>
            </div>

            <!-- Absent toggle -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_absent" id="is_absent" value="1" {{ $result->is_absent ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-350 text-amber-600 focus:ring-amber-500">
                <label for="is_absent" class="text-sm font-semibold text-slate-700">Candidate was absent</label>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition">
                Update Result
            </button>
        </form>
    </div>
</div>
@endsection
