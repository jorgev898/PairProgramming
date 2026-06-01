<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Challenges') }} — PairSync</title>
    <meta name="description" content="Browse mobile development challenges and courses on PairSync.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:           #050511;
            --surface:      #0a0b16;
            --card-bg:      rgba(10, 11, 22, 0.7);
            --border:       #1a1b36;
            --border-hi:    #2a2c56;
            --primary:      #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --blue:         #3b82f6;
            --green:        #10b981;
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

        /* ── GRID BACKGROUND ── */
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
            will-change: transform;
        }

        /* ── AMBIENT GLOWS (static radial gradients — no blur, no animation) ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 700px 700px at 85% 5%, rgba(99, 102, 241, 0.10), transparent),
                radial-gradient(ellipse 500px 500px at 5% 95%, rgba(59, 130, 246, 0.07), transparent),
                radial-gradient(ellipse 400px 400px at 40% 50%, rgba(16, 185, 129, 0.05), transparent);
            pointer-events: none;
            z-index: 0;
        }

        /* Ambient glows replaced by body::after radial gradients for performance */

        /* ── NAVIGATION ── */
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

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .logo-name {
            font-weight: 800;
            font-size: 1.4rem;
            color: #fff;
            letter-spacing: -.02em;
        }

        .logo-name span { color: var(--primary); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link {
            color: var(--text-dim);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s;
        }

        .nav-link:hover { color: #fff; }

        .lang-switch {
            display: flex;
            gap: 8px;
            border-right: 1px solid var(--border);
            padding-right: 24px;
        }

        .lang-link {
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .nav-user {
            color: var(--text-dim);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-nav-outline {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: rgba(255,255,255,0.02);
        }

        .btn-nav-outline:hover {
            border-color: rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
            transform: translateY(-1px);
        }

        /* ── PAGE HEADER ── */
        .page-header {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 64px 24px 16px;
            max-width: 800px;
            margin: 0 auto;
        }

        .page-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 100px;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .page-header h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.03em;
            margin-bottom: 16px;
        }

        .page-header h1 .gradient-text {
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header h1 .accent-text {
            background: linear-gradient(135deg, var(--primary), var(--blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            font-size: clamp(1rem, 1.8vw, 1.15rem);
            color: var(--text-dim);
            line-height: 1.6;
            max-width: 560px;
            margin: 0 auto;
            font-weight: 500;
        }

        /* ── PROGRESS BAR ── */
        .progress-section {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 32px auto 0;
            padding: 0 24px;
        }

        .progress-bar-wrapper {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
            padding: 20px 28px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .progress-info {
            flex-shrink: 0;
        }

        .progress-info .label {
            font-size: 0.8rem;
            color: var(--text-dim);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }

        .progress-info .value {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
        }

        .progress-info .value em {
            font-style: normal;
            color: var(--primary);
        }

        .progress-track {
            flex: 1;
            height: 8px;
            background: rgba(255,255,255,0.06);
            border-radius: 100px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary), var(--blue));
            border-radius: 100px;
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 12px var(--primary-glow);
        }

        /* ── CHALLENGES GRID ── */
        .challenges-grid {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 40px auto 0;
            padding: 0 24px 80px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        /* ── CHALLENGE CARD ── */
        .challenge-card {
            background: rgba(10, 11, 22, 0.85);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 32px 28px 28px;
            position: relative;
            overflow: hidden;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
                        border-color 0.3s,
                        box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            will-change: transform, opacity;
        }

        .challenge-card:hover {
            transform: translateY(-6px);
            border-color: rgba(99, 102, 241, 0.25);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
        }

        /* Top accent line */
        .challenge-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--card-accent, var(--primary));
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .challenge-card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .challenge-card:hover .card-icon {
            transform: scale(1.08) rotate(-3deg);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        .card-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-lo);
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.06em;
        }

        .challenge-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
            letter-spacing: -.02em;
            line-height: 1.3;
        }

        .challenge-card .description {
            font-size: 0.88rem;
            color: var(--text-dim);
            line-height: 1.65;
            margin-bottom: 20px;
            flex: 1;
            font-weight: 500;
        }

        /* ── CARD META ── */
        .card-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .level-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 100px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
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

        .lessons-count {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: var(--text-lo);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ── CARD BUTTON ── */
        .btn-challenge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px 20px;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            background: rgba(255,255,255,0.03);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s;
            letter-spacing: 0.01em;
        }

        .btn-challenge:hover {
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--primary-glow);
        }

        .btn-challenge .arrow {
            transition: transform 0.25s;
        }

        .btn-challenge:hover .arrow {
            transform: translateX(4px);
        }

        /* ── FOOTER ── */
        footer {
            position: relative;
            z-index: 10;
            text-align: center;
            padding: 32px;
            color: var(--text-lo);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            margin-top: auto;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .challenges-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            nav { padding: 20px 24px; }
        }

        @media (max-width: 600px) {
            .challenges-grid {
                grid-template-columns: 1fr;
            }
            nav { padding: 16px; }
            .nav-links { gap: 12px; }
            .lang-switch { border-right: none; padding-right: 0; }
            .progress-bar-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
        }

        /* ── ENTRANCE ANIMATIONS ── */
        .challenge-card {
            opacity: 0;
            transform: translateY(30px);
        }

        .challenge-card.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.5s ease, transform 0.5s ease,
                        border-color 0.3s, box-shadow 0.3s;
        }
    </style>
</head>
<body>

<!-- Ambient glows replaced by CSS radial gradients on body::after for performance -->

{{-- ── NAVIGATION ── --}}
<nav>
    <a href="{{ url('/') }}" class="logo">
        <div class="logo-icon">⌨</div>
        <span class="logo-name">Pair<span>Sync</span></span>
    </a>

    <div class="nav-links">
        <div class="lang-switch">
            <a href="{{ route('lang.switch', 'en') }}" class="lang-link" style="color: {{ app()->getLocale() === 'en' ? 'var(--primary)' : 'var(--text-dim)' }};">EN</a>
            <span style="color: var(--text-lo);">/</span>
            <a href="{{ route('lang.switch', 'es') }}" class="lang-link" style="color: {{ app()->getLocale() === 'es' ? 'var(--primary)' : 'var(--text-dim)' }};">ES</a>
        </div>

        @auth
            <span class="nav-user">{{ auth()->user()->name }}</span>
            @if(auth()->user()->isTeacher())
                <a href="{{ route('courses.index') }}" class="btn-nav-outline">{{ __('Manage Courses') }}</a>
                <a href="{{ route('grades.index') }}" class="btn-nav-outline">{{ __('Grades') }}</a>
            @endif
            <a href="{{ route('pair.index') }}" class="btn-nav-outline">{{ __('Sessions') }}</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;">
                @csrf
                <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;">{{ __('Log out') }}</button>
            </form>
        @endauth
    </div>
</nav>

{{-- ── PAGE HEADER ── --}}
<section class="page-header">
    <div class="page-badge">📱 {{ __('Mobile Development') }}</div>
    <h1>
        <span class="gradient-text">{{ __('Choose your') }}</span>
        <span class="accent-text">{{ __('challenge') }}</span>
    </h1>
    <p>{{ __('Progressive courses designed for pair programming. Pick a challenge and start coding with your partner.') }}</p>
</section>

{{-- ── PROGRESS ── --}}
<section class="progress-section">
    <div class="progress-bar-wrapper">
        <div class="progress-info">
            <div class="label">{{ __('Your progress') }}</div>
            <div class="value"><em>0</em> / {{ count($challenges) }} {{ __('completed') }}</div>
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="progressFill"></div>
        </div>
    </div>
</section>

{{-- ── CHALLENGES GRID ── --}}
<section class="challenges-grid" id="challengesGrid">
    @foreach ($challenges as $challenge)
        <article class="challenge-card" style="--card-accent: {{ $challenge['color'] }};" data-index="{{ $loop->index }}">
            <div class="card-header">
                <div class="card-icon" style="border-color: {{ $challenge['color'] }}20;">
                    {{ $challenge['icon'] }}
                </div>
                <span class="card-number">#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            </div>

            <h3>{{ __($challenge['title']) }}</h3>
            <p class="description">{{ __($challenge['description']) }}</p>

            <div class="card-meta">
                <span class="level-badge level-{{ $challenge['level'] }}">
                    {{ __($challenge['level']) }}
                </span>
                <span class="lessons-count">
                    📚 {{ $challenge->lessons_count }} {{ __('lessons') }}
                </span>
                @if(auth()->check() && auth()->user()->role === 'student' && !empty($challenge->is_enrolled))
                    <span class="level-badge level-beginner" style="margin-left:auto;">✅ {{ __('Inscrito') }}</span>
                @endif
            </div>

            <a href="{{ route('challenges.show', $challenge['id']) }}" class="btn-challenge" id="btn-challenge-{{ $challenge['id'] }}">
                {{ __('Start Challenge') }}
                <span class="arrow">→</span>
            </a>
        </article>
    @endforeach
</section>

<footer>
    {{ __('PairSync · Designed for developers, built for teams.') }}
</footer>

<script>
    // Staggered entrance animation
    document.addEventListener('DOMContentLoaded', function () {
        const cards = document.querySelectorAll('.challenge-card');
        cards.forEach((card, i) => {
            setTimeout(() => {
                card.classList.add('visible');
            }, 150 + i * 100);
        });

        // Animate progress bar (currently 0%)
        setTimeout(() => {
            // When progress tracking is implemented, set the real width here
            document.getElementById('progressFill').style.width = '0%';
        }, 600);
    });
</script>

</body>
</html>
