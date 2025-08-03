<?php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        // Mostrar mensagens do usuário logado (enviadas ou recebidas)
        $userId = Auth::id();

        $messages = Message::with(['sender', 'receiver'])->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest()
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'content' => 'required|string',
        ]);

        $validated['sender_id'] = Auth::id();

        $message = Message::create($validated);

        // Aqui você pode disparar evento para notificação ou broadcast

        return response()->json($message, 201);
    }

    public function show(Message $message)
    {
        $this->authorize('view', $message);
        return $message;
    }

    public function update(Request $request, Message $message)
    {
        $this->authorize('update', $message);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $message->update($validated);
        return $message;
    }

    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        $message->delete();
        return response()->noContent();
    }
}
