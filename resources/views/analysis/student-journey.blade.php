@extends('layouts.app')

@section('title', 'Student Journey')
@section('page-title', 'Student Academic Journey')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12">
    <!-- Candidate Search and Selection Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    Select Candidate Journey
                    <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-150 text-[10px] font-extrabold text-indigo-700 rounded-full normal-case">
                        {{ count($candidates) }} Unique Candidates
                    </span>
                </h3>
                <p class="text-xs text-slate-450 mt-1">Select a student from the dropdown list to visualize their complete progression.</p>
            </div>
            
            <!-- Quick Search Input for candidates -->
            <div class="w-full md:w-64">
                <input 
                    type="text" 
                    id="candidate-search" 
                    placeholder="Search candidate name..." 
                    class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                />
            </div>
        </div>

        <form method="GET" action="{{ route('analysis.student-journey') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <select 
                    id="candidate-select" 
                    name="candidate_name" 
                    required 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-sans"
                >
                    <option value="">-- Choose Candidate --</option>
                    @foreach($candidates as $cand)
                        <option 
                            value="{{ $cand->candidate_name }}" 
                            data-name="{{ strtolower($cand->candidate_name) }}"
                            {{ $selected_candidate_name == $cand->candidate_name ? 'selected' : '' }}
                        >
                            {{ $cand->candidate_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition shrink-0">
                Visualize Journey
            </button>
        </form>
    </div>

    @if($student)
        <!-- Candidate Profile Summary Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <span class="text-xxs font-extrabold text-indigo-700 uppercase bg-indigo-50 border border-indigo-150 px-2 py-0.5 rounded">
                        Candidate Profile
                    </span>
                    <h2 class="text-xl font-bold text-slate-800 mt-2">{{ $student->candidate_name }}</h2>
                    <p class="text-xs text-slate-400 mt-1 font-medium">
                        Candidate Number(s): <span class="font-mono font-semibold text-slate-600 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded">{{ implode(', ', $all_candidate_numbers) }}</span> 
                        <span class="mx-1.5">|</span> School: <span class="text-slate-600 font-semibold">{{ $student->school->school_name }}</span>
                        @if($student->date_of_birth)
                            <span class="mx-1.5">|</span> DOB: <span class="text-slate-600 font-semibold">{{ $student->date_of_birth->format('d M Y') }}</span>
                        @endif
                    </p>
                </div>
                
                <!-- Overall Stats Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full md:w-auto shrink-0 pt-4 md:pt-0 border-t border-slate-100 md:border-none">
                    <div class="px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-center">
                        <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block">Total Papers</span>
                        <span class="text-lg font-black text-slate-800">{{ $total_results_count }}</span>
                    </div>
                    <div class="px-4 py-2.5 bg-indigo-50/50 border border-indigo-100 rounded-xl text-center">
                        <span class="text-xxs font-bold text-indigo-500 uppercase tracking-wider block">Avg PUM</span>
                        <span class="text-lg font-black text-indigo-700">
                            {{ $avg_pum_overall }}%
                        </span>
                    </div>
                    <div class="px-4 py-2.5 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                        <span class="text-xxs font-bold text-emerald-600 uppercase tracking-wider block">Best Grade</span>
                        <span class="text-lg font-black text-emerald-800 font-sans">
                            {{ $best_grade }}
                        </span>
                    </div>
                    <div class="px-4 py-2.5 bg-amber-50 border border-amber-100 rounded-xl text-center">
                        <span class="text-xxs font-bold text-amber-600 uppercase tracking-wider block">Pass Rate</span>
                        <span class="text-lg font-black text-amber-800">
                            {{ $pass_rate_overall }}%
                        </span>
                    </div>
                </div>
            </div>

            {{-- Export PDF Button --}}
            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                <a 
                    href="{{ route('analysis.student-journey.preview', ['candidate_name' => $student->candidate_name]) }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md transition duration-200 hover:-translate-y-0.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview &amp; Download Report Card
                </a>
            </div>
        </div>

        <!-- Progression Chart — Full Width -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150" id="chart-container">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Academic Progression Curve (PUM)</h3>
            @if(count($journey) > 0)
                <div class="h-64">
                    <canvas id="progressionChart"></canvas>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 text-center font-medium">Click any data point to jump to that series in the carousel below</p>
            @else
                <div class="h-64 flex items-center justify-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <p class="text-xs text-slate-450 italic">Insufficient data points to render progress chart.</p>
                </div>
            @endif
        </div>

        <!-- Series Journey Carousel -->
        @if(!empty($journey))
            <div class="bg-slate-100/50 rounded-3xl border border-slate-200 overflow-hidden relative shadow-inner">
                <!-- Carousel Header -->
                <div class="px-6 py-4 border-b border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Series Progression Journey</h3>
                    
                    <div class="flex items-center gap-4">
                        <!-- View Switcher Toggle -->
                        <div class="bg-slate-100 p-0.5 rounded-lg flex items-center gap-0.5 text-[11px] font-bold">
                            <button id="view-mode-carousel" class="px-2.5 py-1.5 rounded-md bg-white text-slate-800 shadow-sm transition flex items-center gap-1" type="button">
                                <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                </svg>
                                Carousel
                            </button>
                            <button id="view-mode-list" class="px-2.5 py-1.5 rounded-md text-slate-500 hover:text-slate-800 transition flex items-center gap-1" type="button">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                List View
                            </button>
                        </div>
                        
                        <!-- Carousel Navigation Controls -->
                        <div id="carousel-nav-controls" class="flex items-center gap-2">
                            <span id="carousel-counter" class="text-xs font-bold text-slate-400">1 / {{ count($journey) }}</span>
                            <button id="carousel-prev" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-650 hover:text-white text-slate-500 flex items-center justify-center transition-all duration-200 text-sm font-bold" type="button">&larr;</button>
                            <button id="carousel-next" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-indigo-650 hover:text-white text-slate-500 flex items-center justify-center transition-all duration-200 text-sm font-bold" type="button">&rarr;</button>
                        </div>
                    </div>
                </div>

                <!-- 3D Carousel Viewport -->
                <div id="carousel-viewport" class="relative w-full min-h-[460px] flex items-center justify-center overflow-hidden transition-all duration-300 py-6 select-none cursor-grab active:cursor-grabbing">
                    @php
                        $slideColors = [
                            'bg-indigo-50 border-indigo-150',
                            'bg-emerald-50 border-emerald-150',
                            'bg-amber-50 border-amber-150',
                            'bg-rose-50 border-rose-150',
                            'bg-sky-50 border-sky-150',
                            'bg-purple-50 border-purple-150',
                            'bg-teal-50 border-teal-150',
                            'bg-pink-50 border-pink-150',
                        ];
                    @endphp
                    @foreach($journey as $index => $stage)
                        @php
                            $colorClass = $slideColors[$index % count($slideColors)];
                        @endphp
                        <div class="carousel-slide p-6 rounded-3xl border shadow-md {{ $colorClass }}" data-slide="{{ $index }}">

                            <!-- Card Header Row -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-black text-slate-800">{{ $stage['series_name'] }}</h4>
                                    <p class="text-xxs font-semibold text-slate-400 mt-0.5">{{ $stage['month'] }} {{ $stage['year'] }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($stage['pum_delta'] !== null)
                                        @if($stage['pum_delta'] > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-100">
                                                ▲ +{{ $stage['pum_delta'] }}
                                            </span>
                                        @elseif($stage['pum_delta'] < 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100">
                                                ▼ {{ $stage['pum_delta'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-slate-500 bg-slate-50 border border-slate-100">
                                                ● 0.0
                                            </span>
                                        @endif
                                    @endif
                                    <span class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xxs font-extrabold text-slate-600 uppercase tracking-wide">
                                        Series {{ $index + 1 }} of {{ count($journey) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Stats Row -->
                            <div class="grid grid-cols-3 gap-2 mb-4">
                                <div class="px-3 py-2 bg-white border border-slate-100 rounded-xl text-center">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Subjects</span>
                                    <span class="text-lg font-black text-slate-800">{{ $stage['total_subjects'] }}</span>
                                </div>
                                <div class="px-3 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-center">
                                    <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-wider block">Avg PUM</span>
                                    <span class="text-lg font-black text-indigo-700">{{ $stage['avg_pum'] }}%</span>
                                </div>
                                <div class="px-3 py-2 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                                    <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider block">Best Grade</span>
                                    <span class="inline-flex items-center justify-center w-7 h-7 bg-emerald-600 text-white rounded-full text-xs font-extrabold shadow-sm mt-0.5">{{ $stage['best_grade'] }}</span>
                                </div>
                            </div>

                            <!-- Subject Results Scrollable Grid -->
                            <div class="max-h-[160px] overflow-y-auto pr-1 space-y-2 mb-4 scrollbar-thin">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($stage['results'] as $res)
                                        <div class="p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-indigo-50/20 hover:border-indigo-200 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between gap-1.5"
                                             onclick="openSubjectModal(this)"
                                             data-subject-name="{{ $res->subject->subject_name }}"
                                             data-subject-code="{{ $res->subject->subject_code }}"
                                             data-qualification="{{ $res->subject->qualification->qualification_name }}"
                                             data-grade="{{ $res->grade }}"
                                             data-pum="{{ $res->pum }}"
                                             data-components='{!! json_encode($res->componentMarks->map(fn($m) => ["code" => $m->component->component_code, "name" => $m->component->component_name, "obtained" => $m->obtained_marks, "total" => $m->component->total_marks])) !!}'>
                                            <div class="flex justify-between items-start gap-1">
                                                <div class="min-w-0">
                                                    <span class="px-1.5 py-0.2 bg-slate-100 border border-slate-200 text-[8px] font-bold text-slate-500 rounded">
                                                        {{ $res->subject->qualification->qualification_name }}
                                                    </span>
                                                    <h5 class="text-[11px] font-extrabold text-slate-800 mt-1 truncate" title="{{ $res->subject->subject_name }} ({{ $res->subject->subject_code }})">{{ $res->subject->subject_name }} ({{ $res->subject->subject_code }})</h5>
                                                </div>
                                                <span class="inline-flex items-center justify-center w-6 h-6 bg-slate-900 text-white rounded-full text-[10px] font-extrabold shadow-sm shrink-0">
                                                    {{ $res->grade }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between items-center pt-1 border-t border-slate-100">
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">PUM</span>
                                                <span class="text-sm font-black text-indigo-650">{{ $res->pum }}%</span>
                                            </div>

                                            <!-- Component Marks Breakdowns (If any) -->
                                            @if($res->componentMarks->isNotEmpty())
                                                <div class="pt-1 border-t border-slate-100 grid grid-cols-3 gap-0.5 bg-white p-1 rounded-lg border border-slate-150 text-[8px]">
                                                    @foreach($res->componentMarks as $mark)
                                                        <div class="text-center">
                                                            <span class="text-slate-400 font-bold font-mono block" title="{{ $mark->component->component_label ?? $mark->component->component_name }}">{{ $mark->component->component_label ?? $mark->component->component_name }} ({{ $mark->component->component_code }})</span>
                                                            <span class="font-black text-slate-700">{{ $mark->obtained_marks }}</span><span class="text-[7px] text-slate-400">/{{ $mark->component->total_marks }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Qualification Tags -->
                            @if(!empty($stage['qualifications']))
                                <div class="flex flex-wrap gap-1.5 pt-3 border-t border-slate-200">
                                    @foreach($stage['qualifications'] as $qual)
                                        <span class="px-2.5 py-0.5 bg-white border border-slate-200 rounded-full text-[9px] font-bold text-slate-500 uppercase tracking-wide">{{ $qual }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Summary List View -->
                <div id="summary-list-view" class="hidden divide-y divide-slate-150 border-t border-slate-200 bg-white">
                    @foreach($journey as $index => $stage)
                        <div class="p-6 hover:bg-slate-50/30 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-sm font-black text-indigo-700">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-slate-800">{{ $stage['series_name'] }}</h4>
                                        <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $stage['month'] }} {{ $stage['year'] }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if($stage['pum_delta'] !== null)
                                        @if($stage['pum_delta'] > 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-100">
                                                ▲ +{{ $stage['pum_delta'] }}
                                            </span>
                                        @elseif($stage['pum_delta'] < 0)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold text-rose-600 bg-rose-50 border border-rose-100">
                                                ▼ {{ $stage['pum_delta'] }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold text-slate-500 bg-slate-50 border border-slate-100">
                                                ● 0.0
                                            </span>
                                        @endif
                                    @endif
                                    <span class="text-sm font-black text-indigo-750 bg-indigo-50 border border-indigo-150 px-3 py-1 rounded-lg">
                                        Average PUM: {{ $stage['avg_pum'] }}%
                                    </span>
                                    <span class="text-xs font-extrabold text-slate-600 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-lg">
                                        {{ $stage['total_subjects'] }} {{ Str::plural('Subject', $stage['total_subjects']) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Subjects in this series -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($stage['results'] as $res)
                                    <div class="p-3.5 bg-slate-50/50 border border-slate-100 rounded-xl hover:bg-indigo-50/20 hover:border-indigo-200 cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-between gap-3"
                                         onclick="openSubjectModal(this)"
                                         data-subject-name="{{ $res->subject->subject_name }}"
                                         data-subject-code="{{ $res->subject->subject_code }}"
                                         data-qualification="{{ $res->subject->qualification->qualification_name }}"
                                         data-grade="{{ $res->grade }}"
                                         data-pum="{{ $res->pum }}"
                                         data-components='{!! json_encode($res->componentMarks->map(fn($m) => ["code" => $m->component->component_code, "name" => $m->component->component_name, "obtained" => $m->obtained_marks, "total" => $m->component->total_marks])) !!}'>
                                        <div class="min-w-0">
                                            <span class="px-2 py-0.5 bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-500 rounded">
                                                {{ $res->subject->qualification->qualification_name }}
                                            </span>
                                            <h5 class="text-sm font-bold text-slate-800 mt-1.5 truncate" title="{{ $res->subject->subject_name }} ({{ $res->subject->subject_code }})">{{ $res->subject->subject_name }} ({{ $res->subject->subject_code }})</h5>
                                            <p class="text-xs text-slate-450 font-semibold mt-1">PUM: <span class="text-base font-black text-indigo-600 ml-1">{{ $res->pum }}%</span></p>
                                        </div>
                                        <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-900 text-white rounded-full text-xs font-extrabold shadow-sm shrink-0">
                                            {{ $res->grade }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Dot Indicators -->
                <div id="carousel-dots-container" class="px-6 py-4 border-t border-slate-200 bg-white flex justify-center gap-2">
                    @foreach($journey as $index => $stage)
                        <button class="carousel-dot w-2.5 h-2.5 rounded-full transition-all duration-200 {{ $index === 0 ? 'bg-indigo-600 scale-125' : 'bg-slate-200' }}" data-target="{{ $index }}" title="{{ $stage['series_name'] }}"></button>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Series Journey Carousel</h3>
                <p class="text-sm text-slate-450 italic">No exams/subjects recorded in chronological series for this candidate.</p>
            </div>
        @endif

        <!-- Insights & Action Panel — 2-column grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Academic Insights — spans 2 cols -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        Academic Insights
                    </h3>

                    @if(!empty($insights))
                        <div class="space-y-4">
                            @foreach($insights as $insight)
                                <div class="p-4 rounded-xl border border-slate-150 {{ $insight['class'] }} flex items-start gap-3">
                                    <div class="space-y-1">
                                        <h4 class="text-xs font-bold font-sans tracking-wide uppercase">{{ $insight['title'] }}</h4>
                                        <p class="text-xs leading-relaxed font-medium mt-1">{!! $insight['description'] !!}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-xs text-slate-450 italic">No insights available. Ensure candidate has result records.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Action Box -->
            <div>
                <div class="bg-indigo-900 text-indigo-100 p-6 rounded-2xl shadow-sm border border-indigo-950">
                    <h4 class="text-xs font-extrabold uppercase tracking-widest text-indigo-300">Action Recommendations</h4>
                    <h3 class="text-base font-black text-white mt-1">Review & Plan Journey</h3>
                    <p class="text-xs leading-relaxed text-indigo-200 mt-2 font-sans font-medium">
                        Compare component mark splits of lower scoring subjects to plan targeted study programs before upcoming exams.
                    </p>
                    <div class="mt-4 pt-4 border-t border-indigo-800">
                        <a href="{{ route('analysis.student-wise') }}" class="inline-flex items-center text-xs font-bold text-white hover:text-indigo-200 gap-1 transition">
                            Go to Student Analysis
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js + Carousel Scripts -->
        @if(count($journey) > 0)
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // ─── Chart.js Progression Chart ───
                    const ctx = document.getElementById('progressionChart').getContext('2d');
                    
                    const labels = @json(collect($journey)->pluck('series_name')->toArray());
                    const pumData = @json(collect($journey)->pluck('avg_pum')->toArray());
                    
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)');
                    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Average PUM %',
                                data: pumData,
                                borderColor: '#4f46e5',
                                borderWidth: 3.5,
                                backgroundColor: gradient,
                                fill: true,
                                tension: 0.38,
                                pointBackgroundColor: '#4f46e5',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            onClick: function(evt, elements) {
                                if (elements.length > 0) {
                                    const index = elements[0].index;
                                    scrollToSlide(index);
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    titleFont: { size: 12, weight: 'bold' },
                                    bodyFont: { size: 12, weight: 'medium' },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            return `Average: ${context.raw}%`;
                                        },
                                        afterLabel: function(context) {
                                            return 'Click to view series details';
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    min: 0,
                                    max: 100,
                                    ticks: {
                                        stepSize: 20,
                                        font: { size: 10, weight: 'bold' },
                                        color: '#64748b'
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: { size: 10, weight: 'bold' },
                                        color: '#64748b'
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });

                    // ─── View Toggle Logic ───
                    const viewModeCarouselBtn = document.getElementById('view-mode-carousel');
                    const viewModeListBtn = document.getElementById('view-mode-list');
                    const carouselViewport = document.getElementById('carousel-viewport');
                    const summaryListView = document.getElementById('summary-list-view');
                    const carouselNavControls = document.getElementById('carousel-nav-controls');
                    const carouselDotsContainer = document.getElementById('carousel-dots-container');

                    function switchView(mode) {
                        if (mode === 'carousel') {
                            if (carouselViewport) carouselViewport.classList.remove('hidden');
                            if (summaryListView) summaryListView.classList.add('hidden');
                            if (carouselNavControls) carouselNavControls.classList.remove('hidden');
                            if (carouselDotsContainer) carouselDotsContainer.classList.remove('hidden');

                            if (viewModeCarouselBtn) {
                                viewModeCarouselBtn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                                viewModeCarouselBtn.classList.remove('text-slate-500');
                                const svg = viewModeCarouselBtn.querySelector('svg');
                                if (svg) svg.classList.add('text-indigo-500');
                            }

                            if (viewModeListBtn) {
                                viewModeListBtn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                                viewModeListBtn.classList.add('text-slate-500');
                            }
                        } else {
                            if (carouselViewport) carouselViewport.classList.add('hidden');
                            if (summaryListView) summaryListView.classList.remove('hidden');
                            if (carouselNavControls) carouselNavControls.classList.add('hidden');
                            if (carouselDotsContainer) carouselDotsContainer.classList.add('hidden');

                            if (viewModeListBtn) {
                                viewModeListBtn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
                                viewModeListBtn.classList.remove('text-slate-500');
                            }

                            if (viewModeCarouselBtn) {
                                viewModeCarouselBtn.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                                viewModeCarouselBtn.classList.add('text-slate-500');
                                const svg = viewModeCarouselBtn.querySelector('svg');
                                if (svg) svg.classList.remove('text-indigo-500');
                            }
                        }
                    }

                    if (viewModeCarouselBtn && viewModeListBtn) {
                        viewModeCarouselBtn.addEventListener('click', () => switchView('carousel'));
                        viewModeListBtn.addEventListener('click', () => switchView('list'));
                    }

                    // ─── 3D Carousel Coverflow Logic ───
                    const track = document.getElementById('carousel-viewport');
                    const counter = document.getElementById('carousel-counter');
                    const prevBtn = document.getElementById('carousel-prev');
                    const nextBtn = document.getElementById('carousel-next');
                    const dots = document.querySelectorAll('.carousel-dot');
                    const slides = track.querySelectorAll('.carousel-slide');
                    const totalSlides = {{ count($journey) }};
                    let currentSlide = 0;

                    function scrollToSlide(index) {
                        if (index < 0 || index >= totalSlides) return;
                        switchView('carousel'); 
                        updateCarouselClasses(index);
                    }

                    function updateCarouselClasses(index) {
                        currentSlide = index;
                        counter.textContent = `${index + 1} / ${totalSlides}`;
                        
                        slides.forEach((slide, i) => {
                            // Reset class state but preserve inline/Blade classes
                            slide.classList.remove('active', 'prev', 'next', 'far-prev', 'far-next');
                            
                            if (i === index) {
                                slide.classList.add('active');
                            } else if (i === index - 1) {
                                slide.classList.add('prev');
                            } else if (i === index + 1) {
                                slide.classList.add('next');
                            } else if (i < index) {
                                slide.classList.add('far-prev');
                            } else if (i > index) {
                                slide.classList.add('far-next');
                            }
                        });

                        dots.forEach((dot, i) => {
                            if (i === index) {
                                dot.classList.remove('bg-slate-200');
                                dot.classList.add('bg-indigo-600', 'scale-125');
                            } else {
                                dot.classList.remove('bg-indigo-600', 'scale-125');
                                dot.classList.add('bg-slate-200');
                            }
                        });

                        // Adjust container height to match active slide
                        setTimeout(() => {
                            const activeSlide = track.querySelector('.carousel-slide.active');
                            if (activeSlide) {
                                track.style.height = `${activeSlide.offsetHeight + 10}px`;
                            }
                        }, 50);
                    }

                    // Prev / Next buttons
                    prevBtn.addEventListener('click', () => scrollToSlide(currentSlide - 1));
                    nextBtn.addEventListener('click', () => scrollToSlide(currentSlide + 1));

                    // Card Click Navigation
                    slides.forEach((slide, i) => {
                        slide.addEventListener('click', () => {
                            if (slide.classList.contains('prev') || slide.classList.contains('next')) {
                                scrollToSlide(i);
                            }
                        });
                    });

                    // Dot click
                    dots.forEach(dot => {
                        dot.addEventListener('click', () => {
                            const target = parseInt(dot.dataset.target);
                            scrollToSlide(target);
                        });
                    });

                    // Keyboard navigation
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowLeft') scrollToSlide(currentSlide - 1);
                        if (e.key === 'ArrowRight') scrollToSlide(currentSlide + 1);
                    });

                    // ─── Touch and Gestures (Two-finger & Swipe) ───
                    let touchStartX = 0;
                    let touchEndX = 0;
                    let isThrottled = false;

                    track.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                    }, { passive: true });

                    track.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        handleSwipe();
                    }, { passive: true });

                    function handleSwipe() {
                        const diff = touchEndX - touchStartX;
                        if (Math.abs(diff) > 50) {
                            if (diff < 0) {
                                scrollToSlide(currentSlide + 1);
                            } else {
                                scrollToSlide(currentSlide - 1);
                            }
                        }
                    }

                    // Two-finger trackpad/scroll wheel gesture
                    track.addEventListener('wheel', function(e) {
                        // Detect horizontal swipe/scroll
                        if (Math.abs(e.deltaX) > Math.abs(e.deltaY) && Math.abs(e.deltaX) > 15) {
                            e.preventDefault();
                            if (isThrottled) return;
                            isThrottled = true;

                            if (e.deltaX > 0) {
                                scrollToSlide(currentSlide + 1);
                            } else {
                                scrollToSlide(currentSlide - 1);
                            }

                            setTimeout(() => {
                                isThrottled = false;
                            }, 500);
                        }
                    }, { passive: false });

                    // Initialize
                    scrollToSlide(0);

                    // Make scrollToSlide available globally for Chart.js onClick
                    window.scrollToSlide = scrollToSlide;
                });
            </script>
        @endif

        {{-- Styles for 3D Coverflow Carousel --}}
        <style>
            .carousel-slide {
                position: absolute;
                width: 85%;
                max-width: 580px;
                transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                opacity: 0;
                pointer-events: none;
                z-index: 0;
            }
            .carousel-slide.active {
                opacity: 1;
                transform: translateX(0) scale(1);
                z-index: 30;
                pointer-events: auto;
            }
            .carousel-slide.prev {
                opacity: 1;
                transform: translateX(-35%) scale(0.85);
                z-index: 20;
                pointer-events: auto;
                cursor: pointer;
            }
            .carousel-slide.next {
                opacity: 1;
                transform: translateX(35%) scale(0.85);
                z-index: 20;
                pointer-events: auto;
                cursor: pointer;
            }
            .carousel-slide.far-prev {
                opacity: 0;
                transform: translateX(-70%) scale(0.7);
                z-index: 10;
            }
            .carousel-slide.far-next {
                opacity: 0;
                transform: translateX(70%) scale(0.7);
                z-index: 10;
            }

            /* Custom scrollbar for subject list inside slide */
            .scrollbar-thin::-webkit-scrollbar {
                width: 4px;
            }
            .scrollbar-thin::-webkit-scrollbar-track {
                background: transparent;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 2px;
            }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>

    @else
        <!-- No Journey Loaded State -->
        <div class="text-center py-20 bg-white rounded-2xl border border-slate-150 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A2 2 0 013 15.485V5.132a2 2 0 011.553-1.954l5.447-1.362A2 2 0 0112 2.724l5.447 1.362A2 2 0 0119 5.132v10.353a2 2 0 01-1.553 1.954L12 20m0-18v18" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Track Student Journey</h3>
            <p class="text-sm text-slate-450 max-w-sm mx-auto">Track a candidate's complete multi-year progression timeline across qualifications. Select a candidate above to visualize.</p>
        </div>
    @endif
</div>

<!-- Simple JS Filter Script for Candidate Search Dropdown -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('candidate-search');
        const candidateSelect = document.getElementById('candidate-select');
        
        if (searchInput && candidateSelect) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                const options = candidateSelect.options;
                
                for (let i = 0; i < options.length; i++) {
                    const opt = options[i];
                    if (opt.value === "") continue; // Skip placeholder option
                    
                    const name = opt.getAttribute('data-name') || '';
                    const text = opt.text.toLowerCase();
                    
                    if (name.includes(query) || text.includes(query)) {
                        opt.style.display = "";
                    } else {
                        opt.style.display = "none";
                    }
                }
            });
        }

        const backdrop = document.getElementById('modal-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', closeSubjectModal);
        }
    });

    function openSubjectModal(element) {
        const name = element.getAttribute('data-subject-name');
        const code = element.getAttribute('data-subject-code');
        const qual = element.getAttribute('data-qualification');
        const grade = element.getAttribute('data-grade');
        const pum = element.getAttribute('data-pum');
        const components = JSON.parse(element.getAttribute('data-components') || '[]');

        document.getElementById('modal-subject-name').textContent = name;
        document.getElementById('modal-subject-code').textContent = 'Syllabus ' + code;
        document.getElementById('modal-qualification').textContent = qual;
        document.getElementById('modal-grade').textContent = grade;
        document.getElementById('modal-pum').textContent = pum + '%';

        const listContainer = document.getElementById('modal-components-list');
        listContainer.innerHTML = '';

        let totalObtained = 0;
        let totalMax = 0;
        let hasMarks = false;

        if (components.length > 0) {
            document.getElementById('modal-components-section').classList.remove('hidden');
            components.forEach(c => {
                if (c.obtained !== null && c.obtained !== undefined) {
                    hasMarks = true;
                    totalObtained += parseFloat(c.obtained);
                    totalMax += parseFloat(c.total);
                }
                
                const row = document.createElement('div');
                row.className = 'flex justify-between items-center bg-slate-50 border border-slate-100 p-2.5 rounded-xl text-xs';
                row.innerHTML = `
                    <div>
                        <span class="font-bold text-slate-700 block text-[11px]">${c.name || 'Component'}</span>
                        <span class="text-[9px] font-mono text-slate-400 font-bold">${c.code}</span>
                    </div>
                    <div class="font-mono font-bold text-slate-800 text-[11px]">
                        ${c.obtained !== null ? c.obtained : '—'} <span class="text-slate-400 text-[9px]">/ ${c.total}</span>
                    </div>
                `;
                listContainer.appendChild(row);
            });
        } else {
            document.getElementById('modal-components-section').classList.add('hidden');
        }

        if (hasMarks) {
            document.getElementById('modal-total-score').classList.remove('hidden');
            document.getElementById('modal-total-marks-val').textContent = `${totalObtained} / ${totalMax}`;
        } else {
            document.getElementById('modal-total-score').classList.add('hidden');
        }

        const modal = document.getElementById('subject-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.bg-white').classList.remove('scale-95', 'opacity-0');
            modal.querySelector('.bg-white').classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeSubjectModal() {
        const modal = document.getElementById('subject-modal');
        const box = modal.querySelector('.bg-white');
        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>

<!-- Modern, Minimalist Subject Details Modal -->
<div id="subject-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div id="modal-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>
    
    <!-- Modal Box -->
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-150 w-full max-w-md relative z-10 transform scale-95 opacity-0 transition-all duration-300 overflow-hidden">
        <!-- Close Button -->
        <button onclick="closeSubjectModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-50 border border-slate-100 hover:bg-slate-100 text-slate-500 flex items-center justify-center transition shadow-sm text-lg font-bold" type="button">
            &times;
        </button>

        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 bg-slate-50/50">
            <span id="modal-qualification" class="inline-block px-2.5 py-0.5 bg-indigo-50 border border-indigo-150 text-[9px] font-bold text-indigo-700 rounded-md uppercase tracking-wide"></span>
            <h3 id="modal-subject-name" class="text-base font-bold text-slate-800 mt-2"></h3>
            <p id="modal-subject-code" class="text-xs font-mono font-bold text-slate-400 mt-0.5"></p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-5">
            <!-- Grade & PUM Big Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 border border-slate-100 p-4 rounded-2xl text-center flex flex-col items-center justify-center">
                    <span class="text-[9px] font-bold text-slate-450 uppercase tracking-wider block">Grade</span>
                    <span id="modal-grade" class="inline-flex items-center justify-center w-8 h-8 bg-slate-900 text-white rounded-full text-xs font-extrabold shadow-sm mt-1.5"></span>
                </div>
                <div class="bg-indigo-50/30 border border-indigo-100 p-4 rounded-2xl text-center flex flex-col items-center justify-center">
                    <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-wider block">PUM</span>
                    <span id="modal-pum" class="text-base font-extrabold text-indigo-700 block mt-1.5"></span>
                </div>
            </div>

            <!-- Component Marks Section -->
            <div id="modal-components-section" class="space-y-3">
                <h4 class="text-[9px] font-bold text-slate-450 uppercase tracking-wider">Component Breakdown</h4>
                <div id="modal-components-list" class="space-y-2 max-h-48 overflow-y-auto pr-1 scrollbar-thin">
                    <!-- Dynamic Component Rows -->
                </div>
            </div>
            
            <!-- Total Score Summary -->
            <div id="modal-total-score" class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs font-bold text-slate-700">
                <span>Total Marks Obtained:</span>
                <span id="modal-total-marks-val" class="font-bold font-mono text-slate-800"></span>
            </div>
        </div>
    </div>
</div>
@endsection
