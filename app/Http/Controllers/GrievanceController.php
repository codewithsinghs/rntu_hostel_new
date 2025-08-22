<?php

namespace App\Http\Controllers;

use App\Models\Grievance;
use App\Models\GrievanceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PHPUnit\TextUI\Help;
use App\Helpers\Helper;

class GrievanceController extends Controller
{
    // 1. Submit a grievance
    public function submitGrievance(Request $request)
    {
        try {
            $validated = $request->validate([
                'type_of_complaint' => 'required|string|max:255',
                'description' => 'required|string',
                'token_id' => 'required|string|unique:grievances,token_id',
                'photo' => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
            ]);

            $photoPath = $request->hasFile('photo')
                ? $request->file('photo')->store('photos')
                : null;

            $grievance = Grievance::create([
                'resident_id' => Helper::get_resident_details($request->header('auth-id'))->id,
                'created_by' => $request->header('auth-id'),
                'type_of_complaint' => $validated['type_of_complaint'],
                'description' => $validated['description'],
                'token_id' => $validated['token_id'],
                'photo' => $photoPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grievance submitted successfully',
                'data' => $grievance,
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Submit grievance failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // 2. Admin responds to a grievance
    public function respondToGrievance(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'description' => 'required|string',
            ]);

            $grievance = Grievance::find($id);

            if (!$grievance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grievance not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $grievance->update(['status' => 'agreed_by_resident']);

            $response = GrievanceResponse::create([
                'grievance_id' => $id,
                'created_by' => $grievance->created_by,
                'responded_by' => $request->header('auth-id'),
                'description' => $validated['description'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grievance responded successfully',
                'data' => $response,
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Admin response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // 3. Resident responds to a grievance
    public function residentRespond(Request $request, $id)
    {
        try {
            // $resident_id= Helper::get_resident_details($request->header('auth-id'))->id;
            $resident_id = $request->header('auth-id');
            $validated = $request->validate([                
                'description' => 'required|string',
            ]);

            $grievance = Grievance::find($id);
            if (!$grievance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grievance not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $response = GrievanceResponse::create([
                'grievance_id' => $id,
                'responded_by' => $resident_id,
                'description' => $validated['description'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resident responded successfully',
                'data' => $response,
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Resident response failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // 4. Get all grievances (for Admin)
    public function getAllGrievances()
    {
        try {
            // Eager load 'responses' and 'resident' with only 'id' and 'name' from its associated 'user'
            $grievances = Grievance::with(['responses', 'resident.user:id,name'])->get();

            $data = $grievances->map(function ($grievance) {
                return [
                    'id' => $grievance->id,
                    'resident_id' => $grievance->resident_id, // <-- ADDED THIS LINE
                    'resident_name' => optional($grievance->resident->user)->name,
                    'type_of_complaint' => $grievance->type_of_complaint,
                    'description' => $grievance->description,
                    'token_id' => $grievance->token_id,
                    'photo' => $grievance->photo,
                    'status' => $grievance->status,
                    'responses' => $grievance->responses,
                    'created_at' => $grievance->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Grievances fetched successfully',
                'data' => $data,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch grievances', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch grievances',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }


    // 5. Get grievance by ID (Admin/Resident)
    public function getGrievanceById($id)
    {
        try {
            $grievance = Grievance::with('responses')->find($id);

            if (!$grievance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grievance not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grievance fetched successfully',
                'data' => $grievance,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get grievance by ID', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // 6. Close grievance (by Resident)
    public function closeGrievance(Request $request, $id)
    {
        try {
            $grievance = Grievance::find($id);

            if (!$grievance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grievance not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            if ($grievance->status == 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Grievance already closed',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $grievance->update(['status' => 'closed']);

            return response()->json([
                'success' => true,
                'message' => 'Grievance closed successfully',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to close grievance', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }

    // 7. Get grievance by resident id
    public function getGrievancesByResident($resident_id)
    {
        try {
            $grievances = Grievance::where('resident_id', $resident_id)->with('responses')->get();

            if ($grievances->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No grievances found for this resident.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grievances fetched successfully',
                'data' => $grievances,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch resident grievances', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'data' => null,
                'errors' => ['details' => $e->getMessage()],
            ], 500);
        }
    }
}
