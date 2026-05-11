<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __($lesson['title']) }} — {{ __($challenge['title']) }} — PairSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#050511;--surface:#0a0b16;--card-bg:rgba(10,11,22,0.85);
            --border:#1a1b36;--border-hi:#2a2c56;--primary:#6366f1;
            --primary-glow:rgba(99,102,241,0.4);--blue:#3b82f6;--green:#10b981;
            --text:#f8fafc;--text-dim:#94a3b8;--text-lo:#475569;--radius:16px;
            --accent:{{ $challenge['color'] }};
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:'Syne',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden}
        body::before{content:'';position:absolute;inset:0;height:100%;background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px);background-size:50px 50px;opacity:.15;pointer-events:none;z-index:0;will-change:transform}
        body::after{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 500px 500px at 70% 10%,{{ $challenge['color'] }}12,transparent),radial-gradient(ellipse 400px 400px at 10% 80%,rgba(59,130,246,0.05),transparent);pointer-events:none;z-index:0}

        /* NAV */
        nav{position:sticky;top:0;z-index:100;display:flex;justify-content:space-between;align-items:center;padding:16px 32px;background:rgba(5,5,17,0.95);border-bottom:1px solid rgba(255,255,255,0.05)}
        .nav-left{display:flex;align-items:center;gap:14px}
        .logo{display:flex;align-items:center;gap:10px;text-decoration:none}
        .logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--blue));border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 0 16px var(--primary-glow)}
        .logo-name{font-weight:800;font-size:1.2rem;color:#fff;letter-spacing:-.02em}
        .logo-name span{color:var(--primary)}
        .nav-divider{width:1px;height:18px;background:rgba(255,255,255,0.1)}
        .breadcrumb{display:flex;align-items:center;gap:6px;font-size:.8rem}
        .breadcrumb a{color:var(--text-dim);text-decoration:none;font-weight:600;transition:color .2s}
        .breadcrumb a:hover{color:#fff}
        .breadcrumb .sep{color:var(--text-lo)}
        .breadcrumb .cur{color:var(--accent);font-weight:700}
        .nav-right{display:flex;align-items:center;gap:16px}
        .btn-small{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;text-decoration:none;font-weight:600;font-size:.82rem;transition:all .2s;background:rgba(255,255,255,0.02)}
        .btn-small:hover{border-color:rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);transform:translateY(-1px)}

        /* LAYOUT */
        .page-layout{position:relative;z-index:1;display:flex;max-width:1200px;margin:0 auto;padding:0 24px;gap:32px}

        /* SIDEBAR */
        .sidebar{width:280px;flex-shrink:0;padding:32px 0;position:sticky;top:72px;max-height:calc(100vh - 72px);overflow-y:auto}
        .sidebar::-webkit-scrollbar{width:3px}
        .sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px}
        .sidebar-title{font-size:.72rem;font-weight:700;color:var(--text-dim);letter-spacing:.08em;text-transform:uppercase;margin-bottom:14px;padding:0 12px}
        .sidebar-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--text-dim);font-size:.82rem;font-weight:600;transition:all .2s;border:1px solid transparent;margin-bottom:4px}
        .sidebar-item:hover{color:#fff;background:rgba(255,255,255,0.03)}
        .sidebar-item.active{color:var(--accent);background:{{ $challenge['color'] }}10;border-color:{{ $challenge['color'] }}25}
        .sidebar-item.locked{opacity:.35;pointer-events:none}
        .sidebar-num{font-family:'JetBrains Mono',monospace;font-size:.7rem;width:24px;text-align:center;flex-shrink:0}
        .sidebar-label{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

        /* MAIN CONTENT */
        .main-content{flex:1;min-width:0;padding:40px 0 80px}
        .lesson-header{margin-bottom:40px}
        .lesson-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;background:{{ $challenge['color'] }}12;border:1px solid {{ $challenge['color'] }}25;border-radius:100px;font-size:.75rem;font-weight:700;color:var(--accent);letter-spacing:.05em;text-transform:uppercase;margin-bottom:16px}
        .lesson-header h1{font-size:clamp(1.6rem,3.5vw,2.4rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;margin-bottom:8px}
        .lesson-header .subtitle{font-size:.95rem;color:var(--text-dim);line-height:1.6;font-weight:500}

        /* CONTENT SECTIONS */
        .content-section{margin-bottom:40px}
        .content-section h2{font-size:1.3rem;font-weight:700;letter-spacing:-.02em;margin-bottom:14px;display:flex;align-items:center;gap:10px}
        .content-section h2 .section-icon{font-size:1.1rem}
        .content-section p{font-size:.92rem;color:var(--text-dim);line-height:1.75;margin-bottom:14px;font-weight:500}
        .content-section ul{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:16px}
        .content-section ul li{font-size:.88rem;color:var(--text-dim);line-height:1.65;display:flex;gap:10px;font-weight:500}
        .content-section ul li::before{content:'▸';color:var(--accent);flex-shrink:0;font-weight:700}
        .content-section ul li strong,.content-section ul li code{color:#fff}
        .content-section code{font-family:'JetBrains Mono',monospace;background:rgba(255,255,255,0.07);padding:2px 7px;border-radius:5px;font-size:.82rem;color:var(--green)}

        /* CODE BLOCKS */
        .code-block{background:rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.08);border-radius:12px;margin:16px 0 20px;overflow:hidden}
        .code-header{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:rgba(255,255,255,0.03);border-bottom:1px solid rgba(255,255,255,0.06)}
        .code-lang{font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--accent);font-weight:600;letter-spacing:.06em;text-transform:uppercase}
        .code-copy{background:none;border:none;color:var(--text-lo);cursor:pointer;font-size:.8rem;padding:4px 8px;border-radius:4px;transition:all .2s;font-family:'JetBrains Mono',monospace;font-size:.7rem}
        .code-copy:hover{color:#fff;background:rgba(255,255,255,0.06)}
        .code-body{padding:16px 20px;overflow-x:auto}
        .code-body pre{font-family:'JetBrains Mono',monospace;font-size:.82rem;line-height:1.65;color:var(--text);white-space:pre;margin:0}
        .code-note{font-size:.8rem;color:var(--text-dim);font-style:italic;padding:0 4px;margin-top:4px;line-height:1.6}
        .code-note code{font-style:normal}

        /* EXERCISE SECTION */
        .exercise-section{background:rgba(16,185,129,0.05);border:1px solid rgba(16,185,129,0.15);border-radius:var(--radius);padding:32px;margin:40px 0}
        .exercise-section h2{color:var(--green)}
        .exercise-section p{color:var(--text-dim)}
        .task-card{background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:24px;margin-top:20px}
        .task-card h3{font-size:1.05rem;font-weight:700;color:#fff;margin-bottom:8px}
        .task-card .task-desc{font-size:.88rem;color:var(--text-dim);margin-bottom:12px;line-height:1.6}
        .task-hint{font-size:.82rem;color:var(--text-lo);font-style:italic;padding:10px 14px;background:rgba(255,255,255,0.03);border-radius:8px;border-left:3px solid var(--accent);margin-bottom:14px}
        .task-hint strong{color:var(--text-dim);font-style:normal}

        /* SUMMARY SECTION */
        .summary-section{background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15);border-radius:var(--radius);padding:32px;margin:40px 0}
        .summary-section h2{color:var(--primary)}
        .summary-section ul li::before{color:var(--primary)}

        /* CTA */
        .cta-section{text-align:center;padding:48px 24px;margin:20px 0}
        .cta-section h2{font-size:1.5rem;font-weight:800;margin-bottom:10px;letter-spacing:-.02em}
        .cta-section p{color:var(--text-dim);font-size:.92rem;margin-bottom:28px;font-weight:500}
        .btn-cta{display:inline-flex;align-items:center;gap:10px;padding:16px 36px;background:linear-gradient(135deg,var(--primary),var(--blue));border:none;border-radius:12px;color:#fff;font-family:'Syne',sans-serif;font-weight:700;font-size:1.05rem;text-decoration:none;transition:all .25s;box-shadow:0 8px 32px var(--primary-glow);cursor:pointer;letter-spacing:.01em}
        .btn-cta:hover{transform:translateY(-3px);box-shadow:0 12px 40px var(--primary-glow)}
        .btn-cta .arrow{transition:transform .25s}
        .btn-cta:hover .arrow{transform:translateX(4px)}
        .btn-back-lesson{display:inline-flex;align-items:center;gap:6px;margin-top:14px;color:var(--text-dim);text-decoration:none;font-size:.85rem;font-weight:600;transition:color .2s}
        .btn-back-lesson:hover{color:#fff}

        /* FOOTER */
        footer{position:relative;z-index:10;text-align:center;padding:32px;color:var(--text-lo);font-family:'JetBrains Mono',monospace;font-size:.85rem;border-top:1px solid rgba(255,255,255,0.05)}

        /* RESPONSIVE */
        @media(max-width:900px){
            .sidebar{display:none}
            .page-layout{padding:0 16px}
        }
        @media(max-width:600px){
            nav{padding:12px 16px;flex-wrap:wrap;gap:8px}
            .exercise-section,.summary-section{padding:20px}
            .task-card{padding:16px}
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-left">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon">⌨</div>
            <span class="logo-name">Pair<span>Sync</span></span>
        </a>
        <div class="nav-divider"></div>
        <div class="breadcrumb">
            <a href="{{ route('challenges.index') }}">{{ __('Challenges') }}</a>
            <span class="sep">›</span>
            <a href="{{ route('challenges.show', $challenge['id']) }}">{{ __($challenge['title']) }}</a>
            <span class="sep">›</span>
            <span class="cur">{{ __('Lesson') }} {{ $lesson['id'] }}</span>
        </div>
    </div>
    <div class="nav-right">
        @auth <span style="color:var(--text-dim);font-size:.85rem;font-weight:600">{{ auth()->user()->name }}</span> @endauth
        <a href="{{ route('challenges.show', $challenge['id']) }}" class="btn-small">← {{ __('All Lessons') }}</a>
    </div>
</nav>

<div class="page-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-title">{{ __($challenge['title']) }}</div>
        @foreach ($lessons as $sideLesson)
            @if ($sideLesson['available'])
                <a href="{{ route('challenges.lesson', [$challenge['id'], $sideLesson['id']]) }}"
                   class="sidebar-item {{ $sideLesson['id'] === $lesson['id'] ? 'active' : '' }}">
            @else
                <div class="sidebar-item locked">
            @endif
                <span class="sidebar-num">{{ str_pad($sideLesson['id'], 2, '0', STR_PAD_LEFT) }}</span>
                <span class="sidebar-label">{{ __($sideLesson['title']) }}</span>
            @if ($sideLesson['available'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </aside>

    {{-- MAIN --}}
    <main class="main-content">
        <div class="lesson-header">
            <div class="lesson-badge">{{ $lesson['icon'] }} {{ __('Lesson') }} {{ $lesson['id'] }} · {{ $lesson['duration'] }}</div>
            <h1>{{ __($lesson['title']) }}</h1>
            <p class="subtitle">{{ __($lesson['description']) }}</p>
        </div>

        @if (!empty($lesson['content']['sections']))
            @foreach ($lesson['content']['sections'] as $section)

                @if ($section['type'] === 'intro')
                    <div class="content-section">
                        <h2><span class="section-icon">📖</span> {{ __($section['title']) }}</h2>
                        <p>{{ __($section['body']) }}</p>
                    </div>

                @elseif ($section['type'] === 'video')
                    <div class="content-section">
                        <h2><span class="section-icon">🎥</span> {{ __($section['title']) }}</h2>
                        @if (!empty($section['body']))
                            <p>{{ __($section['body']) }}</p>
                        @endif
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.5); box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin: 20px 0;">
                            <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="{{ $section['url'] }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                    </div>

                @elseif ($section['type'] === 'concept')
                    <div class="content-section">
                        <h2><span class="section-icon">💡</span> {{ __($section['title']) }}</h2>
                        @if (!empty($section['body']))
                            <p>{{ __($section['body']) }}</p>
                        @endif
                        @if (!empty($section['bullets']))
                            <ul>
                                @foreach ($section['bullets'] as $bullet)
                                    <li>{!! $bullet !!}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                @elseif ($section['type'] === 'code')
                    <div class="content-section">
                        <h2><span class="section-icon">💻</span> {{ __($section['title']) }}</h2>
                        @if (!empty($section['body']))
                            <p>{!! $section['body'] !!}</p>
                        @endif
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-lang">{{ $section['language'] ?? 'kotlin' }}</span>
                                <button class="code-copy" onclick="copyCode(this)">📋 {{ __('Copy') }}</button>
                            </div>
                            <div class="code-body">
                                <pre>{{ $section['code'] }}</pre>
                            </div>
                        </div>
                        @if (!empty($section['note']))
                            <p class="code-note">💡 {!! $section['note'] !!}</p>
                        @endif
                    </div>

                @elseif ($section['type'] === 'exercise')
                    <div class="exercise-section">
                        <h2><span class="section-icon">🎯</span> {{ __($section['title']) }}</h2>
                        <p>{{ __($section['body']) }}</p>
                        @foreach ($section['tasks'] as $task)
                            <div class="task-card">
                                <h3>{{ __($task['title']) }}</h3>
                                <p class="task-desc">{{ __($task['description']) }}</p>
                                @if (!empty($task['hint']))
                                    <div class="task-hint"><strong>💡 {{ __('Hint') }}:</strong> {{ __($task['hint']) }}</div>
                                @endif
                                @if (!empty($task['starter_code']))
                                    <div class="code-block">
                                        <div class="code-header">
                                            <span class="code-lang">kotlin · starter code</span>
                                            <button class="code-copy" onclick="copyCode(this)">📋 {{ __('Copy') }}</button>
                                        </div>
                                        <div class="code-body">
                                            <pre>{{ $task['starter_code'] }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                @elseif ($section['type'] === 'summary')
                    <div class="summary-section">
                        <h2><span class="section-icon">✅</span> {{ __($section['title']) }}</h2>
                        <ul>
                            @foreach ($section['bullets'] as $bullet)
                                <li>{{ __($bullet) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            @endforeach
        @endif

        {{-- CTA --}}
        <div class="cta-section">
            <h2>{{ __('Ready to Practice?') }}</h2>
            <p>{{ __('Start a pair programming session and work on the exercises with your partner.') }}</p>
            <a href="{{ route('pair.index') }}" class="btn-cta" id="btn-start-session">
                ⚡ {{ __('Start Pair Session') }}
                <span class="arrow">→</span>
            </a>
            <br>
            <a href="{{ route('challenges.show', $challenge['id']) }}" class="btn-back-lesson">← {{ __('Back to all lessons') }}</a>
        </div>
    </main>
</div>

<footer>{{ __('PairSync · Designed for developers, built for teams.') }}</footer>

<script>
function copyCode(btn) {
    const pre = btn.closest('.code-block').querySelector('pre');
    navigator.clipboard.writeText(pre.textContent).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✅ {{ __("Copied!") }}';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}
</script>
</body>
</html>
