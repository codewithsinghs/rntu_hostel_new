<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use Illuminate\Http\Request;
use App\Models\Bed;
use App\Models\RoomChangeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\RoomChangeMessage;
use Illuminate\Support\Str;
use App\Helpers\Helper;



class RoomChangeController extends Controller
{
    public function requestRoomChange(Request $request)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string',
                'preference' => 'nullable|string',
            ]);
            $validated['created_by'] = $request->header('auth-id'); // Admin ID from header
            $resident= Helper::get_resident_details($request->header('auth-id'));
            
            $requestData = RoomChangeRequest::create([
                'resident_id' => $resident->id,
                'reason' => $validated['reason'],
                'preference' => $validated['preference'] ?? null,
                'action' => 'pending',
                'created_by' => $validated['created_by'],
                'token' => Str::random(30),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room change request submitted successfully.',
                'data' => $requestData,
                'errors' => null
            ], 201);
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
                'message' => 'An error occurred while submitting the room change request.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

   public function getAllRoomChangeRequests()
    {
        try {
            $requests = RoomChangeRequest::with([
                'resident.user:id,name',
                'resident.room'
            ])->get();

            $data = $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'token' => $request->token,
                    'resident_name' => $request->resident->user->name ?? null,
                    'room_number' => $request->resident->room->room_number ?? null,
                    'reason' => $request->reason,
                    'preference' => $request->preference,
                    'action' => $request->action,
                    // Add the 'resident_agree' field from the RoomChangeRequest
                    'resident_agree' => $request->resident_agree,
                    'created_by' => $request->created_by,
                    'created_at' => $request->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Room change requests retrieved successfully.',
                'data' => $data,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch room change requests.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()]
            ], 500);
        }
    }

    public function respondToRequest(Request $request, $request_id)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:available,not_available',
                'remark' => 'nullable|string',
            ]);

            $roomRequest = RoomChangeRequest::findOrFail($request_id);
            $roomRequest->update([
                'remark' => $validated['remark'] ?? null,
            ]);

            RoomChangeMessage::create([
                'room_change_request_id' => $request_id,
                'sender' => 'admin',
                'message' => $validated['remark'] ?? 'No remark',
                'created_by' => $request->header('auth-id'), // Admin ID from header
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Response updated successfully.',
                'data' => $roomRequest,
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null
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
                'message' => 'Failed to update the room change request.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    public function respondToAdmin(Request $request, $request_id)
    {
        try {
            $validated = $request->validate([
                'resident_agree' => 'required|boolean',
                'message' => 'nullable|string',
                'created_by' => 'required|integer',
            ]);

            $roomRequest = RoomChangeRequest::findOrFail($request_id);

            RoomChangeMessage::create([
                'room_change_request_id' => $request_id,
                'sender' => 'resident',
                'message' => $validated['message'] ?? 'No message',
                'created_by' => $validated['created_by'],
            ]);

            if ($validated['resident_agree']) {
                $roomRequest->update(['resident_agree' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resident response updated successfully.',
                'data' => $roomRequest,
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null
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
                'message' => 'Failed to update the resident response.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    public function denyRoomChangeByAdmin(Request $request, $request_id)
    {
        try {
            $validated = $request->validate([
                'remark' => 'required|string',
            ]);

            $roomRequest = RoomChangeRequest::findOrFail($request_id);

            $roomRequest->update([
                'action' => 'not_available',
                'remark' => $validated['remark'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room change request marked as not available by admin.',
                'data' => $roomRequest,
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null
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
                'message' => 'Failed to update the room change request.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    public function confirmRoomChange(Request $request, $request_id)
    {
        try {
            $roomRequest = RoomChangeRequest::findOrFail($request_id);

            $roomRequest->update([
                'resident_agree' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Room change confirmed by resident successfully.',
                'data' => $roomRequest,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request not found.',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm room change.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    public function finalApproval($request_id)
    {
        try {
            $roomRequest = RoomChangeRequest::findOrFail($request_id);

            if ($roomRequest->resident_agree !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident has not agreed yet.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $resident = $roomRequest->resident;
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            $roomRequest->update(['action' => 'completed']);

            if ($resident->bed_id) {
                $bed = Bed::find($resident->bed_id);
                if ($bed) {
                    $bed->update(['status' => 'available']);
                }
                $resident->update(['bed_id' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Room change process completed successfully.',
                'data' => null,
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room change request or resident not found.',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete room change process.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    public function getRoomChangeRequestsByResidentId($residentId)
    {
    try {
        $requests = RoomChangeRequest::with([
            'resident.user:id,name',
            'resident.room'
        ])->where('resident_id', $residentId)->get();

        $data = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'token' => $request->token,
                'resident_name' => $request->resident->user->name ?? null,
                'room_number' => $request->resident->room->room_number ?? null,
                'reason' => $request->reason,
                'preference' => $request->preference,
                'action' => $request->action,
                'created_by' => $request->created_by,
                'created_at' => $request->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Room change requests retrieved successfully.',
            'data' => $data,
            'errors' => null
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch room change requests for this resident.',
            'data' => null,
            'errors' => ['details' => $e->getMessage()]
        ], 500);
    }
}

    public function getRoomChangeRequests(Request $request)
    {
    try {
        $residentId = Resident::where('user_id', $request->header('auth-id'))->value('id');
        $requests = RoomChangeRequest::with([
            'resident.user:id,name',
            'resident.room'
        ])->where('resident_id', $residentId)->get();

        $data = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'token' => $request->token,
                'resident_name' => $request->resident->user->name ?? null,
                'room_number' => $request->resident->room->room_number ?? null,
                'reason' => $request->reason,
                'preference' => $request->preference,
                'action' => $request->action,
                'created_by' => $request->created_by,
                'created_at' => $request->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Room change requests retrieved successfully.',
            'data' => $data,
            'errors' => null
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch room change requests for this resident.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()]
            ], 500);
        }
    }

    public function getRoomChangeRequestsById(Request $request, $id)
    {
    try {
        $residentId = Resident::where('user_id', $request->header('auth-id'))->value('id');
        $requests = RoomChangeRequest::with([
            'resident.user:id,name',
            'resident.room'
        ])->where('resident_id', $residentId)->where('id', $id)->get();

        $data = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'token' => $request->token,
                'resident_name' => $request->resident->user->name ?? null,
                'room_number' => $request->resident->room->room_number ?? null,
                'reason' => $request->reason,
                'preference' => $request->preference,
                'action' => $request->action,
                'created_by' => $request->created_by,
                'created_at' => $request->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Room change requests retrieved successfully.',
            'data' => $data,
            'errors' => null
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch room change requests for this resident.',
                'data' => null,
                'errors' => ['details' => $e->getMessage()]
            ], 500);
        }
    }
}