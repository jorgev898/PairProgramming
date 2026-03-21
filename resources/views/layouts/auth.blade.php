<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Account') — PairSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c0f;
            --surface: #0f1318;
            --border: #1e2530;
            --border-hi: #2e3d50;
            --green: #00e5a0;
            --green-dim: #00b87a;
            --blue: #4da6ff;
            --text: #c8d6e8;
            --text-dim: #5a6a7e;
            --text-lo: #2e3d50;
            --card-bg: #0d1117;
            --radius: 12px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'JetBrains Mono', monospace;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
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
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: .12;
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 { width: 500px; height: 500px; background: var(--green); top: -180px; left: -180px; }
        .blob-2 { width: 400px; height: 400px; background: var(--blue); bottom: -120px; right: -120px; }
        .wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px 32px;
            position: relative;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--green), var(--blue));
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .logo-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .logo-name {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.35rem;
            color: #fff;
        }
        .logo-name span { color: var(--green); }
        h1 {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 8px;
        }
        .lead {
            font-size: .8rem;
            color: var(--text-dim);
            margin-bottom: 28px;
            line-height: 1.6;
        }
        label {
            display: block;
            font-size: .72rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--text-dim);
            margin-bottom: 7px;
        }
        .form-group { margin-bottom: 14px; }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
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
        input::placeholder { color: var(--text-lo); }
        input:focus {
            border-color: var(--green-dim);
            background: rgba(0,229,160,.04);
        }
        .field-error {
            font-size: .72rem;
            color: #ff6b6b;
            margin-top: 5px;
            display: block;
        }
        .btn {
            width: 100%;
            padding: 13px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--green), var(--green-dim));
            color: #001a0d;
            transition: opacity .2s, transform .15s;
        }
        .btn:hover { opacity: .88; transform: translateY(-1px); }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            font-size: .78rem;
            color: var(--text-dim);
        }
        .remember-row input { width: auto; accent-color: var(--green); }
        .footer-links {
            margin-top: 24px;
            text-align: center;
            font-size: .78rem;
            color: var(--text-dim);
        }
        .footer-links a {
            color: var(--blue);
            text-decoration: none;
        }
        .footer-links a:hover { text-decoration: underline; }
        .back-home {
            margin-top: 20px;
            text-align: center;
        }
        .back-home a {
            font-size: .75rem;
            color: var(--text-lo);
            text-decoration: none;
        }
        .back-home a:hover { color: var(--text-dim); }
    </style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="wrapper">
    <div class="auth-card">
        <div class="logo-row">
            <div class="logo-icon">⌨</div>
            <span class="logo-name">Pair<span>Sync</span></span>
        </div>
        @yield('content')
        <div class="back-home">
            <a href="{{ route('pair.index') }}">← Back to sessions</a>
        </div>
    </div>
</div>
</body>
</html>
