<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $session->code }} — PairSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0c0f;
            --border:    #1e2530;
            --border-hi: #2e3d50;
            --green:     #00e5a0;
            --green-dim: #00b87a;
            --blue:      #4da6ff;
            --amber:     #ffb347;
            --purple:    #b47eff;
            --text:      #c8d6e8;
            --text-dim:  #5a6a7e;
            --text-lo:   #2e3d50;
            --card-bg:   #0d1117;
            --radius:    12px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'JetBrains Mono', monospace; background: var(--bg); color: var(--text); min-height: 100vh; }
        body::before {
            content: ''; position: fixed; inset: 0;
            background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px; opacity: .2; pointer-events: none; z-index: 0;
        }

        /* ── TOPBAR ── */
        .topbar {
            position: relative; z-index: 10;
            display: flex; align-items: center; gap: 16px;
            padding: 14px 28px;
            background: rgba(10,12,15,.9); border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px); flex-wrap: wrap;
        }
        .topbar-logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.1rem; color: #fff; }
        .topbar-logo span { color: var(--green); }
        .topbar-divider { width: 1px; height: 20px; background: var(--border); }
        .session-badge { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.04); border: 1px solid var(--border); border-radius: 8px; padding: 6px 14px; }
        .session-label { font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; color: var(--text-dim); }
        .session-code  { font-size: 1rem; font-weight: 700; letter-spacing: .2em; color: var(--green); }
        .copy-btn { background: none; border: none; cursor: pointer; color: var(--text-dim); font-size: .9rem; transition: color .2s; }
        .copy-btn:hover { color: var(--green); }
        .topbar-spacer { flex: 1; }
        .status-pill { display: inline-flex; align-items: center; gap: 6px; font-size: .72rem; letter-spacing: .06em; text-transform: uppercase; padding: 5px 12px; border-radius: 20px; }
        .pill-waiting { background: rgba(255,179,71,.1); color: var(--amber); border: 1px solid rgba(255,179,71,.25); }
        .pill-active  { background: rgba(0,229,160,.1);  color: var(--green); border: 1px solid rgba(0,229,160,.25); }
        .pill-dot { width: 6px; height: 6px; border-radius: 50%; animation: pulse 1.8s ease-in-out infinite; }
        .pill-waiting .pill-dot { background: var(--amber); }
        .pill-active  .pill-dot { background: var(--green); }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.65)} }

        /* ── MAIN ── */
        .main { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 60px); padding: 48px 24px; }
        .room-panel { width: 100%; max-width: 700px; }

        /* ── ROLE BANNER ── */
        .role-banner { border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; font-size: .82rem; display: flex; align-items: center; gap: 10px; transition: background .4s, border-color .4s, color .4s; }
        .banner-driver    { background: rgba(0,229,160,.08);  border: 1px solid rgba(0,229,160,.2);  color: var(--green); }
        .banner-navigator { background: rgba(77,166,255,.08); border: 1px solid rgba(77,166,255,.2); color: var(--blue);  }
        .banner-unknown   { background: rgba(255,255,255,.04); border: 1px solid var(--border);      color: var(--text-dim); }

        /* ── PARTICIPANTS ── */
        .participants { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px; }
        @media (max-width: 520px) { .participants { grid-template-columns: 1fr; } }
        .participant-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px 22px; position: relative; overflow: hidden; transition: border-color .4s; }
        .participant-card.is-me { border-color: var(--green-dim); }
        .participant-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
        .pc-driver::before    { background: linear-gradient(90deg, var(--green), transparent); }
        .pc-navigator::before { background: linear-gradient(90deg, var(--blue),  transparent); }
        .pc-role-label { font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 12px; }
        .pc-driver    .pc-role-label { color: var(--green); }
        .pc-navigator .pc-role-label { color: var(--blue);  }
        .pc-avatar { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px; }
        .pc-driver    .pc-avatar { background: rgba(0,229,160,.1);  border: 1px solid rgba(0,229,160,.2); }
        .pc-navigator .pc-avatar { background: rgba(77,166,255,.1); border: 1px solid rgba(77,166,255,.2); }
        .pc-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1.1rem; color: #fff; margin-bottom: 4px; }
        .pc-name.empty { color: var(--text-lo); font-family: 'JetBrains Mono', monospace; font-weight: 400; font-size: .9rem; }
        .pc-sub  { font-size: .75rem; color: var(--text-dim); }
        .you-tag { display: inline-block; font-size: .6rem; letter-spacing: .08em; text-transform: uppercase; background: rgba(0,229,160,.12); color: var(--green); border: 1px solid rgba(0,229,160,.25); border-radius: 4px; padding: 2px 7px; margin-left: 8px; vertical-align: middle; }

        /* ── INFO BOX ── */
        .info-box { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 28px; margin-bottom: 24px; }
        .info-box h3 { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 14px; }
        .info-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .info-list li { display: flex; gap: 10px; font-size: .8rem; color: var(--text-dim); line-height: 1.55; }
        .info-list li::before { content: '▸'; color: var(--green); flex-shrink: 0; }

        /* ── WAITING ── */
        .waiting-state { text-align: center; padding: 24px 0 8px; }
        .waiting-spinner { display: inline-block; width: 40px; height: 40px; border: 2px solid var(--border); border-top-color: var(--green); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .waiting-state h3 { font-family: 'Syne', sans-serif; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .waiting-state p  { font-size: .8rem; color: var(--text-dim); }
        .share-code-box { display: inline-flex; align-items: center; gap: 10px; background: rgba(0,229,160,.06); border: 1px solid rgba(0,229,160,.2); border-radius: 8px; padding: 10px 18px; margin-top: 18px; }
        .share-code-box .code { font-size: 1.4rem; font-weight: 700; letter-spacing: .25em; color: var(--green); }

        /* ── ACTIONS ── */
        .actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 11px 20px; border: none; border-radius: 8px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: .88rem; cursor: pointer; text-decoration: none; transition: opacity .2s, transform .15s; }
        .btn:hover  { opacity: .85; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-swap  { background: linear-gradient(135deg, var(--amber), #e6952e); color: #1a0d00; flex: 1; }
        .btn-leave { background: rgba(255,255,255,.06); border: 1px solid var(--border); color: var(--text-dim); }

        /* ── TOAST ── */
        #toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(80px); background: var(--green); color: #001a0d; font-weight: 700; font-size: .82rem; padding: 10px 20px; border-radius: 8px; transition: transform .35s cubic-bezier(.34,1.56,.64,1); z-index: 999; white-space: nowrap; }
        #toast.show { transform: translateX(-50%) translateY(0); }

        /* ══════════════════════════════════════════════
           CHATBOT — FLOATING BUTTON + PANEL
        ══════════════════════════════════════════════ */

        /* Botón flotante */
        #chat-fab {
            position: fixed;
            bottom: 28px; right: 28px;
            width: 56px; height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), #8040e0);
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 24px rgba(180,126,255,.4);
            z-index: 1000;
            transition: transform .2s, box-shadow .2s;
        }
        #chat-fab:hover { transform: scale(1.08); box-shadow: 0 6px 32px rgba(180,126,255,.6); }
        #chat-fab:active { transform: scale(.96); }

        /* Badge de mensajes no leídos */
        #chat-fab .badge {
            position: absolute; top: -2px; right: -2px;
            width: 18px; height: 18px; border-radius: 50%;
            background: #ff4f4f; color: #fff;
            font-size: .6rem; font-weight: 700;
            display: none; align-items: center; justify-content: center;
            border: 2px solid var(--bg);
        }
        #chat-fab .badge.show { display: flex; }

        /* Panel del chat */
        #chat-panel {
            position: fixed;
            bottom: 96px; right: 28px;
            width: 360px;
            max-height: 520px;
            background: #0d1117;
            border: 1px solid var(--border);
            border-radius: 16px;
            display: flex; flex-direction: column;
            box-shadow: 0 24px 64px rgba(0,0,0,.6);
            z-index: 1000;
            overflow: hidden;
            transform: scale(.92) translateY(16px);
            opacity: 0;
            pointer-events: none;
            transition: transform .25s cubic-bezier(.34,1.2,.64,1), opacity .2s;
        }
        #chat-panel.open {
            transform: scale(1) translateY(0);
            opacity: 1;
            pointer-events: all;
        }
        @media (max-width: 480px) {
            #chat-panel { width: calc(100vw - 32px); right: 16px; bottom: 88px; }
        }

        /* Header del chat */
        .chat-header {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 16px;
            background: rgba(180,126,255,.08);
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .chat-header-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, var(--purple), #8040e0);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .chat-header-info { flex: 1; }
        .chat-header-name { font-family: 'Syne', sans-serif; font-weight: 700; font-size: .88rem; color: #fff; }
        .chat-header-status { font-size: .68rem; color: var(--purple); letter-spacing: .04em; display: flex; align-items: center; gap: 5px; }
        .chat-header-status::before { content: ''; width: 5px; height: 5px; background: var(--green); border-radius: 50%; display: inline-block; }
        .chat-close { background: none; border: none; cursor: pointer; color: var(--text-dim); font-size: 1.1rem; padding: 4px; transition: color .2s; }
        .chat-close:hover { color: #fff; }

        /* Mensajes */
        #chat-messages {
            flex: 1; overflow-y: auto; padding: 16px;
            display: flex; flex-direction: column; gap: 12px;
            scroll-behavior: smooth;
        }
        #chat-messages::-webkit-scrollbar { width: 4px; }
        #chat-messages::-webkit-scrollbar-track { background: transparent; }
        #chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

        .msg {
            display: flex; flex-direction: column; gap: 4px;
            animation: msgIn .2s ease;
        }
        @keyframes msgIn { from { opacity:0; transform: translateY(6px); } to { opacity:1; transform: translateY(0); } }

        .msg-meta { font-size: .65rem; color: var(--text-dim); letter-spacing: .04em; display: flex; align-items: center; gap: 6px; }
        .msg-meta .sender { font-weight: 700; }
        .msg-meta .sender.bot-name { color: var(--purple); }
        .msg-meta .sender.user-name { color: var(--green); }

        .msg-bubble {
            padding: 10px 13px;
            border-radius: 10px;
            font-size: .8rem;
            line-height: 1.6;
            max-width: 90%;
            word-break: break-word;
        }
        .msg.bot  .msg-bubble { background: rgba(180,126,255,.1); border: 1px solid rgba(180,126,255,.2); color: var(--text); border-radius: 2px 10px 10px 10px; }
        .msg.user .msg-bubble { background: rgba(0,229,160,.08);  border: 1px solid rgba(0,229,160,.18); color: var(--text); border-radius: 10px 2px 10px 10px; align-self: flex-end; }
        .msg.user { align-items: flex-end; }

        /* Typing indicator */
        .typing-bubble {
            display: flex; gap: 4px; align-items: center;
            padding: 10px 14px;
            background: rgba(180,126,255,.1); border: 1px solid rgba(180,126,255,.2);
            border-radius: 2px 10px 10px 10px;
            width: fit-content;
        }
        .typing-bubble span {
            width: 6px; height: 6px; background: var(--purple);
            border-radius: 50%; animation: bounce 1.2s infinite;
        }
        .typing-bubble span:nth-child(2) { animation-delay: .2s; }
        .typing-bubble span:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce { 0%,80%,100%{transform:translateY(0)} 40%{transform:translateY(-6px)} }

        /* Input área */
        .chat-input-area {
            padding: 12px;
            border-top: 1px solid var(--border);
            display: flex; gap: 8px; align-items: flex-end;
            flex-shrink: 0;
        }
        #chat-input {
            flex: 1; background: rgba(255,255,255,.04);
            border: 1px solid var(--border); border-radius: 10px;
            padding: 9px 12px;
            font-family: 'JetBrains Mono', monospace; font-size: .8rem;
            color: #fff; resize: none; outline: none;
            max-height: 100px; min-height: 38px;
            transition: border-color .2s;
            line-height: 1.5;
        }
        #chat-input:focus { border-color: var(--purple); }
        #chat-input::placeholder { color: var(--text-lo); }
        #chat-send {
            width: 38px; height: 38px; border-radius: 9px;
            background: linear-gradient(135deg, var(--purple), #8040e0);
            border: none; cursor: pointer; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; transition: opacity .2s, transform .15s;
        }
        #chat-send:hover  { opacity: .85; transform: scale(1.05); }
        #chat-send:active { transform: scale(.96); }
        #chat-send:disabled { opacity: .4; cursor: not-allowed; transform: none; }

        /* Mensaje de bienvenida vacío */
        .chat-empty {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 32px 16px; gap: 10px; height: 100%;
        }
        .chat-empty-icon { font-size: 36px; margin-bottom: 4px; }
        .chat-empty h4 { font-family: 'Syne', sans-serif; font-weight: 700; color: #fff; font-size: .9rem; }
        .chat-empty p  { font-size: .75rem; color: var(--text-dim); line-height: 1.6; max-width: 240px; }

        /* Markdown básico en respuestas del bot */
        .msg-bubble code {
            background: rgba(255,255,255,.08); border-radius: 4px;
            padding: 1px 5px; font-family: 'JetBrains Mono', monospace;
            font-size: .78rem; color: var(--green);
        }
        .msg-bubble pre {
            background: rgba(0,0,0,.4); border: 1px solid var(--border);
            border-radius: 6px; padding: 10px 12px; margin: 6px 0;
            overflow-x: auto; font-size: .75rem; line-height: 1.55;
        }
        .msg-bubble pre code { background: none; padding: 0; color: var(--text); }
        .msg-bubble strong { color: #fff; }
    </style>
</head>
<body>

<header class="topbar">
    <span class="topbar-logo">Pair<span>Sync</span></span>
    <div class="topbar-divider"></div>
    <div class="session-badge">
        <span class="session-label">Room</span>
        <span class="session-code">{{ $session->code }}</span>
        <button class="copy-btn" onclick="copyCode()">⧉</button>
    </div>
    <div class="topbar-spacer"></div>
    <span class="status-pill {{ $session->status === 'waiting' ? 'pill-waiting' : 'pill-active' }}" id="status-pill">
        <span class="pill-dot"></span>
        <span id="status-text">{{ $session->status === 'waiting' ? 'Waiting for partner' : 'Session active' }}</span>
    </span>
</header>

<div class="main">
    <div class="room-panel">

        <div class="role-banner {{ $myRole === 'driver' ? 'banner-driver' : ($myRole === 'navigator' ? 'banner-navigator' : 'banner-unknown') }}" id="role-banner">
            @if($myRole === 'driver')
                🧑‍💻 <strong>You are the Driver</strong> — focus on writing the code.
            @elseif($myRole === 'navigator')
                🧭 <strong>You are the Navigator</strong> — guide, review, and think ahead.
            @else
                👀 Reconnecting…
            @endif
        </div>

        <div class="participants">
            <div class="participant-card pc-driver {{ $myRole === 'driver' ? 'is-me' : '' }}" id="card-driver">
                <div class="pc-role-label">🧑‍💻 Driver</div>
                <div class="pc-avatar">🧑‍💻</div>
                <div class="pc-name {{ $session->driver ? '' : 'empty' }}" id="driver-name">
                    {{ $session->driver ?? 'waiting...' }}
                    @if($myRole === 'driver')<span class="you-tag">You</span>@endif
                </div>
                <div class="pc-sub">Writes the code</div>
            </div>
            <div class="participant-card pc-navigator {{ $myRole === 'navigator' ? 'is-me' : '' }}" id="card-navigator">
                <div class="pc-role-label">🧭 Navigator</div>
                <div class="pc-avatar">🧭</div>
                <div class="pc-name {{ $session->navigator ? '' : 'empty' }}" id="navigator-name">
                    {{ $session->navigator ?? 'waiting...' }}
                    @if($myRole === 'navigator')<span class="you-tag">You</span>@endif
                </div>
                <div class="pc-sub" id="navigator-sub">{{ $session->navigator ? 'Guides the strategy' : 'Share your code →' }}</div>
            </div>
        </div>

        <div class="info-box" id="info-box">
            @if($session->status === 'waiting')
            <div class="waiting-state">
                <div class="waiting-spinner"></div>
                <h3>Waiting for your navigator...</h3>
                <p>Share this code with your partner.</p>
                <div class="share-code-box">
                    <span class="code">{{ $session->code }}</span>
                    <button class="copy-btn" onclick="copyCode()" style="font-size:1.1rem;">⧉</button>
                </div>
            </div>
            @else
            <h3>⚡ Session in progress</h3>
            <ul class="info-list">
                <li><strong>Driver</strong> — focus on the code at hand. Type, implement, execute.</li>
                <li><strong>Navigator</strong> — watch for bugs, think ahead, suggest directions.</li>
                <li>Swap roles every 15–25 minutes to share ownership.</li>
                <li>Communicate continuously — narrate what you're doing and why.</li>
            </ul>
            @endif
        </div>

        <div class="actions" id="actions">
            @if($session->status === 'active')
            <button class="btn btn-swap" id="btn-swap" style="flex:1;" onclick="swapRoles(this)">🔄 Swap Roles</button>
            @endif
            <a href="{{ route('pair.index') }}" class="btn btn-leave">← Leave</a>
        </div>

    </div>
</div>

<!-- ══ CHATBOT ══════════════════════════════════════════════════════ -->

<!-- Botón flotante -->
<button id="chat-fab" onclick="toggleChat()" title="Ask the Android Kotlin Tutor">
    🤖
    <span class="badge" id="chat-badge"></span>
</button>

<!-- Panel -->
<div id="chat-panel">
    <div class="chat-header">
        <div class="chat-header-icon">🤖</div>
        <div class="chat-header-info">
            <div class="chat-header-name">Android Kotlin Tutor</div>
            <div class="chat-header-status">online · shared session</div>
        </div>
        <button class="chat-close" onclick="toggleChat()">✕</button>
    </div>

    <div id="chat-messages">
        <div class="chat-empty" id="chat-empty">
            <div class="chat-empty-icon">🤖</div>
            <h4>Android Kotlin Tutor</h4>
            <p>Ask me anything about Android or Kotlin. Both of you can see this conversation.</p>
        </div>
    </div>

    <div class="chat-input-area">
        <textarea
            id="chat-input"
            placeholder="Ask about Kotlin, Android, Jetpack..."
            rows="1"
            onkeydown="handleKey(event)"
            oninput="autoResize(this)"
        ></textarea>
        <button id="chat-send" onclick="sendMessage()" title="Send">➤</button>
    </div>
</div>

<div id="toast">Copied!</div>

<script>
    /* ── LangGraph ─────────────────────────────────────────────────── */
    const LG_URL   = '{{ $lgConfig["url"] }}';
    const LG_KEY   = '{{ $lgConfig["key"] }}';
    const LG_AGENT = '{{ $lgConfig["agent_id"] }}';
    const LG_HEADERS  = { 'Content-Type': 'application/json', 'X-Api-Key': LG_KEY, 'X-Auth-Scheme': 'langsmith-api-key' };

    /* ── Identidad / sala ──────────────────────────────────────────── */
    const EMBEDDED_NAME = @json($myName);
    const ROOM_CODE     = @json($session->code);
    const CSRF          = '{{ csrf_token() }}';
    const POLL_BASE     = '/room/' + ROOM_CODE + '/poll';
    const APP_HEADERS   = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF };

    const STORAGE_KEY = 'ps_name_' + ROOM_CODE;
    if (EMBEDDED_NAME) localStorage.setItem(STORAGE_KEY, EMBEDDED_NAME);
    const MY_NAME = localStorage.getItem(STORAGE_KEY) ?? '';

    /* ── Poll state ─────────────────────────────────────────────────── */
    let state = {
        driver:    @json($session->driver),
        navigator: @json($session->navigator),
        status:    @json($session->status),
        myRole:    @json($myRole),
    };

    function render(s) {
        setName('driver-name',    s.driver,    s.myRole === 'driver');
        setName('navigator-name', s.navigator, s.myRole === 'navigator');
        document.getElementById('navigator-sub').textContent =
            s.navigator ? 'Guides the strategy' : 'Share your code →';
        document.getElementById('card-driver').classList.toggle('is-me',    s.myRole === 'driver');
        document.getElementById('card-navigator').classList.toggle('is-me', s.myRole === 'navigator');
        const banner = document.getElementById('role-banner');
        if (s.myRole === 'driver') {
            banner.className = 'role-banner banner-driver';
            banner.innerHTML = '🧑‍💻 <strong>You are the Driver</strong> — focus on writing the code.';
        } else if (s.myRole === 'navigator') {
            banner.className = 'role-banner banner-navigator';
            banner.innerHTML = '🧭 <strong>You are the Navigator</strong> — guide, review, and think ahead.';
        }
        const pill = document.getElementById('status-pill');
        pill.className = 'status-pill ' + (s.status === 'active' ? 'pill-active' : 'pill-waiting');
        document.getElementById('status-text').textContent =
            s.status === 'active' ? 'Session active' : 'Waiting for partner';
        if (s.status === 'active' && state.status === 'waiting') {
            document.getElementById('info-box').innerHTML = `
                <h3>⚡ Session in progress</h3>
                <ul class="info-list">
                    <li><strong>Driver</strong> — focus on the code at hand.</li>
                    <li><strong>Navigator</strong> — watch for bugs, think ahead.</li>
                    <li>Swap roles every 15–25 minutes.</li>
                    <li>Communicate continuously.</li>
                </ul>`;
            if (!document.getElementById('btn-swap')) {
                const btn = document.createElement('button');
                btn.id = 'btn-swap'; btn.className = 'btn btn-swap'; btn.style = 'flex:1;';
                btn.textContent = '🔄 Swap Roles';
                btn.onclick = function () { swapRoles(this); };
                document.getElementById('actions').insertBefore(btn, document.getElementById('actions').firstChild);
            }
        }
        if (s.thread_id && !threadId) threadId = s.thread_id;

        // Sincronizar historial si el otro dispositivo añadió mensajes
        if (Array.isArray(s.chat_history) && s.chat_history.length > chatHistory.length) {
            const newMessages = s.chat_history.slice(chatHistory.length);
            chatHistory = s.chat_history;
            hideEmpty();
            newMessages.forEach(m => appendMessageDOM(m.role, m.sender, m.text));
            if (!chatOpen) {
                const foreign = newMessages.filter(m => m.sender !== MY_NAME);
                if (foreign.length > 0) {
                    unread += foreign.length;
                    const badge = document.getElementById('chat-badge');
                    badge.textContent = unread > 9 ? '9+' : unread;
                    badge.classList.add('show');
                }
            }
        }

        state = { ...s };
    }

    function setName(id, name, isMe) {
        const el = document.getElementById(id);
        el.className = 'pc-name' + (name ? '' : ' empty');
        el.textContent = name ?? 'waiting...';
        if (isMe && name) { const t = document.createElement('span'); t.className = 'you-tag'; t.textContent = 'You'; el.appendChild(t); }
    }

    async function poll() {
        try {
            const res = await fetch(POLL_BASE + '?name=' + encodeURIComponent(MY_NAME));
            if (res.ok) render(await res.json());
        } catch (e) { console.warn('[poll]', e); }
    }
    poll(); setInterval(poll, 2000);

    async function swapRoles(btn) {
        btn.disabled = true; btn.textContent = '⏳ Swapping...';
        try {
            await fetch('/room/' + ROOM_CODE + '/swap', { method: 'POST', headers: APP_HEADERS });
            await poll();
        } catch (e) { console.warn('[swap]', e); }
        finally { btn.disabled = false; btn.textContent = '🔄 Swap Roles'; }
    }

    function copyCode() {
        navigator.clipboard.writeText(ROOM_CODE).then(() => {
            const t = document.getElementById('toast'); t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2200);
        });
    }

    /* ══════════════════════════════════════════════════════════════
       CHAT
    ══════════════════════════════════════════════════════════════ */
    let threadId  = null;
    let chatOpen  = false;
    let unread    = 0;
    let isSending = false;
    let chatHistory = [];   // [{ role, sender, text }]

    /* ── Persistencia: guardar historial en la BD ─────────────────── */
    async function persistHistory() {
        try {
            const res = await fetch('/room/' + ROOM_CODE + '/chat', {
                method: 'POST',
                headers: APP_HEADERS,
                body: JSON.stringify({ history: chatHistory }),
            });
            if (res.ok) {
                console.log('[chat persist] ✅ saved', chatHistory.length, 'messages');
            } else {
                const body = await res.text();
                console.error('[chat persist] ❌ HTTP', res.status, body);
            }
        } catch (e) { console.error('[chat persist] ❌ exception', e); }
    }

    async function persistThread(tid) {
        try {
            await fetch('/room/' + ROOM_CODE + '/thread', {
                method: 'POST',
                headers: APP_HEADERS,
                body: JSON.stringify({ thread_id: tid }),
            });
        } catch (e) { console.warn('[thread persist]', e); }
    }

    /* ── Inicializar: cargar historial y thread_id desde la BD ─────── */
    async function initChat() {
        try {
            console.log('[initChat] loading from /room/' + ROOM_CODE + '/chat');
            const res = await fetch('/room/' + ROOM_CODE + '/chat');
            if (!res.ok) { console.error('[initChat] ❌ HTTP', res.status, await res.text()); return; }
            const data = await res.json();
            console.log('[initChat] ✅ received thread_id:', data.thread_id, 'messages:', data.history?.length ?? 0);
            if (data.thread_id) threadId = data.thread_id;
            if (Array.isArray(data.history) && data.history.length > 0) {
                chatHistory = data.history;
                hideEmpty();
                chatHistory.forEach(m => appendMessageDOM(m.role, m.sender, m.text));
                scrollToBottom();
            }
        } catch (e) { console.error('[initChat] ❌ exception', e); }
    }
    initChat();

    /* ── Thread de LangGraph ───────────────────────────────────────── */
    async function getOrCreateThread() {
        if (threadId) return threadId;
        const res = await fetch(LG_URL + '/threads', {
            method: 'POST', headers: LG_HEADERS, body: JSON.stringify({}),
        });
        if (!res.ok) throw new Error('Could not create thread: ' + res.status);
        const data  = await res.json();
        threadId = data.thread_id;
        persistThread(threadId);   // fire-and-forget, no bloqueamos
        return threadId;
    }

    /* ── Enviar mensaje ────────────────────────────────────────────── */
    async function sendMessage() {
        const input = document.getElementById('chat-input');
        const text  = input.value.trim();
        if (!text || isSending) return;

        isSending = true;
        input.value = ''; autoResize(input);
        document.getElementById('chat-send').disabled = true;
        hideEmpty();

        // 1. Mostrar y persistir mensaje del usuario INMEDIATAMENTE
        const userMsg = { role: 'user', sender: MY_NAME || 'You', text };
        chatHistory.push(userMsg);
        appendMessageDOM('user', userMsg.sender, text);
        persistHistory();   // guardar ya, sin await — el historial del user no se pierde

        // 2. Typing indicator — visible desde ya, antes del fetch
        const typingId = appendTyping();
        let bubbleEl   = null;
        let botText    = '';

        try {
            const tid = await getOrCreateThread();

            // 3. Abrir stream — el browser recibe el 200 OK con headers
            //    pero el body llega en chunks. El typing se mantiene visible
            //    hasta que llega el primer chunk con texto.
            const res = await fetch(`${LG_URL}/threads/${tid}/runs/stream`, {
                method: 'POST',
                headers: LG_HEADERS,
                body: JSON.stringify({
                    assistant_id: LG_AGENT,
                    input: { messages: [{ type: 'human', content: text }] },
                    stream_mode: 'messages',
                }),
            });

            if (!res.ok) {
                removeTyping(typingId);
                const errMsg = `⚠️ Error ${res.status}. Please try again.`;
                chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: errMsg });
                appendMessageDOM('bot', 'Android Kotlin Tutor', errMsg);
                persistHistory();
                return;
            }

            // 4. Leer el stream SSE chunk a chunk
            const reader  = res.body.getReader();
            const decoder = new TextDecoder();
            let   buffer  = '';

            outer: while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop();   // línea incompleta queda en buffer

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    const raw = line.slice(6).trim();
                    if (!raw || raw === '[DONE]') continue;

                    let parsed;
                    try { parsed = JSON.parse(raw); } catch (_) { continue; }

                    const delta = extractAIText(parsed);
                    if (!delta) continue;

                    // Primer delta → quitar typing y crear burbuja del bot
                    if (!bubbleEl) {
                        removeTyping(typingId);
                        bubbleEl = appendMessageDOM('bot', 'Android Kotlin Tutor', '');
                    }
                    botText += delta;
                    bubbleEl.innerHTML = formatMarkdown(botText);
                    scrollToBottom();
                }
            }

            // 5. Si el stream terminó sin texto, fallback al estado del thread
            if (!botText) {
                removeTyping(typingId);
                botText = await fallbackSync(tid);
                if (!bubbleEl) bubbleEl = appendMessageDOM('bot', 'Android Kotlin Tutor', '');
                bubbleEl.innerHTML = formatMarkdown(botText);
                scrollToBottom();
            } else {
                removeTyping(typingId);   // por si no se ejecutó antes
            }

            // 6. Persistir respuesta del bot
            if (botText) {
                chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: botText });
                await persistHistory();   // aquí sí esperamos para asegurar guardado
            }

            // 7. Badge de no leídos
            if (!chatOpen) {
                unread++;
                const badge = document.getElementById('chat-badge');
                badge.textContent = unread > 9 ? '9+' : unread;
                badge.classList.add('show');
            }

        } catch (err) {
            removeTyping(typingId);
            const errMsg = '⚠️ ' + err.message;
            chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: errMsg });
            appendMessageDOM('bot', 'Android Kotlin Tutor', errMsg);
            persistHistory();
            console.error('[chat]', err);
        } finally {
            isSending = false;
            document.getElementById('chat-send').disabled = false;
            document.getElementById('chat-input').focus();
        }
    }

    /* ── Fallback: leer último mensaje del estado del thread ─────── */
    async function fallbackSync(tid) {
        try {
            const res = await fetch(`${LG_URL}/threads/${tid}/state`, { headers: LG_HEADERS });
            if (!res.ok) return '(no response)';
            const data     = await res.json();
            const messages = data?.values?.messages ?? data?.messages ?? [];
            for (let i = messages.length - 1; i >= 0; i--) {
                const m = messages[i];
                if (m.type === 'ai' || m.type === 'AIMessage') {
                    return extractFromMessage(m) || '(empty response)';
                }
            }
        } catch (e) { console.warn('[fallback]', e); }
        return '(no response)';
    }

    /* ── Extraer texto AI de cualquier formato LangGraph ─────────── */
    function extractAIText(p) {
        if (Array.isArray(p) && p.length >= 2)          return extractFromMessage(p[1]);
        if (p?.type === 'AIMessageChunk' || p?.type === 'AIMessage') return extractFromMessage(p);
        if (p?.messages)                                 return extractFromMessage(p.messages[p.messages.length - 1]);
        if (p?.agent?.messages)                          return extractFromMessage(p.agent.messages[p.agent.messages.length - 1]);
        if (p?.data)                                     return extractAIText(p.data);
        return null;
    }

    function extractFromMessage(msg) {
        if (!msg) return null;
        if (msg.type === 'human' || msg.type === 'HumanMessage') return null;
        const c = msg.content ?? msg.text ?? null;
        if (!c) return null;
        if (typeof c === 'string') return c;
        if (Array.isArray(c)) return c.map(x => (typeof x === 'string' ? x : (x?.text ?? ''))).join('');
        return null;
    }

    /* ── UI helpers ──────────────────────────────────────────────── */
    function toggleChat() {
        chatOpen = !chatOpen;
        document.getElementById('chat-panel').classList.toggle('open', chatOpen);
        if (chatOpen) {
            unread = 0;
            const badge = document.getElementById('chat-badge');
            badge.textContent = ''; badge.classList.remove('show');
            setTimeout(() => { document.getElementById('chat-input').focus(); scrollToBottom(); }, 280);
        }
    }

    function hideEmpty() {
        const e = document.getElementById('chat-empty');
        if (e) e.remove();
    }

    function appendMessageDOM(role, sender, text) {
        const msgs   = document.getElementById('chat-messages');
        const wrap   = document.createElement('div');
        wrap.className = 'msg ' + role;
        const meta   = document.createElement('div');
        meta.className = 'msg-meta';
        const sp     = document.createElement('span');
        sp.className = 'sender ' + (role === 'bot' ? 'bot-name' : 'user-name');
        sp.textContent = sender;
        meta.appendChild(sp);
        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.innerHTML = formatMarkdown(text);
        wrap.appendChild(meta);
        wrap.appendChild(bubble);
        msgs.appendChild(wrap);
        scrollToBottom();
        return bubble;
    }

    function appendTyping() {
        const msgs = document.getElementById('chat-messages');
        const div  = document.createElement('div');
        div.className = 'msg bot';
        div.id = 'typing-' + Date.now();
        div.innerHTML = '<div class="msg-meta"><span class="sender bot-name">Android Kotlin Tutor</span></div>'
            + '<div class="typing-bubble"><span></span><span></span><span></span></div>';
        msgs.appendChild(div);
        scrollToBottom();
        return div.id;
    }

    function removeTyping(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        const msgs = document.getElementById('chat-messages');
        msgs.scrollTop = msgs.scrollHeight;
    }

    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 100) + 'px';
    }

    function handleKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    }

    function formatMarkdown(text) {
        return text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/```([\s\S]*?)```/g, (_, c) => `<pre><code>${c.trim()}</code></pre>`)
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }
</script>

</body>
</html>