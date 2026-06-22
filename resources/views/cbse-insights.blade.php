<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBSE Insights — BoardLens</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
        h1, h2, h3, .font-display {
            font-family: 'Outfit', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .animated-bg {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #0f172a, #1e1b4b);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
        }
    </style>
</head>
<body class="antialiased text-slate-800">
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass-nav border-b border-white/40 shadow-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-display font-bold text-xl text-slate-900 tracking-tight">BoardLens</span>
                        <span class="ml-2 text-[10px] font-bold tracking-widest text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full uppercase border border-indigo-100">CBSE</span>
                    </div>
                </a>
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors px-4 py-2 rounded-xl hover:bg-indigo-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden animated-bg">
        <div class="blob bg-indigo-500 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bg-cyan-500 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-panel border border-white/20 mb-8 transform hover:scale-105 transition-transform cursor-default">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <span class="text-xs font-bold tracking-widest uppercase text-cyan-50">Live CBSE Updates</span>
            </div>
            
            <h1 class="text-5xl lg:text-7xl font-display font-extrabold text-white mb-6 leading-tight tracking-tight">
                Empower your <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-indigo-400">Academic Journey</span>
            </h1>
            
            <p class="mt-4 max-w-2xl mx-auto text-lg text-slate-300 font-medium">
                Deep dive into the latest CBSE announcements, result statistics, syllabus updates, and performance analytics for Class X & XII.
            </p>

            <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="glass-panel bg-white/10 rounded-2xl p-6 transform hover:-translate-y-2 transition-all duration-300 border border-white/20 shadow-xl">
                    <div class="text-3xl font-display font-black text-white mb-1">3.5Cr+</div>
                    <div class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Students Enrolled</div>
                </div>
                <div class="glass-panel bg-white/10 rounded-2xl p-6 transform hover:-translate-y-2 transition-all duration-300 border border-white/20 shadow-xl">
                    <div class="text-3xl font-display font-black text-cyan-400 mb-1">X & XII</div>
                    <div class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Classes Covered</div>
                </div>
                <div class="glass-panel bg-white/10 rounded-2xl p-6 transform hover:-translate-y-2 transition-all duration-300 border border-white/20 shadow-xl">
                    <div class="text-3xl font-display font-black text-white mb-1">Daily</div>
                    <div class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Live Updates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-20 pb-24">
        
        <!-- Alert Card -->
        <div class="bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl shadow-xl p-1 mb-16 transform hover:-translate-y-1 transition-transform duration-300">
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-bold text-white mb-1">CBSE Board Result 2026 — Expected in May</h3>
                        <p class="text-indigo-100 text-sm">Class X & XII results are expected to be declared in the third week of May 2026. Track live at cbseresults.nic.in</p>
                    </div>
                </div>
                <a href="https://cbseresults.nic.in" target="_blank" class="shrink-0 px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl shadow-md hover:bg-indigo-50 hover:scale-105 transition-all duration-200">
                    Check Results →
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Left Column: Featured & Quick Links -->
            <div class="lg:col-span-2 space-y-10">
                
                <!-- Section Title -->
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-display font-extrabold text-slate-800">Featured Story</h2>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <!-- Featured Card -->
                <article class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                    <div class="grid md:grid-cols-5 h-full">
                        <div class="md:col-span-2 relative overflow-hidden bg-slate-900 min-h-[250px]">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-cyan-500/20 mix-blend-overlay z-10"></div>
                            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?q=80&w=1000&auto=format&fit=crop" alt="Students" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 opacity-80" />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent z-10"></div>
                        </div>
                        <div class="md:col-span-3 p-8 flex flex-col justify-center">
                            <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold uppercase tracking-wider rounded-full mb-4 w-fit border border-indigo-100">Results Analysis</span>
                            <h3 class="text-2xl font-display font-bold text-slate-900 mb-3 leading-tight group-hover:text-indigo-600 transition-colors">CBSE Class XII Pass Percentage Hits Record High in 2025</h3>
                            <p class="text-slate-600 text-sm leading-relaxed mb-6">The Central Board of Secondary Education announced that the Class XII pass percentage for the academic year 2024-25 reached 87.98%, the highest in a decade. Girls outperformed boys for the 8th consecutive year with a 91.5% pass rate.</p>
                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-4 text-xs font-semibold text-slate-500">
                                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg> June 2025</span>
                                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg> Board Announcement</span>
                                </div>
                                <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group/link">
                                    Read Full <svg class="w-4 h-4 transform group-hover/link:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Section Title -->
                <div class="flex items-center gap-4 pt-4">
                    <h2 class="text-2xl font-display font-extrabold text-slate-800">Quick Access</h2>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="https://cbseresults.nic.in" target="_blank" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-sky-50 text-sky-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Result Portal</h4>
                        <p class="text-xs text-slate-500">cbseresults.nic.in</p>
                    </a>
                    <a href="https://cbseacademic.nic.in" target="_blank" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-teal-50 text-teal-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Syllabus 2026</h4>
                        <p class="text-xs text-slate-500">Curriculum updates</p>
                    </a>
                    <a href="https://cbse.gov.in" target="_blank" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Official CBSE</h4>
                        <p class="text-xs text-slate-500">cbse.gov.in</p>
                    </a>
                    <a href="https://cbse.gov.in/newsite/pages/date-sheet.html" target="_blank" class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col items-center text-center hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Date Sheet</h4>
                        <p class="text-xs text-slate-500">Exam schedule</p>
                    </a>
                </div>

            </div>

            <!-- Right Column: Latest Updates -->
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-display font-extrabold text-slate-800">Latest Updates</h2>
                    <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div class="space-y-4">
                    <!-- Update Item -->
                    <a href="#" class="block bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:border-sky-200 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-sky-400 transform origin-bottom scale-y-0 group-hover:scale-y-100 transition-transform duration-300"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-sky-500 uppercase tracking-wider mb-1 block">Date Sheet</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-sky-600 transition-colors">CBSE Board Exams 2026 Timetable Released</h4>
                                <p class="text-xs text-slate-500 line-clamp-2">CBSE has released the official date sheet for Class X and XII board examinations. Exams scheduled to begin in February 2026.</p>
                            </div>
                        </div>
                    </a>

                    <!-- Update Item -->
                    <a href="#" class="block bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:border-teal-200 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-teal-400 transform origin-bottom scale-y-0 group-hover:scale-y-100 transition-transform duration-300"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-500 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-teal-500 uppercase tracking-wider mb-1 block">Syllabus</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-teal-600 transition-colors">Revised CBSE Syllabus for 2025–26 Includes New Topics</h4>
                                <p class="text-xs text-slate-500 line-clamp-2">Science and Mathematics syllabi updated with modern applications and AI-related concepts for Classes IX–XII.</p>
                            </div>
                        </div>
                    </a>

                    <!-- Update Item -->
                    <a href="#" class="block bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:border-amber-200 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-amber-400 transform origin-bottom scale-y-0 group-hover:scale-y-100 transition-transform duration-300"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider mb-1 block">Toppers</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-amber-600 transition-colors">Class XII Top Scorers Achieve Perfect 500/500</h4>
                                <p class="text-xs text-slate-500 line-clamp-2">Multiple students across India scored full marks in all five subjects, with the highest number of perfect scorers in PCM stream.</p>
                            </div>
                        </div>
                    </a>

                    <!-- Update Item -->
                    <a href="#" class="block bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-lg hover:border-violet-200 transition-all duration-300 group relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-violet-400 transform origin-bottom scale-y-0 group-hover:scale-y-100 transition-transform duration-300"></div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-violet-50 text-violet-500 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-violet-500 uppercase tracking-wider mb-1 block">Technology</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-violet-600 transition-colors">CBSE Introduces AI as Elective Subject for Class XI</h4>
                                <p class="text-xs text-slate-500 line-clamp-2">The board expands its Artificial Intelligence elective to over 2,000 schools, aiming to build computational thinking from an early stage.</p>
                            </div>
                        </div>
                    </a>

                </div>
                
                <a href="#" class="block text-center text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-3 rounded-xl transition-colors mt-6">
                    View All Updates
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-slate-500 text-sm font-medium">&copy; {{ date('Y') }} BoardLens. CBSE Insights Portal.</p>
        </div>
    </footer>
</body>
</html>
