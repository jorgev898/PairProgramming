<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Grades') }} — PairSync</title>
    <meta name="description" content="{{ __('Teacher grade panel on PairSync.') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:           #050511;
            --surface:      #0a0b16;
            --card-bg:      rgba(10, 11, 22, 0.85);
            --border:       #1a1b36;
            --border-hi:    #2a2c56;
            --primary:      #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --blue:         #3b82f6;
            --green:        #10b981;
            --amber:        #f59e0b;
            --red:          #ef4444;
            --text:         #f8fafc;
            --text-dim:     #94a3b8;
            --text-lo:      #475569;
            --radius:       16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Syne', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            height: 100%;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: .15;
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 700px 700px at 85% 5%, rgba(99, 102, 241, 0.10), transparent),
                radial-gradient(ellipse 500px 500px at 5% 95%, rgba(59, 130, 246, 0.07), transparent);
            pointer-events: none;
            z-index: 0;
        }

        /* ── NAV ── */
        nav {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 48px;
            background: rgba(5, 5, 17, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .nav-left { display: flex; align-items: center; gap: 16px; }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .logo-name { font-weight: 800; font-size: 1.4rem; color: #fff; letter-spacing: -.02em; }
        .logo-name span { color: var(--primary); }

        .nav-divider { width: 1px; height: 20px; background: rgba(255,255,255,0.1); }

        .nav-breadcrumb {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem;
        }
        .nav-breadcrumb a {
            color: var(--text-dim); text-decoration: none; font-weight: 600;
            transition: color 0.2s;
        }
        .nav-breadcrumb a:hover { color: #fff; }
        .nav-breadcrumb .separator { color: var(--text-lo); }
        .nav-breadcrumb .current { color: var(--primary); font-weight: 700; }

        .nav-links { display: flex; align-items: center; gap: 16px; }
        .nav-user { color: var(--text-dim); font-size: 0.9rem; font-weight: 600; }

        .btn-nav-outline {
            display: inline-flex; align-items: center;
            padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #fff; text-decoration: none;
            font-weight: 600; font-size: 0.9rem;
            transition: all 0.2s;
            background: rgba(255,255,255,0.02);
        }
        .btn-nav-outline:hover {
            border-color: rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
            transform: translateY(-1px);
        }

        /* ── HEADER ── */
        .page-header {
            position: relative; z-index: 1;
            text-align: center;
            padding: 56px 24px 24px;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 100px;
            color: var(--primary);
            font-size: 0.85rem; font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 0.05em; text-transform: uppercase;
        }

        .page-header h1 {
            font-size: clamp(2rem, 4.5vw, 3rem);
            font-weight: 800; line-height: 1.1;
            letter-spacing: -.03em; margin-bottom: 14px;
        }

        .page-header h1 .gradient-text {
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .page-header h1 .accent-text {
            background: linear-gradient(135deg, var(--primary), var(--blue));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .page-header p {
            font-size: 1.05rem; color: var(--text-dim);
            line-height: 1.6; max-width: 560px;
            margin: 0 auto; font-weight: 500;
        }

        /* ── STATS BAR ── */
        .stats-bar {
            position: relative; z-index: 1;
            max-width: 1100px;
            margin: 32px auto 0; padding: 0 24px;
        }

        .stats-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 24px;
            text-align: center;
            transition: border-color 0.3s;
        }

        .stat-card:hover { border-color: rgba(255,255,255,0.12); }

        .stat-icon { font-size: 1.5rem; margin-bottom: 10px; }

        .stat-value {
            font-size: 2rem; font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--text-dim));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 0.8rem; color: var(--text-dim);
            font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.06em; margin-top: 4px;
        }

        /* ── COURSE GRID ── */
        .courses-grid {
            position: relative; z-index: 1;
            max-width: 1100px;
            margin: 40px auto 0;
            padding: 0 24px 80px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 24px;
        }

        /* ── COURSE CARD ── */
        .course-card {
            background: var(--card-bg);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                        border-color 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            opacity: 0;
            transform: translateY(30px);
        }

        .course-card.visible {
            opacity: 1; transform: translateY(0);
            transition: opacity 0.5s ease, transform 0.5s ease,
                        border-color 0.3s, box-shadow 0.3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.25);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        .course-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--card-accent, var(--primary));
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .course-card:hover::before { opacity: 1; }

        .card-top {
            display: flex; align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-icon-wrapper {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            transition: transform 0.3s;
        }

        .course-card:hover .card-icon-wrapper {
            transform: scale(1.08) rotate(-3deg);
        }

        .card-level {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem; font-weight: 600;
            padding: 5px 12px; border-radius: 100px;
            letter-spacing: 0.06em; text-transform: uppercase;
        }

        .level-beginner {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .level-intermediate {
            background: rgba(59, 130, 246, 0.12);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .level-advanced {
            background: rgba(250, 204, 21, 0.12);
            color: #facc15;
            border: 1px solid rgba(250, 204, 21, 0.2);
        }

        .card-title {
            font-size: 1.15rem; font-weight: 700;
            color: #fff; margin-bottom: 8px;
            letter-spacing: -.02em;
        }

        .card-description {
            font-size: 0.88rem; color: var(--text-dim);
            line-height: 1.6; margin-bottom: 20px;
            flex: 1; font-weight: 500;
        }

        /* ── PROGRESS IN CARD ── */
        .card-progress {
            margin-bottom: 20px;
        }

        .progress-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 8px;
        }

        .progress-label {
            font-size: 0.78rem; font-weight: 600; color: var(--text-dim);
        }

        .progress-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem; font-weight: 700; color: var(--primary);
        }

        .progress-track {
            height: 6px;
            background: rgba(255,255,255,0.06);
            border-radius: 100px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--blue));
            border-radius: 100px;
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }

        /* ── CARD STATS ── */
        .card-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .card-stat {
            text-align: center;
            padding: 12px 8px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 10px;
        }

        .card-stat-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.1rem; font-weight: 700; color: #fff;
        }

        .card-stat-label {
            font-size: 0.68rem; color: var(--text-lo);
            font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.04em; margin-top: 2px;
        }

        /* ── CARD BUTTON ── */
        .btn-grade {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; width: 100%;
            padding: 14px 20px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            background: rgba(255,255,255,0.03);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-weight: 700; font-size: 0.9rem;
            text-decoration: none; cursor: pointer;
            transition: all 0.25s;
        }

        .btn-grade:hover {
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .btn-grade .arrow {
            transition: transform 0.25s;
        }

        .btn-grade:hover .arrow {
            transform: translateX(4px);
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            position: relative; z-index: 1;
            text-align: center;
            padding: 80px 24px;
            max-width: 500px;
            margin: 0 auto;
        }

        .empty-icon { font-size: 3rem; margin-bottom: 16px; opacity: 0.5; }

        .empty-state h2 {
            font-size: 1.3rem; font-weight: 700;
            margin-bottom: 8px; color: var(--text-dim);
        }

        .empty-state p {
            font-size: 0.9rem; color: var(--text-lo);
            line-height: 1.6; margin-bottom: 24px;
        }

        .btn-create {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border: none; border-radius: 10px;
            color: #fff; font-family: 'Syne', sans-serif;
            font-weight: 700; font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.25s;
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px var(--primary-glow);
        }

        /* ── FOOTER ── */
        footer {
            position: relative; z-index: 10;
            text-align: center; padding: 32px;
            color: var(--text-lo);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: auto;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            nav { padding: 20px 24px; }
            .stats-wrapper { grid-template-columns: 1fr; }
            .courses-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            nav { padding: 16px; flex-wrap: wrap; gap: 12px; }
            .card-stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        }
    </style>
</head>
<body>

{{-- ── NAVIGATION ── --}}
<nav>
    <div class="nav-left">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon">⌨</div>
            <span class="logo-name">Pair<span>Sync</span></span>
        </a>
        <div class="nav-divider"></div>
        <div class="nav-breadcrumb">
            <a href="{{ route('challenges.index') }}">{{ __('Challenges') }}</a>
            <span class="separator">›</span>
            <span class="current">{{ __('Grades') }}</span>
        </div>
    </div>

    <div class="nav-links">
        @auth
            <span class="nav-user">{{ auth()->user()->name }}</span>
            <a href="{{ route('challenges.index') }}" class="btn-nav-outline">← {{ __('Challenges') }}</a>
        @endauth
    </div>
</nav>

{{-- ── HEADER ── --}}
<section class="page-header">
    <div class="page-badge">📝 {{ __('Teacher Dashboard') }}</div>
    <h1>
        <span class="gradient-text">{{ __('Grade your') }}</span>
        <span class="accent-text">{{ __('challenges') }}</span>
    </h1>
    <p>{{ __('Review student performance, assign grades, and sync with Moodle.') }}</p>
</section>

{{-- ── STATS ── --}}
@if($courses->count() > 0)
<section class="stats-bar">
    <div class="stats-wrapper">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-value">{{ $courses->count() }}</div>
            <div class="stat-label">{{ __('Courses') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value">{{ $courses->max('student_count') ?? 0 }}</div>
            <div class="stat-label">{{ __('Students') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value">{{ $courses->sum('graded_count') }}</div>
            <div class="stat-label">{{ __('Grades') }}</div>
        </div>
    </div>
</section>

{{-- ── COURSES GRID ── --}}
<section class="courses-grid" id="coursesGrid">
    @foreach ($courses as $course)
        <article class="course-card" style="--card-accent: {{ $course->color }};" data-index="{{ $loop->index }}">
            <div class="card-top">
                <div class="card-icon-wrapper" style="border-color: {{ $course->color }}20;">
                    {{ $course->icon }}
                </div>
                <span class="card-level level-{{ $course->level }}">{{ __($course->level) }}</span>
            </div>

            <h3 class="card-title">{{ $course->title }}</h3>
            <p class="card-description">{{ Str::limit($course->description, 100) }}</p>

            <div class="card-progress">
                <div class="progress-header">
                    <span class="progress-label">{{ __('Graded') }}</span>
                    <span class="progress-value">{{ $course->graded_percent }}%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: {{ $course->graded_percent }}%;"></div>
                </div>
            </div>

            <div class="card-stats">
                <div class="card-stat">
                    <div class="card-stat-value">{{ $course->lessons_count }}</div>
                    <div class="card-stat-label">{{ __('Lessons') }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-value">{{ $course->student_count }}</div>
                    <div class="card-stat-label">{{ __('Students') }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-value">{{ $course->synced_count }}</div>
                    <div class="card-stat-label">{{ __('Moodle') }} ✓</div>
                </div>
            </div>

            <a href="{{ route('grades.show', $course->id) }}" class="btn-grade" id="btn-grade-{{ $course->id }}">
                📝 {{ __('Grade') }}
                <span class="arrow">→</span>
            </a>
        </article>
    @endforeach
</section>

@else
    <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h2>{{ __("You don't have any courses yet") }}</h2>
        <p>{{ __('Create a course to start grading your students.') }}</p>
        <a href="{{ route('courses.create') }}" class="btn-create">
            ✨ {{ __('Create Course') }}
        </a>
    </div>
@endif

<footer>
    {{ __('PairSync · Designed for developers, built for teams.') }}
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.course-card');
        cards.forEach((card, i) => {
            setTimeout(() => card.classList.add('visible'), 150 + i * 100);
        });
    });
</script>

</body>
</html>
