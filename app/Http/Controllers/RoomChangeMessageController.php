<?php

namespace App\Http\Controllers;


use App\Models\RoomChangeMessage;
use App\Models\RoomChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class RoomChangeMessageController extends Controller
{
    // Send message (by resident or admin)
    public function sendMessage(Request $request, $request_id)
    {
        try {
            $validated = $request->validate([
                'sender' => 'required|in:admin,resident',
                'message' => 'required|string',
            ]);

            $roomChangeRequest = RoomChangeRequest::findOrFail($request_id);

            $message = RoomChangeMessage::create([
                'room_change_request_id' => $roomChangeRequest->id,
                'created_by' => $request->header('auth-id'),
                'sender' => $validated['sender'],
                'message' => $validated['message'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'data' => $message,
                'errors' => null,
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // Get all messages related to a room change request
    public function getMessages($request_id)
    {
        try {
            $roomChangeRequest = RoomChangeRequest::findOrFail($request_id);

            $messages = RoomChangeMessage::where('room_change_request_id', $request_id)
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Messages fetched successfully.',
                'data' => $messages,
                'errors' => null,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch messages.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }
}
