<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::query();

        if ($request->has('category')) {
            $projects->where('category', $request->category);
        }

        return response()->json($projects->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required', 'description' => 'required', 'budget' => 'required|numeric',
            'deadline' => 'required|date', 'category' => 'required'
        ]);

        $validated['client_id'] = Auth::id();

        return Project::create($validated);
    }

    public function show(Project $project)
    {
        return $project;
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->all());
        return $project;
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->noContent();
    }
}
