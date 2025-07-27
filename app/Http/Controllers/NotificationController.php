<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'message' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();
        $notification = Notification::create($validated);

        return response()->json($notification, 201);
    }

    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);
        return $notification;
    }

    public function update(Request $request, Notification $notification)
    {
        $this->authorize('update', $notification);

        $validated = $request->validate([
            'read' => 'required|boolean',
        ]);

        $notification->update($validated);
        return $notification;
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        $notification->delete();
        return response()->noContent();
    }
}
