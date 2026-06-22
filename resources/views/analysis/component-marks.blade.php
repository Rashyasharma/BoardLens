@extends('layouts.app')

@section('title', 'Component Marks Analysis')
@section('page-title', 'Component-wise Marks Analysis')

@section('content')
<div class="space-y-6 max-w-full px-6 mx-auto py-2">
    
    <!-- Step 1: Selector Panel -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm animate-fade-in space-y-4">
        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Select Qualification &amp; Subject</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Qualification Selector -->
            <div>
                <label class="block text-xxs font-extrabold text-slate-400 uppercase mb-2">Qualification</label>
                <select id="qual-select" onchange="filterSubjects()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs font-bold text-slate-700">
                    <option value="">-- Choose Qualification --</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->qualification_name }} ({{ $qual->qualification_type }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Subject Selector -->
            <div>
                <label class="block text-xxs font-extrabold text-slate-400 uppercase mb-2">Subject Syllabus</label>
                <select id="subj-select" onchange="loadSubjectAnalysis()" disabled class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-xs font-bold text-slate-700 disabled:opacity-50">
                    <option value="">-- Choose Subject --</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div id="analysis-container" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Side: listed Components tiles (Spans 5 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="border-b border-slate-100 pb-2 flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider">Available Components</span>
                <span id="components-count-badge" class="text-xxs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md">0 Papers</span>
            </div>
            
            <div id="components-list-container" class="space-y-3">
                <!-- Dynamically loaded component buttons -->
            </div>
        </div>

        <!-- Right Side: Component Detailed Dashboard & Analytics (Spans 8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Default Placeholder (All Components Graph) -->
            <div id="comp-dashboard-placeholder" class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4 text-center text-slate-400 text-xs">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider text-left">All Components Comparison</h4>
                <div class="relative h-80 w-full">
                    <canvas id="allComponentsChart"></canvas>
                </div>
                <p class="pt-2">Select a component tile from the list to view its specific yearly trend performance, max/min scores, and top scorer candidate details.</p>
            </div>

            <!-- Component Details View Panel -->
            <div id="comp-dashboard-panel" class="hidden space-y-6">
                <!-- Summary Stats Header Card -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <span id="comp-code-badge" class="px-2.5 py-0.5 bg-indigo-50 border border-indigo-150 text-indigo-700 font-extrabold rounded text-[10px] uppercase tracking-wider font-mono">
                                P1
                            </span>
                            <h3 id="comp-name-title" class="text-lg font-black text-slate-800 tracking-tight">Component Name</h3>
                            <p class="text-xs text-slate-450 font-bold">
                                Weight/Total Marks: <span id="comp-total-marks" class="font-mono text-slate-700">0</span>
                                <span class="mx-2 text-slate-300">|</span>
                                Candidates Sat: <span id="comp-candidates-sat-container" class="cursor-help underline decoration-dotted text-indigo-750" title=""><span id="comp-candidates-sat" class="font-mono">0</span></span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-3 border-t border-slate-100">
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex flex-col justify-center">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Avg Percentage</span>
                            <span id="comp-avg-pct" class="text-base font-black text-slate-800 mt-0.5">0%</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 flex flex-col justify-center">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Median Mark</span>
                            <span id="comp-median" class="text-base font-black text-slate-800 mt-0.5">0</span>
                        </div>
                        <div class="bg-emerald-50/40 rounded-xl p-3 border border-emerald-100/60 flex flex-col justify-center">
                            <span class="block text-[10px] font-black text-emerald-600/80 uppercase tracking-wider">Highest Score</span>
                            <span id="comp-highest" class="text-base font-black text-emerald-650 mt-0.5">0</span>
                        </div>
                        <div class="bg-rose-50/40 rounded-xl p-3 border border-rose-100/60 flex flex-col justify-center">
                            <span class="block text-[10px] font-black text-rose-600/80 uppercase tracking-wider">Lowest Score</span>
                            <span id="comp-lowest" class="text-base font-black text-rose-650 mt-0.5">0</span>
                        </div>
                    </div>
                </div>

                <!-- Yearly Performance Trend Chart Card -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Yearly Performance Trends</h4>
                    <div class="relative h-64">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Series-wise detailed min/max card (Tiles Layout) -->
                <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-6 space-y-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Series High / Low Performers</h4>
                    
                    <div id="series-performers-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Dynamically filled tile records -->
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<!-- Data Injection -->
<script>
    const qualificationData = @json($qualifications);

    const componentStats = @json($componentAnalysis);
    const trendsData = @json($componentTrends);
    
    let trendChartInstance = null;
    let allComponentsChartInstance = null;

    function filterSubjects() {
        const qualId = document.getElementById('qual-select').value;
        const subjSelect = document.getElementById('subj-select');
        
        subjSelect.innerHTML = '<option value="">-- Choose Subject --</option>';
        subjSelect.disabled = true;
        document.getElementById('analysis-container').classList.add('hidden');

        if (!qualId) return;

        const qual = qualificationData.find(q => q.id == qualId);
        if (qual && qual.subjects && qual.subjects.length > 0) {
            qual.subjects.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `${s.subject_name} (${s.subject_code})`;
                subjSelect.appendChild(opt);
            });
            subjSelect.disabled = false;
        }
    }

    function loadSubjectAnalysis() {
        const subjId = document.getElementById('subj-select').value;
        const container = document.getElementById('analysis-container');
        const listContainer = document.getElementById('components-list-container');
        const badgeCount = document.getElementById('components-count-badge');
        
        listContainer.innerHTML = '';
        document.getElementById('comp-dashboard-placeholder').classList.remove('hidden');
        document.getElementById('comp-dashboard-panel').classList.add('hidden');

        if (!subjId) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');

        // Fetch selected subject details
        const qualSelect = document.getElementById('qual-select');
        const qualId = qualSelect.value;
        const qual = qualificationData.find(q => q.id == qualId);
        const subj = qual ? qual.subjects.find(s => s.id == subjId) : null;
        const subjCode = subj ? subj.subject_code : '';

        // Build list from subject's registered components, filling stats if they exist
        const filtered = [];
        if (subj && subj.components) {
            subj.components.forEach(comp => {
                const uniqueKey = `${subj.id}_${comp.component_code} - ${comp.component_name}`;
                const stats = componentStats[uniqueKey] || {
                    code: comp.component_code,
                    name: comp.component_name,
                    total_marks: comp.total_marks,
                    subject_id: subj.id,
                    candidate_count: 0,
                    avg_marks: 0,
                    avg_percentage: 0,
                    highest: 0,
                    lowest: 0,
                    median: 0,
                    std_dev: 0,
                    distribution: [0, 0, 0, 0, 0],
                    has_data: false
                };
                if (componentStats[uniqueKey]) {
                    stats.has_data = true;
                }
                filtered.push(stats);
            });
        }

        // Sort naturally by code (e.g. Paper 1, Paper 2...)
        filtered.sort((a, b) => {
            return a.code.localeCompare(b.code, undefined, {numeric: true, sensitivity: 'base'});
        });

        badgeCount.textContent = `${filtered.length} Paper(s)`;

        if (filtered.length === 0) {
            listContainer.innerHTML = '<div class="text-xs text-slate-400 italic p-4 text-center">No components configured for this subject.</div>';
            return;
        }

        filtered.forEach((c, idx) => {
            const uniqueKey = `${c.subject_id}_${c.code} - ${c.name}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'comp-tile-btn w-full text-left bg-white p-4 rounded-2xl border border-slate-200 transition duration-150 shadow-sm hover:shadow hover:border-slate-350 flex justify-between items-center group';
            btn.onclick = () => selectComponent(uniqueKey, c, btn);
            
            btn.innerHTML = `
                <div class="space-y-1">
                    <span class="inline-block px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xxs font-mono font-bold text-slate-700">
                        ${c.code}
                    </span>
                    <h5 class="text-xs font-bold text-slate-800 line-clamp-1 group-hover:text-indigo-900 transition">${c.name}</h5>
                </div>
                <div class="text-right">
                    <span class="text-xxs text-slate-400 font-semibold block">Average</span>
                    <span class="text-xs font-extrabold text-indigo-755">${c.has_data ? Math.round(c.avg_percentage) + '%' : 'N/A'}</span>
                </div>
            `;
            listContainer.appendChild(btn);
        });

        // Render All Components Chart
        renderAllComponentsChart(filtered);
    }

    function renderAllComponentsChart(componentsList) {
        if (allComponentsChartInstance) {
            allComponentsChartInstance.destroy();
        }

        // Collect all unique series labels across all components
        const allSeriesSet = new Set();
        componentsList.forEach(c => {
            const uniqueKey = `${c.subject_id}_${c.code} - ${c.name}`;
            const trend = trendsData[uniqueKey];
            if (trend && trend.series_trends) {
                trend.series_trends.forEach(s => allSeriesSet.add(s.series));
            }
        });
        
        // Month order for sorting chronologically
        const monthOrder = {'March': 1, 'June': 2, 'November': 3};
        const seriesLabels = Array.from(allSeriesSet).sort((a, b) => {
            // Assume format "June 2023"
            const [monthA, yearA] = a.split(' ');
            const [monthB, yearB] = b.split(' ');
            if (yearA !== yearB) return yearA - yearB;
            return (monthOrder[monthA] || 0) - (monthOrder[monthB] || 0);
        });

        const datasets = [];
        const colors = [
            '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', 
            '#0ea5e9', '#ec4899', '#14b8a6', '#f97316', '#64748b'
        ];

        componentsList.forEach((c, index) => {
            if (!c.has_data) return;
            const uniqueKey = `${c.subject_id}_${c.code} - ${c.name}`;
            const trend = trendsData[uniqueKey];
            if (!trend || !trend.series_trends) return;

            const dataPoints = seriesLabels.map(label => {
                const found = trend.series_trends.find(s => s.series === label);
                return found ? found.avg_pct : null;
            });

            const color = colors[index % colors.length];
            datasets.push({
                label: `${c.code} - ${c.name}`,
                data: dataPoints,
                borderColor: color,
                backgroundColor: color,
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                spanGaps: true
            });
        });

        const ctx = document.getElementById('allComponentsChart').getContext('2d');
        allComponentsChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: seriesLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 10 }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#f1f5f9' },
                        title: { display: true, text: 'Average Percentage (%)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    function selectComponent(uniqueKey, component, btnElement) {
        // Toggle selected state style
        document.querySelectorAll('.comp-tile-btn').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500/10');
            btn.classList.add('border-slate-200');
        });

        if (btnElement) {
            btnElement.classList.remove('border-slate-200');
            btnElement.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500/10');
        }

        // Toggle visibility
        document.getElementById('comp-dashboard-placeholder').classList.add('hidden');
        document.getElementById('comp-dashboard-panel').classList.remove('hidden');

        // Fill details
        document.getElementById('comp-code-badge').textContent = component.code;
        document.getElementById('comp-name-title').textContent = component.name;
        document.getElementById('comp-total-marks').textContent = component.total_marks;

        if (!component.has_data) {
            document.getElementById('comp-avg-pct').textContent = 'N/A';
            document.getElementById('comp-median').textContent = 'N/A';
            document.getElementById('comp-highest').innerHTML = '<span class="text-slate-400 font-bold">N/A</span>';
            document.getElementById('comp-lowest').innerHTML = '<span class="text-slate-400 font-bold">N/A</span>';
            document.getElementById('comp-candidates-sat').textContent = '0';
            document.getElementById('comp-candidates-sat-container').title = 'No candidate data';
            document.getElementById('series-performers-grid').innerHTML = '<div class="col-span-full text-center text-slate-400 italic py-6">No candidate marks uploaded for this component yet.</div>';
            
            if (trendChartInstance) {
                trendChartInstance.destroy();
                trendChartInstance = null;
            }
            return;
        }

        const trend = trendsData[uniqueKey];
        if (!trend) return;

        // Build series-wise hover tooltip text
        const tooltipParts = [];
        if (trend.series_trends) {
            trend.series_trends.forEach(s => {
                tooltipParts.push(`${s.series}: ${s.candidate_count || 0} candidate(s)`);
            });
        }
        const tooltipText = tooltipParts.join('\n') || 'No candidate data';

        document.getElementById('comp-candidates-sat').textContent = component.candidate_count;
        document.getElementById('comp-candidates-sat-container').title = tooltipText;

        // Fill data - Overall stats (All Students, All Series, All Years) in main dashboard
        document.getElementById('comp-code-badge').textContent = component.code;
        document.getElementById('comp-name-title').textContent = component.name;
        document.getElementById('comp-total-marks').textContent = component.total_marks;
        
        // At-a-glance average, max, and min details
        document.getElementById('comp-avg-pct').textContent = `${Math.round(component.avg_percentage)}%`;
        document.getElementById('comp-median').textContent = `${component.median} / ${component.total_marks}`;
        
        // Overall Highest/Lowest marks ever with candidate names
        document.getElementById('comp-highest').innerHTML = `
            <span class="block text-slate-800 font-black">${component.highest}/${component.total_marks}</span>
            <span class="block text-[10px] text-slate-400 leading-tight font-semibold truncate">${trend.highest_candidate}</span>
        `;
        document.getElementById('comp-lowest').innerHTML = `
            <span class="block text-slate-800 font-black">${component.lowest}/${component.total_marks}</span>
            <span class="block text-[10px] text-slate-400 leading-tight font-semibold truncate">${trend.lowest_candidate}</span>
        `;

        // Populate tiles grid
        const grid = document.getElementById('series-performers-grid');
        grid.innerHTML = '';
        trend.series_trends.forEach(row => {
            const tile = document.createElement('div');
            tile.className = 'bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition';
            tile.innerHTML = `
                <div class="flex justify-between items-start mb-3 border-b border-slate-150 pb-2">
                    <span class="font-extrabold text-slate-800 text-sm">${row.series}</span>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-black px-2 py-0.5 rounded-md">${row.avg_pct}% Avg</span>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-1">
                    <div class="bg-emerald-50/50 rounded-xl p-2 border border-emerald-100">
                        <span class="block text-[9px] font-black text-emerald-600/80 uppercase tracking-wider mb-0.5">Highest</span>
                        <span class="block font-extrabold text-emerald-700 text-sm leading-none">${Math.round(row.max_score)}/${component.total_marks}</span>
                        <span class="block text-[9px] text-slate-400 font-semibold mt-1 truncate" title="${row.max_candidate}">${row.max_candidate}</span>
                    </div>
                    <div class="bg-rose-50/50 rounded-xl p-2 border border-rose-100">
                        <span class="block text-[9px] font-black text-rose-600/80 uppercase tracking-wider mb-0.5">Lowest</span>
                        <span class="block font-extrabold text-rose-700 text-sm leading-none">${Math.round(row.min_score)}/${component.total_marks}</span>
                        <span class="block text-[9px] text-slate-400 font-semibold mt-1 truncate" title="${row.min_candidate}">${row.min_candidate}</span>
                    </div>
                </div>
            `;
            grid.appendChild(tile);
        });

        // Render Chart
        const labels = trend.series_trends.map(t => t.series);
        const datasetData = trend.series_trends.map(t => t.avg_pct);

        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        const ctx = document.getElementById('trendChart').getContext('2d');
        trendChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Performance %',
                    data: datasetData,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    borderWidth: 3,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
</script>
@endsection
