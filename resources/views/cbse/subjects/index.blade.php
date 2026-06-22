@extends('layouts.app')

@section('title', 'CBSE Subjects')
@section('page-title', 'CBSE Subjects')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-slate-500 text-sm">Browse all subjects across CBSE qualifications.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('cbse.subjects.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-xl text-xs font-bold transition-all duration-200 shadow-sm hover:shadow-md">
                + Add Subject
            </a>
        </div>
    </div>

    <!-- Dynamic Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Client-side Qualification Filter -->
            <div class="space-y-1.5 flex-1 min-w-[200px]">
                <label for="qualification_filter" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Qualification</label>
                <select id="qualification_filter" class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
                    <option value="">All Qualifications</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">
                            {{ $qual->qualification_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Dynamic Subject Search -->
            <div class="space-y-1.5 flex-1 min-w-[200px]">
                <label for="subject_search" class="text-xxs font-bold text-slate-400 uppercase tracking-wider">Search Subject</label>
                <input type="text" id="subject_search" placeholder="Type subject name or code..." class="w-full bg-slate-50 border border-slate-250 rounded-xl px-4.5 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:bg-white transition-all duration-150">
            </div>

            <button type="button" id="reset_btn" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Reset
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="flex flex-wrap gap-3">
        <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 flex items-center gap-2 shadow-xs">
            <span class="text-amber-600 text-sm">📚</span>
            <span class="text-xs font-bold text-slate-700"><span id="stats_subject_count">{{ $subjects->count() }}</span> Subjects</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl px-4 py-2 flex items-center gap-2 shadow-xs">
            <span class="text-indigo-500 text-sm">👥</span>
            <span class="text-xs font-bold text-slate-700"><span id="stats_student_count">{{ $subjects->sum('total_candidates') }}</span> Total Registrations</span>
        </div>
    </div>

    <!-- Subjects Catalog (Premium Grid) -->
    <div id="subjects_grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($subjects as $subject)
            @php
                $qualType = $subject->qualification->qualification_type ?? '';
                $qualName = ($qualType === 'CLASS_10') ? 'Class 10' : (($qualType === 'CLASS_12') ? 'Class 12' : $qualType);
                $isClass12 = $qualType === 'CLASS_12';
                $accentFrom = $isClass12 ? 'from-amber-500' : 'from-indigo-500';
                $accentTo = $isClass12 ? 'to-orange-400' : 'to-blue-400';
                $badgeBg = $isClass12 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200';
                $avgPct = $subject->avg_percentage;
                $candidates = $subject->total_candidates ?? 0;
            @endphp
            <div class="subject-card group relative bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col"
                 data-qualification-id="{{ $subject->qualification_id }}"
                 data-subject-name="{{ strtolower($subject->subject_name) }}"
                 data-subject-code="{{ strtolower($subject->subject_code) }}"
                 data-candidates="{{ $candidates }}">
                {{-- Gradient accent bar --}}
                <div class="h-1 bg-gradient-to-r {{ $accentFrom }} {{ $accentTo }} opacity-60 group-hover:opacity-100 transition-opacity duration-300"></div>

                <div class="p-5 flex flex-col flex-1">
                    {{-- Header: Badge + Code --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-lg border {{ $badgeBg }} tracking-wider">
                            {{ $qualName }}
                        </span>
                        <span class="text-[11px] font-bold text-slate-400 font-mono tracking-wide group-hover:text-slate-600 transition">
                            {{ $subject->subject_code }}
                        </span>
                    </div>

                    {{-- Subject Name --}}
                    <h4 class="text-[15px] font-bold text-slate-800 group-hover:text-slate-900 transition leading-snug line-clamp-2 mb-4">
                        {{ $subject->subject_name }}
                    </h4>

                    {{-- Metrics --}}
                    <div class="mt-auto grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl px-3 py-2.5 text-center border border-slate-100">
                            <p class="text-[18px] font-extrabold text-slate-800 leading-none">{{ number_format($candidates) }}</p>
                            <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider mt-1">Students</p>
                        </div>
                        <div class="rounded-xl px-3 py-2.5 text-center border {{ $avgPct !== null ? ($isClass12 ? 'bg-amber-50/50 border-amber-100' : 'bg-indigo-50/50 border-indigo-100') : 'bg-slate-50 border-slate-100' }}">
                            @if($avgPct !== null)
                                <p class="text-[18px] font-extrabold leading-none {{ $isClass12 ? 'text-amber-700' : 'text-indigo-700' }}">{{ round($avgPct, 1) }}%</p>
                            @else
                                <p class="text-[14px] font-bold text-slate-300 leading-none mt-0.5">—</p>
                            @endif
                            <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider mt-1">Avg Score</p>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-slate-100 bg-slate-50/30">
                    <a href="{{ route('cbse.subjects.edit', $subject->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-amber-50 border border-slate-200 hover:border-amber-300 text-slate-500 hover:text-amber-700 text-[10px] font-bold rounded-lg transition duration-150">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('cbse.subjects.destroy', $subject->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subject?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-300 text-slate-500 hover:text-rose-600 text-[10px] font-bold rounded-lg transition duration-150 cursor-pointer">
                            🗑️ Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div id="no_subjects_msg" class="col-span-full text-center py-20 bg-white rounded-2xl border border-slate-200">
                <span class="text-4xl block mb-3">📚</span>
                <p class="text-slate-500 font-medium">No subjects matching search or filters.</p>
            </div>
        @endforelse
        
        <!-- Client-side fallback message for no results -->
        <div id="no_results_fallback" class="col-span-full text-center py-20 bg-white rounded-2xl border border-slate-200 hidden">
            <span class="text-4xl block mb-3">📚</span>
            <p class="text-slate-500 font-medium">No subjects matching search or filters.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qualFilter = document.getElementById('qualification_filter');
    const subjectSearch = document.getElementById('subject_search');
    const resetBtn = document.getElementById('reset_btn');
    const subjectCards = document.querySelectorAll('.subject-card');
    const fallbackMsg = document.getElementById('no_results_fallback');
    
    const statsSubjectCount = document.getElementById('stats_subject_count');
    const statsStudentCount = document.getElementById('stats_student_count');

    function applyFilters() {
        const selectedQual = qualFilter.value;
        const searchQuery = subjectSearch.value.trim().toLowerCase();
        
        let visibleCount = 0;
        let totalStudents = 0;

        subjectCards.forEach(card => {
            const cardQualId = card.getAttribute('data-qualification-id');
            const cardName = card.getAttribute('data-subject-name');
            const cardCode = card.getAttribute('data-subject-code');
            const cardCandidates = parseInt(card.getAttribute('data-candidates')) || 0;

            const matchesQual = !selectedQual || cardQualId === selectedQual;
            const matchesSearch = !searchQuery || cardName.includes(searchQuery) || cardCode.includes(searchQuery);

            if (matchesQual && matchesSearch) {
                card.classList.remove('hidden');
                visibleCount++;
                totalStudents += cardCandidates;
            } else {
                card.classList.add('hidden');
            }
        });

        // Update Stats Counters
        if (statsSubjectCount) statsSubjectCount.textContent = visibleCount;
        if (statsStudentCount) statsStudentCount.textContent = totalStudents.toLocaleString();

        // Handle empty state display
        if (visibleCount === 0 && subjectCards.length > 0) {
            fallbackMsg.classList.remove('hidden');
        } else {
            fallbackMsg.classList.add('hidden');
        }
    }

    qualFilter.addEventListener('change', applyFilters);
    subjectSearch.addEventListener('input', applyFilters);
    
    resetBtn.addEventListener('click', function() {
        qualFilter.value = '';
        subjectSearch.value = '';
        applyFilters();
    });

    // Run initial filter application
    applyFilters();
});
</script>
@endsection

