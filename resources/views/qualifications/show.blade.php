@extends('layouts.app')

@section('title', $qualification->qualification_name)
@section('page-title', $qualification->qualification_name)

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-4">
    <!-- Breadcrumb / Header Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-3xl border border-slate-200 shadow-sm animate-fade-in">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xxs font-extrabold uppercase tracking-wider text-slate-400">
                <a href="{{ route('qualifications.index') }}" class="hover:text-indigo-600 transition">Qualifications</a>
                <span>/</span>
                <span class="text-slate-600">{{ $qualification->qualification_type }}</span>
            </div>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ $qualification->qualification_name }}</h2>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('qualifications.edit', $qualification->id) }}" class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl shadow-sm transition">
                Edit Qualification
            </a>
            <form action="{{ route('qualifications.destroy', $qualification->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this qualification? This will delete all associated subjects and results.');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 text-xs font-bold rounded-xl shadow-sm transition">
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if($qualification->description)
        <div class="bg-slate-50 border border-slate-150 p-5 rounded-2xl text-xs font-medium text-slate-600 leading-relaxed max-w-3xl">
            <span class="font-bold text-slate-700 block mb-1">About Qualification:</span>
            {{ $qualification->description }}
        </div>
    @endif

    @if(count($subjects_with_stats) > 0)
        <!-- Two Column Interactive Catalog -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Side: Subjects Selector Tiles List (Spans 5 cols) -->
            <div class="lg:col-span-5 space-y-4 max-h-[800px] overflow-y-auto pr-2 scrollbar-thin">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Configured Subjects</span>
                    <span class="text-xxs font-black text-indigo-650 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md">{{ count($subjects_with_stats) }} Total</span>
                </div>
                
                <div class="space-y-3.5">
                    @foreach($subjects_with_stats as $idx => $subj)
                        @php
                            $isSelected = $idx === 0;
                            $passRate = $subj['statistics'] ? $subj['statistics']['pass_rate'] : null;
                        @endphp
                        <button 
                            type="button"
                            onclick="selectSubject('{{ $subj['id'] }}')"
                            id="subj-tile-{{ $subj['id'] }}"
                            class="subj-tile w-full text-left p-5 rounded-2xl border transition-all duration-200 flex flex-col justify-between space-y-3 shadow-sm hover:shadow group focus:outline-none {{ $isSelected ? 'border-indigo-500 ring-2 ring-indigo-500/10 bg-indigo-50/50' : 'border-slate-200 hover:border-slate-300 bg-white' }}"
                        >
                            <div class="flex justify-between items-start gap-2 w-full">
                                <div class="space-y-0.5">
                                    <h4 class="text-sm font-bold text-slate-800 group-hover:text-indigo-900 transition line-clamp-1">
                                        {{ $subj['name'] }}
                                    </h4>
                                    <span class="font-mono text-xxs font-bold text-slate-400 bg-slate-50 border border-slate-200 px-1.5 py-0.5 rounded">
                                        Syllabus Code: {{ $subj['code'] }}
                                    </span>
                                </div>
                                <span class="text-xxs font-bold text-slate-400 bg-slate-50 border border-slate-200/80 px-2 py-1 rounded-lg">
                                    {{ $subj['components']->count() }} Papers
                                </span>
                            </div>

                            @if($subj['statistics'])
                                <div class="flex items-center gap-4 text-xxs border-t border-slate-50 pt-2.5 w-full">
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400">Avg PUM:</span>
                                        <span class="font-extrabold text-slate-700">{{ number_format($subj['statistics']['avg_pum'], 1) }}%</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400">Pass Rate:</span>
                                        <span class="font-extrabold text-emerald-600">{{ number_format($passRate, 1) }}%</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-[10px] text-slate-400 italic pt-1 w-full">
                                    No results uploaded yet
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right Side: Selected Subject Details Dashboard (Spans 7 cols) -->
            <div class="lg:col-span-7">
                @foreach($subjects_with_stats as $idx => $subj)
                    <div 
                        id="subj-details-{{ $subj['id'] }}"
                        class="subj-details-pane {{ $idx === 0 ? '' : 'hidden' }} space-y-6 animate-fade-in"
                    >
                        <!-- Main Summary Header Card -->
                        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                <div class="space-y-1">
                                    <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-100 text-indigo-750 font-bold rounded text-[10px] uppercase tracking-wider">
                                        Subject Profile
                                    </span>
                                    <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ $subj['name'] }}</h3>
                                    <p class="text-xs text-slate-450 font-semibold">Syllabus Code: <span class="font-mono">{{ $subj['code'] }}</span></p>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto sm:self-center">
                                    <a href="{{ route('subjects.edit', $subj['id']) }}" class="text-center w-full sm:w-auto px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold rounded-xl transition">
                                        Edit Subject &amp; Papers
                                    </a>
                                </div>
                            </div>

                            @if($subj['statistics'])
                                <!-- Subject Performance stats -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-3 border-t border-slate-100">
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Candidates</span>
                                        <span class="text-base font-black text-slate-800 mt-0.5">{{ $subj['total_students'] }}</span>
                                    </div>
                                    <div class="bg-emerald-50/40 rounded-xl p-3 border border-emerald-100/60 flex flex-col justify-center">
                                        <span class="block text-[10px] font-black text-emerald-600/80 uppercase tracking-wider">Pass Rate</span>
                                        <span class="text-base font-black text-emerald-650 mt-0.5">{{ number_format($subj['statistics']['pass_rate'], 1) }}%</span>
                                    </div>
                                    <div class="bg-indigo-50/40 rounded-xl p-3 border border-indigo-100/60 flex flex-col justify-center">
                                        <span class="block text-[10px] font-black text-indigo-650/80 uppercase tracking-wider">Average PUM</span>
                                        <span class="text-base font-black text-indigo-700 mt-0.5">{{ number_format($subj['statistics']['avg_pum'], 1) }}%</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex flex-col justify-center">
                                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Highest / Lowest</span>
                                        <span class="text-xs font-bold text-slate-850 mt-0.5">
                                            H: {{ $subj['statistics']['highest'] }}% / L: {{ $subj['statistics']['lowest'] }}%
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Papers & Components list -->
                        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Components &amp; Papers Configuration</h4>
                                <span class="text-xxs font-black text-slate-500 bg-slate-150 px-2 py-0.5 rounded">
                                    Total Marks: {{ $subj['components']->sum('total_marks') }}
                                </span>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @forelse($subj['components'] as $comp)
                                    <div class="px-5 py-3.5 flex justify-between items-center hover:bg-slate-50/30 transition">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-xxs font-mono font-black text-slate-700">
                                                {{ $comp->component_code }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-700">{{ $comp->component_name }}</span>
                                        </div>
                                        <div class="text-xs font-extrabold text-slate-450">
                                            {{ $comp->total_marks }} Marks
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-400 text-xs italic">
                                        No components configured for this subject.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Grade Distribution Section (if results exist) -->
                        @if($subj['statistics'])
                            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Candidate Grade Distribution</h4>
                                <div class="space-y-3.5">
                                    @php
                                        $grades = ($qualification->qualification_type === 'AS_A_LEVEL')
                                            ? ['A*', 'A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e', 'U']
                                            : ['A*', 'A*A*', 'A', 'AA', 'B', 'BB', 'C', 'CC', 'D', 'DD', 'E', 'EE', 'F', 'FF', 'G', 'GG', 'U', 'UU'];
                                        $total = $subj['total_students'];
                                    @endphp
                                    @foreach($grades as $grade)
                                        @php
                                            $count = $subj['grade_distribution'][$grade] ?? 0;
                                            $pct = $total > 0 ? ($count / $total) * 100 : 0;
                                            
                                            // Dynamic coloring for grade bars
                                            $barColor = 'bg-slate-500';
                                            if (in_array($grade, ['A*', 'A*A*', 'A', 'AA', 'a'])) $barColor = 'bg-emerald-500';
                                            elseif (in_array($grade, ['B', 'BB', 'b', 'C', 'CC', 'c'])) $barColor = 'bg-indigo-500';
                                            elseif (in_array($grade, ['D', 'DD', 'd', 'E', 'EE', 'e'])) $barColor = 'bg-amber-500';
                                            elseif (in_array($grade, ['F', 'FF', 'G', 'GG'])) $barColor = 'bg-orange-500';
                                            elseif (in_array($grade, ['U', 'UU', 'u'])) $barColor = 'bg-rose-500';
                                        @endphp
                                        @if($count > 0 || $total === 0)
                                            <div class="space-y-1">
                                                <div class="flex justify-between items-center text-xs">
                                                    <span class="font-bold text-slate-700 w-24">
                                                        Grade {{ in_array($grade, ['a', 'b', 'c', 'd', 'e']) ? $grade . ' (AS)' : $grade }}
                                                    </span>
                                                    <span class="text-slate-400 font-bold text-xxs">
                                                        {{ $count }} {{ $count == 1 ? 'candidate' : 'candidates' }} ({{ number_format($pct, 1) }}%)
                                                    </span>
                                                </div>
                                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-slate-50 border border-dashed border-slate-200 rounded-3xl p-8 text-center text-slate-400 text-xs">
                                No exam results uploaded for this subject yet.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    @else
        <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center mx-auto text-indigo-650 font-bold text-lg">
                📚
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-800">No Subjects Configured</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">There are currently no subjects added to this qualification's profile catalog.</p>
            </div>
            <div class="pt-2">
                <a href="{{ route('subjects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                    + Add New Subject
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeSubjId = urlParams.get('subject');
        if (activeSubjId) {
            selectSubject(activeSubjId);
        }
    });

    function selectSubject(subjectId) {
        // Toggle selected state style on subject tiles
        document.querySelectorAll('.subj-tile').forEach(tile => {
            tile.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500/10', 'bg-indigo-50/50');
            tile.classList.add('border-slate-200', 'bg-white');
        });

        const activeTile = document.getElementById('subj-tile-' + subjectId);
        if (activeTile) {
            activeTile.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500/10', 'bg-indigo-50/50');
            activeTile.classList.remove('border-slate-200', 'bg-white');
        }

        // Toggle details panes
        document.querySelectorAll('.subj-details-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        const activePane = document.getElementById('subj-details-' + subjectId);
        if (activePane) {
            activePane.classList.remove('hidden');
        }
    }
</script>
@endsection
