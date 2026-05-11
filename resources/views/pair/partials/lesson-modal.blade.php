@if($session->lesson)
<div class="lesson-modal-overlay" id="lesson-modal" style="display:none;" onclick="handleModalOverlayClick(event)">
    <div class="lesson-modal-content">
        <button class="lesson-modal-close" onclick="toggleLessonModal()">✕</button>
        <div class="lesson-modal-body">
            <div class="lesson-header">
                <h2>{{ __($session->lesson->title) }}</h2>
                <p class="subtitle">{{ __($session->lesson->description) }}</p>
            </div>
            
            @if (!empty($session->lesson->content['sections']))
                @foreach ($session->lesson->content['sections'] as $section)
                    @if ($section['type'] === 'intro')
                        <div class="content-section">
                            <h3><span class="section-icon">📖</span> {{ __($section['title']) }}</h3>
                            <p>{{ __($section['body']) }}</p>
                        </div>
                    @elseif ($section['type'] === 'video')
                        <div class="content-section">
                            <h3><span class="section-icon">🎥</span> {{ __($section['title']) }}</h3>
                            @if (!empty($section['body']))
                                <p>{{ __($section['body']) }}</p>
                            @endif
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.5); box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin: 20px 0;">
                                <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" src="{{ $section['url'] }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        </div>
                    @elseif ($section['type'] === 'concept')
                        <div class="content-section">
                            <h3><span class="section-icon">💡</span> {{ __($section['title']) }}</h3>
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
                            <h3><span class="section-icon">💻</span> {{ __($section['title']) }}</h3>
                            @if (!empty($section['body']))
                                <p>{!! $section['body'] !!}</p>
                            @endif
                            <div class="code-block">
                                <div class="code-header">
                                    <span class="code-lang">{{ $section['language'] ?? 'kotlin' }}</span>
                                    <button class="code-copy" onclick="copyModalCode(this)">📋 {{ __('Copy') }}</button>
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
                            <h3><span class="section-icon">🎯</span> {{ __($section['title']) }}</h3>
                            <p>{{ __($section['body']) }}</p>
                            @foreach ($section['tasks'] as $task)
                                <div class="task-card">
                                    <h4>{{ __($task['title']) }}</h4>
                                    <p class="task-desc">{{ __($task['description']) }}</p>
                                    @if (!empty($task['hint']))
                                        <div class="task-hint"><strong>💡 {{ __('Hint') }}:</strong> {{ __($task['hint']) }}</div>
                                    @endif
                                    @if (!empty($task['starter_code']))
                                        <div class="code-block">
                                            <div class="code-header">
                                                <span class="code-lang">kotlin · starter code</span>
                                                <button class="code-copy" onclick="copyModalCode(this)">📋 {{ __('Copy') }}</button>
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
                            <h3><span class="section-icon">✅</span> {{ __($section['title']) }}</h3>
                            <ul>
                                @foreach ($section['bullets'] as $bullet)
                                    <li>{{ __($bullet) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</div>

<script>
    function toggleLessonModal() {
        const modal = document.getElementById('lesson-modal');
        if (modal.style.display === 'none') {
            modal.style.display = 'flex';
        } else {
            modal.style.display = 'none';
        }
    }
    function handleModalOverlayClick(event) {
        if (event.target.id === 'lesson-modal') {
            toggleLessonModal();
        }
    }
    function copyModalCode(btn) {
        const pre = btn.closest('.code-block').querySelector('pre');
        navigator.clipboard.writeText(pre.textContent).then(() => {
            const orig = btn.textContent;
            btn.textContent = '✅ {{ __("Copied!") }}';
            setTimeout(() => btn.textContent = orig, 2000);
        });
    }
</script>
<style>
    .lesson-modal-btn {
        background: rgba(99, 102, 241, 0.15);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: var(--primary, #6366f1);
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .lesson-modal-btn:hover {
        background: rgba(99, 102, 241, 0.25);
        transform: translateY(-1px);
    }
    .lesson-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 5, 17, 0.85);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
    }
    .lesson-modal-content {
        background: var(--surface, #0a0b16);
        border: 1px solid var(--border-hi, #2a2c56);
        border-radius: var(--radius, 16px);
        width: 100%;
        max-width: 800px;
        max-height: 85vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        padding: 32px 40px;
    }
    .lesson-modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: var(--text-dim, #94a3b8);
        font-size: 1.2rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .lesson-modal-close:hover {
        color: #fff;
    }
    .lesson-modal-body h2 {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 8px;
        color: #fff;
    }
    .lesson-modal-body .subtitle {
        color: var(--text-dim, #94a3b8);
        margin-bottom: 24px;
        font-size: 0.95rem;
    }
    .lesson-modal-body .content-section {
        margin-bottom: 32px;
    }
    .lesson-modal-body .content-section h3 {
        font-size: 1.2rem;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
    }
    .lesson-modal-body p {
        color: var(--text-dim, #94a3b8);
        line-height: 1.6;
        margin-bottom: 12px;
        font-size: 0.9rem;
    }
    .lesson-modal-body ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
        padding: 0;
    }
    .lesson-modal-body ul li {
        font-size: 0.88rem;
        color: var(--text-dim, #94a3b8);
        display: flex;
        gap: 8px;
    }
    .lesson-modal-body ul li::before {
        content: '▸';
        color: var(--primary, #6366f1);
    }
    .lesson-modal-body .code-block {
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        margin: 12px 0;
        overflow: hidden;
    }
    .lesson-modal-body .code-header {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: rgba(255,255,255,0.03);
        border-bottom: 1px solid rgba(255,255,255,0.06);
        font-size: 0.75rem;
        font-family: 'JetBrains Mono', monospace;
        color: var(--text-dim, #94a3b8);
    }
    .lesson-modal-body .code-copy {
        background: none;
        border: none;
        color: var(--text-dim, #94a3b8);
        cursor: pointer;
        font-size: 0.75rem;
    }
    .lesson-modal-body .code-copy:hover {
        color: #fff;
    }
    .lesson-modal-body pre {
        padding: 12px;
        margin: 0;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        overflow-x: auto;
        color: var(--text, #f8fafc);
    }
    .lesson-modal-body .exercise-section {
        background: rgba(16, 185, 129, 0.05);
        border: 1px solid rgba(16, 185, 129, 0.15);
        border-radius: 12px;
        padding: 24px;
        margin: 32px 0;
    }
    .lesson-modal-body .exercise-section h3 {
        color: var(--green, #10b981);
        margin-bottom: 12px;
        font-size: 1.2rem;
    }
    .lesson-modal-body .task-card {
        background: rgba(0,0,0,0.25);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }
    .lesson-modal-body .task-card h4 {
        color: #fff;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    .lesson-modal-body .task-hint {
        font-size: 0.82rem;
        color: var(--text-lo, #475569);
        font-style: italic;
        padding: 10px;
        background: rgba(255,255,255,0.03);
        border-radius: 6px;
        border-left: 3px solid var(--primary, #6366f1);
        margin-bottom: 12px;
    }
    .lesson-modal-content::-webkit-scrollbar { width: 6px; }
    .lesson-modal-content::-webkit-scrollbar-thumb { background: var(--border-hi, #2a2c56); border-radius: 4px; }
</style>
@endif
