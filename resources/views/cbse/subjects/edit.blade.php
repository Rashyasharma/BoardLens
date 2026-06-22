@extends('layouts.app')

@section('title', 'Edit CBSE Subject')
@section('page-title', 'Edit CBSE Subject')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.subjects.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('cbse.subjects.update', $subject->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="qualification_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Qualification</label>
                <select name="qualification_id" id="qualification_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}" {{ $subject->qualification_id == $qual->id ? 'selected' : '' }}>
                            {{ $qual->qualification_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="subject_code" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Subject Code (e.g. 041)</label>
                <input type="text" name="subject_code" id="subject_code" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required value="{{ $subject->subject_code }}">
            </div>

            <div class="space-y-1.5">
                <label for="subject_name" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Subject Name</label>
                <input type="text" name="subject_name" id="subject_name" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required value="{{ $subject->subject_name }}">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="theory_marks" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Theory Max Marks</label>
                    <input type="number" name="theory_marks" id="theory_marks" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required min="0" max="100" value="{{ $subject->theory_marks }}">
                </div>

                <div class="space-y-1.5">
                    <label for="practical_marks" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Practical/IA Max Marks</label>
                    <input type="number" name="practical_marks" id="practical_marks" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required min="0" max="100" value="{{ $subject->practical_marks }}">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="practical_type" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Practical Component Type</label>
                <select name="practical_type" id="practical_type" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="Practical" {{ $subject->practical_type == 'Practical' ? 'selected' : '' }}>🔬 Practical</option>
                    <option value="Project" {{ $subject->practical_type == 'Project' ? 'selected' : '' }}>📁 Project</option>
                    <option value="Internal Assessment" {{ $subject->practical_type == 'Internal Assessment' ? 'selected' : '' }}>📋 Internal Assessment</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="description" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Description (Optional)</label>
                <textarea name="description" id="description" rows="3" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">{{ $subject->description }}</textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition">
                Update Subject
            </button>
        </form>
    </div>
</div>
@endsection
