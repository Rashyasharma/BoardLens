@extends('layouts.app')

@section('title', 'Upload Results')
@section('page-title', 'Upload Student Results')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
    <h2 class="text-2xl font-bold text-slate-800 mb-2">Upload Results (Grade + PUM)</h2>
    <p class="text-slate-500 mb-6">Phase 1: Upload the core student results by specifying qualification, series, and subject.</p>
    
    <form id="uploadResultForm" method="POST" action="{{ route('uploads.results.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Qualification -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Qualification *</label>
                <select id="qualification" name="qualification_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">-- Select Qualification --</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Year -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Year *</label>
                <select id="year" name="year" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">-- Select Year --</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Month -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Month *</label>
                <select id="month" name="month" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">-- Select Month --</option>
                </select>
            </div>

            <!-- Subject -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Subject *</label>
                <select id="subject" name="subject_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">-- Select Subject --</option>
                </select>
            </div>
        </div>

        <!-- Hidden series_id -->
        <input type="hidden" id="series_id" name="series_id" />

        <!-- Upload Area -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Upload File (CSV or Excel) *</label>
            <div class="border-2 border-dashed border-slate-200 hover:border-indigo-500 rounded-2xl p-8 text-center cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition relative">
                <input type="file" name="results_file" accept=".csv,.xlsx,.xls" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" id="fileInput" />
                <div class="flex flex-col items-center justify-center pointer-events-none">
                    <svg class="w-10 h-10 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-slate-600 font-medium">Drag and drop your file or click to browse</p>
                    <p class="text-xs text-slate-400 mt-1">CSV or Excel (.csv, .xlsx, .xls)</p>
                </div>
                <p id="fileName" class="mt-4 text-sm font-semibold text-emerald-600"></p>
            </div>
        </div>

        <!-- File Format Info -->
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-indigo-900 mb-2">Required File Structure:</h4>
            <ul class="text-xs text-indigo-800 space-y-1 list-disc list-inside">
                <li>Column 1: Candidate Number</li>
                <li>Column 2: Student Name</li>
                <li>Column 3: Grade (A*, A, B, C, D, E, U)</li>
                <li>Column 4: PUM (0 to 100)</li>
            </ul>
        </div>

        <button type="submit" id="submitBtn" class="w-full bg-indigo-600 text-white py-3 rounded-xl hover:bg-indigo-700 font-bold tracking-wide transition shadow-lg shadow-indigo-600/20 hover:shadow-indigo-600/30">
            Upload & Process Results
        </button>
    </form>

    <!-- Results Display -->
    <div id="uploadResults" class="mt-8"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qualificationSelect = document.getElementById('qualification');
        const yearSelect = document.getElementById('year');
        const monthSelect = document.getElementById('month');
        const subjectSelect = document.getElementById('subject');
        const seriesInput = document.getElementById('series_id');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');

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

        // File input label update
        fileInput.addEventListener('change', function() {
            fileName.textContent = this.files[0]?.name || '';
        });

        // Form submission
        document.getElementById('uploadResultForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!seriesInput.value) {
                alert('No valid exam series found for the selected combination.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing upload...';

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
                } else {
                    displayResults(result, 'error');
                }
            } catch (err) {
                displayResults({ message: 'A communication error occurred: ' + err.message }, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Upload & Process Results';
            }
        });

        function displayResults(data, status) {
            const resultsDiv = document.getElementById('uploadResults');
            let html = `
                <div class="mt-8 p-6 rounded-2xl border ${status === 'success' ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100'}">
                    <h3 class="text-lg font-bold ${status === 'success' ? 'text-emerald-800' : 'text-rose-800'} mb-4">
                        ${data.message}
                    </h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-4 bg-white rounded-xl shadow-sm border border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Successful Records</p>
                            <p class="text-3xl font-extrabold text-emerald-600 mt-1">${data.successful_count || 0}</p>
                        </div>
                        <div class="p-4 bg-white rounded-xl shadow-sm border border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Failed Records</p>
                            <p class="text-3xl font-extrabold text-rose-600 mt-1">${data.failed_count || 0}</p>
                        </div>
                    </div>
            `;

            if (data.data?.failed?.length > 0) {
                html += `
                    <div class="mt-4">
                        <h4 class="font-bold text-slate-700 mb-2 text-sm">Failed Records Details:</h4>
                        <div class="bg-white p-4 rounded-xl border border-slate-100 text-xs text-rose-700 max-h-48 overflow-y-auto space-y-1">
                            ${data.data.failed.map(f => `<p><strong>Row ${f.row}:</strong> Candidate [${f.candidate}] - ${f.error}</p>`).join('')}
                        </div>
                    </div>
                `;
            }

            if (status === 'success' && data.successful_count > 0) {
                html += `
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('uploads.components') }}?series_id=${seriesInput.value}&subject_id=${subjectSelect.value}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-750 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition">
                            Proceed to Component Upload (Phase 2)
                            <svg class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                `;
            }

            html += `</div>`;
            resultsDiv.innerHTML = html;
            
            // Scroll to results
            resultsDiv.scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>
@endsection
