<?php
namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Mostrar avaliações que o usuário recebeu
        $ratings = Rating::where('to_user_id', $user->id)->latest()->get();

        return response()->json($ratings);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id|different:'.$user->id,
            'project_id' => 'required|exists:projects,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $validated['from_user_id'] = $user->id;

        $rating = Rating::create($validated);

        return response()->json($rating, 201);
    }

    public function show(Rating $rating)
    {
        $this->authorize('view', $rating);
        return $rating;
    }

    public function update(Request $request, Rating $rating)
    {
        $this->authorize('update', $rating);

        $validated = $request->validate([
            'rating' => 'integer|min:1|max:5',
            'comment' => 'string',
        ]);

        $rating->update($validated);
        return $rating;
    }

    public function destroy(Rating $rating)
    {
        $this->authorize('delete', $rating);
        $rating->delete();
        return response()->noContent();
    }
}
