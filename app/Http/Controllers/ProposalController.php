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
            $proposals = Proposal::where('freelancer_id', $user->id)->latest()->get();
        } else {
            $proposals = Proposal::whereHas('project', function ($query) use ($user) {
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
        return response()->json(Proposal::latest()->get());
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
}
