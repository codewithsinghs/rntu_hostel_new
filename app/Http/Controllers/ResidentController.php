<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Bed;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class ResidentController extends Controller
{
    public function getAllResidents()
    {
        try {
            $residents = Resident::with(['user', 'bed.room.building', 'guest', 'creator'])->get();

            return response()->json([
                'success' => true,
                'message' => 'All residents fetched successfully',
                'data' => $residents,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch residents',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getResidentById($id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid resident ID.',
                    'data' => null,
                    'errors' => ['resident_id' => ['The resident ID must be numeric.']],
                ], 400);
            }

            // Eager load 'user', 'bed' (and nested 'bed.room'), 'guest', 'creator' relationships
            $resident = Resident::with(['user', 'bed.room', 'guest', 'creator'])->find($id);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resident fetched successfully',
                'data' => $resident,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch resident',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getResidentProfile(Request $request)
    {
        try {
            $residentId = Helper::get_resident_details($request->header('auth-id'));
            $resident = Resident::with(['user', 'bed.room.building', 'guest', 'creator'])
                ->where('id', $residentId)
                ->first();

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resident profile fetched successfully',
                'data' => $resident,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch resident profile',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function assignBed(Request $request)
    {
        try {
            $validated = $request->validate([
                'resident_id' => 'required|integer|exists:residents,id',
                'bed_id' => 'required|integer|exists:beds,id',
            ]);

            $resident = Resident::findOrFail($validated['resident_id']);
            $bed = Bed::findOrFail($validated['bed_id']);

            if (!is_null($resident->bed_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident already has a bed assigned.',
                    'data' => ['current_bed_id' => $resident->bed_id],
                    'errors' => null,
                ], 400);
            }

            if ($bed->status === 'occupied') {
                return response()->json([
                    'success' => false,
                    'message' => 'This bed is already occupied.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $resident->bed_id = $bed->id;
            $resident->status = 'active';
            $resident->save();

            $bed->status = 'occupied';
            $bed->save();

            return response()->json([
                'success' => true,
                'message' => 'Bed assigned successfully.',
                'data' => [
                    'resident_id' => $resident->id,
                    'bed_id' => $bed->id,
                ],
                'errors' => null,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resident or bed not found.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while assigning bed.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getUnassignedResidents()
    {
        // Log::info('Fetching unassigned residents');
        try {
            $residents = Resident::whereNull('bed_id')
                ->where('status', 'pending')
                ->with(['user:id,name', 'guest:id,gender,scholar_no'])
                ->get()
                ->map(function ($resident) {
                    return [
                        'id' => $resident->id,
                        'name' => optional($resident->user)->name,
                        'gender' => optional($resident->guest)->gender,
                        'scholar_number' => optional($resident->guest)->scholar_no,
                    ];
                });
            Log::info('Unassigned residents fetched successfully', ['count' => $residents->count()]);
            return response()->json([
                'success' => true,
                'message' => 'Unassigned residents fetched successfully.',
                'data' => $residents,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unassigned residents.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }
}
