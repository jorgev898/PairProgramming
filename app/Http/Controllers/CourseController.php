<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $courses = auth()->user()->courses()->withCount('lessons')->get();
        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        return view('courses.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:10',
            'lessons' => 'nullable|array',
            'lessons.*.title' => 'required|string|max:255',
            'lessons.*.description' => 'nullable|string',
            'lessons.*.duration' => 'nullable|string|max:50',
            'lessons.*.video_url' => 'nullable|url|max:255',
            'lessons.*.icon' => 'nullable|string|max:10',
            'lessons.*.available' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            $course = auth()->user()->courses()->create([
                'title' => $request->title,
                'description' => $request->description,
                'level' => $request->level,
                'color' => $request->color,
                'icon' => $request->icon,
            ]);

            if ($request->has('lessons') && is_array($request->lessons)) {
                foreach ($request->lessons as $index => $lessonData) {
                    $course->lessons()->create([
                        'title' => $lessonData['title'],
                        'description' => $lessonData['description'] ?? null,
                        'duration' => $lessonData['duration'] ?? null,
                        'video_url' => $lessonData['video_url'] ?? null,
                        'icon' => $lessonData['icon'] ?? null,
                        'available' => isset($lessonData['available']) ? (bool) $lessonData['available'] : false,
                        'order' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $course = auth()->user()->courses()->with('lessons')->findOrFail($id);
        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $course = auth()->user()->courses()->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:10',
            'lessons' => 'nullable|array',
            'lessons.*.id' => 'nullable|exists:lessons,id',
            'lessons.*.title' => 'required|string|max:255',
            'lessons.*.description' => 'nullable|string',
            'lessons.*.duration' => 'nullable|string|max:50',
            'lessons.*.video_url' => 'nullable|url|max:255',
            'lessons.*.icon' => 'nullable|string|max:10',
            'lessons.*.available' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $course) {
            $course->update([
                'title' => $request->title,
                'description' => $request->description,
                'level' => $request->level,
                'color' => $request->color,
                'icon' => $request->icon,
            ]);

            $existingLessonIds = [];
            
            if ($request->has('lessons') && is_array($request->lessons)) {
                foreach ($request->lessons as $index => $lessonData) {
                    $lessonPayload = [
                        'title' => $lessonData['title'],
                        'description' => $lessonData['description'] ?? null,
                        'duration' => $lessonData['duration'] ?? null,
                        'video_url' => $lessonData['video_url'] ?? null,
                        'icon' => $lessonData['icon'] ?? null,
                        'available' => isset($lessonData['available']) ? (bool) $lessonData['available'] : false,
                        'order' => $index + 1,
                    ];

                    if (!empty($lessonData['id'])) {
                        // Update existing
                        $lesson = $course->lessons()->find($lessonData['id']);
                        if ($lesson) {
                            $lesson->update($lessonPayload);
                            $existingLessonIds[] = $lesson->id;
                        }
                    } else {
                        // Create new
                        $newLesson = $course->lessons()->create($lessonPayload);
                        $existingLessonIds[] = $newLesson->id;
                    }
                }
            }
            
            // Delete lessons that were removed
            $course->lessons()->whereNotIn('id', $existingLessonIds)->delete();
        });

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403);
        }

        $course = auth()->user()->courses()->findOrFail($id);
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}
