<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log; // Import Log facade
// use AWS\CRT\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoomController extends Controller
{
    /**
     * Store a new room.
     */
    public function store(Request $request)
    {
        try {
        $validated = $request->validate([
            'room_number' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Room::where('room_number', $value)
                        ->where('building_id', $request->building_id)
                        ->exists();
                    if ($exists) {
                        $fail("The $attribute has already been taken for this building.");
                    }
                }
            ],
            'building_id' => 'required|exists:buildings,id',
            'floor_no' => 'required|integer|min:1',
            
        ]);

        $validated['status'] = $request->input('status', 'available'); // Default to 'available' if not provided

            $room = Room::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Room created successfully',
                'data' => $room,
                'errors' => null
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Show all rooms.
     */
    public function index()
    {
        try {
            // $rooms = Room::all();
            $rooms = Room::with('building')->get(); // Eager load the building relationship
            $rooms->transform(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'building_id' => $room->building_id,
                    'building_name' => $room->building ? $room->building->name : 'Unknown',
                    'floor_no' => $room->floor_no,
                    'status' => $room->status,
                ];
            });
            if ($rooms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rooms found',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rooms retrieved successfully',
                'data' => $rooms,
                'errors' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Show a single room by ID.
     */
    public function show($id)
    {
        try {
            $room = Room::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Room found',
                'data' => $room,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch room',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update an existing room.
     */
    public function update(Request $request, $id)
    {
        try {
            $room = Room::findOrFail($id);

            $validated = $request->validate([
                'room_number' => 'sometimes|required|unique:rooms,room_number,' . $room->id . ',id,building_id,' . $room->building_id,
                'building_id' => 'sometimes|required|exists:buildings,id',
                'floor_no' => 'sometimes|required|integer|min:1',
                'status' => 'sometimes|required|in:available,occupied,maintenance',
            ]);

            $room->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'data' => $room,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Delete a room by ID.
     */
    public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully',
                'data' => null,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get rooms by building ID.
     */
    public function getRooms($id)
    {
        try {
            $rooms = Room::where('building_id', $id)->get();

            if ($rooms->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rooms found for this building',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rooms for building retrieved successfully',
                'data' => $rooms,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms for building',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }
}
