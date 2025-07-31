<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projectsQuery = Project::with('categories');

        if ($request->has('category')) {
            // Filtrar projetos que tenham uma categoria específica (many-to-many)
            $projectsQuery->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.id', $request->category);
            });
        }

        $projects = $projectsQuery->latest()->get();

        $projects->each(function ($project) {
            $project->proposals_count = $project->proposals()->count();
        });

        return response()->json($projects);
    }



    public function getProjectsbyClient()
    {
        $user = Auth::user();

        $projectsQuery = Project::with('categories')
        ->where('client_id', $user->id);

        $projects = $projectsQuery->latest()->get();

        $projects->each(function ($project) {
            $project->proposals_count = $project->proposals()->count();
        });

        return response()->json($projects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'deadline' => 'required|date',
            'categories' => 'required|array',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        $validated['client_id'] = Auth::id();

        // Cria o projeto
        $project = Project::create($validated);

        // Associa as categorias
        $project->categories()->sync($validated['categories']);

        return response()->json($project->load('categories'), 201);
    }

    public function show(Project $project)
    {
        $project->load('categories')->loadCount('proposals');
        return $project;
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'budget' => 'sometimes|required|numeric',
            'deadline' => 'sometimes|required|date',
            'categories' => 'sometimes|array',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        $project->update($validated);

        // Atualiza as categorias se forem enviadas
        if ($request->has('categories')) {
            $project->categories()->sync($validated['categories']);
        }

        return response()->json($project->load('categories'));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->noContent();
    }
}
