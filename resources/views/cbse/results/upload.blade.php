@extends('layouts.app')

@section('title', 'Bulk Upload CBSE Results')
@section('page-title', 'Bulk Upload CBSE Results')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('cbse.results.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
            ← Back
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('cbse.results.store-upload') }}" class="space-y-4">
            @csrf

            <!-- Subject selection -->
            <div class="space-y-1.5">
                <label for="subject_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Subject</label>
                <select name="subject_id" id="subject_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->subject_name }} (Code: {{ $subject->subject_code }} - Class: {{ $subject->qualification->qualification_type }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Academic Year -->
            <div class="space-y-1.5">
                <label for="academic_year_id" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="">Select Academic Year</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Paste CSV area -->
            <div class="space-y-1.5">
                <label for="file_content" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Paste CSV Data</label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xxs text-slate-500 mb-2">
                    Supports two formats (one record per line):<br>
                    1. <strong>admission_number/roll_number,total_obtained</strong> (Total out of 100)<br>
                    2. <strong>admission_number/roll_number,theory_obtained,practical_obtained</strong> (Bifurcated)<br>
                    <span class="text-slate-400 italic">Note: You can upload total marks now and upload the bifurcation details later.</span><br>
                    Examples:<br>
                    <code class="font-mono text-amber-700">CBSE-2026-001,85 (Single score)</code><br>
                    <code class="font-mono text-amber-700">CBSE-2026-001,65,20 (Bifurcated)</code>
                </div>
                <textarea name="file_content" id="file_content" rows="12" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 font-mono text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required placeholder="Paste CSV text here..."></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition">
                Process & Import Results
            </button>
        </form>
    </div>
</div>
@endsection
