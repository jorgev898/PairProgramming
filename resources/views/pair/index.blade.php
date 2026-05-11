<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PairSync — Collaborative Coding</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0c0f;
            --surface:   #0f1318;
            --border:    #1e2530;
            --border-hi: #2e3d50;
            --green:     #00e5a0;
            --green-dim: #00b87a;
            --blue:      #4da6ff;
            --amber:     #ffb347;
            --text:      #c8d6e8;
            --text-dim:  #5a6a7e;
            --text-lo:   #2e3d50;
            --card-bg:   #0d1117;
            --radius:    12px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'JetBrains Mono', monospace;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── GRID BACKGROUND ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: .35;
            pointer-events: none;
            z-index: 0;
        }

        /* ── GLOW BLOBS ── */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .12;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 { width: 600px; height: 600px; background: var(--green);  top: -200px; left: -200px; }
        .blob-2 { width: 500px; height: 500px; background: var(--blue);   bottom: -150px; right: -150px; }

        /* ── LAYOUT ── */
        .wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* ── HEADER ── */
        header {
            text-align: center;
            margin-bottom: 56px;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .logo-name {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            color: #fff;
            letter-spacing: -.02em;
        }

        .logo-name span { color: var(--green); }

        h1 {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            line-height: 1.05;
            letter-spacing: -.04em;
            color: #fff;
            margin-bottom: 16px;
        }

        h1 em {
            font-style: normal;
            color: var(--green);
        }

        .subtitle {
            font-size: .88rem;
            color: var(--text-dim);
            letter-spacing: .04em;
            max-width: 480px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── CARDS ROW ── */
        .cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            width: 100%;
            max-width: 820px;
        }

        @media (max-width: 660px) {
            .cards { grid-template-columns: 1fr; }
        }

        /* ── CARD ── */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px 32px;
            position: relative;
            overflow: hidden;
            transition: border-color .25s, transform .25s;
        }

        .card:hover {
            border-color: var(--border-hi);
            transform: translateY(-3px);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }

        .card-create::before { background: linear-gradient(90deg, var(--green), var(--blue)); }
        .card-join::before   { background: linear-gradient(90deg, var(--blue), var(--amber)); }

        .card-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .7rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .badge-create { background: rgba(0,229,160,.1); color: var(--green); border: 1px solid rgba(0,229,160,.25); }
        .badge-join   { background: rgba(77,166,255,.1); color: var(--blue);  border: 1px solid rgba(77,166,255,.25); }

        .badge-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            animation: pulse 1.8s ease-in-out infinite;
        }

        .badge-create .badge-dot { background: var(--green); }
        .badge-join   .badge-dot { background: var(--blue);  }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(.7); }
        }

        .card h2 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 8px;
            letter-spacing: -.02em;
        }

        .card p {
            font-size: .8rem;
            color: var(--text-dim);
            line-height: 1.7;
            margin-bottom: 28px;
        }

        /* ── ROLE PILLS ── */
        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .72rem;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 4px 10px;
            margin-bottom: 24px;
            color: var(--text-dim);
        }

        .role-pill strong { color: var(--text); }

        /* ── FORM ── */
        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: .72rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 7px;
        }

        .input-row {
            display: flex;
            gap: 8px;
        }

        input[type="text"] {
            width: 100%;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 11px 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: .85rem;
            color: #fff;
            outline: none;
            transition: border-color .2s, background .2s;
        }

        input[type="text"]::placeholder { color: var(--text-lo); }

        input[type="text"]:focus {
            border-color: var(--green-dim);
            background: rgba(0,229,160,.04);
        }

        input[type="text"].input-code {
            letter-spacing: .25em;
            text-transform: uppercase;
            font-size: 1.1rem;
            font-weight: 700;
            text-align: center;
        }

        .input-code:focus { border-color: var(--blue); background: rgba(77,166,255,.04); }

        .field-error {
            font-size: .72rem;
            color: #ff6b6b;
            margin-top: 5px;
            display: block;
        }

        /* ── BUTTON ── */
        .btn {
            width: 100%;
            padding: 13px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: .95rem;
            letter-spacing: .02em;
            cursor: pointer;
            transition: opacity .2s, transform .15s;
            margin-top: 4px;
            position: relative;
            overflow: hidden;
        }

        .btn:hover  { opacity: .88; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .btn-create {
            background: linear-gradient(135deg, var(--green), var(--green-dim));
            color: #001a0d;
        }

        .btn-join {
            background: linear-gradient(135deg, var(--blue), #2980ff);
            color: #001020;
        }

        /* ── DIVIDER ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
        }

        .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid var(--border);
        }

        .divider span {
            font-size: .7rem;
            color: var(--text-dim);
            letter-spacing: .08em;
        }

        /* ── ROLES LEGEND ── */
        .roles-legend {
            margin-top: 40px;
            display: flex;
            gap: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .role-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            max-width: 240px;
        }

        .role-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .icon-driver   { background: rgba(0,229,160,.1); border: 1px solid rgba(0,229,160,.2); }
        .icon-navigator{ background: rgba(77,166,255,.1); border: 1px solid rgba(77,166,255,.2); }

        .role-desc h3 {
            font-family: 'Syne', sans-serif;
            font-size: .9rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 3px;
        }

        .role-desc p {
            font-size: .75rem;
            color: var(--text-dim);
            line-height: 1.55;
            margin: 0;
        }

        /* ── TERMINAL DECORATION ── */
        .terminal-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            background: rgba(255,255,255,.03);
            border-bottom: 1px solid var(--border);
            border-radius: var(--radius) var(--radius) 0 0;
            margin: -36px -32px 24px;
        }

        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .dot-r { background: #ff5f57; }
        .dot-y { background: #ffbd2e; }
        .dot-g { background: #28ca41; }

        .terminal-title {
            margin-left: auto;
            font-size: .68rem;
            color: var(--text-lo);
            letter-spacing: .06em;
        }

        /* ── FOOTER ── */
        footer {
            margin-top: 48px;
            font-size: .72rem;
            color: var(--text-lo);
            letter-spacing: .04em;
        }

        /* ── FLASH ERROR ── */
        .flash-error {
            background: rgba(255,107,107,.1);
            border: 1px solid rgba(255,107,107,.3);
            color: #ff8a8a;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .78rem;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="wrapper">

    <nav class="auth-nav" aria-label="Account">
        <div class="lang-switch" style="margin-right: auto; display: flex; gap: 8px; align-items: center; border:none; padding: 0;">
            <a href="{{ route('lang.switch', 'en') }}" style="color: {{ app()->getLocale() === 'en' ? 'var(--green)' : 'var(--text-dim)' }}; border:none; text-decoration: none; font-weight: bold; font-size: 0.8rem; padding: 0; background: transparent;">EN</a>
            <span style="color: var(--border);">/</span>
            <a href="{{ route('lang.switch', 'es') }}" style="color: {{ app()->getLocale() === 'es' ? 'var(--green)' : 'var(--text-dim)' }}; border:none; text-decoration: none; font-weight: bold; font-size: 0.8rem; padding: 0; background: transparent;">ES</a>
        </div>
        @auth
            <span class="auth-nav-user">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;margin:0;">
                @csrf
                <button type="submit" style="background:none; border:none; color:var(--text-dim); font-size: 0.85rem; font-weight: 600; cursor:pointer;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--text-dim)'">{{ __('Log out') }}</button>
            </form>
        @endauth
    </nav>
    <header>
        <a href="{{ url('/') }}" class="logo" style="text-decoration: none;">
            <div class="logo-icon">⌨</div>
            <span class="logo-name">Pair<span>Sync</span></span>
        </a>
        <h1 style="font-size: clamp(1.8rem, 4vw, 2.8rem); margin-bottom: 8px;">{{ __('Dashboard') }}</h1>
        <p class="subtitle">
            {{ __('Create a new room or join an existing session.') }}
        </p>
    </header>

    <div class="cards">

        {{-- ── CREATE SESSION ── --}}
        <div class="card card-create">
            <div class="terminal-bar">
                <span class="dot dot-r"></span>
                <span class="dot dot-y"></span>
                <span class="dot dot-g"></span>
                <span class="terminal-title">new_session.sh</span>
            </div>

            <div class="card-badge badge-create">
                <span class="badge-dot"></span>
                {{ __('Start session') }}
            </div>

            <h2>{{ __('Create a Room') }}</h2>
            <p>{{ __('Generate a unique 6-character code and wait for your partner to join. You\'ll be the') }} <strong>{{ __('Driver') }}</strong>.</p>

            <div class="role-pill">
                🧑‍💻 <strong>{{ __('Your role:') }}</strong>&nbsp;{{ __('Driver — writes the code') }}
            </div>

            @if ($errors->hasBag('create') || (!$errors->hasBag('join') && $errors->any()))
                @foreach ($errors->all() as $error)
                    <div class="flash-error">{{ $error }}</div>
                @endforeach
            @endif

            <form method="POST" action="{{ route('pair.create') }}" id="form-create">
                @csrf
                @if(request()->has('lesson_id'))
                    <input type="hidden" name="lesson_id" value="{{ request('lesson_id') }}">
                @endif
                <div class="form-group">
                    <label for="username-create">{{ __('Your display name') }}</label>
                    <input
                        type="text"
                        id="username-create"
                        name="username"
                        placeholder="{{ __('e.g. alex_dev') }}"
                        maxlength="30"
                        autocomplete="off"
                        value="{{ old('username') }}"
                        required
                    >
                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-create">
                    {{ __('⚡ Generate Room Code') }}
                </button>
            </form>
        </div>

        {{-- ── JOIN SESSION ── --}}
        <div class="card card-join">
            <div class="terminal-bar">
                <span class="dot dot-r"></span>
                <span class="dot dot-y"></span>
                <span class="dot dot-g"></span>
                <span class="terminal-title">join_session.sh</span>
            </div>

            <div class="card-badge badge-join">
                <span class="badge-dot"></span>
                {{ __('Join session') }}
            </div>

            <h2>{{ __('Join a Room') }}</h2>
            <p>{{ __('Enter the 6-character code your partner shared. You\'ll be the') }} <strong>{{ __('Navigator') }}</strong>.</p>

            <div class="role-pill">
                🧭 <strong>{{ __('Your role:') }}</strong>&nbsp;{{ __('Navigator — guides the strategy') }}
            </div>

            @if ($errors->hasBag('join'))
                @foreach ($errors->getBag('join')->all() as $error)
                    <div class="flash-error">{{ $error }}</div>
                @endforeach
            @endif

            <form method="POST" action="{{ route('pair.join') }}" id="form-join">
                @csrf
                <div class="form-group">
                    <label for="session-code">{{ __('Session code') }}</label>
                    <input
                        type="text"
                        id="session-code"
                        name="code"
                        class="input-code"
                        placeholder="ABC123"
                        maxlength="6"
                        autocomplete="off"
                        value="{{ old('code') }}"
                        required
                    >
                    @error('code')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="username-join">{{ __('Your display name') }}</label>
                    <input
                        type="text"
                        id="username-join"
                        name="username"
                        placeholder="{{ __('e.g. sam_navigator') }}"
                        maxlength="30"
                        autocomplete="off"
                        value="{{ old('username') }}"
                        required
                    >
                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-join">
                    {{ __('🔗 Join Session') }}
                </button>
            </form>
        </div>

    </div>

    {{-- ── ROLES LEGEND ── --}}
    <div class="roles-legend">
        <div class="role-item">
            <div class="role-icon icon-driver">🧑‍💻</div>
            <div class="role-desc">
                <h3>{{ __('Driver') }}</h3>
                <p>{{ __('Controls the keyboard. Focuses on the tactical implementation of the immediate task.') }}</p>
            </div>
        </div>
        <div class="role-item">
            <div class="role-icon icon-navigator">🧭</div>
            <div class="role-desc">
                <h3>{{ __('Navigator') }}</h3>
                <p>{{ __('Reviews the code in real time, thinks about direction, architecture, and catches bugs.') }}</p>
            </div>
        </div>
    </div>

    <footer>{{ __('PairSync · real-time collaborative coding') }}</footer>

</div>

<script>
    // Auto-uppercase the session code input
    document.getElementById('session-code').addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Subtle card entrance animation
    document.querySelectorAll('.card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(24px)';
        card.style.transition = 'opacity .5s ease, transform .5s ease, border-color .25s, box-shadow .25s';
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 120 + i * 100);
    });
</script>

</body>
</html>
