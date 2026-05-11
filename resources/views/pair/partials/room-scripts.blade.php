<script>
    /* ── Translations ── */
    const trans = {
        driverRole: @json(__('You are the Driver')),
        driverDesc: @json(__('focus on writing the code.')),
        navigatorRole: @json(__('You are the Navigator')),
        navigatorDesc: @json(__('guide, review, and think ahead.')),
        sessionActive: @json(__('Session active')),
        waitingPartner: @json(__('Waiting for partner')),
        swapRoles: @json(__('Swap Roles')),
        swapping: @json(__('Swapping...')),
        guidesStrategy: @json(__('Guides the strategy')),
        shareCode: @json(__('Share your code →')),
        waiting: @json(__('waiting...')),
        you: @json(__('You'))
    };

    /* ── LangGraph Config ── */
    const LG_URL = '{{ $lgConfig["url"] }}';
    const LG_KEY = '{{ $lgConfig["key"] }}';
    const LG_AGENT = '{{ $lgConfig["agent_id"] }}';
    const LG_HEADERS = { 'Content-Type': 'application/json', 'X-Api-Key': LG_KEY, 'X-Auth-Scheme': 'langsmith-api-key' };

    /* ── Identity ── */
    const EMBEDDED_NAME = @json($myName);
    const ROOM_CODE = @json($session->code);
    const CSRF = '{{ csrf_token() }}';
    const APP_HEADERS = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF };
    const STORAGE_KEY = 'ps_name_' + ROOM_CODE;
    if (EMBEDDED_NAME) localStorage.setItem(STORAGE_KEY, EMBEDDED_NAME);
    const MY_NAME = localStorage.getItem(STORAGE_KEY) ?? '';

    /* ── State ── */
    let state = {
        driver: @json($session->driver),
        navigator: @json($session->navigator),
        status: @json($session->status),
        myRole: @json($myRole),
    };
    let editor = null;
    let saveTimeout = null;
    let threadId = null;
    let chatOpen = false;
    let unread = 0;
    let isSending = false;
    let chatHistory = [];
    let pChatHistory = [];
    let isRunning = false;
    let cursorTimeout = null;
    let remoteCursorDecos = [];
    let mouseTimeout = null;
    let previewOpen = false;
    let autoPreview = false;
    let previewTimeout = null;
    let isPreviewing = false;
    let lastPreviewCode = '';

    /* ══════════════════════════════════════════
       MONACO EDITOR
    ══════════════════════════════════════════ */
    const DEFAULT_CODE = `fun main() {\n    println("Hello, Kotlin!")\n    println("Welcome to PairSync 🚀")\n\n    // Start coding here...\n}`;

    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
    require(['vs/editor/editor.main'], function () {
        const initialCode = @json($session->code_content) || DEFAULT_CODE;
        editor = monaco.editor.create(document.getElementById('monaco-container'), {
            value: initialCode,
            language: 'kotlin',
            theme: 'vs-dark',
            fontFamily: "'JetBrains Mono', monospace",
            fontSize: 14,
            lineHeight: 22,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            padding: { top: 12 },
            readOnly: state.myRole !== 'driver',
            automaticLayout: true,
            tabSize: 4,
            wordWrap: 'on',
            renderLineHighlight: 'gutter',
            cursorBlinking: 'smooth',
            smoothScrolling: true,
        });

        // Set custom background to match our theme
        monaco.editor.defineTheme('pairsync-dark', {
            base: 'vs-dark',
            inherit: true,
            rules: [],
            colors: {
                'editor.background': '#0d1117',
                'editor.lineHighlightBackground': '#161b22',
                'editorLineNumber.foreground': '#2e3d50',
                'editorLineNumber.activeForeground': '#5a6a7e',
                'editor.selectionBackground': '#1a3a5c',
                'editorCursor.foreground': '#00e5a0',
            }
        });
        monaco.editor.setTheme('pairsync-dark');

        // Driver: save on change (debounced)
        editor.onDidChangeModelContent(() => {
            if (state.myRole !== 'driver') return;
            setSaveStatus('saving');
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => saveCode(editor.getValue()), 600);
            // Auto-preview trigger
            if (autoPreview && previewOpen) {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(() => previewCode(), 2000);
            }
        });

        // Cursor position: send to server (debounced)
        editor.onDidChangeCursorPosition((e) => {
            clearTimeout(cursorTimeout);
            cursorTimeout = setTimeout(() => {
                saveCursor(e.position.lineNumber, e.position.column);
            }, 300);
        });

        // Update readonly badge
        updateReadonlyBadge();
    });

    function updateReadonlyBadge() {
        const badge = document.getElementById('readonly-badge');
        if (badge) badge.style.display = state.myRole === 'driver' ? 'none' : 'block';
    }

    async function saveCode(content) {
        try {
            await fetch('/room/' + ROOM_CODE + '/code', {
                method: 'POST', headers: APP_HEADERS,
                body: JSON.stringify({ content })
            });
            setSaveStatus('saved');
        } catch (e) {
            setSaveStatus('error');
            console.warn('[save]', e);
        }
    }

    function setSaveStatus(s) {
        const el = document.getElementById('save-status');
        if (!el) return;
        if (s === 'saving') { el.textContent = '⏳ Saving...'; el.className = 'save-status saving'; }
        else if (s === 'saved') { el.textContent = '✓ Saved'; el.className = 'save-status saved'; }
        else { el.textContent = '⚠ Error'; el.className = 'save-status'; }
    }

    async function saveCursor(line, column) {
        try {
            await fetch('/room/' + ROOM_CODE + '/cursor', {
                method: 'POST', headers: APP_HEADERS,
                body: JSON.stringify({ line, column })
            });
        } catch (e) { /* silent */ }
    }

    async function saveMousePos(mx, my) {
        try {
            await fetch('/room/' + ROOM_CODE + '/cursor', {
                method: 'POST', headers: APP_HEADERS,
                body: JSON.stringify({ mouse_x: mx, mouse_y: my })
            });
        } catch (e) { /* silent */ }
    }

    function renderRemoteCursor(cursors) {
        if (!editor || !cursors) return;
        const partnerRole = state.myRole === 'driver' ? 'navigator' : 'driver';
        const partner = cursors[partnerRole];
        if (!partner) return;

        const color = partnerRole === 'driver' ? '#00e5a0' : '#4da6ff';
        const name = partner.name || partnerRole;
        const line = partner.line;
        const col = partner.column;

        remoteCursorDecos = editor.deltaDecorations(remoteCursorDecos, [
            {
                range: new monaco.Range(line, col, line, col + 1),
                options: {
                    className: 'remote-cursor-' + partnerRole,
                    beforeContentClassName: 'remote-cursor-line-' + partnerRole,
                    stickiness: monaco.editor.TrackedRangeStickiness.NeverGrowsWhenTypingAtEdges,
                }
            },
            {
                range: new monaco.Range(line, col, line, col),
                options: {
                    after: {
                        content: ' ' + name,
                        inlineClassName: 'remote-cursor-label-' + partnerRole,
                    },
                    stickiness: monaco.editor.TrackedRangeStickiness.NeverGrowsWhenTypingAtEdges,
                }
            }
        ]);
    }

    /* ══════════════════════════════════════════
       CODE EXECUTION
    ══════════════════════════════════════════ */
    async function runCode() {
        if (!editor || isRunning) return;
        isRunning = true;
        const btn = document.getElementById('btn-run');
        btn.disabled = true; btn.textContent = '⏳ Running...';
        const out = document.getElementById('output-content');
        out.innerHTML = '<span class="output-info">Compiling and running...</span>';

        try {
            const res = await fetch('/room/' + ROOM_CODE + '/run', {
                method: 'POST', headers: APP_HEADERS,
                body: JSON.stringify({ code: editor.getValue(), language: 'kotlin' })
            });
            const data = await res.json();
            if (data.error) {
                out.innerHTML = '<span class="output-error">' + escHtml(data.error) + '</span>';
            } else {
                let html = '';
                if (data.compile && data.compile.stderr) {
                    html += '<span class="output-error">Compile Error:\n' + escHtml(data.compile.stderr) + '</span>\n';
                }
                if (data.run) {
                    if (data.run.stdout) html += '<span class="output-success">' + escHtml(data.run.stdout) + '</span>';
                    if (data.run.stderr) html += '<span class="output-error">' + escHtml(data.run.stderr) + '</span>';
                    if (!data.run.stdout && !data.run.stderr && !data.compile?.stderr) html += '<span class="output-info">(No output)</span>';
                }
                out.innerHTML = html || '<span class="output-info">(No output)</span>';
            }
        } catch (e) {
            out.innerHTML = '<span class="output-error">⚠ ' + escHtml(e.message) + '</span>';
        } finally {
            isRunning = false;
            btn.disabled = false; btn.textContent = '▶ Run';
        }
    }

    function escHtml(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    /* ══════════════════════════════════════════
       POLL & RENDER
    ══════════════════════════════════════════ */
    function render(s) {
        // Participants
        setParticipant('driver', s.driver, s.myRole === 'driver');
        setParticipant('navigator', s.navigator, s.myRole === 'navigator');

        // Role indicator in topbar
        const ri = document.getElementById('role-indicator');
        if (s.myRole === 'driver') { ri.className = 'role-indicator is-driver'; ri.textContent = '🧑‍💻 Driver'; }
        else if (s.myRole === 'navigator') { ri.className = 'role-indicator is-navigator'; ri.textContent = '🧭 Navigator'; }

        // Status pill
        const pill = document.getElementById('status-pill');
        pill.className = 'status-pill ' + (s.status === 'active' ? 'pill-active' : 'pill-waiting');
        document.getElementById('status-text').textContent = s.status === 'active' ? trans.sessionActive : trans.waitingPartner;

        // Show swap button when active
        const swapBtn = document.getElementById('btn-swap');
        if (swapBtn) swapBtn.style.display = s.status === 'active' ? 'flex' : 'none';

        // Editor readonly toggle on role change
        if (editor && s.myRole !== state.myRole) {
            editor.updateOptions({ readOnly: s.myRole !== 'driver' });
            updateReadonlyBadge();
        }

        // Code sync for navigator
        if (editor && s.code_content !== null && s.code_content !== undefined && s.myRole !== 'driver') {
            const current = editor.getValue();
            if (s.code_content !== current) {
                const pos = editor.getPosition();
                editor.setValue(s.code_content);
                if (pos) editor.setPosition(pos);
            }
        }

        // Remote cursor sync (editor + mouse)
        if (s.cursors) {
            renderRemoteCursor(s.cursors);
            renderRemoteMouseCursor(s.cursors);
        }

        // Thread sync
        if (s.thread_id && !threadId) threadId = s.thread_id;

        // Chat sync
        if (Array.isArray(s.chat_history) && s.chat_history.length > chatHistory.length) {
            const newMsgs = s.chat_history.slice(chatHistory.length);
            chatHistory = s.chat_history;
            hideEmpty();
            newMsgs.forEach(m => appendMessageDOM(m.role, m.sender, m.text));
        }

        // Participant Chat sync
        if (Array.isArray(s.participant_chat) && s.participant_chat.length > pChatHistory.length) {
            const newPMsgs = s.participant_chat.slice(pChatHistory.length);
            pChatHistory = s.participant_chat;
            const pEmpty = document.getElementById('pchat-empty');
            if (pEmpty) pEmpty.remove();
            newPMsgs.forEach(m => appendPChatMessage(m.sender, m.text));
        }

        state = { ...s };
    }

    function setParticipant(role, name, isMe) {
        const card = document.getElementById('p-' + role);
        const nameEl = card.querySelector('.participant-name');
        card.classList.toggle('is-me', isMe);
        nameEl.className = 'participant-name' + (name ? '' : ' empty');
        nameEl.textContent = name || trans.waiting;
        if (isMe && name) {
            const t = document.createElement('span'); t.className = 'you-tag'; t.textContent = trans.you;
            nameEl.appendChild(t);
        }
    }

    async function poll() {
        try {
            const res = await fetch('/room/' + ROOM_CODE + '/poll?name=' + encodeURIComponent(MY_NAME));
            if (res.ok) render(await res.json());
        } catch (e) { console.warn('[poll]', e); }
    }
    poll(); setInterval(poll, 2000);

    /* ── Fast cursor-only poll (500ms for low latency) ── */
    async function pollCursors() {
        try {
            const res = await fetch('/room/' + ROOM_CODE + '/cursors');
            if (!res.ok) return;
            const data = await res.json();
            if (data.cursors) {
                renderRemoteCursor(data.cursors);
                updateMouseTarget(data.cursors);
            }
        } catch (e) { /* silent */ }
    }
    setInterval(pollCursors, 500);

    async function swapRoles(btn) {
        btn.disabled = true; btn.textContent = '⏳ ' + trans.swapping;
        try {
            await fetch('/room/' + ROOM_CODE + '/swap', { method: 'POST', headers: APP_HEADERS });
            await poll();
        } catch (e) { console.warn('[swap]', e); }
        finally { btn.disabled = false; btn.textContent = '🔄 ' + trans.swapRoles; }
    }

    function copyCode() {
        navigator.clipboard.writeText(ROOM_CODE).then(() => {
            const t = document.getElementById('toast'); t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2200);
        });
    }

    /* ══════════════════════════════════════════
       CHAT (preserved from original)
    ══════════════════════════════════════════ */
    async function persistHistory() {
        try { await fetch('/room/' + ROOM_CODE + '/chat', { method: 'POST', headers: APP_HEADERS, body: JSON.stringify({ history: chatHistory }) }); }
        catch (e) { console.error('[chat persist]', e); }
    }

    async function persistThread(tid) {
        try { await fetch('/room/' + ROOM_CODE + '/thread', { method: 'POST', headers: APP_HEADERS, body: JSON.stringify({ thread_id: tid }) }); }
        catch (e) { console.warn('[thread persist]', e); }
    }

    async function initChat() {
        try {
            const res = await fetch('/room/' + ROOM_CODE + '/chat');
            if (!res.ok) return;
            const data = await res.json();
            if (data.thread_id) threadId = data.thread_id;
            if (Array.isArray(data.history) && data.history.length > 0) {
                chatHistory = data.history;
                hideEmpty();
                chatHistory.forEach(m => appendMessageDOM(m.role, m.sender, m.text));
                scrollToBottom();
            }
        } catch (e) { console.error('[initChat]', e); }
    }
    initChat();

    async function getOrCreateThread() {
        if (threadId) return threadId;
        const res = await fetch(LG_URL + '/threads', { method: 'POST', headers: LG_HEADERS, body: JSON.stringify({}) });
        if (!res.ok) throw new Error('Could not create thread: ' + res.status);
        const data = await res.json();
        threadId = data.thread_id;
        persistThread(threadId);
        return threadId;
    }

    async function sendMessage() {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text || isSending) return;
        isSending = true;
        input.value = ''; autoResize(input);
        document.getElementById('chat-send').disabled = true;
        hideEmpty();

        const userMsg = { role: 'user', sender: MY_NAME || 'You', text };
        chatHistory.push(userMsg);
        appendMessageDOM('user', userMsg.sender, text);
        persistHistory();

        const typingId = appendTyping();
        let bubbleEl = null, botText = '';

        try {
            const tid = await getOrCreateThread();
            const res = await fetch(`${LG_URL}/threads/${tid}/runs/stream`, {
                method: 'POST', headers: LG_HEADERS,
                body: JSON.stringify({ assistant_id: LG_AGENT, input: { messages: [{ type: 'human', content: text }] }, stream_mode: 'messages' })
            });

            if (!res.ok) {
                removeTyping(typingId);
                const errMsg = `⚠️ Error ${res.status}. Please try again.`;
                chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: errMsg });
                appendMessageDOM('bot', 'Android Kotlin Tutor', errMsg);
                persistHistory(); return;
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop();
                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    const raw = line.slice(6).trim();
                    if (!raw || raw === '[DONE]') continue;
                    let parsed;
                    try { parsed = JSON.parse(raw); } catch (_) { continue; }
                    const delta = extractAIText(parsed);
                    if (!delta) continue;
                    if (!bubbleEl) { removeTyping(typingId); bubbleEl = appendMessageDOM('bot', 'Android Kotlin Tutor', ''); }
                    botText += delta;
                    bubbleEl.innerHTML = formatMarkdown(botText);
                    scrollToBottom();
                }
            }

            if (!botText) {
                removeTyping(typingId);
                botText = await fallbackSync(tid);
                if (!bubbleEl) bubbleEl = appendMessageDOM('bot', 'Android Kotlin Tutor', '');
                bubbleEl.innerHTML = formatMarkdown(botText);
                scrollToBottom();
            } else { removeTyping(typingId); }

            if (botText) {
                chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: botText });
                await persistHistory();
            }
        } catch (err) {
            removeTyping(typingId);
            const errMsg = '⚠️ ' + err.message;
            chatHistory.push({ role: 'bot', sender: 'Android Kotlin Tutor', text: errMsg });
            appendMessageDOM('bot', 'Android Kotlin Tutor', errMsg);
            persistHistory();
        } finally {
            isSending = false;
            document.getElementById('chat-send').disabled = false;
            document.getElementById('chat-input').focus();
        }
    }

    async function fallbackSync(tid) {
        try {
            const res = await fetch(`${LG_URL}/threads/${tid}/state`, { headers: LG_HEADERS });
            if (!res.ok) return '(no response)';
            const data = await res.json();
            const msgs = data?.values?.messages ?? data?.messages ?? [];
            for (let i = msgs.length - 1; i >= 0; i--) {
                if (msgs[i].type === 'ai' || msgs[i].type === 'AIMessage') return extractFromMessage(msgs[i]) || '(empty response)';
            }
        } catch (e) { console.warn('[fallback]', e); }
        return '(no response)';
    }

    function extractAIText(p) {
        if (Array.isArray(p) && p.length >= 2) return extractFromMessage(p[1]);
        if (p?.type === 'AIMessageChunk' || p?.type === 'AIMessage') return extractFromMessage(p);
        if (p?.messages) return extractFromMessage(p.messages[p.messages.length - 1]);
        if (p?.agent?.messages) return extractFromMessage(p.agent.messages[p.agent.messages.length - 1]);
        if (p?.data) return extractAIText(p.data);
        return null;
    }

    function extractFromMessage(msg) {
        if (!msg) return null;
        if (msg.type === 'human' || msg.type === 'HumanMessage') return null;
        const c = msg.content ?? msg.text ?? null;
        if (!c) return null;
        if (typeof c === 'string') return c;
        if (Array.isArray(c)) return c.map(x => typeof x === 'string' ? x : (x?.text ?? '')).join('');
        return null;
    }

    /* ── Chat UI helpers ── */
    function hideEmpty() { const e = document.getElementById('chat-empty'); if (e) e.remove(); }

    function appendMessageDOM(role, sender, text) {
        const msgs = document.getElementById('chat-messages');
        const wrap = document.createElement('div'); wrap.className = 'msg ' + role;
        const meta = document.createElement('div'); meta.className = 'msg-meta';
        const sp = document.createElement('span'); sp.className = 'sender ' + (role === 'bot' ? 'bot-name' : 'user-name'); sp.textContent = sender;
        meta.appendChild(sp);
        const bubble = document.createElement('div'); bubble.className = 'msg-bubble';
        bubble.innerHTML = formatMarkdown(text);
        wrap.appendChild(meta); wrap.appendChild(bubble); msgs.appendChild(wrap);
        scrollToBottom();
        return bubble;
    }

    function appendTyping() {
        const msgs = document.getElementById('chat-messages');
        const div = document.createElement('div'); div.className = 'msg bot'; div.id = 'typing-' + Date.now();
        div.innerHTML = '<div class="msg-meta"><span class="sender bot-name">Android Kotlin Tutor</span></div><div class="typing-bubble"><span></span><span></span><span></span></div>';
        msgs.appendChild(div); scrollToBottom();
        return div.id;
    }

    function removeTyping(id) { const el = document.getElementById(id); if (el) el.remove(); }
    function scrollToBottom() { const m = document.getElementById('chat-messages'); m.scrollTop = m.scrollHeight; }
    function autoResize(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 80) + 'px'; }
    function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }

    function formatMarkdown(text) {
        return text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/```([\s\S]*?)```/g, (_, c) => `<pre><code>${c.trim()}</code></pre>`)
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }

    function clearOutput() { document.getElementById('output-content').innerHTML = '<span class="output-info">Ready to run code...</span>'; }

    /* ══════════════════════════════════════════
       PARTICIPANT CHAT
    ══════════════════════════════════════════ */
    async function sendPChat() {
        const input = document.getElementById('pchat-input');
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        const btn = document.getElementById('pchat-send');
        btn.disabled = true;

        // Optimistic UI update
        const pEmpty = document.getElementById('pchat-empty');
        if (pEmpty) pEmpty.remove();
        appendPChatMessage(MY_NAME || 'You', text);
        pChatHistory.push({ sender: MY_NAME || 'You', text: text });

        try {
            await fetch('/room/' + ROOM_CODE + '/participant-chat', {
                method: 'POST',
                headers: APP_HEADERS,
                body: JSON.stringify({ text })
            });
        } catch (e) {
            console.warn('[pchat]', e);
        } finally {
            btn.disabled = false;
            input.focus();
        }
    }

    function handlePChatKey(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendPChat();
        }
    }

    function appendPChatMessage(sender, text) {
        const msgs = document.getElementById('pchat-messages');
        if (!msgs) return;

        const isMe = sender === MY_NAME || sender === 'You';
        const msg = document.createElement('div');
        msg.className = 'pchat-msg' + (isMe ? ' is-me' : '');

        msg.innerHTML = `
        <div class="pchat-meta ${isMe ? 'is-me' : ''}">${escHtml(sender)}</div>
        <div class="pchat-bubble">${escHtml(text)}</div>
    `;

        msgs.appendChild(msg);
        msgs.scrollTop = msgs.scrollHeight;
    }

    /* ══════════════════════════════════════════
       MOUSE CURSOR TRACKING (smooth interpolation)
    ══════════════════════════════════════════ */

    // Current & target positions for lerp
    let mouseTargetX = 0, mouseTargetY = 0;
    let mouseCurrentX = 0, mouseCurrentY = 0;
    let mouseVisible = false;
    const LERP_SPEED = 0.25; // higher = snappier follow (0-1)

    // Track local mouse movement — send every 100ms for low latency
    let lastMouseSendTime = 0;
    document.addEventListener('mousemove', (e) => {
        const now = Date.now();
        if (now - lastMouseSendTime < 100) return;
        lastMouseSendTime = now;
        const mx = (e.clientX / window.innerWidth * 100).toFixed(2);
        const my = (e.clientY / window.innerHeight * 100).toFixed(2);
        saveMousePos(mx, my);
    });

    // pollCursors is defined above in the main section (runs every 500ms)

    // Update lerp target from poll data
    function updateMouseTarget(cursors) {
        if (!cursors) return;
        const partnerRole = state.myRole === 'driver' ? 'navigator' : 'driver';
        const partner = cursors[partnerRole];
        if (!partner || partner.mouse_x == null || partner.mouse_y == null) return;

        mouseTargetX = (partner.mouse_x / 100) * window.innerWidth;
        mouseTargetY = (partner.mouse_y / 100) * window.innerHeight;

        const el = ensureRemoteCursorEl();
        el.className = 'remote-mouse-cursor remote-mouse-' + partnerRole;
        const tag = el.querySelector('.cursor-name-tag');
        if (tag) tag.textContent = partner.name || partnerRole;

        if (!mouseVisible) {
            mouseCurrentX = mouseTargetX;
            mouseCurrentY = mouseTargetY;
            mouseVisible = true;
            el.style.display = 'block';
        }
    }

    // Create floating cursor element for partner
    function ensureRemoteCursorEl() {
        let el = document.getElementById('remote-mouse-cursor');
        if (el) return el;
        el = document.createElement('div');
        el.id = 'remote-mouse-cursor';
        el.className = 'remote-mouse-cursor';
        el.innerHTML = `
        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" class="cursor-arrow">
            <path d="M0.5 0.5L15 11.5H7.5L4 19L0.5 0.5Z" fill="currentColor" stroke="rgba(0,0,0,.5)" stroke-width="1"/>
        </svg>
        <span class="cursor-name-tag"></span>
    `;
        document.body.appendChild(el);
        return el;
    }

    // Unused now — mouse is rendered via lerp loop, not from main poll
    function renderRemoteMouseCursor(cursors) { /* handled by updateMouseTarget + animLoop */ }

    // Animation loop: lerp cursor toward target every frame
    function animateCursor() {
        if (mouseVisible) {
            const dx = mouseTargetX - mouseCurrentX;
            const dy = mouseTargetY - mouseCurrentY;

            // Only update DOM if cursor is still moving
            if (Math.abs(dx) > 0.3 || Math.abs(dy) > 0.3) {
                mouseCurrentX += dx * LERP_SPEED;
                mouseCurrentY += dy * LERP_SPEED;
            } else {
                mouseCurrentX = mouseTargetX;
                mouseCurrentY = mouseTargetY;
            }

            const el = document.getElementById('remote-mouse-cursor');
            if (el) {
                el.style.transform = `translate(${mouseCurrentX.toFixed(1)}px, ${mouseCurrentY.toFixed(1)}px)`;
            }
        }
        requestAnimationFrame(animateCursor);
    }
    requestAnimationFrame(animateCursor);

    /* ══════════════════════════════════════════
       PREVIEW SYSTEM
    ══════════════════════════════════════════ */
    function togglePreview() {
        previewOpen = !previewOpen;
        const area = document.getElementById('editor-area');
        const btn = document.getElementById('btn-preview');
        if (previewOpen) {
            area.classList.add('split-view');
            btn.classList.add('active');
            if (editor) {
                setTimeout(() => editor.layout(), 100);
                previewCode();
            }
        } else {
            area.classList.remove('split-view');
            btn.classList.remove('active');
            if (editor) setTimeout(() => editor.layout(), 100);
        }
    }

    function toggleAutoPreview() {
        autoPreview = !autoPreview;
        const el = document.getElementById('auto-toggle');
        el.classList.toggle('on', autoPreview);
    }

    async function previewCode() {
        if (!editor || isPreviewing) return;
        const code = editor.getValue();
        if (code === lastPreviewCode && !Object.keys(ComposeSimulator.state).length) return;
        lastPreviewCode = code;
        isPreviewing = true;
        setPreviewStatus('compiling', '⏳ Compiling...');

        try {
            const res = await fetch('/room/' + ROOM_CODE + '/preview', {
                method: 'POST', headers: APP_HEADERS,
                body: JSON.stringify({ code })
            });
            const data = await res.json();

            if (data.errors && data.errors.length > 0) {
                setPreviewStatus('error', '⚠ ' + data.errors.length + ' error(s)');
                const errorHtml = ComposeSimulator.renderPhone(
                    `<div class="cs-error"><span>⚠️</span>${data.errors.map(e => '<p>' + escHtml(e) + '</p>').join('')}</div>`
                );
                document.getElementById('preview-content').innerHTML = errorHtml;
                return;
            }

            if (data.mode === 'compose') {
                const html = ComposeSimulator.parse(data.kotlinCode);
                document.getElementById('preview-content').innerHTML = html;
                setPreviewStatus('ready', '✓ Compose Mode');
            } else {
                runConsolePreview(data.jsCode);
                setPreviewStatus('ready', '✓ Console Mode');
            }
        } catch (e) {
            setPreviewStatus('error', '⚠ ' + e.message);
        } finally {
            isPreviewing = false;
        }
    }

    function refreshPreview() {
        if (!editor || !previewOpen) return;
        const code = editor.getValue();
        const activeId = document.activeElement ? document.activeElement.id : null;
        const activeSelStart = document.activeElement ? document.activeElement.selectionStart : null;
        
        const html = ComposeSimulator.parse(code, true);
        document.getElementById('preview-content').innerHTML = html;
        
        if (activeId && activeId.startsWith('tf_')) {
            const el = document.getElementById(activeId);
            if (el) {
                el.focus();
                if (activeSelStart !== null && typeof el.selectionStart === 'number') {
                    el.selectionStart = el.selectionEnd = activeSelStart;
                }
            }
        }
    }

    function runConsolePreview(jsCode) {
        if (!jsCode) {
            document.getElementById('preview-content').innerHTML =
                ComposeSimulator.renderPhone('<div class="cs-error"><span>📋</span><p>No output</p></div>');
            return;
        }
        const outputLines = [];
        const sandboxHtml = `<!DOCTYPE html>
<html><head><style>
    body { font-family: 'JetBrains Mono', monospace; background: #1a1a2e; color: #00e5a0;
           padding: 12px; font-size: 12px; line-height: 1.6; margin: 0; }
    .line { padding: 2px 0; white-space: pre-wrap; word-break: break-all; }
    .error { color: #ff6b6b; }
</style></head><body><div id="out"></div>
<script>
    const out = document.getElementById('out');
    const origLog = console.log;
    console.log = function() {
        const text = Array.from(arguments).map(a => typeof a === 'object' ? JSON.stringify(a) : String(a)).join(' ');
        const div = document.createElement('div');
        div.className = 'line';
        div.textContent = '> ' + text;
        out.appendChild(div);
    };
    console.error = function() {
        const text = Array.from(arguments).join(' ');
        const div = document.createElement('div');
        div.className = 'line error';
        div.textContent = '⚠ ' + text;
        out.appendChild(div);
    };
    // Redirect Kotlin println
    if (typeof kotlin !== 'undefined' || true) {
        var println = console.log;
    }
    try { ${jsCode} } catch(e) { console.error(e.message); }
<\/script></body></html>`;

        const phoneHtml = ComposeSimulator.renderPhone('').replace(
            '<div class="phone-screen"></div>',
            `<iframe class="phone-screen" style="border:none;flex:1;background:#1a1a2e" sandbox="allow-scripts" srcdoc="${sandboxHtml.replace(/"/g, '&quot;')}"></iframe>`
        );
        document.getElementById('preview-content').innerHTML = phoneHtml;
    }

    function setPreviewStatus(type, text) {
        const el = document.getElementById('preview-status');
        if (!el) return;
        el.className = 'preview-status ' + type;
        el.textContent = text;
    }
</script>