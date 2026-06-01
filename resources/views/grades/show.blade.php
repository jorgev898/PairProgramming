<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Grade') }} — {{ $course->title }} — PairSync</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#050511;--surface:#0a0b16;--card-bg:rgba(10,11,22,0.85);--border:#1a1b36;--primary:#6366f1;--primary-glow:rgba(99,102,241,0.4);--blue:#3b82f6;--green:#10b981;--amber:#f59e0b;--red:#ef4444;--text:#f8fafc;--text-dim:#94a3b8;--text-lo:#475569;--radius:16px;--accent:{{ $course->color }}}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:'Syne',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;display:flex;flex-direction:column}
        body::before{content:'';position:absolute;inset:0;height:100%;background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px);background-size:50px 50px;opacity:.15;pointer-events:none;z-index:0}
        body::after{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 600px 600px at 80% 5%,{{ $course->color }}15,transparent),radial-gradient(ellipse 400px 400px at 10% 80%,rgba(59,130,246,0.06),transparent);pointer-events:none;z-index:0}

        nav{position:sticky;top:0;z-index:100;display:flex;justify-content:space-between;align-items:center;padding:16px 32px;background:rgba(5,5,17,0.95);border-bottom:1px solid rgba(255,255,255,0.05);flex-wrap:wrap;gap:10px}
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
        .nav-right{display:flex;align-items:center;gap:12px}
        .btn-small{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;text-decoration:none;font-weight:600;font-size:.82rem;transition:all .2s;background:rgba(255,255,255,0.02);cursor:pointer;font-family:'Syne',sans-serif}
        .btn-small:hover{border-color:rgba(255,255,255,0.2);background:rgba(255,255,255,0.05);transform:translateY(-1px)}
        .btn-sync-nav{background:linear-gradient(135deg,var(--green),#059669);border-color:transparent;box-shadow:0 4px 16px rgba(16,185,129,0.3)}
        .btn-sync-nav:hover{box-shadow:0 6px 24px rgba(16,185,129,0.4)}
        .btn-sync-nav:disabled{opacity:.5;cursor:not-allowed;transform:none}

        .page-hero{position:relative;z-index:1;max-width:860px;margin:0 auto;padding:32px 24px 20px}
        .hero-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;background:{{ $course->color }}12;border:1px solid {{ $course->color }}25;border-radius:100px;font-size:.78rem;font-weight:700;color:var(--accent);letter-spacing:.05em;text-transform:uppercase;margin-bottom:14px}
        .page-hero h1{font-size:clamp(1.5rem,3vw,2.2rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;margin-bottom:8px}
        .page-hero .subtitle{font-size:.92rem;color:var(--text-dim);font-weight:500}

        /* ACCORDION LIST */
        .accordion-list{position:relative;z-index:1;max-width:860px;margin:0 auto;padding:0 24px 80px;display:flex;flex-direction:column;gap:8px}

        /* STUDENT ROW (collapsed) */
        .student-toggle{width:100%;display:flex;align-items:center;gap:14px;padding:16px 20px;background:var(--card-bg);border:1px solid rgba(255,255,255,0.06);border-radius:12px;cursor:pointer;transition:all .25s;text-align:left;color:inherit;font-family:inherit;opacity:0;transform:translateY(16px)}
        .student-toggle.visible{opacity:1;transform:translateY(0);transition:opacity .35s ease,transform .35s ease,background .2s,border-color .2s}
        .student-toggle:hover{background:rgba(255,255,255,0.04);border-color:rgba(255,255,255,0.12)}
        .student-toggle.open{border-radius:12px 12px 0 0;border-bottom-color:transparent;background:rgba(255,255,255,0.03)}

        .st-avatar{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--blue));display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;color:#fff;flex-shrink:0}
        .st-info{flex:1;min-width:0}
        .st-name{font-weight:700;font-size:.92rem;color:#fff}
        .st-email{font-size:.72rem;color:var(--text-lo);font-family:'JetBrains Mono',monospace}

        .st-badges{display:flex;align-items:center;gap:10px;flex-shrink:0}
        .st-badge{font-family:'JetBrains Mono',monospace;font-size:.7rem;font-weight:600;padding:4px 10px;border-radius:100px;letter-spacing:.04em}
        .st-badge-count{background:rgba(99,102,241,0.12);color:#818cf8;border:1px solid rgba(99,102,241,0.2)}
        .st-badge-avg{background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.2)}

        .st-chevron{font-size:.9rem;color:var(--text-lo);transition:transform .3s cubic-bezier(.4,0,.2,1);flex-shrink:0}
        .student-toggle.open .st-chevron{transform:rotate(180deg)}

        /* DROPDOWN PANEL */
        .student-panel{max-height:0;overflow:hidden;transition:max-height .4s cubic-bezier(.4,0,.2,1);background:var(--card-bg);border:1px solid rgba(255,255,255,0.06);border-top:none;border-radius:0 0 12px 12px;margin-top:-8px;margin-bottom:0}
        .student-panel.open{margin-bottom:0}
        .panel-inner{padding:4px 0 12px}

        .lesson-row{display:flex;align-items:center;gap:14px;padding:12px 20px;transition:background .15s}
        .lesson-row:hover{background:rgba(255,255,255,0.02)}
        .l-order{font-family:'JetBrains Mono',monospace;font-size:.68rem;font-weight:700;color:var(--text-lo);background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .l-icon{font-size:1.1rem;flex-shrink:0;width:24px;text-align:center}
        .l-info{flex:1;min-width:0}
        .l-title{font-size:.85rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .l-dur{font-family:'JetBrains Mono',monospace;font-size:.68rem;color:var(--text-lo)}

        .g-area{display:flex;align-items:center;gap:8px;flex-shrink:0}
        .g-input{width:66px;padding:8px 8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-family:'JetBrains Mono',monospace;font-size:.88rem;font-weight:600;text-align:center;outline:none;transition:border-color .2s,box-shadow .2s}
        .g-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-glow)}
        .g-input.saved{border-color:var(--green);box-shadow:0 0 0 3px rgba(16,185,129,0.2)}
        .g-input.error{border-color:var(--red);box-shadow:0 0 0 3px rgba(239,68,68,0.2)}
        .g-max{font-size:.7rem;color:var(--text-lo);font-family:'JetBrains Mono',monospace}

        .act-btns{display:flex;align-items:center;gap:2px;flex-shrink:0}
        .btn-i{background:none;border:none;cursor:pointer;font-size:.82rem;padding:4px;border-radius:5px;transition:all .15s;opacity:.45;line-height:1}
        .btn-i:hover{opacity:1;background:rgba(255,255,255,0.06)}
        .btn-i.has-fb{opacity:1;color:var(--amber)}
        .sync-d{font-size:.7rem}
        .sync-d.synced{color:var(--green)}
        .sync-d.pending{color:var(--amber)}
        .sync-d.none{color:var(--text-lo);opacity:.3}

        .empty-state{position:relative;z-index:1;text-align:center;padding:80px 24px}
        .empty-state .ei{font-size:3rem;margin-bottom:16px;opacity:.5}
        .empty-state h2{font-size:1.2rem;font-weight:700;color:var(--text-dim);margin-bottom:8px}
        .empty-state p{font-size:.88rem;color:var(--text-lo);line-height:1.6}

        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:200;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
        .modal-overlay.active{display:flex}
        .modal{background:var(--surface);border:1px solid rgba(255,255,255,0.1);border-radius:var(--radius);padding:32px;width:90%;max-width:500px}
        .modal h3{font-size:1.1rem;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .modal textarea{width:100%;min-height:120px;padding:14px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:var(--text);font-family:'Syne',sans-serif;font-size:.9rem;resize:vertical;outline:none;transition:border-color .2s}
        .modal textarea:focus{border-color:var(--primary)}
        .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
        .btn-m{padding:10px 20px;border-radius:8px;font-weight:700;font-size:.88rem;cursor:pointer;transition:all .2s;font-family:'Syne',sans-serif;border:none}
        .btn-mc{background:rgba(255,255,255,0.06);color:var(--text-dim)}
        .btn-mc:hover{background:rgba(255,255,255,0.1);color:#fff}
        .btn-ms{background:linear-gradient(135deg,var(--primary),var(--blue));color:#fff;box-shadow:0 4px 16px var(--primary-glow)}
        .btn-ms:hover{transform:translateY(-1px);box-shadow:0 6px 20px var(--primary-glow)}

        .toast{position:fixed;bottom:24px;right:24px;z-index:300;padding:14px 24px;border-radius:12px;font-size:.88rem;font-weight:600;display:none;align-items:center;gap:8px;box-shadow:0 8px 32px rgba(0,0,0,0.4)}
        .toast.show{display:flex;animation:slideIn .3s ease}
        .toast.success{background:rgba(16,185,129,0.9);color:#fff}
        .toast.error{background:rgba(239,68,68,0.9);color:#fff}
        @keyframes slideIn{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}

        footer{position:relative;z-index:10;text-align:center;padding:32px;color:var(--text-lo);font-family:'JetBrains Mono',monospace;font-size:.85rem;border-top:1px solid rgba(255,255,255,0.05);margin-top:auto}

        @media(max-width:600px){
            nav{padding:12px 16px}
            .page-hero,.accordion-list{padding-left:16px;padding-right:16px}
            .student-toggle{padding:14px 16px;gap:10px}
            .lesson-row{padding:10px 16px;gap:10px;flex-wrap:wrap}
            .g-area{margin-left:auto}
            .st-badges{display:none}
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-left">
        <a href="{{ url('/') }}" class="logo"><div class="logo-icon">⌨</div><span class="logo-name">Pair<span>Sync</span></span></a>
        <div class="nav-divider"></div>
        <div class="breadcrumb">
            <a href="{{ route('grades.index') }}">{{ __('Grades') }}</a>
            <span class="sep">›</span>
            <span class="cur">{{ $course->title }}</span>
        </div>
    </div>
    <div class="nav-right">
        @auth <span style="color:var(--text-dim);font-size:.85rem;font-weight:600">{{ auth()->user()->name }}</span> @endauth
        <a href="{{ route('grades.index') }}" class="btn-small">← {{ __('Back') }}</a>
        @if($moodleConfigured)
            <button class="btn-small btn-sync-nav" id="btnSyncAll" onclick="syncAll()">🔄 {{ __('Sync Moodle') }}</button>
        @else
            <span class="btn-small" style="opacity:.5;cursor:default" title="Configura MOODLE_URL y MOODLE_WS_TOKEN en .env">⚠️ Moodle no configurado</span>
        @endif
    </div>
</nav>

<section class="page-hero">
    <div class="hero-badge">{{ $course->icon }} {{ $course->title }}</div>
    <h1>{{ __('Grades') }} — {{ $course->title }}</h1>
    <p class="subtitle">{{ __('Click a student to expand lessons · Scale 0.0 – 5.0 · 💬 feedback') }}</p>
</section>

<section class="accordion-list" id="accordionList">
@forelse($students as $student)
    @php
        $sg = $grades[$student->id] ?? collect();
        $gc = $sg->count();
        $avg = $gc > 0 ? number_format($sg->avg('grade'), 1) : '—';
    @endphp

    {{-- Toggle row --}}
    <button class="student-toggle" data-target="panel-{{ $student->id }}" data-index="{{ $loop->index }}" onclick="toggleStudent(this)">
        <div class="st-avatar">{{ strtoupper(substr($student->name, 0, 2)) }}</div>
        <div class="st-info">
            <div class="st-name">{{ $student->name }}</div>
            <div class="st-email">{{ $student->email }}</div>
        </div>
        <div class="st-badges">
            <span class="st-badge st-badge-count">{{ $gc }}/{{ $lessons->count() }} {{ __('grades') }}</span>
            <span class="st-badge st-badge-avg">⌀ {{ $avg }}</span>
        </div>
        <span class="st-chevron">▼</span>
    </button>

    {{-- Expandable panel --}}
    <div class="student-panel" id="panel-{{ $student->id }}">
        <div class="panel-inner">
            @foreach($lessons as $lesson)
                @php
                    $g = $sg[$lesson->id] ?? null;
                    $gVal = $g ? $g->grade : '';
                    $fb = $g ? ($g->feedback ?? '') : '';
                    $synced = $g ? $g->synced_to_moodle : false;
                    $gId = $g ? $g->id : '';
                @endphp
                <div class="lesson-row">
                    <div class="l-order">#{{ $lesson->order ?: $loop->iteration }}</div>
                    <div class="l-icon">{{ $lesson->icon }}</div>
                    <div class="l-info">
                        <div class="l-title">{{ $lesson->title }}</div>
                        @if($lesson->duration)<div class="l-dur">{{ $lesson->duration }}</div>@endif
                    </div>
                    <div class="g-area">
                        <input type="number" class="g-input" min="0" max="5" step="0.1"
                               value="{{ $gVal }}" placeholder="—"
                               data-student="{{ $student->id }}" data-lesson="{{ $lesson->id }}" data-course="{{ $course->id }}" data-grade-id="{{ $gId }}"
                               id="grade-{{ $student->id }}-{{ $lesson->id }}"
                               onchange="saveGrade(this)">
                        <span class="g-max">/ 5</span>
                    </div>
                    <div class="act-btns">
                        <button class="btn-i {{ $fb ? 'has-fb' : '' }}"
                                onclick="openFb({{ $student->id }},{{ $lesson->id }},'{{ $course->id }}')"
                                title="Feedback" id="fb-btn-{{ $student->id }}-{{ $lesson->id }}">💬</button>
                        @if($g)
                            <span class="sync-d {{ $synced ? 'synced' : 'pending' }}" id="sync-{{ $student->id }}-{{ $lesson->id }}">{{ $synced ? '✅' : '⏳' }}</span>
                            @if(!$synced && $moodleConfigured)
                                <button class="btn-i" onclick="syncOne('{{ $gId }}')" title="Sincronizar">🔄</button>
                            @endif
                        @else
                            <span class="sync-d none" id="sync-{{ $student->id }}-{{ $lesson->id }}">—</span>
                        @endif
                    </div>
                    <textarea style="display:none" id="feedback-{{ $student->id }}-{{ $lesson->id }}">{{ $fb }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="ei">👥</div>
        <h2>{{ __('No students enrolled') }}</h2>
        <p>{{ __('Students must enroll in the course from the Challenges page.') }}</p>
    </div>
@endforelse
</section>

<div class="modal-overlay" id="fbModal">
    <div class="modal">
        <h3>💬 {{ __('Feedback') }}</h3>
        <textarea id="fbText" placeholder="{{ __('Write your feedback...') }}"></textarea>
        <input type="hidden" id="fbSid"><input type="hidden" id="fbLid"><input type="hidden" id="fbCid">
        <div class="modal-actions">
            <button class="btn-m btn-mc" onclick="closeFb()">{{ __('Cancel') }}</button>
            <button class="btn-m btn-ms" onclick="saveFb()">{{ __('Save') }}</button>
        </div>
    </div>
</div>
<div class="toast" id="toast"></div>
<footer>{{ __('PairSync · Designed for developers, built for teams.') }}</footer>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

// Entrance animation
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.student-toggle').forEach((el, i) => {
        setTimeout(() => el.classList.add('visible'), 100 + i * 70);
    });
});

// Accordion toggle
function toggleStudent(btn) {
    const panel = document.getElementById(btn.dataset.target);
    const isOpen = btn.classList.contains('open');

    // Close all
    document.querySelectorAll('.student-toggle.open').forEach(b => {
        b.classList.remove('open');
        const p = document.getElementById(b.dataset.target);
        p.style.maxHeight = null;
        p.classList.remove('open');
    });

    // Open clicked (if was closed)
    if (!isOpen) {
        btn.classList.add('open');
        panel.classList.add('open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
    }
}

function showToast(msg, t='success') {
    const el = document.getElementById('toast');
    el.textContent = (t==='success'?'✅ ':'❌ ') + msg;
    el.className = 'toast show ' + t;
    setTimeout(() => el.classList.remove('show'), 3500);
}

async function saveGrade(inp) {
    const v = parseFloat(inp.value);
    if (isNaN(v)||v<0||v>5) { inp.classList.add('error'); showToast('Nota entre 0.0 y 5.0','error'); return; }
    const s=inp.dataset.student, l=inp.dataset.lesson, c=inp.dataset.course;
    const fe=document.getElementById(`feedback-${s}-${l}`);
    try {
        const r = await fetch('{{ route("grades.store") }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body:JSON.stringify({student_id:s,lesson_id:l,course_id:c,grade:v,feedback:fe?fe.value:''})
        });
        const d = await r.json();
        if (d.success) {
            inp.classList.remove('error'); inp.classList.add('saved'); inp.dataset.gradeId=d.grade.id;
            const se=document.getElementById(`sync-${s}-${l}`);
            if(se){se.className='sync-d pending';se.textContent='⏳';}
            setTimeout(()=>inp.classList.remove('saved'),2000);
            showToast('Calificación guardada');
            // Recalc panel height
            const panel = inp.closest('.student-panel');
            if(panel) panel.style.maxHeight = panel.scrollHeight + 'px';
        } else { inp.classList.add('error'); showToast(d.message||'Error','error'); }
    } catch(e) { inp.classList.add('error'); showToast('Error de conexión','error'); }
}

function openFb(s,l,c){
    const el=document.getElementById(`feedback-${s}-${l}`);
    document.getElementById('fbText').value=el?el.value:'';
    document.getElementById('fbSid').value=s;
    document.getElementById('fbLid').value=l;
    document.getElementById('fbCid').value=c;
    document.getElementById('fbModal').classList.add('active');
}
function closeFb(){document.getElementById('fbModal').classList.remove('active')}
function saveFb(){
    const s=document.getElementById('fbSid').value, l=document.getElementById('fbLid').value;
    const txt=document.getElementById('fbText').value;
    const el=document.getElementById(`feedback-${s}-${l}`); if(el)el.value=txt;
    const fb=document.getElementById(`fb-btn-${s}-${l}`); if(fb)fb.classList.toggle('has-fb',txt.length>0);
    const gi=document.getElementById(`grade-${s}-${l}`); if(gi&&gi.value)saveGrade(gi);
    closeFb(); showToast('Feedback guardado');
}

async function syncOne(id){
    try{
        const r=await fetch(`/grades/${id}/sync`,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
        const d=await r.json();
        d.success?showToast(d.message):showToast(d.message||'Error','error');
        if(d.success)setTimeout(()=>location.reload(),1000);
    }catch(e){showToast('Error de conexión','error');}
}

async function syncAll(){
    const btn=document.getElementById('btnSyncAll'); btn.disabled=true; btn.textContent='⏳ Sincronizando...';
    try{
        const r=await fetch('/grades/sync-all/{{ $course->id }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}});
        const d=await r.json(); showToast(d.message); setTimeout(()=>location.reload(),1500);
    }catch(e){showToast('Error de conexión','error');}
    finally{btn.disabled=false;btn.textContent='🔄 Sync Moodle';}
}

document.getElementById('fbModal').addEventListener('click',function(e){if(e.target===this)closeFb();});
</script>
</body>
</html>
