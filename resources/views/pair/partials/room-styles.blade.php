<style>
    :root {
        --bg: #0a0c0f;
        --border: #1e2530;
        --border-hi: #2e3d50;
        --green: #00e5a0;
        --green-dim: #00b87a;
        --blue: #4da6ff;
        --amber: #ffb347;
        --purple: #b47eff;
        --text: #c8d6e8;
        --text-dim: #5a6a7e;
        --text-lo: #2e3d50;
        --card-bg: #0d1117;
        --radius: 12px
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0
    }

    body {
        font-family: 'JetBrains Mono', monospace;
        background: var(--bg);
        color: var(--text);
        height: 100vh;
        overflow: hidden;
        display: flex;
        flex-direction: column
    }

    /* TOPBAR */
    .topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        background: rgba(10, 12, 15, .95);
        border-bottom: 1px solid var(--border);
        backdrop-filter: blur(12px);
        flex-shrink: 0;
        z-index: 10;
        flex-wrap: wrap
    }

    .topbar-logo {
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 1rem;
        color: #fff;
        text-decoration: none
    }

    .topbar-logo span {
        color: var(--green)
    }

    .topbar-divider {
        width: 1px;
        height: 18px;
        background: var(--border)
    }

    .session-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, .04);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 4px 10px
    }

    .session-label {
        font-size: .62rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--text-dim)
    }

    .session-code {
        font-size: .9rem;
        font-weight: 700;
        letter-spacing: .2em;
        color: var(--green)
    }

    .copy-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-dim);
        font-size: .85rem;
        transition: color .2s
    }

    .copy-btn:hover {
        color: var(--green)
    }

    .topbar-spacer {
        flex: 1
    }

    .role-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .72rem;
        padding: 4px 10px;
        border-radius: 6px
    }

    .role-indicator.is-driver {
        background: rgba(0, 229, 160, .1);
        color: var(--green);
        border: 1px solid rgba(0, 229, 160, .25)
    }

    .role-indicator.is-navigator {
        background: rgba(77, 166, 255, .1);
        color: var(--blue);
        border: 1px solid rgba(77, 166, 255, .25)
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .68rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 16px
    }

    .pill-waiting {
        background: rgba(255, 179, 71, .1);
        color: var(--amber);
        border: 1px solid rgba(255, 179, 71, .25)
    }

    .pill-active {
        background: rgba(0, 229, 160, .1);
        color: var(--green);
        border: 1px solid rgba(0, 229, 160, .25)
    }

    .pill-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        animation: pulse 1.8s ease-in-out infinite
    }

    .pill-waiting .pill-dot {
        background: var(--amber)
    }

    .pill-active .pill-dot {
        background: var(--green)
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
            transform: scale(1)
        }

        50% {
            opacity: .4;
            transform: scale(.65)
        }
    }

    /* IDE LAYOUT */
    .ide-container {
        flex: 1;
        display: flex;
        overflow: hidden
    }

    .editor-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0
    }

    .editor-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .sidebar {
        width: 280px;
        border-left: 1px solid var(--border);
        background: var(--card-bg);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        overflow-y: auto
    }

    @media(max-width:768px) {
        .sidebar {
            display: none
        }
    }

    /* EDITOR TOOLBAR */
    .editor-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: rgba(13, 17, 23, .95);
        border-bottom: 1px solid var(--border);
        flex-shrink: 0
    }

    .lang-badge {
        font-size: .72rem;
        background: rgba(180, 126, 255, .12);
        color: var(--purple);
        border: 1px solid rgba(180, 126, 255, .25);
        border-radius: 5px;
        padding: 3px 10px;
        font-weight: 600
    }

    .save-status {
        font-size: .68rem;
        color: var(--text-dim);
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 5px
    }

    .save-status.saved {
        color: var(--green)
    }

    .save-status.saving {
        color: var(--amber)
    }

    .btn-run {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        background: linear-gradient(135deg, var(--green), var(--green-dim));
        color: #001a0d;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .78rem;
        cursor: pointer;
        transition: opacity .2s, transform .15s
    }

    .btn-run:hover {
        opacity: .88;
        transform: translateY(-1px)
    }

    .btn-run:active {
        transform: translateY(0)
    }

    .btn-run:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none
    }

    /* MONACO CONTAINER */
    #monaco-container {
        flex: 1;
        min-height: 0;
        height: 100%;
        width: 100%
    }

    /* OUTPUT PANEL */
    .output-panel {
        height: 180px;
        border-top: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        background: var(--card-bg)
    }

    .output-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(255, 255, 255, .02);
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        flex-shrink: 0
    }

    .output-header h4 {
        font-size: .72rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--text-dim);
        font-weight: 600
    }

    .output-header .clear-btn {
        margin-left: auto;
        background: none;
        border: none;
        color: var(--text-dim);
        font-size: .7rem;
        cursor: pointer
    }

    .output-header .clear-btn:hover {
        color: #fff
    }

    #output-content {
        flex: 1;
        overflow-y: auto;
        padding: 10px 16px;
        font-size: .78rem;
        line-height: 1.6;
        white-space: pre-wrap;
        color: var(--text)
    }

    #output-content::-webkit-scrollbar {
        width: 4px
    }

    #output-content::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 2px
    }

    .output-error {
        color: #ff6b6b
    }

    .output-success {
        color: var(--green)
    }

    .output-info {
        color: var(--text-dim);
        font-style: italic
    }

    /* SIDEBAR */
    .sidebar-section {
        padding: 16px;
        border-bottom: 1px solid var(--border)
    }

    .sidebar-section h3 {
        font-family: 'Syne', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 12px;
        letter-spacing: .04em
    }

    .participant {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: rgba(255, 255, 255, .02);
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 8px;
        transition: border-color .3s
    }

    .participant.is-me {
        border-color: rgba(0, 229, 160, .3)
    }

    .participant-avatar {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0
    }

    .participant.p-driver .participant-avatar {
        background: rgba(0, 229, 160, .1);
        border: 1px solid rgba(0, 229, 160, .2)
    }

    .participant.p-navigator .participant-avatar {
        background: rgba(77, 166, 255, .1);
        border: 1px solid rgba(77, 166, 255, .2)
    }

    .participant-info {
        flex: 1;
        min-width: 0
    }

    .participant-role {
        font-size: .6rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        font-weight: 600
    }

    .p-driver .participant-role {
        color: var(--green)
    }

    .p-navigator .participant-role {
        color: var(--blue)
    }

    .participant-name {
        font-size: .8rem;
        color: #fff;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .participant-name.empty {
        color: var(--text-lo);
        font-weight: 400;
        font-size: .75rem
    }

    .you-tag {
        display: inline-block;
        font-size: .55rem;
        background: rgba(0, 229, 160, .12);
        color: var(--green);
        border: 1px solid rgba(0, 229, 160, .25);
        border-radius: 3px;
        padding: 1px 5px;
        margin-left: 6px
    }

    /* SIDEBAR ACTIONS */
    .sidebar-actions {
        padding: 16px;
        margin-top: auto
    }

    .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .82rem;
        cursor: pointer;
        transition: opacity .2s, transform .15s;
        margin-bottom: 8px;
        text-decoration: none
    }

    .btn:hover {
        opacity: .85;
        transform: translateY(-1px)
    }

    .btn:active {
        transform: translateY(0)
    }

    .btn-swap {
        background: linear-gradient(135deg, var(--amber), #e6952e);
        color: #1a0d00
    }

    .btn-leave {
        background: rgba(255, 255, 255, .06);
        border: 1px solid var(--border);
        color: var(--text-dim)
    }

    /* TOAST */
    #toast {
        position: fixed;
        bottom: 28px;
        left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: var(--green);
        color: #001a0d;
        font-weight: 700;
        font-size: .78rem;
        padding: 8px 18px;
        border-radius: 6px;
        transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
        z-index: 999;
        white-space: nowrap
    }

    #toast.show {
        transform: translateX(-50%) translateY(0)
    }

    /* CHAT PANEL (Sidebar integration) */
    #chat-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        max-height: none;
        padding: 16px;
        border-bottom: 1px solid var(--border);
        overflow: hidden;
    }

    .ai-chat-header {
        margin-bottom: 8px;
    }

    .ai-chat-header h3 {
        font-family: 'Syne', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        color: var(--purple);
        margin-bottom: 0;
        letter-spacing: .04em;
    }



    #chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scroll-behavior: smooth
    }

    #chat-messages::-webkit-scrollbar {
        width: 4px
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 2px
    }

    .msg {
        display: flex;
        flex-direction: column;
        gap: 3px;
        animation: msgIn .2s ease
    }

    @keyframes msgIn {
        from {
            opacity: 0;
            transform: translateY(6px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .msg-meta {
        font-size: .6rem;
        color: var(--text-dim)
    }

    .msg-meta .sender {
        font-weight: 700
    }

    .msg-meta .bot-name {
        color: var(--purple)
    }

    .msg-meta .user-name {
        color: var(--green)
    }

    .msg-bubble {
        padding: 8px 11px;
        border-radius: 8px;
        font-size: .76rem;
        line-height: 1.6;
        max-width: 90%;
        word-break: break-word
    }

    .msg.bot .msg-bubble {
        background: rgba(180, 126, 255, .1);
        border: 1px solid rgba(180, 126, 255, .2);
        color: var(--text);
        border-radius: 2px 8px 8px 8px
    }

    .msg.user .msg-bubble {
        background: rgba(0, 229, 160, .08);
        border: 1px solid rgba(0, 229, 160, .18);
        color: var(--text);
        border-radius: 8px 2px 8px 8px;
        align-self: flex-end
    }

    .msg.user {
        align-items: flex-end
    }

    .typing-bubble {
        display: flex;
        gap: 4px;
        align-items: center;
        padding: 8px 12px;
        background: rgba(180, 126, 255, .1);
        border: 1px solid rgba(180, 126, 255, .2);
        border-radius: 2px 8px 8px 8px;
        width: fit-content
    }

    .typing-bubble span {
        width: 5px;
        height: 5px;
        background: var(--purple);
        border-radius: 50%;
        animation: bounce 1.2s infinite
    }

    .typing-bubble span:nth-child(2) {
        animation-delay: .2s
    }

    .typing-bubble span:nth-child(3) {
        animation-delay: .4s
    }

    @keyframes bounce {

        0%,
        80%,
        100% {
            transform: translateY(0)
        }

        40% {
            transform: translateY(-5px)
        }
    }

    .chat-input-area {
        padding: 10px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 6px;
        align-items: flex-end;
        flex-shrink: 0
    }

    #chat-input {
        flex: 1;
        background: rgba(255, 255, 255, .04);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 10px;
        font-family: 'JetBrains Mono', monospace;
        font-size: .76rem;
        color: #fff;
        resize: none;
        outline: none;
        max-height: 80px;
        min-height: 34px;
        transition: border-color .2s;
        line-height: 1.5
    }

    #chat-input:focus {
        border-color: var(--purple)
    }

    #chat-input::placeholder {
        color: var(--text-lo)
    }

    #chat-send {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--purple), #8040e0);
        border: none;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: opacity .2s
    }

    #chat-send:disabled {
        opacity: .4;
        cursor: not-allowed
    }

    .chat-empty p {
        font-size: .7rem;
        color: var(--text-dim);
        line-height: 1.6;
        max-width: 220px;
        margin-top: 20px;
        text-align: center;
    }

    .msg-bubble code {
        background: rgba(255, 255, 255, .08);
        border-radius: 3px;
        padding: 1px 4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: .74rem;
        color: var(--green)
    }

    .msg-bubble pre {
        background: rgba(0, 0, 0, .4);
        border: 1px solid var(--border);
        border-radius: 5px;
        padding: 8px 10px;
        margin: 4px 0;
        overflow-x: auto;
        font-size: .72rem;
        line-height: 1.5
    }

    .msg-bubble pre code {
        background: none;
        padding: 0;
        color: var(--text)
    }

    .msg-bubble strong {
        color: #fff
    }

    /* READ-ONLY OVERLAY */
    .readonly-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 5;
        font-size: .65rem;
        background: rgba(77, 166, 255, .15);
        color: var(--blue);
        border: 1px solid rgba(77, 166, 255, .25);
        border-radius: 4px;
        padding: 2px 8px;
        pointer-events: none
    }

    .editor-wrapper {
        position: relative;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden
    }

    /* REMOTE CURSOR DECORATIONS */
    .remote-cursor-driver {
        background: rgba(0, 229, 160, .25);
        border-left: 2px solid #00e5a0
    }

    .remote-cursor-navigator {
        background: rgba(77, 166, 255, .25);
        border-left: 2px solid #4da6ff
    }

    .remote-cursor-line-driver {
        border-left: 2px solid #00e5a0
    }

    .remote-cursor-line-navigator {
        border-left: 2px solid #4da6ff
    }

    .remote-cursor-label-driver {
        color: #00e5a0;
        font-size: 10px;
        font-weight: 700;
        font-family: 'Syne', sans-serif;
        background: rgba(0, 229, 160, .15);
        border: 1px solid rgba(0, 229, 160, .3);
        border-radius: 3px;
        padding: 0 4px;
        margin-left: 4px;
        pointer-events: none;
        opacity: .9
    }

    .remote-cursor-label-navigator {
        color: #4da6ff;
        font-size: 10px;
        font-weight: 700;
        font-family: 'Syne', sans-serif;
        background: rgba(77, 166, 255, .15);
        border: 1px solid rgba(77, 166, 255, .3);
        border-radius: 3px;
        padding: 0 4px;
        margin-left: 4px;
        pointer-events: none;
        opacity: .9
    }

    /* FLOATING MOUSE CURSOR */
    .remote-mouse-cursor {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 900;
        pointer-events: none;
        display: none;
        will-change: transform
    }

    .remote-mouse-cursor .cursor-arrow {
        filter: drop-shadow(0 1px 3px rgba(0, 0, 0, .5))
    }

    .remote-mouse-driver {
        color: #00e5a0
    }

    .remote-mouse-navigator {
        color: #4da6ff
    }

    .cursor-name-tag {
        position: absolute;
        top: 18px;
        left: 12px;
        font-size: 10px;
        font-weight: 700;
        font-family: 'Syne', sans-serif;
        padding: 2px 6px;
        border-radius: 3px;
        white-space: nowrap;
        pointer-events: none
    }

    .remote-mouse-driver .cursor-name-tag {
        background: rgba(0, 229, 160, .2);
        color: #00e5a0;
        border: 1px solid rgba(0, 229, 160, .35)
    }

    .remote-mouse-navigator .cursor-name-tag {
        background: rgba(77, 166, 255, .2);
        color: #4da6ff;
        border: 1px solid rgba(77, 166, 255, .35)
    }

    /* PARTICIPANT CHAT */
    .pchat-container {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 200px;
        max-height: 300px;
    }

    .pchat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pchat-messages::-webkit-scrollbar {
        width: 3px
    }

    .pchat-messages::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 2px
    }

    .pchat-empty {
        font-size: .7rem;
        color: var(--text-lo);
        text-align: center;
        padding: 12px;
        font-style: italic;
    }

    .pchat-msg {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: .75rem;
        line-height: 1.4;
    }

    .pchat-meta {
        font-size: .65rem;
        font-weight: 700;
        color: var(--text-dim);
    }

    .pchat-meta.is-me {
        color: var(--green);
    }

    .pchat-bubble {
        background: rgba(255, 255, 255, .03);
        border: 1px solid var(--border);
        padding: 6px 10px;
        border-radius: 6px;
        color: var(--text);
        word-break: break-word;
    }

    .pchat-msg.is-me .pchat-bubble {
        background: rgba(0, 229, 160, .06);
        border-color: rgba(0, 229, 160, .15);
    }

    .pchat-input-area {
        display: flex;
        gap: 6px;
        margin-top: 8px;
    }

    #pchat-input {
        flex: 1;
        background: rgba(255, 255, 255, .04);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 6px 10px;
        font-family: 'Syne', sans-serif;
        font-size: .75rem;
        color: #fff;
        outline: none;
        transition: border-color .2s;
    }

    #pchat-input:focus {
        border-color: var(--green);
    }

    #pchat-send {
        background: rgba(255, 255, 255, .05);
        border: 1px solid var(--border);
        color: var(--text-dim);
        border-radius: 6px;
        padding: 0 10px;
        cursor: pointer;
        font-size: .7rem;
        transition: all .2s;
    }

    #pchat-send:hover {
        background: rgba(0, 229, 160, .1);
        color: var(--green);
        border-color: rgba(0, 229, 160, .25);
    }

    #pchat-send:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    /* ══════════════════════════════════════════
       PREVIEW PANEL — SPLIT VIEW
    ══════════════════════════════════════════ */
    .editor-area.split-view {
        flex-direction: row;
    }
    .preview-panel {
        display: none;
        width: 50%;
        border-left: 1px solid var(--border);
        background: #080a0e;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
    }
    .editor-area.split-view .preview-panel {
        display: flex;
    }
    .preview-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: rgba(255,255,255,.02);
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
    }
    .preview-header h4 {
        font-size: .72rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--purple);
        font-weight: 600;
    }
    .preview-status {
        margin-left: auto;
        font-size: .65rem;
        color: var(--text-dim);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .preview-status.compiling { color: var(--amber); }
    .preview-status.ready { color: var(--green); }
    .preview-status.error { color: #ff6b6b; }
    .preview-content {
        flex: 1;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    /* Preview Toolbar Buttons */
    .btn-preview {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border: 1px solid rgba(180,126,255,.3);
        border-radius: 6px;
        background: rgba(180,126,255,.1);
        color: var(--purple);
        font-family: 'Syne', sans-serif;
        font-weight: 700;
        font-size: .75rem;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-preview:hover { background: rgba(180,126,255,.2); }
    .btn-preview.active {
        background: linear-gradient(135deg, var(--purple), #8040e0);
        color: #fff;
        border-color: transparent;
    }
    .auto-toggle {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: .65rem;
        color: var(--text-dim);
        cursor: pointer;
        user-select: none;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all .2s;
    }
    .auto-toggle:hover { color: var(--text); }
    .auto-toggle.on { color: var(--green); }
    .auto-toggle .dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--text-lo);
        transition: all .2s;
    }
    .auto-toggle.on .dot { background: var(--green); box-shadow: 0 0 6px var(--green); }

    /* ══════════════════════════════════════════
       PHONE FRAME
    ══════════════════════════════════════════ */
    .phone-frame {
        width: 280px;
        min-height: 480px;
        max-height: 580px;
        background: #1a1a2e;
        border-radius: 28px;
        border: 3px solid #2a2a40;
        box-shadow: 0 8px 32px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.05), inset 0 1px 0 rgba(255,255,255,.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
        flex-shrink: 0;
    }
    .phone-statusbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 18px;
        font-size: 10px;
        color: rgba(255,255,255,.7);
        background: #1a1a2e;
        flex-shrink: 0;
    }
    .phone-statusbar-icons { font-size: 9px; letter-spacing: 3px; }
    .phone-screen {
        flex: 1;
        background: #fafafa;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
    }
    .phone-screen::-webkit-scrollbar { width: 3px; }
    .phone-screen::-webkit-scrollbar-thumb { background: #ccc; border-radius: 2px; }
    .phone-navbar {
        display: flex;
        justify-content: space-around;
        align-items: center;
        padding: 8px 0;
        font-size: 14px;
        color: rgba(255,255,255,.4);
        background: #1a1a2e;
        flex-shrink: 0;
    }

    /* Dark Mode Theme */
    .phone-frame.theme-dark .phone-screen { background: #121212; color: #E6E1E5; }
    .phone-frame.theme-dark .cs-card { background: #1C1B1F; border-color: #49454F; }
    .phone-frame.theme-dark .cs-text { color: #E6E1E5; }
    .phone-frame.theme-dark .cs-textfield input { color: #E6E1E5; border-color: #938F99; }
    .phone-frame.theme-dark .cs-textfield label { background: transparent; color: #CAC4D0; text-shadow: 0 0 2px #121212, 0 0 4px #121212; }
    .phone-frame.theme-dark .cs-textfield input:focus + label,
    .phone-frame.theme-dark .cs-textfield input:not(:placeholder-shown) + label { color: #D0BCFF; }
    .phone-frame.theme-dark .cs-textfield input:focus { border-color: #D0BCFF; }
    .phone-frame.theme-dark .cs-topbar { background: #322F35; color: #E6E1E5; }
    .phone-frame.theme-dark .cs-fab { background: #D0BCFF; color: #381E72; }

    /* ══════════════════════════════════════════
       MATERIAL DESIGN 3 COMPONENTS
    ══════════════════════════════════════════ */
    .cs-scaffold { display: flex; flex-direction: column; flex: 1; position: relative; }
    .cs-scaffold-content { flex: 1; overflow-y: auto; padding-bottom: 72px; }
    .cs-topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #6750A4;
        color: #fff;
        font-family: 'Syne', sans-serif;
        flex-shrink: 0;
    }
    .cs-topbar-nav { font-size: 16px; cursor: pointer; opacity: .8; }
    .cs-topbar-title { flex: 1; font-weight: 700; font-size: 14px; }
    .cs-topbar-actions { font-size: 16px; opacity: .8; }
    .cs-column {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .cs-row {
        display: flex;
        flex-direction: row;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .cs-text {
        font-family: 'Inter', 'Roboto', sans-serif;
        font-size: 13px;
        color: #1C1B1F;
        line-height: 1.5;
        margin: 0;
        word-break: break-word;
    }
    .cs-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 20px;
        border: none;
        border-radius: 20px;
        background: #6750A4;
        color: #fff;
        font-family: 'Inter', 'Roboto', sans-serif;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        letter-spacing: .02em;
        min-height: 36px;
        position: relative;
        overflow: hidden;
    }
    .cs-button::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0);
        transition: background .15s;
    }
    .cs-button:hover::after { background: rgba(255,255,255,.12); }
    .cs-button:active { transform: scale(.97); }
    .cs-button .cs-text { color: #fff; font-size: inherit; }
    .cs-button-outlined {
        background: transparent;
        border: 1px solid #79747E;
        color: #6750A4;
    }
    .cs-button-outlined .cs-text { color: #6750A4; }
    .cs-button-outlined:hover { background: rgba(103,80,164,.08); }
    .cs-button-outlined::after { display: none; }
    .cs-card {
        background: #FFFBFE;
        border-radius: 12px;
        border: 1px solid #E7E0EC;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        overflow: hidden;
    }
    .cs-textfield {
        position: relative;
        margin: 4px 0;
    }
    .cs-textfield input {
        width: 100%;
        padding: 14px 12px 6px;
        border: 1px solid #79747E;
        border-radius: 4px;
        font-family: 'Inter', 'Roboto', sans-serif;
        font-size: 13px;
        background: transparent;
        color: #1C1B1F;
        outline: none;
        transition: border-color .2s;
        box-sizing: border-box;
    }
    .cs-textfield input:focus { border-color: #6750A4; border-width: 2px; }
    .cs-textfield label {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 13px;
        color: #79747E;
        pointer-events: none;
        transition: all .2s;
        background: #fafafa;
        padding: 0 4px;
    }
    .cs-textfield input:focus + label,
    .cs-textfield input:not(:placeholder-shown) + label {
        top: 0;
        font-size: 10px;
        color: #6750A4;
    }
    .cs-divider {
        border: none;
        border-top: 1px solid #E7E0EC;
        margin: 4px 0;
    }
    .cs-switch {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .cs-switch input { display: none; }
    .cs-switch-track {
        width: 44px; height: 24px;
        background: #E7E0EC;
        border-radius: 12px;
        position: relative;
        transition: background .2s;
        border: 2px solid #79747E;
    }
    .cs-switch input:checked + .cs-switch-track {
        background: #6750A4;
        border-color: #6750A4;
    }
    .cs-switch-thumb {
        width: 16px; height: 16px;
        background: #79747E;
        border-radius: 50%;
        position: absolute;
        top: 2px; left: 2px;
        transition: all .2s;
    }
    .cs-switch input:checked + .cs-switch-track .cs-switch-thumb {
        left: 22px;
        background: #fff;
        width: 20px; height: 20px;
        top: 0;
    }
    .cs-checkbox {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        gap: 8px;
    }
    .cs-checkbox input { display: none; }
    .cs-checkbox-box {
        width: 18px; height: 18px;
        border: 2px solid #79747E;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #fff;
        transition: all .15s;
    }
    .cs-checkbox input:checked + .cs-checkbox-box {
        background: #6750A4;
        border-color: #6750A4;
    }
    .cs-fab {
        position: absolute;
        bottom: 16px;
        right: 16px;
        width: 48px; height: 48px;
        border-radius: 16px;
        background: #EADDFF;
        color: #21005D;
        border: none;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 3px 8px rgba(0,0,0,.2);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        z-index: 5;
    }
    .cs-fab:hover { box-shadow: 0 6px 16px rgba(0,0,0,.3); transform: scale(1.05); }
    .cs-fab:active { transform: scale(.95); }
    .cs-fab .cs-text { color: #21005D; font-size: 20px; margin: 0; }
    .cs-lazy-column {
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        flex: 1;
    }
    .cs-list-item {
        padding: 12px 16px;
        border-bottom: 1px solid #E7E0EC;
        transition: background .15s;
    }
    .cs-list-item:hover { background: rgba(103,80,164,.05); }
    .cs-image-placeholder {
        width: 100%;
        height: 120px;
        background: #E7E0EC;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #79747E;
        font-size: 24px;
        border-radius: 8px;
    }
    .cs-icon { font-size: 18px; }
    .cs-error {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px;
        color: #B3261E;
        text-align: center;
    }
    .cs-error span { font-size: 32px; }
    .cs-error p { font-size: 12px; font-family: 'Inter', sans-serif; }

    /* ── Preview shimmer animation ── */
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .preview-loading .phone-screen {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease-in-out infinite;
    }
</style>