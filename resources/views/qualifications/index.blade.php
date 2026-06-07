@extends('layouts.app')

@section('title', 'Qualifications')
@section('page-title', 'Qualifications Catalog')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto py-4">
    <!-- Header Summary & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/85 shadow-sm mb-6 animate-fade-in">
        <div class="text-left space-y-1">
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Qualifications Catalog</h2>
            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                Select a qualification below to explore subjects, component marks, or configure syllabi.
            </p>
        </div>
        <div>
            <a href="{{ route('qualifications.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-sm transition-all duration-200">
                <span class="text-sm">+</span> Add Qualification
            </a>
        </div>
    </div>

    <!-- Qualifications Tiles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        @foreach($qualifications as $qual)
            @php
                $subjectsCount = count($qual['subjects_with_stats']);
                $isASALevel = ($qual['qualification_type'] === 'AS_A_LEVEL');
                $cardBg = $isASALevel ? 'hover:border-violet-300 hover:shadow-violet-500/5' : 'hover:border-indigo-300 hover:shadow-indigo-500/5';
                $badgeBg = $isASALevel ? 'bg-violet-50 text-violet-750 border-violet-150' : 'bg-indigo-50 text-indigo-750 border-indigo-150';
                $gradientGlow = $isASALevel 
                    ? 'bg-gradient-to-br from-violet-500/10 to-fuchsia-500/10' 
                    : 'bg-gradient-to-br from-indigo-500/10 to-sky-500/10';
            @endphp
            <a href="{{ route('qualifications.show', $qual['id']) }}" 
               class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6 group relative overflow-hidden {{ $cardBg }} animate-fade-in">
                <!-- Background decorative glow on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 {{ $gradientGlow }} pointer-events-none"></div>
                
                <div class="space-y-4 relative z-10">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-3.5 py-1 rounded-xl text-xs font-black tracking-wider border {{ $badgeBg }}">
                            {{ $qual['type'] }}
                        </span>
                        
                        <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-indigo-600 transition">
                            <span class="text-sm font-semibold">→</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight group-hover:text-indigo-900 transition">
                            {{ $qual['name'] }}
                        </h3>
                        <p class="text-xs text-slate-450 font-medium">
                            @if($isASALevel)
                                General Certificate of Education Advanced Subsidiary and Advanced Level syllabi.
                            @else
                                International General Certificate of Secondary Education syllabi.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Footer counts -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-semibold relative z-10">
                    <span class="text-xxs uppercase tracking-wider text-slate-400 font-extrabold">Configured Syllabi</span>
                    <span class="px-3 py-1 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-bold group-hover:bg-white transition">
                        {{ $subjectsCount }} Subjects
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
