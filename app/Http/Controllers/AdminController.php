<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\User;
use App\Models\Resident;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Exception;
use App\Helpers\Helper;

class AdminController extends Controller
{
    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $response['data'] = $data !== null ? $data : null;
        $response['errors'] = $errors !== null ? $errors : null;

        return response()->json($response, $statusCode);
    }

    public function getAdminProfile(Request $request)
    {
        try {
            $admin = User::Where('id', $request->header('auth-id'))
                ->first();

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not found',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin profile fetched successfully',
                'data' => $admin,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Admin profile',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }


    public function getUniversity()
    {
        try {
            $admin = auth()->user();
            $university = University::findOrFail($admin->university_id);

            // Wrap university in data key
            return $this->apiResponse(true, 'University details fetched successfully.', [
                'university' => $university
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch university details.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    public function createResident(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'bed_id' => 'required|exists:beds,id',
            ]);

            $adminId = $request->header('auth-id'); // Admin ID is passed in header
            if (!$adminId) {
                return $this->apiResponse(false, 'Unauthorized.', null, 401);
            }

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $residentRole = Role::where('name', 'resident')->first();
            if (!$residentRole) {
                return $this->apiResponse(false, 'Resident role not found.', null, 500);
            }

            $user->assignRole($residentRole);

            $resident = Resident::create([
                'user_id' => $user->id,
                'bed_id' => $validatedData['bed_id'],
                'created_by' => $adminId,
            ]);

            // Return only safe data for user (hide password etc)
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];

            return $this->apiResponse(true, 'Resident created and assigned to bed successfully.', [
                'user' => $userData,
                'resident' => $resident
            ], 201);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'An error occurred while creating resident.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    public function guestApproval(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'guest_id' => 'required|exists:guests,id'
            ]);

            $guest = Guest::findOrFail($validatedData['guest_id']);

            if ($guest->status === 'approved') {
                return $this->apiResponse(false, 'Payment request already approved.', null, 400);
            }

            $guest->status = 'approved';
            $guest->save();

            return $this->apiResponse(true, 'Guest approved successfully.', [
                'guest' => [
                    'id' => $guest->id,
                    'status' => $guest->status,
                ]
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'An error occurred while approving guest.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // public function rejectPaymentRequest(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'guest_id' => 'required|exists:guests,id'
    //         ]);

    //         $guest = Guest::findOrFail($validatedData['guest_id']);

    //         if ($guest->status === 'rejected') {
    //             return $this->apiResponse(false, 'Payment request already rejected.', null, 400);
    //         }

    //         $guest->status = 'rejected';
    //         $guest->save();

    //         return $this->apiResponse(true, 'Guest payment request rejected successfully.', [
    //             'guest' => [
    //                 'id' => $guest->id,
    //                 'status' => $guest->status,
    //             ]
    //         ]);
    //     } catch (ValidationException $e) {
    //         return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
    //     } catch (Exception $e) {
    //         return $this->apiResponse(false, 'An error occurred while rejecting guest payment request.', null, 500, ['error' => $e->getMessage()]);
    //     }
    // } without admin remakrs


    public function rejectPaymentRequest(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'guest_id' => 'required|exists:guests,id',
                'admin_remarks' => 'nullable|string|max:1000', // Make it nullable if not always required, otherwise 'required'
            ]);

            $guest = Guest::findOrFail($validatedData['guest_id']);

            if ($guest->status === 'rejected') {
                return $this->apiResponse(false, 'Payment request already rejected.', null, 400);
            }

            $guest->status = 'rejected';
            $guest->admin_remarks = $validatedData['admin_remarks'] ?? null; // Store the remarks
            $guest->save();

            return $this->apiResponse(true, 'Guest payment request rejected successfully.', [
                'guest' => [
                    'id' => $guest->id,
                    'status' => $guest->status,
                    'admin_remarks' => $guest->admin_remarks, // Include in response
                ]
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error rejecting guest payment request: ' . $e->getMessage(), [
                'guest_id' => $request->input('guest_id'),
                'exception' => $e
            ]);
            return $this->apiResponse(false, 'An error occurred while rejecting guest payment request.', null, 500, ['error' => $e->getMessage()]);
        }
    }




}
