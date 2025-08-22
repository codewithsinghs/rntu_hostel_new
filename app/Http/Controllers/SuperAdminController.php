<?php


namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class SuperAdminController extends Controller
{
    // Unified response method
    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors
        ], $statusCode);
    }

    /**
     * Create a new admin
     */
    public function createAdmin(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'required|string|max:255',
                'email'         => 'required|email|unique:users,email',
                'password'      => 'required|string|min:6',
                'university_id' => 'required|exists:universities,id'
            ]);

            $admin = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'university_id' => $request->university_id
            ]);

            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

            DB::table('model_has_roles')->insert([
                'role_id'    => $adminRole->id,
                'model_id'   => $admin->id,
                'model_type' => User::class,
            ]);

            return $this->apiResponse(true, 'Admin created successfully.', $admin, 201);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Server error.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all admins
     */
    public function getAdmins()
    {
        try {
            $admins = User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->get();
            return $this->apiResponse(true, 'Admins fetched successfully.', $admins);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve admins.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a single admin
     */
    public function getAdmin($id)
    {
        try {
            $admin = User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->find($id);

            if (!$admin) {
                return $this->apiResponse(false, 'Admin not found.', null, 404);
            }

            return $this->apiResponse(true, 'Admin fetched successfully.', $admin);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve admin.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an admin
     */
    public function updateAdmin(Request $request, $id)
    {
        try {
            $request->validate([
                'name'          => 'sometimes|required|string|max:255',
                'email'         => 'sometimes|required|email|unique:users,email,' . $id,
                'password'      => 'sometimes|required|string|min:6|confirmed',
                'university_id' => 'sometimes|required|exists:universities,id'
            ]);

            $admin = User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->find($id);

            if (!$admin) {
                return $this->apiResponse(false, 'Admin not found.', null, 404);
            }

            $admin->update([
                'name'          => $request->name ?? $admin->name,
                'email'         => $request->email ?? $admin->email,
                'password'      => $request->password ? Hash::make($request->password) : $admin->password,
                'university_id' => $request->university_id ?? $admin->university_id,
            ]);

            return $this->apiResponse(true, 'Admin updated successfully.', $admin);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Server error.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete an admin
     */
    public function deleteAdmin($id)
    {
        try {
            $admin = User::whereHas('roles', fn($query) => $query->where('name', 'admin'))->find($id);

            if (!$admin) {
                return $this->apiResponse(false, 'Admin not found.', null, 404);
            }

            $admin->delete();

            return $this->apiResponse(true, 'Admin deleted successfully.');
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete admin.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}
