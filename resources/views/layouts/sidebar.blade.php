<!-- Sidebar Layout -->
<div class="hidden md:flex md:flex-col md:w-56 bg-white border-r border-slate-200 min-h-screen shrink-0">
    <!-- Header/Logo -->
    <a href="{{ route('home') }}" class="h-14 flex items-center px-5 border-b border-slate-100 gap-2 hover:bg-slate-50 transition group">
        <svg class="h-6 w-6 text-slate-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span class="text-base font-bold text-slate-800 tracking-tight">CIE Board Lens</span>
    </a>

    <!-- Navigation List -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto px-3 py-4">
        <nav class="space-y-0.5">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ Request::is('dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <!-- Qualifications -->
                <a href="{{ route('qualifications.index') }}" class="sidebar-link {{ Request::routeIs('qualifications.*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📋</span>
                    Qualifications
                </a>

                <!-- Subjects -->
                <a href="{{ route('subjects.index') }}" class="sidebar-link {{ Request::routeIs('subjects.*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📚</span>
                    Subjects
                </a>

                <!-- Exam Series -->
                <a href="{{ route('exam-series.index') }}" class="sidebar-link {{ Request::routeIs('exam-series.*') ? 'active' : '' }}">
                    <span class="sidebar-icon">📅</span>
                    Exam Series
                </a>

                <!-- Results -->
                <div>
                    <a href="{{ route('results.index') }}" class="sidebar-link {{ (Request::routeIs('results.*') || Request::routeIs('manual-results.*') || Request::routeIs('uploads.ai_importer') || Request::routeIs('uploads.ai_components')) ? 'active' : '' }}">
                        <span class="sidebar-icon">📊</span>
                        Results
                    </a>
                    <div class="ml-7 mt-0.5 space-y-0.5 {{ (Request::routeIs('results.*') || Request::routeIs('manual-results.*') || Request::routeIs('uploads.ai_importer') || Request::routeIs('uploads.ai_components')) ? '' : 'hidden' }}">
                        <a href="{{ route('manual-results.index') }}" class="sidebar-sublink {{ Request::routeIs('manual-results.*') ? 'active' : '' }}">Upload Marks</a>
                        <a href="{{ route('results.index') }}" class="sidebar-sublink {{ (Request::is('results') || Request::routeIs('results.index') || Request::routeIs('results.subject-results') || Request::routeIs('results.show')) ? 'active' : '' }}">View Records</a>
                        <a href="{{ route('results.broadsheet') }}" class="sidebar-sublink {{ Request::routeIs('results.broadsheet') ? 'active' : '' }}">Broadsheet</a>
                        <a href="{{ route('uploads.ai_importer') }}" class="sidebar-sublink {{ Request::routeIs('uploads.ai_importer') ? 'active' : '' }}">AI Results Importer</a>
                        <a href="{{ route('uploads.ai_components') }}" class="sidebar-sublink {{ Request::routeIs('uploads.ai_components') ? 'active' : '' }}">AI Component Importer</a>
                    </div>
                </div>

                <!-- Analysis Section -->
                <div class="pt-3 pb-1">
                    <span class="px-3 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Analysis</span>
                </div>

                <a href="{{ route('analysis.subject-wise') }}" class="sidebar-link {{ Request::routeIs('analysis.subject-wise') ? 'active' : '' }}">
                    <span class="sidebar-icon">📈</span>
                    Subject Trends
                </a>

                <a href="{{ route('analysis.component-marks') }}" class="sidebar-link {{ Request::routeIs('analysis.component-marks') ? 'active' : '' }}">
                    <span class="sidebar-icon">📄</span>
                    Component Marks
                </a>

                <a href="{{ route('analysis.student-journey') }}" class="sidebar-link {{ Request::routeIs('analysis.student-journey') ? 'active' : '' }}">
                    <span class="sidebar-icon">🛤️</span>
                    Student Journey
                </a>

            <!-- Settings -->
            <div class="pt-3 mt-3 border-t border-slate-100">
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ Request::routeIs('settings.*') ? 'active' : '' }}">
                    <span class="sidebar-icon">⚙️</span>
                    Settings
                </a>
            </div>
        </nav>
    </div>
</div>
