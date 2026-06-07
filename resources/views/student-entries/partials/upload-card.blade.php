<div class="bg-white border border-slate-150 rounded-2xl shadow-sm overflow-hidden flex flex-col">
    {{-- Card Header --}}
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <h4 class="text-xs font-black text-slate-500 uppercase tracking-wider">Register Candidates</h4>
    </div>

    {{-- Switcher --}}
    <div class="p-3 bg-slate-50 border-b border-slate-100 flex gap-1">
        <button type="button" onclick="switchUploadTab('{{ $type }}', 'paste')" id="upload-tab-paste-btn-{{ $type }}"
            class="flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition bg-white shadow-xs text-indigo-600">
            ✍️ Paste List
        </button>
        <button type="button" onclick="switchUploadTab('{{ $type }}', 'csv')" id="upload-tab-csv-btn-{{ $type }}"
            class="flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition text-slate-500 hover:bg-slate-100">
            📂 CSV File
        </button>
        <button type="button" onclick="switchUploadTab('{{ $type }}', 'manual')" id="upload-tab-manual-btn-{{ $type }}"
            class="flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition text-slate-500 hover:bg-slate-100">
            👤 Manual
        </button>
    </div>

    {{-- Contents --}}
    <div class="p-5">
        {{-- ========================================== --}}
        {{-- PASTE LIST FORM --}}
        {{-- ========================================== --}}
        <form method="POST" action="{{ route('student-entries.upload', $series->id) }}" id="upload-content-paste-{{ $type }}" class="space-y-4">
            @csrf
            <input type="hidden" name="qualification_id" value="{{ $qual->id }}" />

            <div class="space-y-1">
                <label class="block text-xxs font-black text-slate-400 uppercase tracking-wider">Paste Candidate List</label>
                <p class="text-[10px] text-slate-400 font-semibold leading-tight">Enter one candidate per line: <code class="font-mono bg-slate-100 px-0.5 rounded text-[9px]">Number, Name</code> or tab-separated (from Excel).</p>
                <textarea
                    name="raw_text"
                    rows="8"
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition placeholder-slate-400 font-medium"
                    placeholder="0001, John Doe&#10;0002, Jane Smith&#10;0003	Alex Mercer (Excel)"
                ></textarea>
            </div>

            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                Import &amp; Register Candidates
            </button>
        </form>

        {{-- ========================================== --}}
        {{-- CSV FILE FORM --}}
        {{-- ========================================== --}}
        <form method="POST" action="{{ route('student-entries.upload', $series->id) }}" enctype="multipart/form-data" id="upload-content-csv-{{ $type }}" class="hidden space-y-4">
            @csrf
            <input type="hidden" name="qualification_id" value="{{ $qual->id }}" />

            <div class="space-y-1">
                <label class="block text-xxs font-black text-slate-400 uppercase tracking-wider">Upload CSV File</label>
                <p class="text-[10px] text-slate-400 font-semibold leading-tight">First row should be a header. Col 1: Number, Col 2: Name.</p>
                <div class="relative group border border-dashed border-slate-250 hover:border-indigo-400 rounded-xl p-8 text-center bg-slate-50 hover:bg-indigo-50/10 transition cursor-pointer">
                    <input
                        type="file"
                        name="candidate_file"
                        accept=".csv,.txt"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="updateFileNameForType(this, '{{ $type }}')"
                    />
                    <div class="space-y-1.5">
                        <span class="inline-block text-xl">📥</span>
                        <p class="text-xxs font-bold text-slate-700" id="file-label-text-{{ $type }}">
                            Choose a CSV file or <span class="text-indigo-600 hover:underline">browse</span>
                        </p>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                Upload &amp; Register Candidates
            </button>
        </form>

        {{-- ========================================== --}}
        {{-- MANUAL ENTRY FORM --}}
        {{-- ========================================== --}}
        <form method="POST" action="{{ route('student-entries.add-candidate', $series->id) }}" id="upload-content-manual-{{ $type }}" class="hidden space-y-4">
            @csrf
            <input type="hidden" name="qualification_id" value="{{ $qual->id }}" />

            <div class="space-y-1">
                <label class="block text-xxs font-black text-slate-400 uppercase tracking-wider">Candidate Number</label>
                <input type="text" name="candidate_number" required placeholder="e.g. 0001" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
            </div>

            <div class="space-y-1">
                <label class="block text-xxs font-black text-slate-400 uppercase tracking-wider">Candidate Name</label>
                <input type="text" name="candidate_name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500/20 font-bold text-slate-700" />
            </div>

            <div class="space-y-1.5">
                <label class="block text-xxs font-black text-slate-400 uppercase tracking-wider">Select Subjects</label>
                <div class="max-h-48 overflow-y-auto space-y-2 border border-slate-100 rounded-xl p-3 bg-slate-50">
                    @foreach($type === 'igcse' ? $igcseSubjects : $gceSubjects as $sub)
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-indigo-600 transition">
                            <input type="checkbox" name="subjects[]" value="{{ $sub->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-350" />
                            <span>{{ $sub->subject_name }} ({{ $sub->subject_code }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                Register Candidate
            </button>
        </form>
    </div>
</div>

<script>
    if (typeof switchUploadTab !== 'function') {
        function switchUploadTab(type, tab) {
            const pasteBtn = document.getElementById('upload-tab-paste-btn-' + type);
            const csvBtn = document.getElementById('upload-tab-csv-btn-' + type);
            const manualBtn = document.getElementById('upload-tab-manual-btn-' + type);

            const pasteContent = document.getElementById('upload-content-paste-' + type);
            const csvContent = document.getElementById('upload-content-csv-' + type);
            const manualContent = document.getElementById('upload-content-manual-' + type);

            // Reset buttons
            [pasteBtn, csvBtn, manualBtn].forEach(btn => {
                if (btn) {
                    btn.className = "flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition text-slate-500 hover:bg-slate-100";
                }
            });

            // Hide contents
            [pasteContent, csvContent, manualContent].forEach(c => {
                if (c) c.classList.add('hidden');
            });

            // Set active
            if (tab === 'paste') {
                if (pasteBtn) pasteBtn.className = "flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition bg-white shadow-xs text-indigo-600";
                if (pasteContent) pasteContent.classList.remove('hidden');
            } else if (tab === 'csv') {
                if (csvBtn) csvBtn.className = "flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition bg-white shadow-xs text-indigo-600";
                if (csvContent) csvContent.classList.remove('hidden');
            } else if (tab === 'manual') {
                if (manualBtn) manualBtn.className = "flex-1 py-1.5 rounded-lg text-xxs font-extrabold transition bg-white shadow-xs text-indigo-600";
                if (manualContent) manualContent.classList.remove('hidden');
            }
        }
    }

    if (typeof updateFileNameForType !== 'function') {
        function updateFileNameForType(input, type) {
            const label = document.getElementById('file-label-text-' + type);
            if (input.files && input.files[0]) {
                label.innerHTML = `Selected: <strong class="text-indigo-700 font-bold">${input.files[0].name}</strong>`;
            } else {
                label.innerHTML = `Choose a CSV file or <span class="text-indigo-600 hover:underline">browse</span>`;
            }
        }
    }
</script>
