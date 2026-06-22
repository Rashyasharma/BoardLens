@extends('layouts.app')

@section('title', 'CBSE Academic Years')
@section('page-title', 'Academic Years')

@section('content')
<div class="space-y-10 max-w-7xl mx-auto">

    {{-- Header & Create Button --}}
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-fade-in">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Manage Academic Years</h2>
            <p class="text-xs text-slate-500 mt-1">Create academic sessions to enroll students in class 10 & 12.</p>
        </div>
        <div class="flex items-end">
            <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-amber-600/20 hover:scale-[1.01] transition duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create Year
            </button>
        </div>
    </div>

    {{-- List --}}
    @if($academicYears->isEmpty())
        <div class="bg-white border border-slate-200 rounded-3xl p-16 text-center shadow-sm animate-fade-in">
            <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">📅</div>
            <h3 class="text-lg font-bold text-slate-700">No Academic Years Found</h3>
            <p class="text-sm text-slate-400 mt-1 font-medium">Create an academic year to start managing students and results.</p>
            <button onclick="openCreateModal()" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 border border-amber-100 text-amber-700 text-sm font-bold rounded-xl hover:bg-amber-100 transition">
                + Create Year
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
            @foreach($academicYears as $ay)
                @php
                    $colors = [
                        ['from' => 'from-blue-50', 'text' => 'group-hover:text-blue-700', 'border' => 'hover:border-blue-300'],
                        ['from' => 'from-emerald-50', 'text' => 'group-hover:text-emerald-700', 'border' => 'hover:border-emerald-300'],
                        ['from' => 'from-amber-50', 'text' => 'group-hover:text-amber-700', 'border' => 'hover:border-amber-300'],
                        ['from' => 'from-purple-50', 'text' => 'group-hover:text-purple-700', 'border' => 'hover:border-purple-300'],
                        ['from' => 'from-rose-50', 'text' => 'group-hover:text-rose-700', 'border' => 'hover:border-rose-300'],
                        ['from' => 'from-teal-50', 'text' => 'group-hover:text-teal-700', 'border' => 'hover:border-teal-300'],
                    ];
                    $color = $colors[$loop->index % count($colors)];
                @endphp
                <div class="bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col group {{ $color['border'] }}">
                    <div class="px-6 pt-6 pb-4 bg-gradient-to-b {{ $color['from'] }} to-white border-b border-slate-100 flex items-start justify-between gap-3">
                        <div class="space-y-1.5">
                            <h3 class="text-xl font-black text-slate-800 leading-tight {{ $color['text'] }} transition">{{ $ay['name'] }}</h3>
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/30 border-b border-slate-100/50 grid grid-cols-2 gap-4">
                        <div class="flex items-start gap-2">
                            <span class="text-lg">👥</span>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Students</span>
                                <span class="text-sm font-black text-slate-800 block">{{ $ay['students'] }} Total</span>
                                <div class="flex gap-2 text-xs font-semibold text-slate-500 mt-1">
                                    <span title="Class 10"><span class="text-slate-400">X:</span> {{ $ay['class_10'] }}</span>
                                    <span title="Class 12"><span class="text-slate-400">XII:</span> {{ $ay['class_12'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-lg">✅</span>
                            <div>
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pass Rate</span>
                                <span class="text-sm font-black text-slate-800">{{ $ay['pass_rate'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto bg-slate-50/20 px-6 py-4 flex items-center justify-between gap-2 border-t border-slate-100/80">
                        <a href="{{ route('cbse.student-entries.show', $ay['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-amber-50 border border-slate-200 hover:border-amber-150 text-slate-700 hover:text-amber-700 text-xs font-bold rounded-lg transition duration-150 shadow-xs">
                            👥 Manage LOC
                        </a>
                        
                        <div class="flex items-center gap-3">
                            <button onclick="openEditModal('{{ $ay['id'] }}', '{{ $ay['name'] }}')" class="text-xs font-bold text-slate-400 hover:text-slate-700 transition cursor-pointer">Edit</button>
                            <span class="text-slate-200 text-xs">|</span>
                            <form action="{{ route('cbse.academic-years.destroy', $ay['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this Academic Year?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-rose-500 hover:text-rose-700 transition cursor-pointer">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

{{-- Create Modal --}}
<div id="create-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity z-40" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
            <form action="{{ route('cbse.academic-years.store') }}" method="POST">
                @csrf
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Create Academic Year</h3>
                    <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Name (e.g. 2024-2025)</label>
                        <input type="text" name="name" required class="w-full border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity z-40" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative z-50 inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-200">
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Edit Academic Year</h3>
                    <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
                <div class="px-6 py-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Name</label>
                        <input type="text" id="edit-name" name="name" required class="w-full border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openCreateModal() { document.getElementById('create-modal').classList.remove('hidden'); }
    function closeCreateModal() { document.getElementById('create-modal').classList.add('hidden'); }
    function openEditModal(id, name) {
        document.getElementById('edit-form').action = '/cbse/academic-years/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() { document.getElementById('edit-modal').classList.add('hidden'); }
</script>
@endsection
