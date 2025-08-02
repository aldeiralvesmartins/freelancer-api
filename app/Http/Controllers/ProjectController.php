<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projectsQuery = Project::with(['categories', 'client']); // carrega relacionamento do cliente

        if ($request->has('category')) {
            $projectsQuery->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.id', $request->category);
            });
        }

        $projects = $projectsQuery->latest()->paginate(10);

        $projects->getCollection()->transform(function ($project) {
            $project->proposals_count = $project->proposals()->count();

            $client = $project->client;

            $project->client = [
                'id' => $client->id,
                'name' => $client->name,
                'avatar' => $client->photo, // ou avatar
                'rating' => $client->rating ?? null,
            ];

            return $project;
        });

        return response()->json($projects);
    }

    public function getProjectsbyClient()
    {
        $user = Auth::user();

        $projects = Project::with(['categories', 'client'])
            ->where('client_id', $user->id)
            ->latest()
            ->get();

        $projects->each(function ($project) use ($user) {
            $project->proposals_count = $project->proposals()->count();
            $project->client = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->photo,
                'rating' => $user->rating ?? null,
            ];
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
        $project->load('categories', 'client')->loadCount('proposals');
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
