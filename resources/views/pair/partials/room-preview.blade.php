<script>
/* ══════════════════════════════════════════
   COMPOSE SIMULATOR ENGINE
   Parses Kotlin Compose code → renders Material 3 HTML
══════════════════════════════════════════ */

const ComposeSimulator = {
    state: {},
    stateListeners: [],

    reset() { this.state = {}; this.stateListeners = []; },

    getState(key, def) { return this.state[key] !== undefined ? this.state[key] : def; },

    setState(key, val) {
        this.state[key] = val;
        this.stateListeners.forEach(fn => fn(key, val));
    },

    /* ── MAIN PARSE ── */
    parse(code, preserveState = false) {
        if (!preserveState) this.reset();
        this.extractState(code);
        const composables = this.extractComposables(code);
        const main = composables.find(c => /Screen|App|Main|Content/i.test(c.name)) || composables[0];
        if (!main) return this.renderError('No @Composable function found');
        return this.renderPhone(this.renderComposable(main.body, code));
    },

    extractState(code) {
        // var x by remember { mutableStateOf(value) }
        const re1 = /(?:var|val)\s+(\w+)\s+by\s+remember\s*\{[^}]*mutableStateOf\s*\(([^)]*)\)/g;
        let m;
        while ((m = re1.exec(code)) !== null) {
            let v = m[2].trim();
            if (v === 'true') v = true;
            else if (v === 'false') v = false;
            else if (!isNaN(v) && v !== '') v = Number(v);
            else v = v.replace(/^["']|["']$/g, '');
            if (this.state[m[1]] === undefined) this.state[m[1]] = v;
        }
        // var x = mutableStateOf(value) inside ViewModel
        const re2 = /(?:var|val)\s+(\w+)\s*(?:=|by)\s*mutableStateOf\s*\(([^)]*)\)/g;
        while ((m = re2.exec(code)) !== null) {
            let v = m[2].trim();
            if (v === 'true') v = true;
            else if (v === 'false') v = false;
            else if (!isNaN(v) && v !== '') v = Number(v);
            else v = v.replace(/^["']|["']$/g, '');
            if (this.state[m[1]] === undefined) this.state[m[1]] = v;
        }
        
        // Custom hack to support QuotesApp demo without full Kotlin parsing
        if (code.includes('QuotesViewModel')) {
            if (this.state['text'] === undefined) this.state['text'] = "Talk is cheap. Show me the code.";
            if (this.state['author'] === undefined) this.state['author'] = "Linus Torvalds";
        }
    },

    extractComposables(code) {
        const results = [];
        const re = /@Composable\s+fun\s+(\w+)\s*\([^)]*\)\s*\{/g;
        let m;
        while ((m = re.exec(code)) !== null) {
            const start = m.index + m[0].length;
            const body = this.extractBlock(code, start);
            results.push({ name: m[1], body });
        }
        return results;
    },

    extractBlock(code, start) {
        let depth = 1, i = start;
        while (i < code.length && depth > 0) {
            if (code[i] === '{') depth++;
            else if (code[i] === '}') depth--;
            i++;
        }
        return code.substring(start, i - 1);
    },

    /* ── RENDER COMPOSABLE BODY ── */
    renderComposable(body, fullCode) {
        let html = '';
        let pos = 0;
        const b = body.trim();

        while (pos < b.length) {
            const rest = b.substring(pos);
            let matched = false;

            // Scaffold
            const scaffM = rest.match(/^Scaffold\s*\(/);
            if (scaffM) {
                const inner = this.extractAfterParen(b, pos);
                html += this.renderScaffold(inner, fullCode);
                pos += inner.fullLen;
                matched = true;
            }

            // Column
            if (!matched) {
                const colM = rest.match(/^Column\s*\(/);
                if (colM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderColumn(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // Row
            if (!matched) {
                const rowM = rest.match(/^Row\s*\(/);
                if (rowM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderRow(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // Text
            if (!matched) {
                const txtM = rest.match(/^Text\s*\(/);
                if (txtM) {
                    const args = this.extractParenContent(b, pos + txtM[0].length - 1);
                    html += this.renderText(args);
                    pos += txtM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // Button
            if (!matched) {
                const btnM = rest.match(/^Button\s*\(/);
                if (btnM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderButton(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // OutlinedButton
            if (!matched) {
                const obtnM = rest.match(/^OutlinedButton\s*\(/);
                if (obtnM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderButton(inner, fullCode, 'outlined');
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // Card
            if (!matched) {
                const cardM = rest.match(/^Card\s*[\({]/);
                if (cardM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderCard(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // TextField
            if (!matched) {
                const tfM = rest.match(/^(?:TextField|OutlinedTextField)\s*\(/);
                if (tfM) {
                    const args = this.extractParenContent(b, pos + tfM[0].length - 1);
                    html += this.renderTextField(args);
                    pos += tfM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // Spacer
            if (!matched) {
                const spM = rest.match(/^Spacer\s*\([^)]*\)/);
                if (spM) {
                    const hm = spM[0].match(/height\s*\(\s*(\d+)/);
                    const h = hm ? hm[1] : '16';
                    html += `<div style="height:${h}px"></div>`;
                    pos += spM[0].length;
                    matched = true;
                }
            }

            // Divider
            if (!matched) {
                const divM = rest.match(/^Divider\s*\([^)]*\)/);
                if (divM) {
                    html += '<hr class="cs-divider">';
                    pos += divM[0].length;
                    matched = true;
                }
            }

            // Switch
            if (!matched) {
                const swM = rest.match(/^Switch\s*\(/);
                if (swM) {
                    const args = this.extractParenContent(b, pos + swM[0].length - 1);
                    html += this.renderSwitch(args);
                    pos += swM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // Checkbox
            if (!matched) {
                const cbM = rest.match(/^Checkbox\s*\(/);
                if (cbM) {
                    const args = this.extractParenContent(b, pos + cbM[0].length - 1);
                    html += this.renderCheckbox(args);
                    pos += cbM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // FloatingActionButton
            if (!matched) {
                const fabM = rest.match(/^FloatingActionButton\s*\(/);
                if (fabM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderFAB(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // LazyColumn
            if (!matched) {
                const lcM = rest.match(/^LazyColumn\s*[\({]/);
                if (lcM) {
                    const inner = this.extractAfterParen(b, pos);
                    html += this.renderLazyColumn(inner, fullCode);
                    pos += inner.fullLen;
                    matched = true;
                }
            }

            // Image placeholder
            if (!matched) {
                const imgM = rest.match(/^Image\s*\(/);
                if (imgM) {
                    const args = this.extractParenContent(b, pos + imgM[0].length - 1);
                    html += '<div class="cs-image-placeholder">🖼️ Image</div>';
                    pos += imgM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // Icon
            if (!matched) {
                const icoM = rest.match(/^Icon\s*\(/);
                if (icoM) {
                    const args = this.extractParenContent(b, pos + icoM[0].length - 1);
                    const iconName = this.extractIcon(args);
                    html += `<span class="cs-icon">${iconName}</span>`;
                    pos += icoM[0].length + args.length + 1;
                    matched = true;
                }
            }

            // TopAppBar
            if (!matched) {
                const topM = rest.match(/^TopAppBar\s*\(/);
                if (topM) {
                    const args = this.extractParenContent(b, pos + topM[0].length - 1);
                    const titleM = args.match(/title\s*=\s*\{\s*Text\s*\(\s*"([^"]*)"/);
                    const title = titleM ? titleM[1] : 'App';
                    html += `<div class="cs-topbar"><span class="cs-topbar-nav">←</span><span class="cs-topbar-title">${this.escHtml(title)}</span><span class="cs-topbar-actions">⋮</span></div>`;
                    pos += topM[0].length + args.length + 1;
                    matched = true;
                }
            }

            if (!matched) pos++;
        }
        return html;
    },

    /* ── COMPONENT RENDERERS ── */
    renderScaffold(inner, fullCode) {
        const topBarCode = this.extractNamedLambda(inner.params, 'topBar');
        const fabCode = this.extractNamedLambda(inner.params, 'floatingActionButton');
        
        const topBar = topBarCode ? this.renderComposable(topBarCode, fullCode) : '';
        const fab = fabCode ? this.renderComposable(fabCode, fullCode) : '';
        const content = inner.body ? this.renderComposable(inner.body, fullCode) : '';

        return `<div class="cs-scaffold">${topBar}<div class="cs-scaffold-content">${content}</div>${fab}</div>`;
    },

    renderColumn(inner, fullCode) {
        const mods = this.parseModifiers(inner.params);
        const style = this.modifiersToStyle(mods, 'column');
        const content = this.renderComposable(inner.body, fullCode);
        return `<div class="cs-column" style="${style}">${content}</div>`;
    },

    renderRow(inner, fullCode) {
        const mods = this.parseModifiers(inner.params);
        const style = this.modifiersToStyle(mods, 'row');
        const content = this.renderComposable(inner.body, fullCode);
        return `<div class="cs-row" style="${style}">${content}</div>`;
    },

    renderText(args) {
        let text = '';
        const strM = args.match(/^\s*"([^"]*)"/);
        if (strM) text = strM[1];
        else {
            // State interpolation: "Count: ${viewModel.count}" or "$count"
            const intM = args.match(/^\s*"([^"]*)"/s) || args.match(/^\s*text\s*=\s*"([^"]*)"/);
            if (intM) text = intM[1];
        }
        // Resolve state interpolations
        text = text.replace(/\$\{?(?:\w+\.)?(\w+)\}?/g, (_, key) => {
            return this.getState(key, `{${key}}`);
        });

        let style = '';
        const fs = args.match(/fontSize\s*=\s*(\d+)\.sp/);
        if (fs) style += `font-size:${Math.round(fs[1] * 0.8)}px;`;
        const fw = args.match(/fontWeight\s*=\s*FontWeight\.(\w+)/);
        if (fw) style += `font-weight:${fw[1] === 'Bold' ? '700' : fw[1] === 'Light' ? '300' : '400'};`;
        const col = args.match(/color\s*=\s*Color\.(\w+)/);
        if (col) style += `color:${this.mapColor(col[1])};`;
        const ta = args.match(/textAlign\s*=\s*TextAlign\.(\w+)/);
        if (ta) style += `text-align:${ta[1].toLowerCase()};`;

        return `<p class="cs-text" style="${style}">${this.escHtml(text)}</p>`;
    },

    renderButton(inner, fullCode, variant = 'filled') {
        const content = this.renderComposable(inner.body, fullCode);
        // Extract onClick action
        const onClickM = inner.params.match(/onClick\s*=\s*\{([^}]*)\}/);
        let action = '';
        if (onClickM) action = this.parseClickAction(onClickM[1]);

        const cls = variant === 'outlined' ? 'cs-button cs-button-outlined' : 'cs-button';
        return `<button class="${cls}" onclick="${action}">${content}</button>`;
    },

    renderCard(inner, fullCode) {
        const content = this.renderComposable(inner.body, fullCode);
        return `<div class="cs-card">${content}</div>`;
    },

    renderTextField(args) {
        const label = args.match(/label\s*=\s*\{\s*Text\s*\(\s*"([^"]*)"/);
        const valM = args.match(/value\s*=\s*(?:\w+\.)?(\w+)/);
        const stateKey = valM ? valM[1] : null;
        const val = stateKey ? this.getState(stateKey, '') : '';
        const lbl = label ? label[1] : 'Input';
        const id = stateKey ? 'tf_' + stateKey : 'tf_' + Math.random().toString(36).slice(2, 8);

        return `<div class="cs-textfield">
            <input type="text" id="${id}" value="${this.escHtml(String(val))}" placeholder=" "
                   oninput="ComposeSimulator.setState('${stateKey}',''+this.value); refreshPreview()">
            <label for="${id}">${this.escHtml(lbl)}</label>
        </div>`;
    },

    renderSwitch(args) {
        const chkM = args.match(/checked\s*=\s*(?:\w+\.)?(\w+)/);
        const key = chkM ? chkM[1] : 'switch_' + Math.random().toString(36).slice(2, 6);
        const checked = this.getState(key, false);
        return `<label class="cs-switch">
            <input type="checkbox" ${checked ? 'checked' : ''}
                   onchange="ComposeSimulator.setState('${key}', this.checked); this.closest('.cs-switch').classList.toggle('on', this.checked); refreshPreview()">
            <span class="cs-switch-track"><span class="cs-switch-thumb"></span></span>
        </label>`;
    },

    renderCheckbox(args) {
        const chkM = args.match(/checked\s*=\s*(?:\w+\.)?(\w+)/);
        const key = chkM ? chkM[1] : 'cb_' + Math.random().toString(36).slice(2, 6);
        const checked = this.getState(key, false);
        return `<label class="cs-checkbox">
            <input type="checkbox" ${checked ? 'checked' : ''}
                   onchange="ComposeSimulator.setState('${key}', this.checked); refreshPreview()">
            <span class="cs-checkbox-box">${checked ? '✓' : ''}</span>
        </label>`;
    },

    renderFAB(inner, fullCode) {
        const content = this.renderComposable(inner.body, fullCode);
        const onClickM = inner.params.match(/onClick\s*=\s*\{([^}]*)\}/);
        let action = '';
        if (onClickM) action = this.parseClickAction(onClickM[1]);
        return `<button class="cs-fab" onclick="${action}">${content || '+'}</button>`;
    },

    renderLazyColumn(inner, fullCode) {
        const itemsM = inner.body.match(/items\s*\(\s*(\d+)\s*\)/);
        const count = itemsM ? Math.min(parseInt(itemsM[1]), 20) : 5;
        const itemBody = inner.body.match(/items\s*\([^)]*\)\s*\{[^}]*->\s*([\s\S]*)\}/);
        let itemHtml = '<div class="cs-list-item"><p class="cs-text">List Item</p></div>';
        if (itemBody) {
            itemHtml = this.renderComposable(itemBody[1], fullCode);
        }
        let html = '<div class="cs-lazy-column">';
        for (let i = 0; i < count; i++) {
            html += `<div class="cs-list-item">${itemHtml.replace(/\$\{?\w*it\w*\}?/g, String(i))}</div>`;
        }
        html += '</div>';
        return html;
    },

    /* ── PHONE FRAME ── */
    renderPhone(content) {
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const isDark = this.getState('darkMode', false) || this.getState('isDarkTheme', false);
        const themeClass = isDark ? 'theme-dark' : 'theme-light';
        return `<div class="phone-frame ${themeClass}">
            <div class="phone-statusbar">
                <span>${time}</span>
                <span class="phone-statusbar-icons">📶 🔋</span>
            </div>
            <div class="phone-screen">${content}</div>
            <div class="phone-navbar">
                <span>◀</span><span>●</span><span>■</span>
            </div>
        </div>`;
    },

    renderError(msg) {
        return this.renderPhone(`<div class="cs-error"><span>⚠️</span><p>${this.escHtml(msg)}</p></div>`);
    },

    /* ── HELPERS ── */
    extractParenContent(str, startParen) {
        let depth = 1, i = startParen + 1;
        while (i < str.length && depth > 0) {
            if (str[i] === '(' || str[i] === '{') depth++;
            else if (str[i] === ')' || str[i] === '}') depth--;
            i++;
        }
        return str.substring(startParen + 1, i - 1);
    },

    extractCallBlock(str, startParen) {
        let depth = 1, i = startParen + 1;
        while (i < str.length && depth > 0) {
            if (str[i] === '(' || str[i] === '{') depth++;
            else if (str[i] === ')' || str[i] === '}') depth--;
            i++;
        }
        return str.substring(startParen + 1, i - 1);
    },

    extractNamedLambda(params, name) {
        const regex = new RegExp(name + '\\s*=\\s*\\{');
        const match = params.match(regex);
        if (!match) return '';
        const start = match.index + match[0].length - 1;
        return this.extractBlock(params, start);
    },

    extractAfterParen(str, pos) {
        // Find the ( after component name
        let i = str.indexOf('(', pos);
        if (i === -1) i = str.indexOf('{', pos);
        const start = i;
        // Get params section
        let depth = 1; i++;
        while (i < str.length && depth > 0) {
            if (str[i] === '(' || str[i] === '{') depth++;
            else if (str[i] === ')' || str[i] === '}') depth--;
            i++;
        }
        const paramsEnd = i;
        const params = str.substring(start + 1, paramsEnd - 1);

        // Check for trailing lambda { ... }
        let body = '';
        let bodyEnd = paramsEnd;
        const afterParams = str.substring(paramsEnd).match(/^\s*\{/);
        if (afterParams) {
            const bStart = paramsEnd + afterParams[0].length;
            depth = 1;
            let j = bStart;
            while (j < str.length && depth > 0) {
                if (str[j] === '{') depth++;
                else if (str[j] === '}') depth--;
                j++;
            }
            body = str.substring(bStart, j - 1);
            bodyEnd = j;
        }

        return { params, body, fullLen: bodyEnd - pos };
    },

    parseModifiers(params) {
        const mods = {};
        const padM = params.match(/padding\s*\(\s*(\d+)/);
        if (padM) mods.padding = padM[1];
        const fillM = params.match(/fillMaxSize|fillMaxWidth/);
        if (fillM) mods.fill = true;
        const hAlign = params.match(/horizontalAlignment\s*=\s*Alignment\.(\w+)/);
        if (hAlign) mods.hAlign = hAlign[1];
        const vAlign = params.match(/verticalAlignment\s*=\s*Alignment\.(\w+)/);
        if (vAlign) mods.vAlign = vAlign[1];
        const hArr = params.match(/horizontalArrangement\s*=\s*Arrangement\.(\w+)/);
        if (hArr) mods.hArrangement = hArr[1];
        const vArr = params.match(/verticalArrangement\s*=\s*Arrangement\.(\w+)/);
        if (vArr) mods.vArrangement = vArr[1];
        const spaced = params.match(/spacedBy\s*\(\s*(\d+)/);
        if (spaced) mods.gap = spaced[1];
        return mods;
    },

    modifiersToStyle(mods, type) {
        let s = '';
        if (mods.padding) s += `padding:${mods.padding}px;`;
        if (mods.fill) s += 'width:100%;min-height:100%;';
        if (mods.gap) s += `gap:${mods.gap}px;`;
        if (type === 'column') {
            if (mods.hAlign === 'CenterHorizontally') s += 'align-items:center;';
            if (mods.vArrangement === 'Center') s += 'justify-content:center;';
            if (mods.vArrangement === 'SpaceBetween') s += 'justify-content:space-between;';
        } else {
            if (mods.vAlign === 'CenterVertically') s += 'align-items:center;';
            if (mods.hArrangement === 'Center') s += 'justify-content:center;';
            if (mods.hArrangement === 'SpaceBetween') s += 'justify-content:space-between;';
            if (mods.hArrangement === 'SpaceEvenly') s += 'justify-content:space-evenly;';
        }
        return s;
    },

    parseClickAction(code) {
        const trimmed = code.trim();
        // viewModel.increment() or increment()
        const fnM = trimmed.match(/(?:\w+\.)?(\w+)\(\)/);
        if (fnM) {
            const fnName = fnM[1];
            // Try to find what the function does
            if (/increment|plus|add/i.test(fnName)) {
                const stateKeys = Object.keys(this.state).filter(k => typeof this.state[k] === 'number');
                if (stateKeys.length > 0) return `ComposeSimulator.setState('${stateKeys[0]}', ComposeSimulator.getState('${stateKeys[0]}',0)+1); refreshPreview()`;
            }
            if (/decrement|minus|sub/i.test(fnName)) {
                const stateKeys = Object.keys(this.state).filter(k => typeof this.state[k] === 'number');
                if (stateKeys.length > 0) return `ComposeSimulator.setState('${stateKeys[0]}', ComposeSimulator.getState('${stateKeys[0]}',0)-1); refreshPreview()`;
            }
            if (/randomQuote/i.test(fnName)) {
                return `const q = [{text: "A user interface is like a joke. If you have to explain it, it’s not that good.", author: "Anonymous"}, {text: "Measuring programming progress by lines of code is like measuring aircraft building progress by weight.", author: "Bill Gates"}, {text: "Talk is cheap. Show me the code.", author: "Linus Torvalds"}]; const r = q[Math.floor(Math.random()*q.length)]; ComposeSimulator.setState('text', r.text); ComposeSimulator.setState('author', r.author); refreshPreview();`;
            }
        }
        // count++ / count--
        const incM = trimmed.match(/(\w+)\+\+/);
        if (incM) return `ComposeSimulator.setState('${incM[1]}', ComposeSimulator.getState('${incM[1]}',0)+1); refreshPreview()`;
        const decM = trimmed.match(/(\w+)--/);
        if (decM) return `ComposeSimulator.setState('${decM[1]}', ComposeSimulator.getState('${decM[1]}',0)-1); refreshPreview()`;
        // state = !state (toggle)
        const togM = trimmed.match(/(\w+)\s*=\s*!(\w+)/);
        if (togM) return `ComposeSimulator.setState('${togM[1]}', !ComposeSimulator.getState('${togM[1]}',false)); refreshPreview()`;

        return '';
    },

    mapColor(c) {
        const map = { Red: '#F44336', Blue: '#2196F3', Green: '#4CAF50', Yellow: '#FFEB3B',
            White: '#FFFFFF', Black: '#000000', Gray: '#9E9E9E', Cyan: '#00BCD4',
            Magenta: '#E91E63', LightGray: '#BDBDBD', DarkGray: '#616161' };
        return map[c] || '#E0E0E0';
    },

    extractIcon(args) {
        const m = args.match(/Icons\.\w+\.(\w+)/);
        if (!m) return '⬥';
        const map = { Add: '＋', Delete: '🗑', Edit: '✏️', Home: '🏠', Settings: '⚙️',
            Search: '🔍', Menu: '☰', Close: '✕', ArrowBack: '←', ArrowForward: '→',
            Check: '✓', Star: '⭐', Favorite: '❤️', Person: '👤', Share: '📤', Send: '➤' };
        return map[m[1]] || '⬥';
    },

    escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
};
</script>
