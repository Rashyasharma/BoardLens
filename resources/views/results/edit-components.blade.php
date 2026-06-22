@extends('layouts.app')

@section('title', 'Edit Component Marks')
@section('page-title', 'Manage Component Marks')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Link -->
    <div>
        <a href="{{ route('results.show', $result->id) }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 text-sm font-semibold transition">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Result Details
        </a>
    </div>

    <!-- Candidate Card -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Editing Marks For</h3>
            <h2 class="text-xl font-bold text-slate-800">{{ $result->enrollment->candidate->candidate_name }} ({{ $result->enrollment->candidate->candidate_number }})</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $result->subject->subject_name }} &bull; {{ $result->series->series_name }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="px-2.5 py-1 bg-slate-900 text-white text-xs font-bold rounded-lg">
                Uploaded Grade: {{ $result->grade }}
            </span>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg">
                Uploaded PUM: {{ $result->pum }}%
            </span>
        </div>
    </div>

    <!-- Components Marks Editing Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Edit Marks per Component</h3>
            <p class="text-xs text-slate-400 mt-1">Enter obtained marks. Marks will automatically calculate percentage and update the subject results when all components are saved.</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($components as $comp)
                @php
                    $existing = isset($existingMarks[$comp->id]) ? $existingMarks[$comp->id] : null;
                    $obtained = $existing ? $existing['obtained_marks'] : '';
                @endphp
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/30 transition">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded border border-indigo-100">
                                {{ $comp->component_code }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">Max: {{ $comp->total_marks }} marks</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">{{ $comp->component_name }}</h4>
                    </div>

                    <!-- Input Form -->
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input 
                                type="number" 
                                step="0.1" 
                                min="0" 
                                max="{{ $comp->total_marks }}" 
                                id="mark_{{ $comp->id }}" 
                                value="{{ $obtained }}" 
                                placeholder="0.0" 
                                class="w-32 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm font-bold text-slate-800 pr-10"
                            />
                            <span class="absolute right-3 top-2.5 text-xs text-slate-400">/{{ $comp->total_marks }}</span>
                        </div>

                        <button 
                            type="button" 
                            onclick="saveComponentMark('{{ $comp->id }}', '{{ $comp->total_marks }}')" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition"
                        >
                            Save Mark
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    No components defined for this subject.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    async function saveComponentMark(componentId, totalMarks) {
        const input = document.getElementById(`mark_${componentId}`);
        const obtained = parseFloat(input.value);

        if (isNaN(obtained)) {
            alert('Please enter a valid numeric mark.');
            return;
        }

        if (obtained < 0 || obtained > parseFloat(totalMarks)) {
            alert(`Obtained marks must be between 0 and ${totalMarks}.`);
            return;
        }

        try {
            const response = await fetch("{{ route('results.store-component', $result->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    component_id: componentId,
                    obtained_marks: obtained
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Show a brief success alert/toast
                alert(data.message || 'Mark saved successfully!');
            } else {
                alert(data.error || 'Failed to save mark.');
            }
        } catch (err) {
            console.error(err);
            alert('An error occurred while communication with server: ' + err.message);
        }
    }
</script>
@endsection
