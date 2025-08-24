<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StaffController extends Controller
{
    public function createStaff(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'building_id' => 'required|exists:buildings,id',
                'role' => 'required|string|in:warden,security,mess_manager,gym_manager,hod,accountant',
            ]);

            $staff = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'building_id' => $validated['building_id'],
            ]);

            $staff->assignRole($validated['role']);

            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully',
                'data' => $staff,
                'errors' => null,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getStaffDetails(Request $request, $id)
    {
        try {
            $users = User::whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['super_admin', 'admin', 'resident']);
            })->with('roles:id,name')->where('id',$id)->select('id', 'name', 'email','building_id')->get();


            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staff found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully',
                'data' => $users,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching staff details: ' . $e->getMessage(), [
                'id' => $id,
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching staff',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }


    public function getStaff()
    {
        try {
            $users = User::whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['super_admin', 'admin', 'resident']);
            })->with('roles:id,name')->select('id', 'name', 'email')->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staff found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff retrieved successfully',
                'data' => $users,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching staff',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function getAllStaff()
    {
        try {
            $staff = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['warden', 'security', 'mess_manager', 'gym_manager', 'hod','accountant']);
            })
                ->with([
                    'roles:id,name',
                    'building:id,name'
                ])
                ->select('id', 'name', 'email', 'building_id')
                ->get();

            if ($staff->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No staff found for specified roles',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'All staff retrieved successfully',
                'data' => $staff,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching all staff',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    public function updateStaff(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($id),
                ],
                // 'password' => 'sometimes|nullable|string|min:6',
                'building_id' => 'sometimes|required|exists:buildings,id',
                'role' => 'sometimes|required|string|in:warden,security,mess_manager,gym_manager',
            ]);

            $staff = User::findOrFail($id);

            if (isset($validated['name'])) $staff->name = $validated['name'];
            if (isset($validated['email'])) $staff->email = $validated['email'];
            if (isset($validated['password'])) $staff->password = Hash::make($validated['password']);
            if (isset($validated['building_id'])) $staff->building_id = $validated['building_id'];

            $staff->save();

            if (isset($validated['role'])) {
                $staff->syncRoles([$validated['role']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully',
                'data' => $staff,
                'errors' => null,
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }
}
