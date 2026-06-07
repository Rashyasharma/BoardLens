@extends('layouts.app')

@section('title', 'AI Provisional Component Marks Importer - Lucky International School')
@section('page-title', 'AI Provisional Component Marks Importer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Info Alert --}}
    <div class="bg-indigo-50/75 border border-indigo-150 p-6 rounded-3xl flex gap-4">
        <span class="text-3xl">⚡</span>
        <div>
            <h4 class="text-indigo-900 font-bold text-base">Next-Gen AI Provisional Component Marks Parser</h4>
            <p class="text-indigo-750 text-sm mt-1 leading-relaxed">
                Upload your Cambridge Provisional Component Marks report Excel sheet. The parser will automatically identify all syllabus tabs, extract raw component marks for each candidate, and compare them with the system's database records before final confirmation.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-150 p-4 rounded-2xl text-rose-800 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Upload Zone Card --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm md:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Upload Provisional Component Marks File</h3>
            
            <form method="POST" action="{{ route('uploads.ai_components.preview') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Exam Series Selector --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Exam Series *</label>
                        <select name="series_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-semibold text-slate-700">
                            <option value="">-- Select Series --</option>
                            @foreach($series as $s)
                                <option value="{{ $s->id }}">{{ $s->series_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Qualification Selector --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Qualification *</label>
                        <select name="qualification_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm font-semibold text-slate-700">
                            <option value="">-- Select Qualification --</option>
                            @foreach($qualifications as $q)
                                <option value="{{ $q->id }}">{{ $q->qualification_name }} ({{ $q->type_display }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                {{-- Drag and drop file select zone --}}
                <div class="border-2 border-dashed border-slate-250 rounded-2xl p-8 text-center bg-slate-50 hover:bg-slate-100/50 hover:border-indigo-400 transition cursor-pointer relative" id="drop-zone">
                    <input type="file" name="components_file" id="file-input" required accept=".xlsx,.xls"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-3">
                        <div class="text-4xl text-slate-400">📊</div>
                        <div class="text-sm font-bold text-slate-700">Drag & drop your Excel report here, or click to browse</div>
                        <div class="text-xs text-slate-400">Supports standard Cambridge Provisional Component Marks Excel (.xlsx, .xls)</div>
                    </div>
                </div>

                {{-- Selected File Name --}}
                <div id="file-list-container" class="hidden space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Selected File</label>
                    <div id="file-list" class="divide-y divide-slate-100 border border-slate-150 rounded-xl bg-white overflow-hidden">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition duration-150 shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2">
                    <span>⚡</span> Parse Report & Preview
                </button>
            </form>
        </div>

        {{-- Guide & Features Card --}}
        <div class="bg-slate-900 text-slate-350 p-6 rounded-3xl shadow-sm border border-slate-800 space-y-5">
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider">Smart Integration</h4>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                    The provisional component marks parser handles complex, multi-syllabus workbook structures in a single click.
                </p>
            </div>

            <ul class="space-y-3 text-xs">
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-indigo-400 font-bold">✓</span>
                    <span><strong>Multi-Tab Parsing</strong>: Automatically parses subject tabs like 8021, 9709, etc. in the same order as in the file.</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-indigo-400 font-bold">✓</span>
                    <span><strong>Component Mapping</strong>: Dynamically matches Excel component numbers (e.g. Component 12) with standard database paper titles (Paper 1).</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-indigo-400 font-bold">✓</span>
                    <span><strong>Pre-verification</strong>: Checks candidate identifiers and grade mappings before importing to prevent orphaned scores.</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-indigo-400 font-bold">✓</span>
                    <span><strong>Grade Calculations</strong>: Triggers automatic aggregation and syllabus grade calculations when all components are loaded.</span>
                </li>
            </ul>

            <div class="pt-4 border-t border-slate-800">
                <span class="block text-[11px] uppercase font-bold tracking-wider" style="color: #94a3b8 !important;">Center Name</span>
                <span class="text-xs font-bold" style="color: #ffffff !important;">Lucky International School</span>
            </div>
        </div>
    </div>

    {{-- Recent Uploads Section --}}
    @if(isset($recentUploads) && $recentUploads->isNotEmpty())
        <div class="space-y-4 pt-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>🕒</span> Last 3 Uploaded Component Sheets
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($recentUploads as $upload)
                    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition duration-200 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            {{-- Header: Filename & Status --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-lg shrink-0">📊</span>
                                    <h4 class="text-sm font-bold text-slate-800 truncate" title="{{ $upload->file_name }}">
                                        {{ $upload->file_name }}
                                    </h4>
                                </div>
                                
                                @if($upload->status === 'success')
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-150 rounded-full text-[10px] font-bold uppercase shrink-0">Success</span>
                                @elseif($upload->status === 'partial')
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-150 rounded-full text-[10px] font-bold uppercase shrink-0">Partial</span>
                                @else
                                    <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-150 rounded-full text-[10px] font-bold uppercase shrink-0">Failed</span>
                                @endif
                            </div>

                            {{-- Series & Qualification Badges --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-150 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                    {{ $upload->series->series_name ?? 'Unknown Series' }}
                                </span>
                                <span class="px-2 py-0.5 bg-violet-50 text-violet-700 border border-violet-150 rounded-lg text-[10px] font-bold uppercase tracking-wider truncate max-w-[180px]" title="{{ $upload->qualification_name }}">
                                    {{ $upload->qualification_name }}
                                </span>
                            </div>
                        </div>

                        {{-- Stats & Meta --}}
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            {{-- Metrics Grid --}}
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-slate-50 rounded-xl p-2 border border-slate-100">
                                    <span class="block text-[10px] font-bold text-slate-450 uppercase tracking-wider">Candidates Updated</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $upload->candidates_updated_count }}</span>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-2 border border-slate-100">
                                    <span class="block text-[10px] font-bold text-slate-450 uppercase tracking-wider">Subjects Updated</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $upload->subjects_updated_count }}</span>
                                </div>
                            </div>

                            {{-- Footer meta info --}}
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium">
                                <span class="truncate">By: {{ $upload->user->name ?? 'System' }}</span>
                                <span>{{ $upload->uploaded_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('drop-zone');
    const fileListContainer = document.getElementById('file-list-container');
    const fileList = document.getElementById('file-list');

    fileInput.addEventListener('change', function(e) {
        updateFileList(e.target.files);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('border-indigo-400', 'bg-indigo-50/50'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('border-indigo-400', 'bg-indigo-50/50'), false);
    });

    function updateFileList(files) {
        fileList.innerHTML = '';
        if (files.length === 0) {
            fileListContainer.classList.add('hidden');
            return;
        }

        fileListContainer.classList.remove('hidden');
        const file = files[0];
        const sizeKB = (file.size / 1024).toFixed(1);
        const item = document.createElement('div');
        item.className = 'px-4 py-3 flex items-center justify-between text-xs text-slate-700 bg-slate-50/30';
        item.innerHTML = `
            <div class="flex items-center gap-2 font-medium">
                <span class="text-lg">📊</span>
                <span class="truncate font-semibold text-slate-800">${file.name}</span>
            </div>
            <div class="text-slate-400 font-mono">${sizeKB} KB</div>
        `;
        fileList.appendChild(item);
    }
</script>
@endsection
