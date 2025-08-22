<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BedController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'bed_number' => [
                    'required',
                    Rule::unique('beds')->where(function ($query) use ($request) {
                        return $query->where('room_id', $request->room_id);
                    })
                ],
                'room_id' => 'required|exists:rooms,id',
            ]);
            $request['status'] = $request->input('status', 'available'); // Default to 'available' if not provided  

            $bed = Bed::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Bed created successfully',
                'data' => $bed,
                'errors' => null,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating bed.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function index()
    {
        try {
            $beds = Bed::with(['room.building'])->get();

            if ($beds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No beds found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Beds retrieved successfully',
                'data' => $beds,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch beds.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $bed = Bed::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Bed found',
                'data' => $bed,
                'errors' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching bed.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bed = Bed::findOrFail($id);

            $request->validate([
                // 'bed_number' => [
                //     'sometimes',
                //     'required',
                //     Rule::unique('beds')->ignore($id)->where(function ($query) use ($request) {
                //         if ($request->has('room_id')) {
                //             return $query->where('room_id', $request->room_id);
                //         }
                //         return $query;
                //     })
                // ],
                // 'room_id' => 'sometimes|required|exists:rooms,id',
                'status' => 'sometimes|required|in:available,occupied,maintenance',
            ]);

            $bed->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Bed updated successfully',
                'data' => $bed,
                'errors' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating bed.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $bed = Bed::findOrFail($id);
            $bed->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bed deleted successfully',
                'data' => null,
                'errors' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while deleting bed.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getAvailableBeds($room_id)
    {
        try {
            $availableBeds = Bed::where('room_id', $room_id)
                ->where('status', 'available')
                ->get();

            if ($availableBeds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available beds found for this room',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Available beds retrieved successfully',
                'data' => $availableBeds,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching available beds.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }
}
