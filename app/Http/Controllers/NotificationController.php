<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return response()->json([
            'notifications' => $user->notifications()->latest()->take(20)->get(),
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All marked as read']);
    }
}