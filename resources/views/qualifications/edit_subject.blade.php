@extends('layouts.app')

@section('title', 'Edit Subject')
@section('page-title', 'Edit Subject')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('subjects.index') }}" class="p-2 hover:bg-slate-100 rounded-xl transition text-slate-500 hover:text-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h2 class="text-xl font-bold text-slate-800">Edit Subject Configuration</h2>
    </div>

    <!-- Basic Information Form -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
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
                    <input type="text" name="subject_code" value="{{ old('subject_code', $subject->subject_code) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-mono font-bold text-slate-800" />
                </div>

                <!-- Subject Name -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Subject Name *</label>
                    <input type="text" name="subject_name" value="{{ old('subject_name', $subject->subject_name) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm" />
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                    Update Subject Details
                </button>
            </div>
        </form>
    </div>

    <!-- Component Sets Section -->
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Syllabus Component Sets</h3>
                <p class="text-sm text-slate-500 mt-1">Configure components for specific year ranges when syllabus changes occur.</p>
            </div>
            <button type="button" onclick="openNewSetModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-md transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New Component Set
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @php
                // Find the latest set (highest end_year) for the 'Latest' badge
                $latestSetId = $componentSets
                    ->sortByDesc(fn($s) => $s->end_year ?? $s->start_year ?? 0)
                    ->first()?->id;
            @endphp

            @foreach($componentSets as $set)
                @php
                    $isLatest = ($set->id === $latestSetId);
                    $tileColor = $isLatest ? 'bg-indigo-50/30 border-indigo-200' : 'bg-white border-slate-200';
                    $iconColor = $isLatest ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-600';
                @endphp
                <div class="border {{ $tileColor }} rounded-xl overflow-hidden">
                    <!-- Tile Header -->
                    <div class="p-4 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition" onclick="toggleSetDetails('set-{{ $set->id }}')">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $iconColor }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 flex items-center gap-2">
                                    {{ $set->display_label }}
                                    @if($isLatest)
                                        <span class="px-2 py-0.5 bg-indigo-600 text-white text-[10px] uppercase font-black tracking-wider rounded-md">Latest</span>
                                    @endif
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $set->components->count() }} Components • {{ $set->components->sum('total_marks') }} Total Marks</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="event.stopPropagation(); openEditSetModal('{{ $set->id }}')" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit Components">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                            @if(!$set->is_default)
                            <button type="button" onclick="event.stopPropagation(); deleteComponentSet('{{ $set->id }}')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Delete Set">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                            @endif
                            <svg id="icon-set-{{ $set->id }}" class="w-5 h-5 text-slate-400 transition-transform duration-200 {{ $isLatest ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <!-- Collapsible Content — auto-expanded for the latest set -->
                    <div id="set-{{ $set->id }}" class="{{ $isLatest ? '' : 'hidden' }} border-t border-slate-100 bg-slate-50/30 p-4">
                        @if($set->components->isEmpty())
                            <p class="text-sm text-slate-500 italic">No components defined yet.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-slate-600">
                                    <thead class="text-xs text-slate-500 uppercase bg-slate-100 border-b border-slate-200">
                                        <tr>
                                            <th class="px-4 py-2 rounded-tl-lg">Code</th>
                                            <th class="px-4 py-2">Name</th>
                                            <th class="px-4 py-2">Label</th>
                                            <th class="px-4 py-2 text-right">Marks</th>
                                            @if($subject->qualification->qualification_type === 'AS_A_LEVEL')
                                                <th class="px-4 py-2 rounded-tr-lg">Level</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($set->components as $comp)
                                            <tr>
                                                <td class="px-4 py-2 font-mono font-bold">{{ $comp->component_code }}</td>
                                                <td class="px-4 py-2">{{ $comp->component_name }}</td>
                                                <td class="px-4 py-2">{{ $comp->component_label ?? '-' }}</td>
                                                <td class="px-4 py-2 text-right font-bold">{{ $comp->total_marks }}</td>
                                                @if($subject->qualification->qualification_type === 'AS_A_LEVEL')
                                                    <td class="px-4 py-2">
                                                        @if($comp->level)
                                                            <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-md">{{ $comp->level->name }}</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Subject Statistics section -->
    @if($statistics)
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150 space-y-6">
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
    @endif
