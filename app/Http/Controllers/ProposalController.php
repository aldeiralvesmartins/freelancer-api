<?php
namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->type === 'freelancer') {
            $proposals = Proposal::with(['freelancer', 'project.categories']) // ou 'project.categories'
            ->when($request->filled('project_id'), function ($query) use ($request) {
                $query->where('project_id', $request->project_id);
            }, function ($query) use ($user) {
                $query->where('freelancer_id', $user->id);
            })
                ->latest()
                ->get();

        } else {
            $proposals = Proposal::with(['freelancer', 'project.categories']) // ou 'project.categories'
            ->whereHas('project', function ($query) use ($user) {
                $query->where('client_id', $user->id);
            })
                ->when($request->filled('project_id'), function ($query) use ($request) {
                    $query->where('project_id', $request->project_id);
                })
                ->latest()
                ->get();
        }

        return response()->json($proposals);
    }


    public function allProposal()
    {
        return response()->json(
            Proposal::with('freelancer')->latest()->get()
        );
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->type !== 'freelancer') {
            return response()->json(['message' => 'Somente freelancers podem enviar propostas'], 403);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric',
            'duration' => 'required|integer|min:1',
            'message' => 'nullable|string',
            'links' => 'nullable|array',
            'links.*' => 'url',
        ]);

        $validated['freelancer_id'] = $user->id;
        $validated['status'] = 'pending';

        $proposal = Proposal::create($validated);

        // Aqui pode enviar notificação para o contratante

        return response()->json($proposal, 201);
    }

    public function show(Proposal $proposal)
    {
        $this->authorize('view', $proposal);
        return $proposal;
    }

    public function update(Request $request, Proposal $proposal)
    {
        $this->authorize('update', $proposal);

        $validated = $request->validate([
            'status' => 'in:pending,accepted,rejected',
        ]);

        $proposal->update($validated);

        return $proposal;
    }

    public function destroy(Proposal $proposal)
    {
        $this->authorize('delete', $proposal);
        $proposal->delete();
        return response()->noContent();
    }

    public function accept(Proposal $proposal)
    {
        $user = Auth::user();

        // 1. Verifica se o usuário é o dono do projeto
        if ($proposal->project->client_id !== $user->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // 2. Atualiza o status da proposta selecionada
        $proposal->status = 'accepted';
        $proposal->save();

        // 3. Rejeita todas as outras propostas do mesmo projeto
        Proposal::where('project_id', $proposal->project_id)
            ->where('id', '!=', $proposal->id)
            ->update(['status' => 'rejected']);

        // 4. Atualiza o status do projeto para "in_progress"
        $proposal->project->update(['status' => 'in_progress']);

        return response()->json([
            'message' => 'Proposta aceita com sucesso.',
            'proposal' => $proposal->fresh(),
            'project' => $proposal->project
        ]);
    }

    public function reject(Proposal $proposal)
    {
        $user = Auth::user();

        // Verifica se o usuário é o dono do projeto
        if ($proposal->project->client_id !== $user->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // Impede que uma proposta aceita seja rejeitada
        if ($proposal->status === 'accepted') {
            return response()->json(['message' => 'Não é possível rejeitar uma proposta já aceita.'], 400);
        }

        // Atualiza a proposta para 'rejected'
        $proposal->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Proposta rejeitada com sucesso.',
            'proposal' => $proposal->fresh()
        ]);
    }
}
