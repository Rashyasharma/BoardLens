@extends('layouts.app')

@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('subjects.index') }}" class="p-2 hover:bg-slate-100 rounded-xl transition text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-bold text-slate-800">Edit Subject Configuration</h2>
    </div>

    <form method="POST" action="{{ route('subjects.update', $subject->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Qualification (Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-slate-400 mb-2">Qualification</label>
                <input type="text" disabled value="{{ $subject->qualification->qualification_name }} ({{ $subject->qualification->qualification_type }})" class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-450 text-sm font-bold cursor-not-allowed" />
            </div>

            <!-- Subject Code -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subject Code *</label>
                <input type="text" name="subject_code" value="{{ old('subject_code', $subject->subject_code) }}" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-mono font-bold text-slate-800" />
            </div>

            <!-- Subject Name -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subject Name *</label>
                <input type="text" name="subject_name" value="{{ old('subject_name', $subject->subject_name) }}" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm" />
            </div>
        </div>

        <!-- Components List -->
        <div class="border-t border-slate-100 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Components & Papers Configuration</h3>
                <button type="button" onclick="addComponentRow()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-150 text-indigo-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Component
                </button>
            </div>

            <div id="components-list" class="space-y-4">
                @forelse($subject->components as $idx => $comp)
                    @php
                        $isAsALevel = $subject->qualification->qualification_type === 'AS_A_LEVEL';
                    @endphp
                    <div class="component-row grid grid-cols-12 gap-3 items-end p-4 bg-slate-50/50 rounded-xl border border-slate-150">
                        <!-- Hidden input for Component ID -->
                        <input type="hidden" name="components[{{ $idx }}][id]" value="{{ $comp->id }}" />
                        
                        <!-- Component Code -->
                        <div class="{{ $isAsALevel ? 'col-span-2' : 'col-span-3' }}">
                            <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Code *</label>
                            <input type="text" name="components[{{ $idx }}][code]" value="{{ old('components.'.$idx.'.code', $comp->component_code) }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800" />
                        </div>

                        <!-- Component Name -->
                        <div class="{{ $isAsALevel ? 'col-span-4' : 'col-span-5' }}">
                            <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Name *</label>
                            <input type="text" name="components[{{ $idx }}][name]" value="{{ old('components.'.$idx.'.name', $comp->component_name) }}" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
                        </div>

                        <!-- Component Max Marks -->
                        <div class="{{ $isAsALevel ? 'col-span-2' : 'col-span-3' }}">
                            <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Marks *</label>
                            <input type="number" name="components[{{ $idx }}][marks]" value="{{ old('components.'.$idx.'.marks', $comp->total_marks) }}" required min="1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
                        </div>

                        <!-- Level Tag Column -->
                        @if($isAsALevel)
                            <div class="col-span-3">
                                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Level Tag</label>
                                <select name="components[{{ $idx }}][level_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs">
                                    <option value="">-- None --</option>
                                    @foreach($levels as $lvl)
                                        <option value="{{ $lvl->id }}" {{ old('components.'.$idx.'.level_id', $comp->level_id) === $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Delete Button -->
                        <div class="col-span-1 text-center pb-2">
                            <button type="button" onclick="removeComponentRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-lg" title="Remove Component">&times;</button>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-455 text-xs py-4 text-center italic">No component papers configured for this subject.</p>
                @endforelse
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('subjects.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-655 text-sm font-semibold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                Update Subject Configuration
            </button>
        </div>
    </form>
</div>

@if($statistics)
    <!-- Subject Statistics section -->
    <div class="max-w-3xl mx-auto mt-8 bg-white p-8 rounded-2xl shadow-sm border border-slate-150 space-y-6">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
            📊 Subject Academic Performance & Statistics
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                <span class="text-xxs text-slate-400 font-bold uppercase tracking-wider block">Total Candidates</span>
                <span class="text-2xl font-black text-slate-800 mt-1 block">{{ $total_students }}</span>
            </div>
            <div class="bg-emerald-50/40 border border-emerald-100/60 rounded-xl p-4">
                <span class="text-xxs text-emerald-600/80 font-bold uppercase tracking-wider block">Pass Rate</span>
                <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ number_format($statistics['pass_rate'], 1) }}%</span>
            </div>
            <div class="bg-indigo-50/40 border border-indigo-100/60 rounded-xl p-4">
                <span class="text-xxs text-indigo-650/80 font-bold uppercase tracking-wider block">Average PUM</span>
                <span class="text-2xl font-black text-indigo-600 mt-1 block">{{ number_format($statistics['avg_pum'], 1) }}%</span>
            </div>
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                <span class="text-xxs text-slate-400 font-bold uppercase tracking-wider block">Highest / Lowest</span>
                <span class="text-sm font-bold text-slate-800 mt-1 block">
                    H: {{ $statistics['highest'] }}% / L: {{ $statistics['lowest'] }}%
                </span>
            </div>
        </div>
    </div>

@else
    <div class="max-w-3xl mx-auto mt-8 bg-white p-8 rounded-2xl border border-slate-150 text-center py-12 text-slate-400 text-sm font-medium italic">
        No statistics records uploaded for this subject yet.
    </div>
@endif

<script>
    let componentIndex = {{ count($subject->components) }};
    const isAsALevel = {{ $subject->qualification->qualification_type === 'AS_A_LEVEL' ? 'true' : 'false' }};

    function addComponentRow() {
        const list = document.getElementById('components-list');
        const row = document.createElement('div');
        row.className = "component-row grid grid-cols-12 gap-3 items-end p-4 bg-slate-50/50 rounded-xl border border-slate-150 animate-fade-in";
        
        const codeColSpan = isAsALevel ? 'col-span-2' : 'col-span-3';
        const nameColSpan = isAsALevel ? 'col-span-4' : 'col-span-5';
        const marksColSpan = isAsALevel ? 'col-span-2' : 'col-span-3';

        let levelDropdownHtml = '';
        if (isAsALevel) {
            levelDropdownHtml = `
                <div class="col-span-3">
                    <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Level Tag</label>
                    <select name="components[${componentIndex}][level_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs">
                        <option value="">-- None --</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                        @endforeach
                    </select>
                </div>
            `;
        }

        row.innerHTML = `
            <div class="${codeColSpan}">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Code *</label>
                <input type="text" name="components[${componentIndex}][code]" placeholder="e.g. P3" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-mono" />
            </div>
            <div class="${nameColSpan}">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Name *</label>
                <input type="text" name="components[${componentIndex}][name]" placeholder="e.g. Paper 3 Alternative" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
            </div>
            <div class="${marksColSpan}">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Marks *</label>
                <input type="number" name="components[${componentIndex}][marks]" placeholder="100" required min="1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
            </div>
            ${levelDropdownHtml}
            <div class="col-span-1 text-center pb-2">
                <button type="button" onclick="removeComponentRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-lg">&times;</button>
            </div>
        `;
        list.appendChild(row);
        componentIndex++;
    }

    function removeComponentRow(button) {
        const rows = document.querySelectorAll('.component-row');
        if (rows.length <= 1) {
            alert('A subject must have at least one component paper.');
            return;
        }
        button.closest('.component-row').remove();
    }
</script>
@endsection