</div>

<!-- ================= MODALS ================= -->

<!-- Backdrop -->
<div id="modal-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden transition-opacity" onclick="closeAllModals()"></div>

<!-- New Component Set Modal -->
<div id="new-set-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden transform scale-95 opacity-0 transition-all duration-200" id="new-set-modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">New Syllabus Year Range</h3>
            <button type="button" onclick="closeAllModals()" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form id="new-set-form" onsubmit="submitNewSet(event)" class="p-6 space-y-6">
            <div id="new-set-error" class="hidden p-3 bg-rose-50 text-rose-600 text-sm font-bold rounded-xl border border-rose-100"></div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Start Year *</label>
                    <input type="number" id="start_year" required min="2000" max="2100" value="2027" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">End Year</label>
                    <input type="number" id="end_year" min="2000" max="2100" placeholder="e.g. {{ date('Y') + 2 }}" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none" />
                    <p class="text-xs text-slate-400 mt-1">Leave blank if "Present"</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Copy components from</label>
                <select id="copy_from_set_id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold text-slate-700">
                    <option value="">-- Start Fresh (Empty) --</option>
                    @foreach($componentSets as $set)
                        <option value="{{ $set->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $set->display_label }} ({{ $set->components->count() }} components)</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeAllModals()" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">Create Range</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Component Set Modal -->
<div id="edit-set-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col transform scale-95 opacity-0 transition-all duration-200" id="edit-set-modal-panel">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Edit Component Set</h3>
                <p id="edit-set-title" class="text-sm text-slate-500"></p>
            </div>
            <button type="button" onclick="closeAllModals()" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Year Range Edit Row -->
        <div id="edit-year-range-section" class="px-6 py-4 bg-indigo-50/50 border-b border-indigo-100 shrink-0">
            <p class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-3">📅 Year Range for this Component Set</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Start Year *</label>
                    <input type="number" id="edit-start-year" min="2000" max="2100" required
                        class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">End Year <span class="text-slate-400 font-normal">(leave blank = Present)</span></label>
                    <input type="number" id="edit-end-year" min="2000" max="2100"
                        class="w-full px-3 py-2 bg-white border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 outline-none font-bold" />
                </div>
            </div>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1 bg-slate-50/30">
            <div id="edit-set-error" class="hidden mb-4 p-3 bg-rose-50 text-rose-600 text-sm font-bold rounded-xl border border-rose-100"></div>
            
            <div class="flex justify-between items-center mb-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Component Papers</p>
                <button type="button" onclick="addModalComponentRow()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 border border-indigo-150 text-indigo-700 text-xs font-bold rounded-lg transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Component
                </button>
            </div>

            <form id="edit-components-form" onsubmit="submitEditSet(event)" class="space-y-4">
                <input type="hidden" id="edit-set-id" />
                <div id="modal-components-list" class="space-y-3">
                    <!-- Rows injected via JS -->
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white shrink-0">
            <button type="button" onclick="closeAllModals()" class="px-4 py-2 text-slate-600 font-bold hover:bg-slate-100 rounded-xl transition">Cancel</button>
            <button type="button" onclick="document.getElementById('edit-components-form').requestSubmit()" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">💾 Save Changes</button>
        </div>
    </div>
</div>

