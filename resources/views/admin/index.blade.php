@extends('layouts.app')

@section('title', 'Admin Console - Cambridge Exam Portal')
@section('page-title', 'Admin Console')

@section('content')
<div class="space-y-6">
    <!-- Quick info card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 tracking-tight">System Resource Configurations</h3>
        <p class="text-sm text-slate-500 mt-1">Configure qualifications, subjects, and papers/components. Modifications are restricted to Administrator access.</p>
    </div>

    <!-- Tabs Header -->
    <div class="flex border-b border-slate-200 mb-6 gap-2">
        <button onclick="switchTab('qualifications')" id="tab-btn-qualifications" class="px-5 py-3 font-bold text-sm border-b-2 border-indigo-600 text-indigo-600 transition focus:outline-none">
            Qualifications
        </button>
        <button onclick="switchTab('subjects')" id="tab-btn-subjects" class="px-5 py-3 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition focus:outline-none">
            Subjects
        </button>
        <button onclick="switchTab('components')" id="tab-btn-components" class="px-5 py-3 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition focus:outline-none">
            Components & Papers
        </button>
    </div>

    <!-- Tab 1: Qualifications -->
    <div id="tab-content-qualifications" class="tab-pane space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Qualification -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                <h4 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Add Qualification</h4>
                <form method="POST" action="{{ route('admin.qualifications.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Type</label>
                        <select name="qualification_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                            <option value="IGCSE">IGCSE</option>
                            <option value="AS_A_LEVEL">GCE AS and A Level</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Name</label>
                        <input type="text" name="qualification_name" placeholder="e.g. IGCSE Qualification" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Description</label>
                        <textarea name="description" placeholder="Optional description..." class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 h-24"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                        Save Qualification
                    </button>
                </form>
            </div>

            <!-- List Qualifications -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h5 class="text-sm font-bold text-slate-800">Syllabi Qualifications</h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-slate-400 text-xs font-semibold uppercase border-b border-slate-150 bg-slate-50/30">
                                    <th class="py-3 px-6">Type</th>
                                    <th class="py-3 px-6">Name</th>
                                    <th class="py-3 px-6">Description</th>
                                    <th class="py-3 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($qualifications as $qual)
                                    <tr>
                                        <td class="py-4 px-6 font-bold text-slate-800">{{ $qual->qualification_type }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ $qual->qualification_name }}</td>
                                        <td class="py-4 px-6 text-xs text-slate-400 max-w-xs truncate">{{ $qual->description ?? '-' }}</td>
                                        <td class="py-4 px-6 text-right space-x-2">
                                            <button onclick="openEditQualModal('{{ $qual->id }}', '{{ $qual->qualification_type }}', '{{ addslashes($qual->qualification_name) }}', '{{ addslashes($qual->description) }}')" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Edit</button>
                                            <form method="POST" action="{{ route('admin.qualifications.destroy', $qual->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this qualification? This will delete all linked subjects and marks!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">No qualifications found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Subjects -->
    <div id="tab-content-subjects" class="tab-pane space-y-6 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Subject -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                <h4 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Add Subject</h4>
                <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Subject Code</label>
                        <input type="text" name="subject_code" placeholder="e.g. 0580" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Subject Name</label>
                        <input type="text" name="subject_name" placeholder="e.g. Mathematics" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification</label>
                        <select name="qualification_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                            @foreach($qualifications as $qual)
                                <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                        Save Subject
                    </button>
                </form>
            </div>

            <!-- List Subjects -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-bold text-slate-800">Syllabi Catalog</h5>
                            <span id="subject-count" class="text-xs text-slate-400 font-semibold"></span>
                        </div>
                        <!-- Subject Filters -->
                        <div class="flex flex-wrap gap-3">
                            <select id="filter-subject-qualification" onchange="filterSubjects()" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Qualifications</option>
                                @foreach($qualifications as $qual)
                                    <option value="{{ $qual->qualification_type }}">{{ $qual->qualification_type }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="filter-subject-search" onkeyup="filterSubjects()" placeholder="Search subject name or code..." class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="subjects-table">
                            <thead>
                                <tr class="text-slate-400 text-xs font-semibold uppercase border-b border-slate-150 bg-slate-50/30">
                                    <th class="py-3 px-6">Code</th>
                                    <th class="py-3 px-6">Name</th>
                                    <th class="py-3 px-6">Qualification</th>
                                    <th class="py-3 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($subjects as $sub)
                                    <tr data-qual-type="{{ $sub->qualification->qualification_type }}" data-subject-name="{{ strtolower($sub->subject_name) }}" data-subject-code="{{ strtolower($sub->subject_code) }}">
                                        <td class="py-4 px-6 font-mono font-bold text-slate-700">{{ $sub->subject_code }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ $sub->subject_name }}</td>
                                        <td class="py-4 px-6">
                                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded-full border border-slate-200">
                                                {{ $sub->qualification->qualification_type }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right space-x-2">
                                            <button onclick="openEditSubModal('{{ $sub->id }}', '{{ $sub->subject_code }}', '{{ addslashes($sub->subject_name) }}', '{{ $sub->qualification_id }}')" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Edit</button>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $sub->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this subject? All components and marks will be deleted!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-subjects-row">
                                        <td colspan="4" class="py-8 text-center text-slate-400">No subjects registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Components & Papers -->
    <div id="tab-content-components" class="tab-pane space-y-6 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Component -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm h-fit">
                <h4 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3">Add Subject Paper/Component</h4>
                <form method="POST" action="{{ route('admin.components.store') }}" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Filter</label>
                        <select id="comp_qualification_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                            <option value="">-- Select Qualification --</option>
                            @foreach($qualifications as $qual)
                                <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Subject</label>
                        <select id="comp_subject_id" name="subject_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                            <option value="">-- Select Qualification First --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Component Code</label>
                        <input type="text" name="component_code" placeholder="e.g. P1, P2" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Component Name</label>
                        <input type="text" name="component_name" placeholder="e.g. Paper 2 (Theory)" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Component Type</label>
                        <select name="component_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                            <option value="paper">Written Paper</option>
                            <option value="practical">Practical / Laboratory</option>
                            <option value="project">Project Work</option>
                            <option value="coursework">Coursework</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Max Component Marks</label>
                        <input type="number" name="total_marks" placeholder="e.g. 100" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Scaling Factor (0–10)</label>
                        <input type="number" min="0" max="10" step="1" name="scaling_factor" placeholder="e.g. 5" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                        <p class="text-[10px] text-slate-400 mt-1">Integer between 0 and 10. Higher values carry more weight in calculations.</p>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                        Save Component
                    </button>
                </form>
            </div>

            <!-- List Components (Single Unified List) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-bold text-slate-800">Unified Components & Papers List</h5>
                            <span id="component-count" class="text-xs text-slate-400 font-semibold"></span>
                        </div>
                        <!-- Component Filters -->
                        <div class="flex flex-wrap gap-3">
                            <select id="filter-comp-qualification" onchange="filterComponents()" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Qualifications</option>
                                @foreach($qualifications as $qual)
                                    <option value="{{ $qual->qualification_type }}">{{ $qual->qualification_type }}</option>
                                @endforeach
                            </select>
                            <select id="filter-comp-subject" onchange="filterComponents()" class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->subject_name }}">{{ $sub->subject_code }} - {{ $sub->subject_name }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="filter-comp-search" onkeyup="filterComponents()" placeholder="Search component..." class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 w-48 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="components-table">
                            <thead>
                                <tr class="text-slate-400 text-xs font-semibold uppercase border-b border-slate-150 bg-slate-50/30">
                                    <th class="py-3 px-6">Code</th>
                                    <th class="py-3 px-6">Name</th>
                                    <th class="py-3 px-6">Subject</th>
                                    <th class="py-3 px-6">Qualification</th>
                                    <th class="py-3 px-6">Max Marks</th>
                                    <th class="py-3 px-6">Scaling Factor</th>
                                    <th class="py-3 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($components as $comp)
                                    <tr data-qual-type="{{ $comp->subject?->qualification?->qualification_type ?? '' }}" data-subject-name="{{ $comp->subject?->subject_name ?? '' }}" data-comp-name="{{ strtolower($comp->component_name) }}" data-comp-code="{{ strtolower($comp->component_code) }}">
                                        <td class="py-4 px-6 font-mono font-bold text-slate-700">{{ $comp->component_code }}</td>
                                        <td class="py-4 px-6 font-semibold">{{ $comp->component_name }}</td>
                                        <td class="py-4 px-6 font-medium">{{ $comp->subject?->subject_name ?? 'N/A' }}</td>
                                        <td class="py-4 px-6">
                                            @if($comp->subject?->qualification)
                                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-bold rounded uppercase border border-indigo-100">
                                                    {{ $comp->subject->qualification->qualification_type }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400 italic">No Qual</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-slate-800">{{ $comp->total_marks }}</td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 bg-slate-100 h-2 rounded-full overflow-hidden">
                                                    <div class="bg-indigo-500 h-full rounded-full" style="width: {{ ($comp->scaling_factor / 10) * 100 }}%"></div>
                                                </div>
                                                <span class="font-bold text-indigo-600 text-xs">{{ $comp->scaling_factor }}/10</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-right space-x-2 shrink-0">
                                            <button onclick="openEditCompModal('{{ $comp->id }}', '{{ $comp->subject_id }}', '{{ $comp->component_code }}', '{{ addslashes($comp->component_name) }}', '{{ $comp->component_type }}', '{{ $comp->total_marks }}', '{{ $comp->scaling_factor }}', '{{ $comp->subject?->qualification_id ?? '' }}')" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Edit</button>
                                            <form method="POST" action="{{ route('admin.components.destroy', $comp->id) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this component?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold text-xs">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-components-row">
                                        <td colspan="7" class="py-8 text-center text-slate-400">No components registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Edit Qualification Modal -->
<div id="editQualModal" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-50 transition duration-150">
    <div class="bg-white p-8 rounded-2xl max-w-md w-full border border-slate-100 shadow-xl space-y-6">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Edit Qualification</h3>
            <button onclick="closeModal('editQualModal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="editQualForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Type</label>
                <select id="edit_qual_type" name="qualification_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    <option value="IGCSE">IGCSE</option>
                    <option value="AS_A_LEVEL">GCE AS and A Level</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Name</label>
                <input type="text" id="edit_qual_name" name="qualification_name" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Description</label>
                <textarea id="edit_qual_description" name="description" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50 h-24"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('editQualModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Edit Subject Modal -->
<div id="editSubModal" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-50 transition duration-150">
    <div class="bg-white p-8 rounded-2xl max-w-md w-full border border-slate-100 shadow-xl space-y-6">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Edit Subject</h3>
            <button onclick="closeModal('editSubModal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="editSubForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Subject Code</label>
                <input type="text" id="edit_sub_code" name="subject_code" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Subject Name</label>
                <input type="text" id="edit_sub_name" name="subject_name" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification</label>
                <select id="edit_sub_qualification_id" name="qualification_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('editSubModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Edit Component Modal -->
<div id="editCompModal" class="fixed inset-0 bg-slate-900/50 hidden flex items-center justify-center z-50 transition duration-150">
    <div class="bg-white p-8 rounded-2xl max-w-md w-full border border-slate-100 shadow-xl space-y-6">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Edit Component/Paper</h3>
            <button onclick="closeModal('editCompModal')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form id="editCompForm" method="POST" action="" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Qualification Filter</label>
                <select id="edit_comp_qualification_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    <option value="">-- Select Qualification --</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Subject</label>
                <select id="edit_comp_subject_id" name="subject_id" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    <option value="">-- Select Qualification First --</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Component Code</label>
                <input type="text" id="edit_comp_code" name="component_code" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Component Name</label>
                <input type="text" id="edit_comp_name" name="component_name" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Component Type</label>
                <select id="edit_comp_type" name="component_type" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                    <option value="paper">Written Paper</option>
                    <option value="practical">Practical / Laboratory</option>
                    <option value="project">Project Work</option>
                    <option value="coursework">Coursework</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Max Component Marks</label>
                <input type="number" id="edit_comp_total_marks" name="total_marks" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Scaling Factor (0–10)</label>
                <input type="number" min="0" max="10" step="1" id="edit_comp_scaling_factor" name="scaling_factor" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-slate-50">
                <p class="text-[10px] text-slate-400 mt-1">Integer between 0 and 10. Higher values carry more weight in calculations.</p>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('editCompModal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching logic
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(`tab-content-${tabId}`).classList.remove('hidden');

        document.querySelectorAll('[id^="tab-btn-"]').forEach(btn => {
            btn.classList.remove('border-indigo-600', 'text-indigo-600');
            btn.classList.add('border-transparent', 'text-slate-500');
        });

        const activeBtn = document.getElementById(`tab-btn-${tabId}`);
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-indigo-600', 'text-indigo-600');
    }

    // Modal helpers
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Open Edit Qualification
    function openEditQualModal(id, type, name, description) {
        document.getElementById('editQualForm').action = `/admin/qualifications/${id}/update`;
        document.getElementById('edit_qual_type').value = type;
        document.getElementById('edit_qual_name').value = name;
        document.getElementById('edit_qual_description').value = description;
        document.getElementById('editQualModal').classList.remove('hidden');
    }

    // Open Edit Subject
    function openEditSubModal(id, code, name, qualId) {
        document.getElementById('editSubForm').action = `/admin/subjects/${id}/update`;
        document.getElementById('edit_sub_code').value = code;
        document.getElementById('edit_sub_name').value = name;
        document.getElementById('edit_sub_qualification_id').value = qualId;
        document.getElementById('editSubModal').classList.remove('hidden');
    }

    // Open Edit Component
    async function openEditCompModal(id, subjectId, code, name, type, totalMarks, scalingFactor, qualId) {
        document.getElementById('editCompForm').action = `/admin/components/${id}/update`;
        document.getElementById('edit_comp_qualification_id').value = qualId;
        document.getElementById('edit_comp_code').value = code;
        document.getElementById('edit_comp_name').value = name;
        document.getElementById('edit_comp_type').value = type;
        document.getElementById('edit_comp_total_marks').value = totalMarks;
        document.getElementById('edit_comp_scaling_factor').value = scalingFactor;

        // Fetch subjects for this qualification first
        await fetchSubjects(qualId, 'edit_comp_subject_id', subjectId);

        document.getElementById('editCompModal').classList.remove('hidden');
    }

    // Dynamic subjects select handler helper
    async function fetchSubjects(qualId, selectElementId, preselectedValue = '') {
        const select = document.getElementById(selectElementId);
        select.innerHTML = '<option value="">-- Loading Subjects --</option>';

        if (!qualId) {
            select.innerHTML = '<option value="">-- Select Qualification First --</option>';
            return;
        }

        try {
            const response = await fetch(`/api/subjects/${qualId}`);
            const data = await response.json();
            
            select.innerHTML = '<option value="">-- Select Subject --</option>';
            data.subjects.forEach(subject => {
                const selectedAttr = (subject.id === preselectedValue) ? 'selected' : '';
                select.innerHTML += `<option value="${subject.id}" ${selectedAttr}>${subject.subject_code} - ${subject.subject_name}</option>`;
            });
        } catch (err) {
            console.error(err);
            select.innerHTML = '<option value="">-- Error Loading Subjects --</option>';
        }
    }

    document.getElementById('comp_qualification_id').addEventListener('change', function() {
        fetchSubjects(this.value, 'comp_subject_id');
    });

    document.getElementById('edit_comp_qualification_id').addEventListener('change', function() {
        fetchSubjects(this.value, 'edit_comp_subject_id');
    });

    // ========== SUBJECT FILTERING ==========
    function filterSubjects() {
        const qualFilter = document.getElementById('filter-subject-qualification').value;
        const searchFilter = document.getElementById('filter-subject-search').value.toLowerCase();

        const rows = document.querySelectorAll('#subjects-table tbody tr[data-qual-type]');
        let visibleCount = 0;

        rows.forEach(row => {
            const qualType = row.getAttribute('data-qual-type');
            const subjectName = row.getAttribute('data-subject-name');
            const subjectCode = row.getAttribute('data-subject-code');

            const qualMatch = !qualFilter || qualType === qualFilter;
            const searchMatch = !searchFilter || subjectName.includes(searchFilter) || subjectCode.includes(searchFilter);

            if (qualMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('subject-count').textContent = `Showing ${visibleCount} of ${rows.length}`;
    }

    // ========== COMPONENT FILTERING ==========
    function filterComponents() {
        const qualFilter = document.getElementById('filter-comp-qualification').value;
        const subjectFilter = document.getElementById('filter-comp-subject').value;
        const searchFilter = document.getElementById('filter-comp-search').value.toLowerCase();

        const rows = document.querySelectorAll('#components-table tbody tr[data-qual-type]');
        let visibleCount = 0;

        rows.forEach(row => {
            const qualType = row.getAttribute('data-qual-type');
            const subjectName = row.getAttribute('data-subject-name');
            const compName = row.getAttribute('data-comp-name');
            const compCode = row.getAttribute('data-comp-code');

            const qualMatch = !qualFilter || qualType === qualFilter;
            const subjectMatch = !subjectFilter || subjectName === subjectFilter;
            const searchMatch = !searchFilter || compName.includes(searchFilter) || compCode.includes(searchFilter);

            if (qualMatch && subjectMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('component-count').textContent = `Showing ${visibleCount} of ${rows.length}`;
    }

    // Initialize counts
    document.addEventListener('DOMContentLoaded', function() {
        const subjectRows = document.querySelectorAll('#subjects-table tbody tr[data-qual-type]');
        document.getElementById('subject-count').textContent = `Showing ${subjectRows.length} of ${subjectRows.length}`;

        const compRows = document.querySelectorAll('#components-table tbody tr[data-qual-type]');
        document.getElementById('component-count').textContent = `Showing ${compRows.length} of ${compRows.length}`;

        // Switch tab based on URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const defaultTab = urlParams.get('tab');
        if (defaultTab && ['qualifications', 'subjects', 'components'].includes(defaultTab)) {
            switchTab(defaultTab);
        }
    });
</script>
@endsection
