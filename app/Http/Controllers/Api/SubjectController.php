<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json([], 403);
        }

        $subjects = $user->teacher->subjects()->withCount('students')->get();
        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json(['message' => 'Only teachers can create subjects'], 403);
        }

        $subject = $user->teacher->subjects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return response()->json($subject, 201);
    }

    public function show(Subject $subject)
    {
        // Check ownership
        $user = Auth::user();
        if ($subject->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $subject->loadCount('students');
        return response()->json($subject);
    }

    public function update(Request $request, Subject $subject)
    {
        // Check ownership
        $user = Auth::user();
        if ($subject->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        $subject->update($request->only('name', 'description', 'icon'));

        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        // Check ownership
        $user = Auth::user();
        if ($subject->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted']);
    }
}
