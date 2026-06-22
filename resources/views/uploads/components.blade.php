@extends('layouts.app')

@section('title', 'Upload Component Marks')
@section('page-title', 'Upload Component-wise Marks')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    @if(empty($series_id) || empty($subject_id))
        <!-- ================== CASE 1: NO SERIES/SUBJECT SELECTED ================== -->
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-150 animate-fade-in">
            <h2 class="text-xl font-bold text-slate-800 mb-2">Select Subject & Series</h2>
            <p class="text-slate-500 text-sm mb-6">Phase 2: Select parameters to load candidates and upload component-wise marks.</p>
            
            <form id="selectorForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Qualification -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Qualification *</label>
                        <select id="qualification" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                            <option value="">-- Select Qualification --</option>
                            @foreach($qualifications as $qual)
                                <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->type_display }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Year *</label>
                        <select id="year" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                            <option value="">-- Select Year --</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Month -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Month *</label>
                        <select id="month" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                            <option value="">-- Select Month --</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Subject *</label>
                        <select id="subject" required class="w-full px-4 py-2.5 bg-slate-55 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm">
                            <option value="">-- Select Subject --</option>
                        </select>
                    </div>
                </div>

                <!-- Hidden series_id -->
                <input type="hidden" id="series_id" />

                <button type="submit" id="proceedBtn" class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 font-bold tracking-wide transition shadow-lg shadow-indigo-600/20">
                    Load Candidate Checklist & Upload Form
                </button>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qualificationSelect = document.getElementById('qualification');
                const yearSelect = document.getElementById('year');
                const monthSelect = document.getElementById('month');
                const subjectSelect = document.getElementById('subject');
                const seriesInput = document.getElementById('series_id');
                const proceedBtn = document.getElementById('proceedBtn');

                // Reset helpers
                function clearSelect(selectElement, defaultText) {
                    selectElement.innerHTML = `<option value="">${defaultText}</option>`;
                }

                // Get years and subjects when qualification changes
                qualificationSelect.addEventListener('change', async function() {
                    const qualificationId = this.value;
                    if (!qualificationId) {
                        clearSelect(yearSelect, '-- Select Year --');
                        clearSelect(monthSelect, '-- Select Month --');
                        clearSelect(subjectSelect, '-- Select Subject --');
                        seriesInput.value = '';
                        return;
                    }

                    // Fetch Years
                    try {
                        const response = await fetch(`/api/years/${qualificationId}`);
                        const data = await response.json();
                        clearSelect(yearSelect, '-- Select Year --');
                        data.years.forEach(year => {
                            yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
                        });
                    } catch (err) {
                        console.error(err);
                    }

                    // Fetch Subjects
                    try {
                        const response = await fetch(`/api/subjects/${qualificationId}`);
                        const data = await response.json();
                        clearSelect(subjectSelect, '-- Select Subject --');
                        data.subjects.forEach(subject => {
                            subjectSelect.innerHTML += `<option value="${subject.id}">${subject.subject_name} (${subject.subject_code})</option>`;
                        });
                    } catch (err) {
                        console.error(err);
                    }
                    
                    clearSelect(monthSelect, '-- Select Month --');
                    seriesInput.value = '';
                });

                // Get months when year changes
                yearSelect.addEventListener('change', async function() {
                    const qualificationId = qualificationSelect.value;
                    const year = this.value;
                    if (!qualificationId || !year) {
                        clearSelect(monthSelect, '-- Select Month --');
                        seriesInput.value = '';
                        return;
                    }

                    try {
                        const response = await fetch(`/api/months?qualification_id=${qualificationId}&year=${year}`);
                        const data = await response.json();
                        clearSelect(monthSelect, '-- Select Month --');
                        data.months.forEach(month => {
                            monthSelect.innerHTML += `<option value="${month}">${month}</option>`;
                        });
                    } catch (err) {
                        console.error(err);
                    }
                    seriesInput.value = '';
                });

                // Get series_id when month changes
                const updateSeries = async function() {
                    const qualificationId = qualificationSelect.value;
                    const year = yearSelect.value;
                    const month = monthSelect.value;

                    if (!qualificationId || !year || !month) {
                        seriesInput.value = '';
                        return;
                    }

                    try {
                        const response = await fetch(`/api/series?` + new URLSearchParams({
                            qualification_id: qualificationId,
                            year: year,
                            month: month
                        }));

                        if (response.ok) {
                            const data = await response.json();
                            seriesInput.value = data.series_id;
                        } else {
                            seriesInput.value = '';
                        }
                    } catch (err) {
                        console.error(err);
                        seriesInput.value = '';
                    }
                };

                monthSelect.addEventListener('change', updateSeries);

                // Handle submit
                document.getElementById('selectorForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!seriesInput.value || !subjectSelect.value) {
                        alert('Please select all required parameters.');
                        return;
                    }
                    window.location.href = `?series_id=${seriesInput.value}&subject_id=${subjectSelect.value}`;
                });
            });
        </script>

    @else
        <!-- ================== CASE 2: SERIES/SUBJECT CHOSEN ================== -->
        <!-- Header Card -->
        @php
            $seriesName = $selectedSeries ? $selectedSeries->series_name : 'Selected Series';
            $subjectName = $selectedSubject ? $selectedSubject->subject_name : 'Selected Subject';
        @endphp
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150 flex flex-col md:flex-row md:items-center md:justify-between gap-6 animate-fade-in">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 mb-1">Upload Component Marks (Phase 2)</h2>
                <p class="text-slate-500 text-sm">Upload marks for each specific component paper for candidates in this subject.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <span class="px-4 py-2 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-bold rounded-xl">
                    Series: {{ $seriesName }}
                </span>
                <span class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl">
                    Subject: {{ $subjectName }}
                </span>
                <a href="{{ route('uploads.components') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-655 text-xs font-bold rounded-xl transition border border-slate-250">
                    Change Selection
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upload Form and Component List -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-base font-bold text-slate-800 mb-4 uppercase tracking-wider">File Upload Form</h3>
                    <form id="componentUploadForm" method="POST" action="{{ route('uploads.components.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="series_id" value="{{ $series_id }}" />
                        <input type="hidden" name="subject_id" value="{{ $subject_id }}" />

                        <!-- Upload Area -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Upload File (CSV or Excel) *</label>
                            <div class="border-2 border-dashed border-slate-200 hover:border-indigo-500 rounded-2xl p-8 text-center cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition relative">
                                <input type="file" name="components_file" accept=".csv,.xlsx,.xls" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="fileInput" />
                                <div class="flex flex-col items-center justify-center pointer-events-none">
                                    <svg class="w-10 h-10 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                    <p class="text-slate-600 font-medium text-sm">Drag and drop your file or click to browse</p>
                                    <p class="text-xs text-slate-400 mt-1">CSV or Excel (.csv, .xlsx, .xls)</p>
                                </div>
                                <p id="fileName" class="mt-4 text-sm font-semibold text-emerald-600"></p>
                            </div>
                        </div>

                        <!-- File Format Info -->
                        <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 mb-6">
                            <h4 class="text-xs font-bold text-indigo-900 mb-2 uppercase tracking-wider">Required File Structure:</h4>
                            <ul class="text-xs text-indigo-800 space-y-1 list-disc list-inside">
                                <li>Column 1: Candidate Number</li>
                                <li>Column 2: Component Code</li>
                                <li>Column 3: Obtained Marks</li>
                            </ul>
                        </div>

                        <button type="submit" id="submitBtn" class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 font-bold tracking-wide transition shadow-lg shadow-indigo-600/20">
                            Upload Component Marks
                        </button>
                    </form>

                    <!-- Results Display -->
                    <div id="uploadResults" class="mt-6"></div>
                </div>

                <!-- Candidate Marks Status Checklist -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-base font-bold text-slate-800 mb-4 uppercase tracking-wider">Enrolled Candidates Status</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-150 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                                    <th class="pb-3">Candidate</th>
                                    <th class="pb-3">Initial Grade & PUM</th>
                                    <th class="pb-3">Component Marks Breakdown</th>
                                    <th class="pb-3 text-center">Status</th>
                                    <th class="pb-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($results as $res)
                                    <tr class="hover:bg-slate-50/20 transition duration-150">
                                        <td class="py-4">
                                            <div class="font-bold text-slate-800">{{ $res->enrollment->candidate->candidate_name }}</div>
                                            <div class="text-xs text-slate-400 font-mono">{{ $res->enrollment->candidate->candidate_number }}</div>
                                        </td>
                                        <td class="py-4">
                                            <span class="inline-flex items-center px-2 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded whitespace-nowrap">
                                                {{ ($res->enrollment->qualification->qualification_type === 'AS_A_LEVEL' && in_array($res->grade, ['a', 'b', 'c', 'd', 'e'])) ? $res->grade . ' (AS Level)' : $res->grade }}
                                            </span>
                                            <span class="text-xs text-slate-450 font-semibold ml-2">PUM: {{ $res->pum }}%</span>
                                        </td>
                                        <td class="py-4">
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($components as $comp)
                                                    @php
                                                        $mark = $res->componentMarks->where('component_id', $comp->id)->first();
                                                    @endphp
                                                    @if($mark)
                                                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-100">
                                                            {{ $comp->component_code }}: {{ round($mark->obtained_marks) }}/{{ $comp->total_marks }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 bg-rose-50 text-rose-700 text-xs font-bold rounded-full border border-rose-100">
                                                            {{ $comp->component_code }}: Missing
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-4 text-center">
                                            @if($res->status === 'component_marks_added' || $res->status === 'complete')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-100 text-emerald-800">
                                                    Complete
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 border border-amber-100 text-amber-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-right">
                                            <a href="{{ route('results.show', $res->id) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-bold hover:underline">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs italic">
                                            No student results uploaded for this subject/series yet. Upload Phase 1 first.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Components List Info -->
            <div class="space-y-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-150">
                    <h3 class="text-base font-bold text-slate-800 mb-4 uppercase tracking-wider">Subject Components</h3>
                    <div class="space-y-4">
                        @forelse($components as $comp)
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-150 text-indigo-750 text-xs font-extrabold rounded font-mono">
                                        {{ $comp->component_code }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500">
                                        Max: {{ $comp->total_marks }} marks
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800">{{ $comp->component_name }}</h4>
                            </div>
                        @empty
                            <div class="text-slate-400 text-xs text-center py-6">
                                No components defined for this subject.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('fileInput');
                const fileName = document.getElementById('fileName');
                const submitBtn = document.getElementById('submitBtn');

                fileInput.addEventListener('change', function() {
                    fileName.textContent = this.files[0]?.name || '';
                });

                document.getElementById('componentUploadForm').addEventListener('submit', async function(e) {
                    e.preventDefault();

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Uploading marks...';

                    const formData = new FormData(this);
                    try {
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            displayResults(result, 'success');
                            // Reload after 1.5 seconds to show updated checklist
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            displayResults(result, 'error');
                        }
                    } catch (err) {
                        displayResults({ message: 'A communication error occurred: ' + err.message }, 'error');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Upload Component Marks';
                    }
                });

                function displayResults(data, status) {
                    const resultsDiv = document.getElementById('uploadResults');
                    let html = `
                        <div class="mt-4 p-4 rounded-xl border ${status === 'success' ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100'}">
                            <h4 class="text-sm font-bold ${status === 'success' ? 'text-emerald-800' : 'text-rose-800'} mb-2">
                                ${data.message}
                            </h4>
                            <div class="flex gap-4 text-xs font-semibold text-slate-500">
                                <p>Successful: <span class="text-emerald-600 font-extrabold">${data.successful_count || 0}</span></p>
                                <p>Failed: <span class="text-rose-600 font-extrabold">${data.failed_count || 0}</span></p>
                            </div>
                    `;

                    if (data.data?.failed?.length > 0) {
                        html += `
                            <div class="mt-3">
                                <div class="bg-white p-3 rounded-lg border border-slate-100 text-[11px] text-rose-700 max-h-36 overflow-y-auto space-y-1 font-mono">
                                    \${data.data.failed.map(f => `<p>Row \${f.row}: Candidate \${f.candidate} - \${f.error}</p>`).join('')}
                                </div>
                            </div>
                        `;
                    }

                    html += `</div>`;
                    resultsDiv.innerHTML = html;
                }
            });
        </script>
    @endif
</div>
@endsection
