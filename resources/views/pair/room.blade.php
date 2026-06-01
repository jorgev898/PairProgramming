<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $session->code }} — PairSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;800&display=swap"
        rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @include('pair.partials.room-styles')
</head>

<body>

    {{-- ── TOPBAR ── --}}
    <header class="topbar">
        <a href="{{ route('pair.index') }}" class="topbar-logo">Pair<span>Sync</span></a>
        <div class="topbar-divider"></div>
        <div class="session-badge">
            <span class="session-label">{{ __('Room') }}</span>
            <span class="session-code">{{ $session->code }}</span>
            <button class="copy-btn" onclick="copyCode()" title="{{ __('Copy code') }}">⧉</button>
        </div>
        <div class="topbar-divider"></div>
        @if($session->lesson)
            <button class="lesson-modal-btn" onclick="toggleLessonModal()" title="{{ __('View Lesson') }}" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); color: var(--primary); padding: 6px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                📖 {{ __('Reto:') }} {{ $session->lesson->title }}
            </button>
            <div class="topbar-divider"></div>
        @endif
        <div id="role-indicator"
            class="role-indicator {{ $myRole === 'driver' ? 'is-driver' : ($myRole === 'navigator' ? 'is-navigator' : '') }}">
            @if($myRole === 'driver') 🧑‍💻 {{ __('Driver') }} @elseif($myRole === 'navigator') 🧭 {{ __('Navigator') }} @else 👀 ... @endif
        </div>
        <div class="topbar-spacer"></div>
        <div style="display:flex;gap:8px;align-items:center;margin-right:8px">
            <a href="{{ route('lang.switch', 'en') }}"
                style="color:{{ app()->getLocale() === 'en' ? 'var(--green)' : 'var(--text-dim)' }};text-decoration:none;font-weight:bold;font-size:.75rem">EN</a>
            <span style="color:var(--border)">/</span>
            <a href="{{ route('lang.switch', 'es') }}"
                style="color:{{ app()->getLocale() === 'es' ? 'var(--green)' : 'var(--text-dim)' }};text-decoration:none;font-weight:bold;font-size:.75rem">ES</a>
        </div>
        <span class="status-pill {{ $session->status === 'waiting' ? 'pill-waiting' : 'pill-active' }}"
            id="status-pill">
            <span class="pill-dot"></span>
            <span
                id="status-text">{{ $session->status === 'waiting' ? __('Waiting for partner') : __('Session active') }}</span>
        </span>
    </header>

    {{-- ── IDE LAYOUT ── --}}
    <div class="ide-container">

        {{-- ── EDITOR AREA (split-view capable) ── --}}
        <div class="editor-area" id="editor-area">

            {{-- ── LEFT: Editor + Output ── --}}
            <div class="editor-main">
                <div class="editor-toolbar">
                    <span class="lang-badge">Kotlin</span>
                    <button class="btn-run" id="btn-run" onclick="runCode()">▶ {{ __('Run') }}</button>
                    <button class="btn-preview" id="btn-preview" onclick="togglePreview()">👁 {{ __('Preview') }}</button>
                    <span class="auto-toggle" id="auto-toggle" onclick="toggleAutoPreview()">
                        <span class="dot"></span> {{ __('Auto') }}
                    </span>
                    <span class="save-status" id="save-status">
                        @if($myRole === 'driver') ✓ {{ __('Ready') }} @else 👁 {{ __('Read-only') }} @endif
                    </span>
                </div>

                <div class="editor-wrapper">
                    @if($myRole !== 'driver')
                        <div class="readonly-badge" id="readonly-badge">👁 {{ __('Read-only · Navigator') }}</div>
                    @else
                        <div class="readonly-badge" id="readonly-badge" style="display:none">👁
                            {{ __('Read-only · Navigator') }}</div>
                    @endif
                    <div id="monaco-container"></div>
                </div>

                <div class="output-panel">
                    <div class="output-header">
                        <h4>▸ {{ __('Output') }}</h4>
                        <button class="clear-btn" onclick="clearOutput()">{{ __('Clear') }}</button>
                    </div>
                    <div id="output-content"><span class="output-info">{{ __('Ready to run code...') }}</span></div>
                </div>
            </div>

            {{-- ── RIGHT: Preview Panel (hidden until toggled) ── --}}
            <div class="preview-panel" id="preview-panel">
                <div class="preview-header">
                    <h4>📱 {{ __('Preview') }}</h4>
                    <span class="preview-status" id="preview-status">{{ __('Ready') }}</span>
                </div>
                <div class="preview-content" id="preview-content">
                    <div style="text-align:center;color:var(--text-lo);font-size:.75rem;padding:20px">
                        <p style="font-size:2rem;margin-bottom:8px">📱</p>
                        <p>{{ __('Write Compose code and click Preview') }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── SIDEBAR ── --}}
        <aside class="sidebar">
            <div class="sidebar-section">
                <h3>👥 {{ __('Participants') }}</h3>
                <div class="participant p-driver {{ $myRole === 'driver' ? 'is-me' : '' }}" id="p-driver">
                    <div class="participant-avatar">🧑‍💻</div>
                    <div class="participant-info">
                        <div class="participant-role">{{ __('Driver') }}</div>
                        <div class="participant-name {{ $session->driver ? '' : 'empty' }}">
                            {{ $session->driver ?? __('waiting...') }}
                            @if($myRole === 'driver')<span class="you-tag">{{ __('You') }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="participant p-navigator {{ $myRole === 'navigator' ? 'is-me' : '' }}" id="p-navigator">
                    <div class="participant-avatar">🧭</div>
                    <div class="participant-info">
                        <div class="participant-role">{{ __('Navigator') }}</div>
                        <div class="participant-name {{ $session->navigator ? '' : 'empty' }}">
                            {{ $session->navigator ?? __('waiting...') }}
                            @if($myRole === 'navigator')<span class="you-tag">{{ __('You') }}</span>@endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar-section pchat-container">
                <h3>💬 {{ __('Team Chat') }}</h3>
                <div id="pchat-messages" class="pchat-messages">
                    <div class="pchat-empty" id="pchat-empty">{{ __('Say hello to your partner!') }}</div>
                </div>
                <div class="pchat-input-area">
                    <input type="text" id="pchat-input" placeholder="{{ __('Type a message...') }}"
                        onkeydown="handlePChatKey(event)">
                    <button onclick="sendPChat()" id="pchat-send" title="{{ __('Send') }}">➤</button>
                </div>
            </div>

            {{-- ── AI TUTOR CHAT ── --}}
            <div class="sidebar-section ai-chat-container" id="chat-panel">
                <div class="ai-chat-header">
                    <h3>🤖 {{ __('Kotlin Tutor') }}</h3>
                </div>
                <div id="chat-messages">
                    <div class="chat-empty" id="chat-empty">
                        <p>{{ __('Ask me anything about Android or Kotlin.') }}</p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <textarea id="chat-input" placeholder="{{ __('Ask about Kotlin...') }}" rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
                    <button id="chat-send" onclick="sendMessage()" title="{{ __('Send') }}">➤</button>
                </div>
            </div>

            <div class="sidebar-actions">
                <button class="btn btn-swap" id="btn-swap" onclick="swapRoles(this)"
                    style="{{ $session->status !== 'active' ? 'display:none' : '' }}">🔄 {{ __('Swap Roles') }}</button>
                <a href="{{ route('challenges.index') }}" class="btn btn-leave">← {{ __('Leave') }}</a>
            </div>
        </aside>
    </div>



    <div id="toast">{{ __('Copied!') }}</div>

    {{-- ── MONACO LOADER ── --}}
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>

    @include('pair.partials.room-preview')
    @include('pair.partials.lesson-modal')
    @include('pair.partials.room-scripts')

</body>

</html>