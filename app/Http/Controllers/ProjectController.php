<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projectsQuery = Project::query();
        if ($request->has('category')) {
            $projectsQuery->where('category', $request->category);
        }
        $projects = $projectsQuery->latest()->get();
        $projects->each(function ($project) {
            $project->proposals_count = $project->proposals()->count();
        });

        return response()->json($projects);
    }


    public function getProjectsbyClient()
    {
        $projectsQuery = Project::query();

        $user = Auth::user();
        $projectsQuery->where('client_id', $user->id);

        $projects = $projectsQuery->latest()->get();
        $projects->each(function ($project) {
            $project->proposals_count = $project->proposals()->count();
        });

        return response()->json($projects);
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
        $project->loadCount('proposals');
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
