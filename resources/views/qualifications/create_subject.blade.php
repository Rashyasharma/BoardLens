@extends('layouts.app')

@section('title', 'Add Subject')
@section('page-title', 'Add New Subject')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('subjects.index') }}" class="p-2 hover:bg-slate-100 rounded-xl transition text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-bold text-slate-800">New Subject Configuration</h2>
    </div>

    <form method="POST" action="{{ route('subjects.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Qualification -->
            <div class="md:col-span-1">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Qualification *</label>
                <select name="qualification_id" id="qualification-select" onchange="toggleLevelSelector()" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                    <option value="">-- Select --</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}" data-type="{{ $qual->qualification_type }}">{{ $qual->qualification_name }} ({{ $qual->type_display }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Code -->
            <div class="md:col-span-1">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subject Code *</label>
                <input type="text" name="subject_code" placeholder="e.g. 0580" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-mono" />
            </div>

            <!-- Subject Name -->
            <div class="md:col-span-1">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subject Name *</label>
                <input type="text" name="subject_name" placeholder="e.g. Mathematics" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm" />
            </div>
        </div>

        <!-- Components Dynamic Section -->
        <div class="border-t border-slate-100 pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Components & Papers</h3>
                <button type="button" onclick="addComponentRow()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-150 text-indigo-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Component
                </button>
            </div>

            <div id="components-list" class="space-y-4">
                <!-- Template row -->
                <div class="component-row grid grid-cols-12 gap-3 items-end p-4 bg-slate-50/50 rounded-xl border border-slate-100">
                    <div class="col-span-2">
                        <label class="block text-xxs font-bold text-slate-450 uppercase mb-1">Code *</label>
                        <input type="text" name="components[0][code]" placeholder="e.g. P1" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-mono" />
                    </div>
                    <div class="name-col-wrapper col-span-7">
                        <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Name *</label>
                        <input type="text" name="components[0][name]" placeholder="e.g. Paper 1 Core" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Marks *</label>
                        <input type="number" name="components[0][marks]" placeholder="80" required min="1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
                    </div>
                    <!-- Level Tag Column (hidden by default unless AS_A_LEVEL) -->
                    <div class="col-span-3 level-tag-column hidden">
                        <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Level Tag</label>
                        <select name="components[0][level_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs">
                            <option value="">-- None --</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1 text-center pb-2">
                        <button type="button" onclick="removeComponentRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-base">&times;</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('subjects.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-655 text-sm font-semibold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                Save Subject
            </button>
        </div>
    </form>
</div>

<script>
    let componentIndex = 1;

    function toggleLevelSelector() {
        const select = document.getElementById('qualification-select');
        const selectedOpt = select.options[select.selectedIndex];
        const isAsALevel = selectedOpt && selectedOpt.getAttribute('data-type') === 'AS_A_LEVEL';
        
        const levelCols = document.querySelectorAll('.level-tag-column');
        const nameCols = document.querySelectorAll('.name-col-wrapper');
        
        levelCols.forEach(col => {
            if (isAsALevel) {
                col.classList.remove('hidden');
            } else {
                col.classList.add('hidden');
                const sel = col.querySelector('select');
                if (sel) sel.value = '';
            }
        });

        nameCols.forEach(col => {
            if (isAsALevel) {
                col.classList.remove('col-span-7');
                col.classList.add('col-span-4');
            } else {
                col.classList.remove('col-span-4');
                col.classList.add('col-span-7');
            }
        });
    }

    function addComponentRow() {
        const select = document.getElementById('qualification-select');
        const selectedOpt = select.options[select.selectedIndex];
        const isAsALevel = selectedOpt && selectedOpt.getAttribute('data-type') === 'AS_A_LEVEL';

        const list = document.getElementById('components-list');
        const row = document.createElement('div');
        row.className = "component-row grid grid-cols-12 gap-3 items-end p-4 bg-slate-50/50 rounded-xl border border-slate-100 animate-fade-in";
        
        const nameColSpan = isAsALevel ? 'col-span-4' : 'col-span-7';
        const levelColClass = isAsALevel ? '' : 'hidden';

        row.innerHTML = `
            <div class="col-span-2">
                <label class="block text-xxs font-bold text-slate-450 uppercase mb-1">Code *</label>
                <input type="text" name="components[${componentIndex}][code]" placeholder="e.g. P2" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-mono" />
            </div>
            <div class="name-col-wrapper ${nameColSpan}">
                <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Name *</label>
                <input type="text" name="components[${componentIndex}][name]" placeholder="e.g. Paper 2 Extended" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
            </div>
            <div class="col-span-2">
                <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Marks *</label>
                <input type="number" name="components[${componentIndex}][marks]" placeholder="120" required min="1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs" />
            </div>
            <div class="col-span-3 level-tag-column ${levelColClass}">
                <label class="block text-xxs font-bold text-slate-455 uppercase mb-1">Level Tag</label>
                <select name="components[${componentIndex}][level_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs">
                    <option value="">-- None --</option>
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-1 text-center pb-2">
                <button type="button" onclick="removeComponentRow(this)" class="text-rose-500 hover:text-rose-700 font-bold text-base">&times;</button>
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
