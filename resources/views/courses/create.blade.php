<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Create Course') }} — PairSync</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:           #050511;
            --surface:      #0a0b16;
            --border:       #1a1b36;
            --primary:      #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --blue:         #3b82f6;
            --text:         #f8fafc;
            --text-dim:     #94a3b8;
            --text-lo:      #475569;
            --radius:       16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Syne', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 48px;
            background: rgba(5, 5, 17, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-icon {
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--blue));
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 18px; box-shadow: 0 0 20px var(--primary-glow);
        }
        .logo-name { font-weight: 800; font-size: 1.4rem; color: #fff; letter-spacing: -.02em; }
        .logo-name span { color: var(--primary); }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .btn-nav-outline {
            display: inline-flex; align-items: center; padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            color: #fff; text-decoration: none; font-weight: 600; font-size: 0.9rem;
            transition: all 0.2s; background: rgba(255,255,255,0.02);
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 60px auto;
            padding: 0 24px 80px;
            width: 100%;
        }

        h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 40px; }

        .form-section {
            background: rgba(10, 11, 22, 0.85);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 32px;
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 0.9rem; font-weight: 600; color: var(--text-dim); margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%; padding: 12px 16px; background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px;
            color: #fff; font-family: 'Syne', sans-serif; font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); }
        
        textarea.form-control { resize: vertical; min-height: 100px; }
        
        .row { display: flex; gap: 20px; }
        .col { flex: 1; }

        .lesson-card {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
            position: relative;
        }
        
        .lesson-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .lesson-title { font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; }
        
        .btn-remove-lesson {
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;
        }

        .btn-add-lesson {
            width: 100%; padding: 12px; background: rgba(255,255,255,0.02);
            border: 1px dashed rgba(255,255,255,0.2); border-radius: 8px;
            color: var(--text-dim); font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-add-lesson:hover { background: rgba(255,255,255,0.05); color: #fff; }

        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--blue));
            border-radius: 10px; color: #fff; text-decoration: none; font-size: 1.1rem;
            font-weight: 700; transition: transform 0.2s, box-shadow 0.2s; border: none; cursor: pointer;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px var(--primary-glow); }
        
        .checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }
        .checkbox-label input { width: 18px; height: 18px; accent-color: var(--primary); }
    </style>
</head>
<body>

<nav>
    <a href="{{ url('/') }}" class="logo">
        <div class="logo-icon">⌨</div>
        <span class="logo-name">Pair<span>Sync</span></span>
    </a>
    <div class="nav-links">
        <a href="{{ route('courses.index') }}" class="btn-nav-outline">← {{ __('Back') }}</a>
    </div>
</nav>

<div class="container">
    <h1>{{ __('Create Course') }}</h1>

    @if ($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf
        
        <div class="form-section">
            <div class="form-section-title">{{ __('Course Details') }}</div>
            
            <div class="row">
                <div class="col" style="flex: 0 0 100px;">
                    <div class="form-group">
                        <label>{{ __('Icon (Emoji)') }}</label>
                        <input type="text" name="icon" class="form-control" value="📚" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>{{ __('Title') }}</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Advanced Kotlin">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea name="description" class="form-control" required placeholder="What will students learn?"></textarea>
            </div>

            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>{{ __('Level') }}</label>
                        <select name="level" class="form-control" required>
                            <option value="beginner">{{ __('Beginner') }}</option>
                            <option value="intermediate">{{ __('Intermediate') }}</option>
                            <option value="advanced">{{ __('Advanced') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>{{ __('Theme Color (Hex)') }}</label>
                        <input type="color" name="color" class="form-control" value="#6366f1" required style="padding: 4px; height: 46px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">{{ __('Lessons') }}</div>
            
            <div id="lessons-container">
                <!-- Lessons will be added here via JS -->
            </div>
            
            <button type="button" class="btn-add-lesson" onclick="addLesson()">
                + {{ __('Add Lesson') }}
            </button>
        </div>

        <button type="submit" class="btn-primary">{{ __('Save Course') }}</button>
    </form>
</div>

<script>
    let lessonCount = 0;

    function addLesson() {
        const container = document.getElementById('lessons-container');
        const lessonHtml = `
            <div class="lesson-card" id="lesson-${lessonCount}">
                <div class="lesson-header">
                    <span class="lesson-title">Lesson #${lessonCount + 1}</span>
                    <button type="button" class="btn-remove-lesson" onclick="removeLesson(${lessonCount})">Remove</button>
                </div>
                
                <div class="row">
                    <div class="col" style="flex: 0 0 80px;">
                        <div class="form-group">
                            <label>Icon</label>
                            <input type="text" name="lessons[${lessonCount}][icon]" class="form-control" value="📝">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="lessons[${lessonCount}][title]" class="form-control" required>
                        </div>
                    </div>
                    <div class="col" style="flex: 0 0 120px;">
                        <div class="form-group">
                            <label>Duration</label>
                            <input type="text" name="lessons[${lessonCount}][duration]" class="form-control" placeholder="e.g. 20 min">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Video URL</label>
                    <input type="url" name="lessons[${lessonCount}][video_url]" class="form-control" placeholder="e.g. https://youtube.com/...">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="lessons[${lessonCount}][description]" class="form-control">
                </div>
                
                <label class="checkbox-label">
                    <input type="hidden" name="lessons[${lessonCount}][available]" value="0">
                    <input type="checkbox" name="lessons[${lessonCount}][available]" value="1" checked>
                    Available immediately
                </label>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', lessonHtml);
        lessonCount++;
        updateLessonNumbers();
    }

    function removeLesson(id) {
        document.getElementById(`lesson-${id}`).remove();
        updateLessonNumbers();
    }

    function updateLessonNumbers() {
        const titles = document.querySelectorAll('.lesson-title');
        titles.forEach((title, index) => {
            title.textContent = `Lesson #${index + 1}`;
        });
    }

    // Add one empty lesson by default
    document.addEventListener('DOMContentLoaded', () => {
        addLesson();
    });
</script>

</body>
</html>
