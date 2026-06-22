@extends('layouts.app')

@section('title', 'Add CBSE Student')
@section('page-title', 'Add CBSE Student')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.students.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('cbse.students.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="admission_number" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Admission Number</label>
                <input type="text" name="admission_number" id="admission_number" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required placeholder="e.g. CBSE-2026-001">
            </div>

            <div class="space-y-1.5">
                <label for="student_name" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Student Name</label>
                <input type="text" name="student_name" id="student_name" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required placeholder="e.g. Aarav Sharma">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="father_name" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Father's Name</label>
                    <input type="text" name="father_name" id="father_name" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" placeholder="Father's name">
                </div>

                <div class="space-y-1.5">
                    <label for="mother_name" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Mother's Name</label>
                    <input type="text" name="mother_name" id="mother_name" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" placeholder="Mother's name">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="date_of_birth" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                </div>

                <div class="space-y-1.5">
                    <label for="gender" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Gender</label>
                    <select name="gender" id="gender" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="O">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="qualification_type" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Target Board Class</label>
                    <select name="qualification_type" id="qualification_type" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                        <option value="CLASS_10">Class 10 (Secondary)</option>
                        <option value="CLASS_12">Class 12 (Senior Secondary)</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="admission_year" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Admission Year</label>
                    <input type="number" name="admission_year" id="admission_year" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required value="{{ date('Y') }}" min="2000" max="2050">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="status" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Status</label>
                <select name="status" id="status" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="active">Active</option>
                    <option value="passed">Passed Out</option>
                    <option value="failed">Failed</option>
                    <option value="transferred">Transferred</option>
                </select>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition">
                Create Student
            </button>
        </form>
    </div>
</div>
@endsection
