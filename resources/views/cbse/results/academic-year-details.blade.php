@extends('layouts.app')

@section('title', 'CBSE Session: ' . $academicYear->name)
@section('page-title', 'CBSE Session Details')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-4 animate-fade-in">
    <!-- Header Summary & Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xxs font-extrabold uppercase tracking-wider text-slate-400">
                <a href="{{ route('cbse.results.index') }}" class="hover:text-amber-600 transition">Results Hub</a>
                <span>/</span>
                <span class="text-slate-650">Session Overview</span>
            </div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Academic Year {{ $academicYear->name }} Results</h2>
            <p class="text-xs text-slate-500 font-semibold font-mono">CBSE Board Performance</p>
        </div>
        <div>
            <a href="{{ route('cbse.results.index') }}" class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl shadow-sm transition">
                ← Back to Results Hub
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-lg shrink-0">
                📚
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Groups</span>
                <span class="text-base font-black text-slate-800">{{ $qualificationsData->count() }}</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-lg shrink-0">
                👥
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Entries</span>
                <span class="text-base font-black text-slate-800">{{ $qualificationsData->sum('total_candidates') }}</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-violet-50 border border-violet-100 flex items-center justify-center text-lg shrink-0">
                📈
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Avg %</span>
                <span class="text-base font-black text-amber-700">
                    @php
                        $avgSession = $qualificationsData->avg('average_percentage');
                    @endphp
                    {{ $avgSession ? round($avgSession, 1) . '%' : 'N/A' }}
                </span>
            </div>
        </div>
        
        <button onclick="document.getElementById('modal-90plus').classList.remove('hidden')" class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm hover:shadow-md hover:border-emerald-300 hover:bg-emerald-50/20 transition text-left flex items-center gap-3 cursor-pointer group">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                🏆
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">90+ Scores</span>
                <span class="text-base font-black text-emerald-700">{{ $topScores->count() }}</span>
            </div>
        </button>
        
        <button onclick="document.getElementById('modal-under33').classList.remove('hidden')" class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm hover:shadow-md hover:border-rose-300 hover:bg-rose-50/20 transition text-left flex items-center gap-3 cursor-pointer group">
            <div class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-lg shrink-0 group-hover:scale-110 transition-transform">
                ⚠️
            </div>
            <div>
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">&lt;33 Scores</span>
                <span class="text-base font-black text-rose-700">{{ $lowScores->count() }}</span>
            </div>
        </button>
    </div>

    <!-- Toppers Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Class 10 Toppers -->
        <div class="bg-gradient-to-br from-amber-50 to-white border border-amber-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-7xl opacity-10">🏅</div>
            <h3 class="text-sm font-black text-amber-800 uppercase tracking-widest mb-4">
                Class 10 Top Performers
            </h3>
            <div class="space-y-3 relative z-10">
                @forelse($toppersClass10 as $index => $topper)
                    <div class="flex items-center justify-between bg-white/60 p-3 rounded-2xl border border-amber-100 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-black text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $topper->student_name }}</h4>
                                <p class="text-xs text-slate-500 font-medium">Marks: {{ $topper->aggregate_obtained }} / {{ $topper->aggregate_marks }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-2">
                            <span class="text-lg font-black text-amber-600">{{ $topper->percentage }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 italic">No Class 10 results yet.</p>
                @endforelse
            </div>
            @if(isset($qual10) && $qual10)
            <div class="mt-4 text-center relative z-10">
                <a href="{{ route('cbse.analysis.broadsheet', ['academic_year_id' => $academicYear->id, 'qualification_id' => $qual10->id]) }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-2">
                    📄 View Full Class 10 Broadsheet
                </a>
            </div>
            @endif
        </div>

        <!-- Class 12 Toppers -->
        <div class="bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-3xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -top-4 text-7xl opacity-10">🎓</div>
            <h3 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-4">
                Class 12 Top Performers
            </h3>
            <div class="space-y-3 relative z-10">
                @forelse($toppersClass12 as $index => $topper)
                    <div class="flex items-center justify-between bg-white/60 p-3 rounded-2xl border border-blue-100 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs shrink-0">
                                #{{ $index + 1 }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $topper->student_name }}</h4>
                                <p class="text-xs text-slate-500 font-medium">Marks: {{ $topper->aggregate_obtained }} / {{ $topper->aggregate_marks }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-2">
                            <span class="text-lg font-black text-blue-600">{{ $topper->percentage }}%</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 italic">No Class 12 results yet.</p>
                @endforelse
            </div>
            @if(isset($qual12) && $qual12)
            <div class="mt-4 text-center relative z-10">
                <a href="{{ route('cbse.analysis.broadsheet', ['academic_year_id' => $academicYear->id, 'qualification_id' => $qual12->id]) }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-2">
                    📄 View Full Class 12 Broadsheet
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Qualifications / Subjects Accordion Group -->
    <div class="space-y-8">
        @forelse($qualificationsData as $qual)
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 bg-amber-50 border border-amber-150 text-amber-700 font-extrabold rounded-lg text-xxs tracking-wider uppercase">
                            {{ $qual['qualification_name'] }}
                        </span>
                        <h3 class="text-sm font-bold text-slate-800">Syllabi &amp; Subject Performance</h3>
                    </div>
                    <span class="text-xxs font-black text-slate-400 uppercase">
                        {{ $qual['subject_count'] }} {{ $qual['subject_count'] == 1 ? 'Subject' : 'Subjects' }}
                    </span>
                </div>

                <!-- Subject Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($qual['subjects'] as $subj)
                        @php
                            $colors = [
                                ['from' => 'from-blue-50/50', 'text' => 'group-hover:text-blue-800', 'border' => 'hover:border-blue-300'],
                                ['from' => 'from-emerald-50/50', 'text' => 'group-hover:text-emerald-800', 'border' => 'hover:border-emerald-300'],
                                ['from' => 'from-amber-50/50', 'text' => 'group-hover:text-amber-800', 'border' => 'hover:border-amber-300'],
                                ['from' => 'from-purple-50/50', 'text' => 'group-hover:text-purple-800', 'border' => 'hover:border-purple-300'],
                                ['from' => 'from-rose-50/50', 'text' => 'group-hover:text-rose-800', 'border' => 'hover:border-rose-300'],
                                ['from' => 'from-teal-50/50', 'text' => 'group-hover:text-teal-800', 'border' => 'hover:border-teal-300'],
                            ];
                            $c = $colors[$loop->index % count($colors)];
                        @endphp
                        <a href="{{ route('cbse.results.subject-details', [$academicYear->id, $subj['subject_id']]) }}"
                           class="bg-gradient-to-b {{ $c['from'] }} to-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md {{ $c['border'] }} transition duration-200 flex flex-col justify-between space-y-4 group">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="text-sm font-bold text-slate-800 truncate {{ $c['text'] }} transition" title="{{ $subj['subject_name'] }}">
                                        {{ $subj['subject_name'] }}
                                    </h4>
                                    <span class="font-mono text-[10px] font-black text-slate-400 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded">
                                        {{ $subj['subject_code'] }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @if($subj['marks_entered'])
                                        <span class="px-2 py-0.5 bg-emerald-50 border border-emerald-150 text-emerald-700 font-extrabold rounded text-[9px] uppercase tracking-wider">
                                            Marks Entered
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-amber-50 border border-amber-150 text-amber-750 font-extrabold rounded text-[9px] uppercase tracking-wider">
                                            Pending Marks
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Subject Performance Metrics -->
                            <div class="space-y-3 pt-3 border-t border-slate-100">
                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                    <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-wider">Candidates</span>
                                        <span class="text-xs font-extrabold text-slate-800">{{ $subj['candidate_count'] }}</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-slate-450 uppercase tracking-wider">Pass / Fail</span>
                                        <span class="text-[10px] font-extrabold text-slate-800">
                                            <span class="text-emerald-600">{{ $subj['passed_count'] }}</span>/<span class="text-rose-500">{{ $subj['failed_count'] }}</span>
                                        </span>
                                    </div>
                                    <div class="bg-amber-50/50 rounded-xl p-2 border border-amber-100 flex flex-col justify-center">
                                        <span class="block text-[9px] font-black text-amber-600 uppercase tracking-wider">Avg %</span>
                                        <span class="text-xs font-black text-amber-800">{{ $subj['average_percentage'] }}%</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-[10px] text-amber-600 font-extrabold tracking-wider uppercase pt-1">
                                    <span>Open Details</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
                <p class="text-slate-500 text-sm font-semibold">No registered subjects found for this academic session.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal 90+ Scores -->
<div id="modal-90plus" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-emerald-50/50">
            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">🏆 90+ Scores ({{ $topScores->count() }})</h3>
            <button onclick="document.getElementById('modal-90plus').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="pb-2">Class</th>
                        <th class="pb-2">Roll No.</th>
                        <th class="pb-2">Student</th>
                        <th class="pb-2">Subject</th>
                        <th class="pb-2 text-right">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach($topScores as $score)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 font-semibold text-slate-500">{{ $score->student->qualification_label }}</td>
                        <td class="py-3 font-mono font-bold text-slate-600">{{ $score->roll_number }}</td>
                        <td class="py-3 font-bold text-slate-800">{{ $score->student->student_name }}</td>
                        <td class="py-3 font-semibold text-slate-600">{{ $score->subject->subject_name }}</td>
                        <td class="py-3 text-right font-black text-emerald-600">{{ $score->percentage }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal <33 Scores -->
<div id="modal-under33" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50/50">
            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">⚠️ &lt;33 Scores ({{ $lowScores->count() }})</h3>
            <button onclick="document.getElementById('modal-under33').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="pb-2">Class</th>
                        <th class="pb-2">Roll No.</th>
                        <th class="pb-2">Student</th>
                        <th class="pb-2">Subject</th>
                        <th class="pb-2 text-right">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach($lowScores as $score)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 font-semibold text-slate-500">{{ $score->student->qualification_label }}</td>
                        <td class="py-3 font-mono font-bold text-slate-600">{{ $score->roll_number }}</td>
                        <td class="py-3 font-bold text-slate-800">{{ $score->student->student_name }}</td>
                        <td class="py-3 font-semibold text-slate-600">{{ $score->subject->subject_name }}</td>
                        <td class="py-3 text-right font-black text-rose-600">{{ $score->percentage }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
