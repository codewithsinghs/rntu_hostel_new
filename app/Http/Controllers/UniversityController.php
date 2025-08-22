<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class UniversityController extends Controller
{
    // Standardized API Response Method
    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * GET /universities
     * Display all universities
     */
    public function index()
    {
        try {
            $universities = University::all();
            return $this->apiResponse(true, 'Universities fetched successfully.', $universities);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch universities.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /universities
     * Store a new university (Only Super Admin)
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name'      => 'required|string|unique:universities',
                'location'  => 'required|string',
                'state'     => 'required|string',
                'district'  => 'required|string',
                'pincode'   => 'required|string|max:6|unique:universities',
                'address'   => 'required|string',
                'mobile'    => 'required|string|max:15|unique:universities',
                'email'     => 'required|email|unique:universities'
            ]);

            $university = University::create($validatedData);
            return $this->apiResponse(true, 'University created successfully.', $university, 201);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Server error.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /universities/staff
     * Get all staff members (Only those with specific roles)
     */
    public function getAllStaff()
    {
        try {
            $staff = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['warden', 'security', 'mess_manager', 'gym_manager', 'hod']);
            })
            ->with(['roles:id,name', 'building:id,name'])
            ->select('id', 'name', 'email', 'building_id')
            ->get();

            $formatted = $staff->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'building_id' => $user->building_id,
                    'building_name' => optional($user->building)->name,
                    'roles' => $user->roles->pluck('name'),
                ];
            });

            return $this->apiResponse(true, 'Staff fetched successfully.', $formatted);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch staff.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * PUT /universities/{id}
     * Update a university (Only Super Admin)
     */
    public function update(Request $request, $id)
    {
        try {
            $university = University::find($id);
            if (!$university) {
                return $this->apiResponse(false, 'University not found.', null, 404);
            }

            $validatedData = $request->validate([
                'name'      => 'sometimes|string|unique:universities,name,' . $id,
                'location'  => 'sometimes|string',
                'state'     => 'sometimes|string',
                'district'  => 'sometimes|string',
                'pincode'   => 'sometimes|string|max:10|unique:universities,pincode,' . $id,
                'address'   => 'sometimes|string',
                'mobile'    => 'sometimes|string|max:15|unique:universities,mobile,' . $id,
                'email'     => 'sometimes|email|unique:universities,email,' . $id
            ]);

            $university->update($validatedData);
            return $this->apiResponse(true, 'University updated successfully.', $university);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Server error.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /universities/{id}
     * Delete a university (Only Super Admin)
     */
    public function destroy($id)
    {
        try {
            $university = University::find($id);
            if (!$university) {
                return $this->apiResponse(false, 'University not found.', null, 404);
            }

            $university->delete();
            return $this->apiResponse(true, 'University deleted successfully.', null);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete university.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * GET /universities/{id}
     * Get a single university by ID
     */
    public function show($id)
    {
        try {
            $university = University::find($id);
            if (!$university) {
                return $this->apiResponse(false, 'University not found.', null, 404);
            }

            return $this->apiResponse(true, 'University fetched successfully.', $university);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch university.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}
