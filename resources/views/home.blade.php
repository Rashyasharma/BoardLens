<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardLens — Choose Your Platform</title>
    <meta name="description" content="BoardLens — Academic insights platform for Cambridge and CBSE examinations.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --cambridge-from: #4f46e5;
            --cambridge-to:   #7c3aed;
            --cbse-from:      #0ea5e9;
            --cbse-to:        #06b6d4;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #0f0f1a;
            color: #fff;
            overflow: hidden;
        }

        /* ── Animated grid bg ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Glowing orbs ── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .35;
            pointer-events: none;
            animation: drift 14s ease-in-out infinite alternate;
            z-index: 0;
        }
        .orb-1 { width: 600px; height: 600px; top: -200px; left: -150px; background: radial-gradient(circle, #4f46e5, transparent 70%); animation-delay: 0s; }
        .orb-2 { width: 500px; height: 500px; bottom: -150px; right: -100px; background: radial-gradient(circle, #0ea5e9, transparent 70%); animation-delay: -7s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.08); }
        }

        /* ── Layout ── */
        .page {
            position: relative;
            z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            gap: 3rem;
        }

        /* ── Logo / Brand ── */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .75rem;
            text-align: center;
        }
        .brand-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 40px rgba(79, 70, 229, .5);
        }
        .brand-icon svg { width: 28px; height: 28px; stroke: #fff; }
        .brand h1 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -.03em;
            background: linear-gradient(135deg, #fff 30%, rgba(255,255,255,.55));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand p {
            font-size: .95rem;
            color: rgba(255,255,255,.45);
            font-weight: 400;
            letter-spacing: .01em;
        }

        /* ── Cards row ── */
        .cards {
            display: flex;
            gap: 1.75rem;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 860px;
        }

        /* ── Individual card ── */
        .card {
            position: relative;
            flex: 1 1 340px;
            max-width: 400px;
            border-radius: 24px;
            overflow: hidden;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
            backdrop-filter: blur(16px);
            transition: transform .3s cubic-bezier(.25,.8,.25,1),
                        box-shadow .3s cubic-bezier(.25,.8,.25,1),
                        border-color .3s;
            animation: slideUp .5s ease both;
        }
        .card:nth-child(1) { animation-delay: .05s; }
        .card:nth-child(2) { animation-delay: .15s; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card:hover {
            transform: translateY(-6px) scale(1.015);
            border-color: rgba(255,255,255,.18);
        }
        .card:active { transform: translateY(-2px) scale(1.008); }

        /* Glow layer on hover */
        .card::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 25px;
            opacity: 0;
            transition: opacity .35s;
            pointer-events: none;
            z-index: 0;
        }
        .card.cambridge::before { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
        .card.cbse::before      { background: linear-gradient(135deg, #0ea5e9, #06b6d4); }
        .card:hover::before     { opacity: .18; }

        /* gradient accent bar at top */
        .card-accent {
            height: 4px;
            width: 100%;
        }
        .cambridge .card-accent { background: linear-gradient(90deg, #4f46e5, #7c3aed, #a855f7); }
        .cbse .card-accent      { background: linear-gradient(90deg, #0ea5e9, #06b6d4, #22d3ee); }

        .card-inner {
            position: relative;
            z-index: 1;
            padding: 2rem 2rem 2.25rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* Icon badge */
        .card-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        .cambridge .card-icon { background: rgba(79, 70, 229, .2); box-shadow: 0 0 24px rgba(79,70,229,.35); }
        .cbse .card-icon      { background: rgba(14, 165, 233, .2); box-shadow: 0 0 24px rgba(14,165,233,.35); }

        .card-body {}
        .card-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }
        .cambridge .card-label { color: #a5b4fc; }
        .cbse .card-label      { color: #7dd3fc; }

        .card-title {
            font-size: clamp(1.25rem, 2vw, 1.55rem);
            font-weight: 800;
            letter-spacing: -.025em;
            line-height: 1.2;
            color: #fff;
            margin-bottom: .6rem;
        }
        .card-desc {
            font-size: .875rem;
            color: rgba(255,255,255,.5);
            line-height: 1.6;
        }

        /* Feature pills */
        .card-pills {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .25rem;
        }
        .pill {
            font-size: .7rem;
            font-weight: 600;
            padding: .3rem .75rem;
            border-radius: 999px;
            letter-spacing: .02em;
        }
        .cambridge .pill { background: rgba(79,70,229,.18); color: #c7d2fe; border: 1px solid rgba(79,70,229,.3); }
        .cbse .pill      { background: rgba(14,165,233,.18); color: #bae6fd; border: 1px solid rgba(14,165,233,.3); }

        /* CTA arrow */
        .card-cta {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .875rem;
            font-weight: 700;
            margin-top: .5rem;
            transition: gap .2s;
        }
        .cambridge .card-cta { color: #a5b4fc; }
        .cbse .card-cta      { color: #7dd3fc; }
        .card:hover .card-cta { gap: .85rem; }
        .card-cta svg { width: 18px; height: 18px; transition: transform .2s; }
        .card:hover .card-cta svg { transform: translateX(3px); }

        /* Coming soon badge */
        .badge-soon {
            position: absolute;
            top: 16px; right: 16px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            color: rgba(255,255,255,.45);
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .25rem .65rem;
            border-radius: 999px;
            z-index: 2;
        }

        /* ── Footer hint ── */
        .footer-hint {
            font-size: .8rem;
            color: rgba(255,255,255,.22);
            text-align: center;
            letter-spacing: .03em;
            animation: fadeIn 1s ease .5s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 600px) {
            html, body { overflow: auto; }
            .page { height: auto; min-height: 100vh; padding: 2rem 1.25rem 3rem; }
            .cards { gap: 1.25rem; }
            .card { flex: 1 1 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="page">

    <!-- Brand -->
    <div class="brand">
        <div class="brand-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h1>BoardLens</h1>
        <p>Choose your academic insights platform</p>
    </div>

    <!-- Cards -->
    <div class="cards">

        <!-- Cambridge Insights -->
        <a href="{{ route('dashboard') }}" class="card cambridge" id="card-cambridge">
            <div class="card-accent"></div>
            <div class="card-inner">
                <div class="card-icon">🎓</div>
                <div class="card-body">
                    <div class="card-label">International Board</div>
                    <div class="card-title">Cambridge<br>Insights</div>
                    <div class="card-desc">
                        Analyse Cambridge IGCSE &amp; AS/A Level results — grade distributions, broadsheets, component marks, and student journeys.
                    </div>
                </div>
                <div class="card-pills">
                    <span class="pill">IGCSE</span>
                    <span class="pill">AS / A Level</span>
                    <span class="pill">Broadsheets</span>
                    <span class="pill">AI Importer</span>
                </div>
                <div class="card-cta">
                    Open Cambridge Insights
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- CBSE Insights -->
        <a href="{{ route('cbse-insights') }}" class="card cbse" id="card-cbse">
            <div class="card-accent"></div>
            <div class="card-inner">
                <div class="card-icon">📘</div>
                <div class="card-body">
                    <div class="card-label">National Board</div>
                    <div class="card-title">CBSE<br>Insights</div>
                    <div class="card-desc">
                        Latest CBSE news, exam dates, result announcements, and academic updates for Class X &amp; XII examinations.
                    </div>
                </div>
                <div class="card-pills">
                    <span class="pill">Class X</span>
                    <span class="pill">Class XII</span>
                    <span class="pill">Board News</span>
                    <span class="pill">Results</span>
                </div>
                <div class="card-cta">
                    Open CBSE Insights
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </div>
        </a>

    </div>

    <p class="footer-hint">BoardLens · Academic Performance Intelligence</p>

</div>



</body>
</html>
