<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBSE Insights — BoardLens</title>
    <meta name="description" content="Latest CBSE news, exam updates, result announcements and academic insights for Class X and XII.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sky: #0ea5e9;
            --cyan: #06b6d4;
            --navy: #0c1a2e;
            --ink: #0f172a;
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }

        /* ── TOP NAV ── */
        nav {
            position: sticky; top: 0; z-index: 50;
            background: rgba(255,255,255,.88);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center;
            justify-content: space-between;
            padding: .875rem 2rem;
        }
        .nav-brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none; color: inherit;
        }
        .nav-logo {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo svg { width: 18px; height: 18px; stroke: #fff; }
        .nav-brand-text { font-size: .95rem; font-weight: 800; color: #0f172a; letter-spacing: -.02em; }
        .nav-badge { font-size: .65rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; background: linear-gradient(135deg, #0ea5e9, #06b6d4); color: #fff; padding: .2rem .6rem; border-radius: 999px; }
        .nav-back {
            display: flex; align-items: center; gap: .4rem;
            font-size: .8rem; font-weight: 600; color: #64748b;
            text-decoration: none;
            padding: .45rem .9rem; border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all .2s;
        }
        .nav-back:hover { color: #0ea5e9; border-color: #bae6fd; background: #f0f9ff; }
        .nav-back svg { width: 14px; height: 14px; }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, #0c2744 50%, #0e3a5c 100%);
            padding: 5rem 2rem 4rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(14,165,233,.15) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }
        .hero-orb {
            position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none;
        }
        .hero-orb-1 { width: 400px; height: 400px; top: -100px; right: -80px; background: rgba(14,165,233,.2); }
        .hero-orb-2 { width: 300px; height: 300px; bottom: -80px; left: -60px; background: rgba(6,182,212,.15); }

        .hero-inner {
            position: relative; z-index: 1;
            max-width: 760px; margin: 0 auto; text-align: center;
        }
        .hero-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(14,165,233,.15); border: 1px solid rgba(14,165,233,.3);
            color: #7dd3fc; font-size: .7rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            padding: .35rem .9rem; border-radius: 999px;
            margin-bottom: 1.5rem;
            animation: fadeUp .5s ease both;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 900; color: #fff;
            letter-spacing: -.04em; line-height: 1.1;
            margin-bottom: 1.25rem;
            animation: fadeUp .5s ease .1s both;
        }
        .hero h1 span { color: #38bdf8; }
        .hero p {
            font-size: 1.05rem; color: #94a3b8;
            line-height: 1.7; max-width: 540px; margin: 0 auto 2.5rem;
            animation: fadeUp .5s ease .18s both;
        }
        .hero-stats {
            display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;
            animation: fadeUp .5s ease .25s both;
        }
        .hero-stat { text-align: center; }
        .hero-stat-val { font-size: 1.6rem; font-weight: 900; color: #38bdf8; display: block; }
        .hero-stat-label { font-size: .7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── CONTENT AREA ── */
        .content { max-width: 1100px; margin: 0 auto; padding: 3rem 2rem 5rem; }

        /* ── SECTION HEADER ── */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .section-title {
            font-size: .7rem; font-weight: 800; letter-spacing: .12em;
            text-transform: uppercase; color: #0ea5e9;
            display: flex; align-items: center; gap: .5rem;
        }
        .section-title::before {
            content: '';
            display: block; width: 20px; height: 2px;
            background: linear-gradient(90deg, #0ea5e9, #06b6d4);
            border-radius: 1px;
        }
        .section-divider { height: 1px; background: #e2e8f0; margin-bottom: 1.75rem; }

        /* ── FEATURED NEWS CARD ── */
        .featured-card {
            background: #fff; border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden; display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0; margin-bottom: 3rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
            transition: box-shadow .2s, transform .2s;
        }
        .featured-card:hover { box-shadow: 0 8px 40px rgba(0,0,0,.1); transform: translateY(-2px); }
        .featured-img {
            background: linear-gradient(135deg, #0c2744, #0e3a5c);
            min-height: 260px;
            display: flex; align-items: center; justify-content: center;
            font-size: 5rem; position: relative; overflow: hidden;
        }
        .featured-img::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 40%, rgba(14,165,233,.2), transparent 60%);
        }
        .featured-body { padding: 2.25rem 2rem; display: flex; flex-direction: column; justify-content: center; gap: 1rem; }
        .featured-tag {
            font-size: .65rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
            background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd;
            padding: .25rem .7rem; border-radius: 999px; width: fit-content;
        }
        .featured-body h2 { font-size: 1.4rem; font-weight: 800; line-height: 1.3; color: #0f172a; letter-spacing: -.025em; }
        .featured-body p { font-size: .875rem; color: #64748b; line-height: 1.6; }
        .featured-meta { display: flex; align-items: center; gap: 1rem; font-size: .75rem; color: #94a3b8; font-weight: 500; }
        .featured-cta {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .8rem; font-weight: 700; color: #0ea5e9;
            text-decoration: none;
            transition: gap .2s;
        }
        .featured-cta:hover { gap: .7rem; }

        /* ── NEWS GRID ── */
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 3rem; }
        @media (max-width: 900px) { .news-grid { grid-template-columns: repeat(2, 1fr); } .featured-card { grid-template-columns: 1fr; } }
        @media (max-width: 600px) { .news-grid { grid-template-columns: 1fr; } }

        .news-card {
            background: #fff; border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,.04);
            transition: box-shadow .2s, transform .2s, border-color .2s;
            display: flex; flex-direction: column;
        }
        .news-card:hover { box-shadow: 0 6px 28px rgba(0,0,0,.09); transform: translateY(-3px); border-color: #bae6fd; }

        .news-card-top {
            height: 120px;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.75rem;
        }
        .bg-sky   { background: linear-gradient(135deg, #f0f9ff, #e0f2fe); }
        .bg-teal  { background: linear-gradient(135deg, #f0fdfa, #ccfbf1); }
        .bg-amber { background: linear-gradient(135deg, #fffbeb, #fef3c7); }
        .bg-rose  { background: linear-gradient(135deg, #fff1f2, #ffe4e6); }
        .bg-violet{ background: linear-gradient(135deg, #f5f3ff, #ede9fe); }
        .bg-emerald{background: linear-gradient(135deg, #f0fdf4, #dcfce7); }

        .news-card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; gap: .6rem; }
        .news-tag {
            font-size: .6rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
            padding: .2rem .6rem; border-radius: 999px; width: fit-content;
        }
        .tag-sky    { background: #f0f9ff; color: #0ea5e9; border: 1px solid #bae6fd; }
        .tag-teal   { background: #f0fdfa; color: #0d9488; border: 1px solid #99f6e4; }
        .tag-amber  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .tag-rose   { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .tag-violet { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
        .tag-emerald{ background: #f0fdf4; color: #059669; border: 1px solid #a7f3d0; }

        .news-card-body h3 { font-size: .95rem; font-weight: 700; color: #0f172a; line-height: 1.4; letter-spacing: -.015em; }
        .news-card-body p  { font-size: .8rem; color: #64748b; line-height: 1.55; flex: 1; }
        .news-footer { display: flex; align-items: center; justify-content: space-between; padding-top: .75rem; border-top: 1px solid #f1f5f9; margin-top: auto; }
        .news-date { font-size: .7rem; color: #94a3b8; font-weight: 500; }
        .read-more { font-size: .7rem; font-weight: 700; color: #0ea5e9; }

        /* ── ALERT BANNER ── */
        .alert-banner {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 16px; padding: 1.5rem 2rem;
            display: flex; align-items: center; gap: 1.5rem;
            color: #fff; margin-bottom: 3rem;
            box-shadow: 0 4px 20px rgba(14,165,233,.3);
        }
        .alert-icon { font-size: 2rem; flex-shrink: 0; }
        .alert-title { font-size: 1rem; font-weight: 800; margin-bottom: .25rem; }
        .alert-sub { font-size: .825rem; opacity: .85; }
        .alert-btn {
            margin-left: auto; flex-shrink: 0;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3);
            color: #fff; font-size: .8rem; font-weight: 700;
            padding: .6rem 1.2rem; border-radius: 10px;
            text-decoration: none; transition: background .2s;
            white-space: nowrap;
        }
        .alert-btn:hover { background: rgba(255,255,255,.28); }

        /* ── QUICK LINKS ── */
        .quick-links { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 3rem; }
        @media (max-width: 700px) { .quick-links { grid-template-columns: repeat(2, 1fr); } }
        .quick-link {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; padding: 1.25rem;
            text-align: center; text-decoration: none; color: inherit;
            transition: all .2s;
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
        }
        .quick-link:hover { border-color: #bae6fd; background: #f0f9ff; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(14,165,233,.12); }
        .quick-link-icon { font-size: 1.75rem; }
        .quick-link-label { font-size: .775rem; font-weight: 700; color: #334155; }
        .quick-link-sub { font-size: .65rem; color: #94a3b8; }
    </style>
</head>
<body>

<!-- Nav -->
<nav>
    <a href="{{ route('home') }}" class="nav-brand">
        <div class="nav-logo">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <span class="nav-brand-text">BoardLens</span>
        <span class="nav-badge">CBSE</span>
    </a>
    <a href="{{ route('home') }}" class="nav-back">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Home
    </a>
</nav>

<!-- Hero -->
<div class="hero">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <div class="hero-inner">
        <div class="hero-pill">📘 CBSE Insights</div>
        <h1>Stay Ahead with<br><span>CBSE News &amp; Updates</span></h1>
        <p>Latest announcements, result dates, syllabus changes, and academic insights for CBSE Class X &amp; XII examinations.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-val">3.5Cr+</span>
                <span class="hero-stat-label">Students Enrolled</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">Class X &amp; XII</span>
                <span class="hero-stat-label">Coverage</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">Daily</span>
                <span class="hero-stat-label">Updates</span>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content">

    <!-- Alert Banner -->
    <div class="alert-banner">
        <span class="alert-icon">🔔</span>
        <div>
            <div class="alert-title">CBSE Board Result 2026 — Expected in May</div>
            <div class="alert-sub">Class X &amp; XII results are expected to be declared in the third week of May 2026. Track live at cbseresults.nic.in</div>
        </div>
        <a href="https://cbseresults.nic.in" target="_blank" rel="noopener" class="alert-btn">Check Results →</a>
    </div>

    <!-- Featured Story -->
    <div class="section-header">
        <span class="section-title">Featured Story</span>
    </div>
    <div class="section-divider"></div>
    <div class="featured-card">
        <div class="featured-img">📊</div>
        <div class="featured-body">
            <span class="featured-tag">Results Analysis</span>
            <h2>CBSE Class XII Pass Percentage Hits Record High in 2025</h2>
            <p>The Central Board of Secondary Education announced that the Class XII pass percentage for the academic year 2024-25 reached 87.98%, the highest in a decade. Girls outperformed boys for the 8th consecutive year with a 91.5% pass rate.</p>
            <div class="featured-meta">
                <span>📅 June 2025</span>
                <span>•</span>
                <span>📌 Board Announcement</span>
            </div>
            <a href="#" class="featured-cta">
                Read Full Story
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:14px;height:14px">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="section-header">
        <span class="section-title">Quick Access</span>
    </div>
    <div class="section-divider"></div>
    <div class="quick-links">
        <a href="https://cbseresults.nic.in" target="_blank" class="quick-link">
            <span class="quick-link-icon">📋</span>
            <span class="quick-link-label">Result Portal</span>
            <span class="quick-link-sub">cbseresults.nic.in</span>
        </a>
        <a href="https://cbseacademic.nic.in" target="_blank" class="quick-link">
            <span class="quick-link-icon">📚</span>
            <span class="quick-link-label">Syllabus 2026</span>
            <span class="quick-link-sub">Curriculum updates</span>
        </a>
        <a href="https://cbse.gov.in" target="_blank" class="quick-link">
            <span class="quick-link-icon">🏛️</span>
            <span class="quick-link-label">Official CBSE</span>
            <span class="quick-link-sub">cbse.gov.in</span>
        </a>
        <a href="https://cbse.gov.in/newsite/pages/date-sheet.html" target="_blank" class="quick-link">
            <span class="quick-link-icon">📅</span>
            <span class="quick-link-label">Date Sheet 2026</span>
            <span class="quick-link-sub">Exam schedule</span>
        </a>
    </div>

    <!-- Latest News Grid -->
    <div class="section-header">
        <span class="section-title">Latest Updates</span>
    </div>
    <div class="section-divider"></div>
    <div class="news-grid">

        <div class="news-card">
            <div class="news-card-top bg-sky">🗓️</div>
            <div class="news-card-body">
                <span class="news-tag tag-sky">Date Sheet</span>
                <h3>CBSE Board Exams 2026 Timetable Released</h3>
                <p>CBSE has released the official date sheet for Class X and XII board examinations. Exams scheduled to begin in February 2026.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Dec 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

        <div class="news-card">
            <div class="news-card-top bg-teal">🧪</div>
            <div class="news-card-body">
                <span class="news-tag tag-teal">Syllabus</span>
                <h3>Revised CBSE Syllabus for 2025–26 Includes New Topics</h3>
                <p>Science and Mathematics syllabi updated with modern applications and AI-related concepts for Classes IX–XII.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Aug 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

        <div class="news-card">
            <div class="news-card-top bg-amber">🏆</div>
            <div class="news-card-body">
                <span class="news-tag tag-amber">Toppers</span>
                <h3>Class XII Top Scorers Achieve Perfect 500/500</h3>
                <p>Multiple students across India scored full marks in all five subjects, with the highest number of perfect scorers in PCM stream.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Jun 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

        <div class="news-card">
            <div class="news-card-top bg-violet">🤖</div>
            <div class="news-card-body">
                <span class="news-tag tag-violet">Technology</span>
                <h3>CBSE Introduces AI as Elective Subject for Class XI</h3>
                <p>The board expands its Artificial Intelligence elective to over 2,000 schools, aiming to build computational thinking from an early stage.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Apr 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

        <div class="news-card">
            <div class="news-card-top bg-rose">📝</div>
            <div class="news-card-body">
                <span class="news-tag tag-rose">Examination</span>
                <h3>Competency-Based Questions to Form 50% of Paper</h3>
                <p>CBSE has mandated that half of all board exam questions will be competency-based, testing application and critical thinking skills.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Mar 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

        <div class="news-card">
            <div class="news-card-top bg-emerald">📈</div>
            <div class="news-card-body">
                <span class="news-tag tag-emerald">Analytics</span>
                <h3>Regional Performance Report: South India Leads in Pass %</h3>
                <p>Southern states lead national CBSE performance metrics, with Kerala, Tamil Nadu, and Karnataka topping average scores.</p>
                <div class="news-footer">
                    <span class="news-date">📅 Jun 2025</span>
                    <span class="read-more">Read →</span>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
