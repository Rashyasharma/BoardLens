@extends('layouts.app')

@section('title', 'AI-Assisted Results Importer - Lucky International School')
@section('page-title', 'AI-Assisted Results Importer')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Info Alert --}}
    <div class="bg-indigo-50/75 border border-indigo-150 p-6 rounded-3xl flex gap-4">
        <span class="text-3xl">✨</span>
        <div>
            <h4 class="text-indigo-900 font-bold text-base">Next-Gen AI Statement Parser</h4>
            <p class="text-indigo-750 text-sm mt-1 leading-relaxed">
                Upload one or more Cambridge Electronic Statements of Results (PDF, Excel, or CSV). The system will use Gemini AI (or local heuristic matching) to analyze the document structure, detect candidate rows, map subjects to standard Cambridge codes, and display a detailed preview before saving to the database.
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
            <h3 class="text-lg font-bold text-slate-800 tracking-tight">Upload Statements of Results</h3>
            
            <form method="POST" action="{{ route('uploads.ai_importer.preview') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                {{-- Drag and drop file select zone --}}
                <div class="border-2 border-dashed border-slate-250 rounded-2xl p-8 text-center bg-slate-50 hover:bg-slate-100/50 hover:border-indigo-400 transition cursor-pointer relative" id="drop-zone">
                    <input type="file" name="statement_files[]" id="file-input" required multiple accept=".csv,.xlsx,.xls,.pdf"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-3">
                        <div class="text-4xl text-slate-400">📂</div>
                        <div class="text-sm font-bold text-slate-700">Drag & drop your files here, or click to browse</div>
                        <div class="text-xs text-slate-400">Supports Excel (.xlsx, .xls), CSV, and PDF statements of results</div>
                    </div>
                </div>

                {{-- Selected Files List --}}
                <div id="file-list-container" class="hidden space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Selected Files</label>
                    <div id="file-list" class="divide-y divide-slate-100 border border-slate-150 rounded-xl bg-white overflow-hidden">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition duration-150 shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2">
                    <span>✨</span> Analyze & Preview
                </button>
            </form>
        </div>

        {{-- Guide & Features Card --}}
        <div class="bg-slate-900 text-slate-350 p-6 rounded-3xl shadow-sm border border-slate-800 space-y-5">
            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider">How it works</h4>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                    Unlike standard loaders, the AI importer doesn't require rigid, pre-formatted templates. Just upload your Cambridge Electronic Statement of Results.
                </p>
            </div>

            <ul class="space-y-3 text-xs">
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span><strong>Flexible Columns</strong>: Dynamically locates student names and candidate numbers.</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span><strong>Fuzzy Subject Mapping</strong>: Automatically links column names (e.g., "Maths") to correct Cambridge syllabus codes.</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span><strong>Format Detection</strong>: Parses standard grades like <code class="bg-slate-850 px-1 py-0.5 rounded text-white" style="background-color: #1e293b; color: #ffffff;">A*(91)</code>, raw scores, or letter grades.</span>
                </li>
                <li class="flex gap-2" style="color: #d1d5db !important;">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span><strong>Match Preview</strong>: Inspects every record against current database values before confirming.</span>
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
                    <span>🕒</span> Last 3 Uploaded Results Statements
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($recentUploads as $upload)
                    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition duration-200 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            {{-- Header: Filename & Status --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-lg shrink-0">📄</span>
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

    // Simple Drag & Drop Style Highlighting
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
        Array.from(files).forEach((file, index) => {
            const sizeKB = (file.size / 1024).toFixed(1);
            const item = document.createElement('div');
            item.className = 'px-4 py-3 flex items-center justify-between text-xs text-slate-700 bg-slate-50/30';
            item.innerHTML = `
                <div class="flex items-center gap-2 font-medium">
                    <span class="text-lg">📄</span>
                    <span class="truncate font-semibold text-slate-800">${file.name}</span>
                </div>
                <div class="text-slate-400 font-mono">${sizeKB} KB</div>
            `;
            fileList.appendChild(item);
        });
    }
</script>
@endsection