<!-- Raw data for JS -->
<script>
    const subjectId = "{{ $subject->id }}";
    const isAsALevel = {{ $subject->qualification->qualification_type === 'AS_A_LEVEL' ? 'true' : 'false' }};
    const levels = @json($levels);
    
    // Pass component sets data for modal editing
    const componentSetsData = {
        @foreach($componentSets as $set)
            "{{ $set->id }}": {
                label: "{{ $set->display_label }}",
                start_year: {{ $set->start_year ?? 'null' }},
                end_year: {{ $set->end_year ?? 'null' }},
                components: @json($set->components)
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    };

    let modalComponentIndex = 0;

    // --- UI Interactions ---

    function toggleSetDetails(id) {
        const el = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            el.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    function openModal(id, panelId) {
        document.getElementById('modal-backdrop').classList.remove('hidden');
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        
        // Trigger animation
        setTimeout(() => {
            const panel = document.getElementById(panelId);
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAllModals() {
        // Reverse animation
        const panels = document.querySelectorAll('#new-set-modal-panel, #edit-set-modal-panel');
        panels.forEach(panel => {
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');
        });

        setTimeout(() => {
            document.getElementById('modal-backdrop').classList.add('hidden');
            document.getElementById('new-set-modal').classList.add('hidden');
            document.getElementById('edit-set-modal').classList.add('hidden');
            
            // Clear forms
            document.getElementById('new-set-error').classList.add('hidden');
            document.getElementById('edit-set-error').classList.add('hidden');
        }, 200);
    }

    // --- New Set ---

    function openNewSetModal() {
        document.getElementById('start_year').value = 2027;
        document.getElementById('end_year').value = '';
        openModal('new-set-modal', 'new-set-modal-panel');
    }

    async function submitNewSet(e) {
        e.preventDefault();
        const start = document.getElementById('start_year').value;
        const end = document.getElementById('end_year').value;
        const copyFrom = document.getElementById('copy_from_set_id').value;
        const errorEl = document.getElementById('new-set-error');

        if (end && parseInt(start) > parseInt(end)) {
            errorEl.textContent = "End year must be greater than or equal to start year.";
            errorEl.classList.remove('hidden');
            return;
        }

        try {
            const res = await fetch(`/subjects/${subjectId}/component-sets`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    start_year: start,
                    end_year: end,
                    copy_from_set_id: copyFrom
                })
            });
            const data = await res.json();
            
            if (res.ok) {
                window.location.reload();
            } else {
                errorEl.textContent = data.message || "Failed to create component set.";
                errorEl.classList.remove('hidden');
            }
        } catch (err) {
            errorEl.textContent = "An unexpected error occurred.";
            errorEl.classList.remove('hidden');
        }
    }

    // --- Edit Set Components ---

    function openEditSetModal(setId) {
        const set = componentSetsData[setId];
        document.getElementById('edit-set-id').value = setId;
        document.getElementById('edit-set-title').textContent = set.label;

        // Populate year-range fields
        document.getElementById('edit-start-year').value = set.start_year ?? '';
        document.getElementById('edit-end-year').value = set.end_year ?? '';
        
        const list = document.getElementById('modal-components-list');
        list.innerHTML = '';
        modalComponentIndex = 0;

        if (set.components && set.components.length > 0) {
            set.components.forEach(comp => {
                addModalComponentRow(comp);
            });
        } else {
            addModalComponentRow(); // empty row
        }

        openModal('edit-set-modal', 'edit-set-modal-panel');
    }

    function addModalComponentRow(comp = null) {
        const list = document.getElementById('modal-components-list');
        const row = document.createElement('div');
        row.className = "component-row flex flex-wrap md:flex-nowrap gap-3 items-end p-4 bg-white rounded-xl border border-slate-200 shadow-sm";
        
        let levelDropdownHtml = '';
        if (isAsALevel) {
            let options = '<option value="">-- None --</option>';
            levels.forEach(lvl => {
                const selected = comp && comp.level_id === lvl.id ? 'selected' : '';
                options += `<option value="${lvl.id}" ${selected}>${lvl.name}</option>`;
            });
            levelDropdownHtml = `
                <div class="w-full md:w-32 shrink-0">
                    <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Level Tag</label>
                    <select name="components[${modalComponentIndex}][level_id]" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500/20">
                        ${options}
                    </select>
                </div>
            `;
        }

        row.innerHTML = `
            <input type="hidden" name="components[${modalComponentIndex}][id]" value="${comp ? comp.id : ''}" />
            <div class="w-full md:w-24 shrink-0">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Code *</label>
                <input type="text" name="components[${modalComponentIndex}][code]" value="${comp ? comp.component_code : ''}" placeholder="e.g. P3" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold text-slate-800 outline-none focus:ring-2 focus:ring-indigo-500/20" />
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Name *</label>
                <input type="text" name="components[${modalComponentIndex}][name]" value="${comp ? comp.component_name : ''}" placeholder="e.g. Paper 3 Alternative" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500/20" />
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Label</label>
                <input type="text" name="components[${modalComponentIndex}][label]" value="${comp && comp.component_label ? comp.component_label : ''}" placeholder="e.g. Theory" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500/20" />
            </div>
            <div class="w-full md:w-24 shrink-0">
                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1">Marks *</label>
                <input type="number" name="components[${modalComponentIndex}][marks]" value="${comp ? comp.total_marks : ''}" placeholder="100" required min="1" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500/20" />
            </div>
            ${levelDropdownHtml}
            <div class="w-full md:w-10 shrink-0 text-center pb-2">
                <button type="button" onclick="this.closest('.component-row').remove()" class="text-rose-400 hover:text-rose-600 font-bold text-lg transition" title="Remove">&times;</button>
            </div>
        `;
        list.appendChild(row);
        modalComponentIndex++;
    }

    async function submitEditSet(e) {
        e.preventDefault();
        const setId = document.getElementById('edit-set-id').value;
        const errorEl = document.getElementById('edit-set-error');
        const form = document.getElementById('edit-components-form');
        const startYear = document.getElementById('edit-start-year').value;
        const endYear = document.getElementById('edit-end-year').value;

        errorEl.classList.add('hidden');

        // Validate year range
        if (!startYear) {
            errorEl.textContent = "Start year is required.";
            errorEl.classList.remove('hidden');
            return;
        }
        if (endYear && parseInt(startYear) > parseInt(endYear)) {
            errorEl.textContent = "End year must be greater than or equal to start year.";
            errorEl.classList.remove('hidden');
            return;
        }
        
        const rows = form.querySelectorAll('.component-row');
        if (rows.length === 0) {
            errorEl.textContent = "A set must have at least one component paper.";
            errorEl.classList.remove('hidden');
            return;
        }

        const data = {
            start_year: startYear ? parseInt(startYear) : null,
            end_year: endYear ? parseInt(endYear) : null,
            components: []
        };
        
        // Parse component rows
        rows.forEach((row) => {
            const inputs = row.querySelectorAll('input, select');
            const compObj = {};
            inputs.forEach(inp => {
                const nameMatch = inp.name.match(/\[([^\]]+)\]$/);
                if (nameMatch) {
                    compObj[nameMatch[1]] = inp.value;
                }
            });
            data.components.push(compObj);
        });

        try {
            const res = await fetch(`/subjects/${subjectId}/component-sets/${setId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await res.json();
            
            if (res.ok) {
                window.location.reload();
            } else {
                errorEl.textContent = result.message || "Failed to update components.";
                errorEl.classList.remove('hidden');
            }
        } catch (err) {
            errorEl.textContent = "An unexpected error occurred.";
            errorEl.classList.remove('hidden');
        }
    }

    // --- Delete Set ---

    async function deleteComponentSet(setId) {
        if (!confirm('Are you sure you want to delete this year range and all its components? Any marks associated might become unlinked. This cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/subjects/${subjectId}/component-sets/${setId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            if (res.ok) {
                window.location.reload();
            } else {
                const result = await res.json();
                alert(result.message || 'Failed to delete component set.');
            }
        } catch (err) {
            alert('An unexpected error occurred.');
        }
    }
</script>
@endsection
