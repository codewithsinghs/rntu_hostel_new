<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\NotificationService;

class NotificationTestController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'subject' => 'nullable|string'
        ]);

        $user = User::findOrFail($request->user_id);

        NotificationService::sendAll($user, $request->message, $request->subject ?? 'Notification');

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully'
        ]);
    }
}
