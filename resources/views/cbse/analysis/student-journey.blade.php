@extends('layouts.app')

@section('title', 'CBSE Student Journey')
@section('page-title', 'CBSE Student Journey')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Select Student to Track Journey</h3>
        <form method="GET" action="{{ route('cbse.analysis.student-journey') }}" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1.5 flex-1 min-w-[240px]">
                <select name="student_name" id="student_name" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150" required>
                    <option value="">Choose a Student</option>
                    @foreach($students as $stud)
                        <option value="{{ $stud->student_name }}" {{ $studentName == $stud->student_name ? 'selected' : '' }}>
                            {{ $stud->student_name }} ({{ $stud->class_bracket }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                Load Journey
            </button>
        </form>
    </div>

    @if($selectedStudent)
        <div class="space-y-8">
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-md">
                <span class="text-xs font-black uppercase tracking-widest bg-white/20 px-3 py-1 rounded-full border border-white/10">Student Profile</span>
                <h2 class="text-2xl font-black mt-3">{{ $selectedStudent->student_name }}</h2>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm opacity-90">
                    <div><strong>Admission No:</strong> <span class="font-mono">{{ $selectedStudent->all_admission_numbers }}</span></div>
                    <div><strong>Gender:</strong> {{ $selectedStudent->gender_label }}</div>
                    <div><strong>Target:</strong> {{ $selectedStudent->class_bracket }}</div>
                    <div><strong>Admission Year:</strong> {{ $selectedStudent->admission_year }}</div>
                </div>
            </div>

            <!-- Timeline of academic results -->
            <div class="space-y-6">
                @foreach($studentJourney as $year => $results)
                    <div class="relative pl-8 sm:pl-32 before:content-[''] before:absolute before:left-3 sm:before:left-24 before:top-2 before:bottom-0 before:w-0.5 before:bg-slate-200">
                        <!-- Year label -->
                        <div class="absolute left-0 top-1 text-xs font-black text-slate-400 sm:text-slate-500 uppercase w-20 text-left sm:text-right hidden sm:block">
                            Year {{ $year }}
                        </div>
                        
                        <!-- Timeline dot -->
                        <div class="absolute left-1.5 sm:left-22.5 top-1.5 w-3.5 h-3.5 rounded-full bg-amber-500 ring-4 ring-amber-50"></div>

                        <!-- Card with results -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-150 bg-slate-50 flex justify-between items-center">
                                <h4 class="font-bold text-slate-850">CBSE Results for Academic Session {{ $year }}</h4>
                                <span class="text-xs font-semibold text-slate-450">{{ $results->count() }} Subjects</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($results as $res)
                                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <span class="text-xxs font-mono text-slate-400 bg-slate-200 px-2 py-0.5 rounded">
                                                    {{ $res->subject->subject_code }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-{{ $res->grade_badge_color }}-50 text-{{ $res->grade_badge_color }}-700 uppercase">
                                                    {{ $res->grade }}
                                                </span>
                                            </div>
                                            <h5 class="font-bold text-slate-800 mt-2 text-sm">{{ $res->subject->subject_name }}</h5>
                                            <p class="text-xxs text-slate-400 mt-0.5">{{ $res->qualification->qualification_name }}</p>
                                        </div>

                                        <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                                            <span>Score: <strong class="font-semibold text-slate-700">{{ $res->total_obtained }} / {{ $res->total_marks }}</strong></span>
                                            <span>Percentage: <strong class="font-semibold text-slate-750">{{ $res->percentage }}%</strong></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-slate-200">
            <span class="text-5xl mb-4">🛤️</span>
            <p class="text-slate-500 font-medium">Select a student above to inspect their year-by-year academic journey.</p>
        </div>
    @endif
</div>
@endsection
