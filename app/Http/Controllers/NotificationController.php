<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->take(20)->get();
        $formattedNotifications = $notifications->map(function ($notif) {
            return [
                'id' => $notif->id,
                'type' => $notif->type,
                'notifiable_type' => $notif->notifiable_type,
                'notifiable_id' => $notif->notifiable_id,
                'data' => $notif->data,
                'read_at' => $notif->read_at ? $notif->read_at->toISOString() : null,
                'created_at' => $notif->created_at->toISOString(),
                'updated_at' => $notif->updated_at->toISOString(),
                'created_at_human' => $notif->created_at->diffForHumans(), 
            ];
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($formattedNotifications);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id): JsonResponse
    {
        $notification = Auth::user()->unreadNotifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }
}
