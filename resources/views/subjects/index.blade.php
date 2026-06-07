@extends('layouts.app')

@section('title', 'Subjects Management')
@section('page-title', 'Subjects Management')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Filter Panel -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Qualification Filter dropdown -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Filter by Qualification</label>
                <select id="filter-qual-select" onchange="applySubjectsFilter()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="all">All Qualifications</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual['id'] }}">{{ $qual['name'] }} ({{ $qual['type'] }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Text search filter -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Search Subject Name or Code</label>
                <input 
                    type="text" 
                    id="filter-search-input" 
                    oninput="applySubjectsFilter()" 
                    placeholder="Search by code or name..." 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm"
                />
            </div>
        </div>

        <div class="shrink-0 flex items-center gap-3">
            <span id="filtered-subjects-count" class="text-xxs font-extrabold text-slate-400 bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-xl uppercase"></span>
            <a href="{{ route('subjects.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition shrink-0">
                + Add New Subject
            </a>
        </div>
    </div>

    <!-- Subjects Grid (Cards/Tiles) -->
    @php
        $allSubjects = [];
    @endphp
    @foreach($qualifications as $qual)
        @foreach($qual['subjects_with_stats'] as $subj)
            @php
                $allSubjects[] = [
                    'id' => $subj['id'],
                    'code' => $subj['code'],
                    'name' => $subj['name'],
                    'qual_id' => $qual['id'],
                    'qual_name' => $qual['name'],
                    'qual_type' => $qual['type'],
                    'qualification_type' => $qual['qualification_type'],
                    'components_list' => $subj['components'],
                    'total_marks' => $subj['components']->sum('total_marks'),
                    'components_str' => $subj['components']->map(function($c) {
                        return "{$c->component_code} ({$c->total_marks}m)";
                    })->join(', '),
                    'statistics' => $subj['statistics'],
                    'total_students' => $subj['total_students'],
                    'grade_distribution' => $subj['grade_distribution']
                ];
            @endphp
        @endforeach
    @endforeach
    @php
        $allSubjects = collect($allSubjects)->sortBy('name')->values()->all();
        
        $cardColors = [
            [
                'bg' => 'bg-blue-50/20 hover:bg-blue-50/40',
                'border' => 'border-blue-200/80 hover:border-blue-400 hover:shadow-blue-100/50',
                'code' => 'text-blue-600 bg-blue-50 border-blue-200/80',
                'qual' => 'bg-indigo-50 text-indigo-700',
                'bullet' => 'bg-blue-500',
                'text' => 'group-hover:text-blue-700',
                'hover_glow' => 'shadow-blue-50/40'
            ],
            [
                'bg' => 'bg-emerald-50/20 hover:bg-emerald-50/40',
                'border' => 'border-emerald-200/80 hover:border-emerald-400 hover:shadow-emerald-100/50',
                'code' => 'text-emerald-600 bg-emerald-50 border-emerald-200/80',
                'qual' => 'bg-emerald-50 text-emerald-700',
                'bullet' => 'bg-emerald-500',
                'text' => 'group-hover:text-emerald-700',
                'hover_glow' => 'shadow-emerald-50/40'
            ],
            [
                'bg' => 'bg-indigo-50/20 hover:bg-indigo-50/40',
                'border' => 'border-indigo-200/80 hover:border-indigo-400 hover:shadow-indigo-100/50',
                'code' => 'text-indigo-600 bg-indigo-50 border-indigo-200/80',
                'qual' => 'bg-indigo-50 text-indigo-700',
                'bullet' => 'bg-indigo-500',
                'text' => 'group-hover:text-indigo-700',
                'hover_glow' => 'shadow-indigo-50/40'
            ],
            [
                'bg' => 'bg-pink-50/20 hover:bg-pink-50/40',
                'border' => 'border-pink-200/80 hover:border-pink-400 hover:shadow-pink-100/50',
                'code' => 'text-pink-600 bg-pink-50 border-pink-200/80',
                'qual' => 'bg-pink-50 text-pink-700',
                'bullet' => 'bg-pink-500',
                'text' => 'group-hover:text-pink-700',
                'hover_glow' => 'shadow-pink-50/40'
            ],
            [
                'bg' => 'bg-purple-50/20 hover:bg-purple-50/40',
                'border' => 'border-purple-200/80 hover:border-purple-400 hover:shadow-purple-100/50',
                'code' => 'text-purple-600 bg-purple-50 border-purple-200/80',
                'qual' => 'bg-purple-50 text-purple-700',
                'bullet' => 'bg-purple-500',
                'text' => 'group-hover:text-purple-700',
                'hover_glow' => 'shadow-purple-50/40'
            ],
            [
                'bg' => 'bg-amber-50/20 hover:bg-amber-50/40',
                'border' => 'border-amber-200/80 hover:border-amber-400 hover:shadow-amber-100/50',
                'code' => 'text-amber-600 bg-amber-50 border-amber-200/80',
                'qual' => 'bg-amber-50 text-amber-700',
                'bullet' => 'bg-amber-500',
                'text' => 'group-hover:text-amber-700',
                'hover_glow' => 'shadow-amber-50/40'
            ],
            [
                'bg' => 'bg-cyan-50/20 hover:bg-cyan-50/40',
                'border' => 'border-cyan-200/80 hover:border-cyan-400 hover:shadow-cyan-100/50',
                'code' => 'text-cyan-600 bg-cyan-50 border-cyan-200/80',
                'qual' => 'bg-cyan-50 text-cyan-700',
                'bullet' => 'bg-cyan-500',
                'text' => 'group-hover:text-cyan-700',
                'hover_glow' => 'shadow-cyan-50/40'
            ],
            [
                'bg' => 'bg-rose-50/20 hover:bg-rose-50/40',
                'border' => 'border-rose-200/80 hover:border-rose-400 hover:shadow-rose-100/50',
                'code' => 'text-rose-600 bg-rose-50 border-rose-200/80',
                'qual' => 'bg-rose-50 text-rose-700',
                'bullet' => 'bg-rose-500',
                'text' => 'group-hover:text-rose-700',
                'hover_glow' => 'shadow-rose-50/40'
            ],
            [
                'bg' => 'bg-teal-50/20 hover:bg-teal-50/40',
                'border' => 'border-teal-200/80 hover:border-teal-400 hover:shadow-teal-100/50',
                'code' => 'text-teal-600 bg-teal-50 border-teal-200/80',
                'qual' => 'bg-teal-50 text-teal-700',
                'bullet' => 'bg-teal-500',
                'text' => 'group-hover:text-teal-700',
                'hover_glow' => 'shadow-teal-50/40'
            ],
            [
                'bg' => 'bg-orange-50/20 hover:bg-orange-50/40',
                'border' => 'border-orange-200/80 hover:border-orange-400 hover:shadow-orange-100/50',
                'code' => 'text-orange-600 bg-orange-50 border-orange-200/80',
                'qual' => 'bg-orange-50 text-orange-700',
                'bullet' => 'bg-orange-500',
                'text' => 'group-hover:text-orange-700',
                'hover_glow' => 'shadow-orange-50/40'
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="subjects-grid">
        @forelse($allSubjects as $index => $s)
            @php
                $color = $cardColors[$index % count($cardColors)];
            @endphp
            <div 
                class="subject-item-card group relative {{ $color['bg'] }} border {{ $color['border'] }} hover:shadow-md rounded-2xl p-5 transition-all duration-300 flex flex-col justify-between cursor-pointer select-none overflow-hidden hover:-translate-y-0.5"
                data-qual-id="{{ $s['qual_id'] }}"
                data-search-str="{{ strtolower($s['code'] . ' ' . $s['name']) }}"
                id="subject-card-{{ $s['id'] }}"
                onclick="window.location.href='{{ route('subjects.edit', $s['id']) }}'"
            >
                <div class="space-y-4">
                    {{-- Header with Code & Qual Type --}}
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10px] font-bold {{ $color['code'] }} px-2 py-0.5 rounded-md border border-black/5 shadow-xxs">#{{ $s['code'] }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md {{ $color['qual'] }} text-[10px] font-black tracking-wider uppercase">
                            {{ $s['qual_type'] }}
                        </span>
                    </div>

                    {{-- Subject Name --}}
                    <div>
                        <h4 class="text-sm font-black text-slate-800 tracking-tight {{ $color['text'] }} transition duration-150 line-clamp-2" title="{{ $s['name'] }}" style="min-height: 2.5rem; line-height: 1.25rem;">
                            {{ $s['name'] }}
                        </h4>
                    </div>
                </div>

                {{-- Footer Action / Metric Strip --}}
                <div class="mt-4 pt-3 border-t border-slate-100/50 flex items-center justify-between">
                    {{-- Entries metric --}}
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500">
                        <span class="w-1.5 h-1.5 rounded-full {{ $color['bullet'] }} animate-pulse"></span>
                        <span>{{ $s['total_students'] }} {{ $s['total_students'] == 1 ? 'entry' : 'entries' }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3">
                        <!-- Red Delete Button -->
                        <form action="{{ route('subjects.destroy', $s['id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subject? All components and student results will be permanently removed.');" class="inline" onclick="event.stopPropagation()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="opacity-0 group-hover:opacity-100 text-rose-500 hover:text-rose-700 transition duration-200 text-[10px] font-extrabold uppercase tracking-wider" title="Delete Subject">
                                Delete
                            </button>
                        </form>

                        <!-- Go to Details indicator arrow -->
                        <span class="text-slate-400 group-hover:text-slate-600 group-hover:translate-x-0.5 transition-all duration-200">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div id="subjects-empty-row" class="col-span-full bg-white border border-slate-150 rounded-2xl p-16 text-center shadow-sm">
                <div class="text-4xl mb-3">🤷‍♂️</div>
                <p class="text-slate-500 text-xs font-semibold">No subjects registered in the system.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        applySubjectsFilter();
    });

    // Client-side grid filtering based on dropdown + search text
    function applySubjectsFilter() {
        const qualSelectVal = document.getElementById('filter-qual-select').value;
        const searchInputVal = document.getElementById('filter-search-input').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.subject-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const rowQualId = card.getAttribute('data-qual-id');
            const rowSearchStr = card.getAttribute('data-search-str');
            
            const matchQual = (qualSelectVal === 'all' || rowQualId === qualSelectVal);
            const matchSearch = (!searchInputVal || rowSearchStr.includes(searchInputVal));
            
            if (matchQual && matchSearch) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Update count indicator
        const countSpan = document.getElementById('filtered-subjects-count');
        if (countSpan) {
            countSpan.textContent = `showing ${visibleCount} / ${cards.length}`;
        }

        // Show empty state row if everything is filtered out
        const emptyRow = document.getElementById('subjects-empty-row');
        if (emptyRow) {
            if (visibleCount === 0 && cards.length > 0) {
                emptyRow.classList.remove('hidden');
            } else if (cards.length > 0) {
                emptyRow.classList.add('hidden');
            }
        }
    }
</script>
@endsection
