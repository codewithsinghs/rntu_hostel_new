<?php

namespace App\Http\Controllers\Apis\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use App\Models\User;
use App\Helpers\Helper; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function AuthenticateGuests(Request $request)
    {
        try {
            $token = $request->header('token');
            $authId = $request->header('auth-id');
            if (!$token || !$authId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            // $user = User::Join(model_has_roles)->where('token', $token)->where('id', $authId)->first();
            $guest = \DB::table('guests')
                ->where('guests.token', $token)
                ->where('guests.id', $authId)
                ->select('guests.*')
                ->first();
            unset($guest->created_at, $guest->updated_at,);

            if (!$guest) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $guest,
            ]);
        } catch (\Exception $e) {
            // Log::error('LoginController@authenticateUsers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' =>
                'Something went wrong'], 500);
        }
    }
    public function authenticateUsers(Request $request)
    {
        try {
            $token = $request->header('token');
            $authId = $request->header('auth-id');
            if (!$token || !$authId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // $user = User::Join(model_has_roles)->where('token', $token)->where('id', $authId)->first();
            $user = \DB::table('users')
                ->where('users.token', $token)
                ->where('users.id', $authId)
                ->join('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', '=', User::class);
                })
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('users.*', 'roles.name as role_name')
                ->first();
            unset($user->created_at, $user->updated_at, $user->password);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('LoginController@authenticateUsers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' =>
                'Something went wrong'], 500);
        }
    }
    public function logout(Request $request)
    {
        try {
            $token = $request->header('token');
            $authId = $request->header('auth-id');

            if (!$token || !$authId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            
            $user = User::where('token', $token)->where('id', $authId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            
            // Invalidate the token
            $user->token = null;
            $user->token_expiry = null;
            $user->save();  
            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        }
        catch (\Exception $e) {
            Log::error('LoginController@logout: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }
    public function guestLogin(Request $request)
    {
        try
        {
            $credentials = $request->validate([
                'email' => 'required|email',
                'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'], // Indian mobile pattern
            ]);

            if($guest_details = Guest::where('email', $credentials['email'])->where('emergency_no', $credentials['mobile'])->first()) 
            {
                $token = Helper::generate_token();
                $guest_details->token = $token;
                $guest_details->token_expiry = Helper::generate_token_expiry();
                $guest_details->save();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'message' => 'Login successful',
                        'user' => $guest_details->makeHidden(['created_at', 'updated_at']),
                        'token' => $token,
                    ]
                ]);
            }
            else
            {
                return response()->json([
                    'success' => 0, 'message' => trans('auth.failed')]
                , 401); 
            }

        }
        catch (\Exception $e)
        {
            Log::error('LoginController@guestLogin: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' =>$e->getMessage(),
                // 'message' => trans('pages.something_wrong')
            ], 500);
        }

    }

    public function adminLogin(Request $request)
    {
    try {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6', // or min:8 for better security
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = Helper::generate_token();
            $user->token = $token;
            $user->token_expiry = Helper::generate_token_expiry();
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Login successful',
                    'user' => $user->makeHidden(['created_at', 'updated_at', 'password']),
                    'token' => $token,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => trans('auth.failed')
        ], 401);

    } catch (\Exception $e) {
        Log::error('LoginController@login: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() // Or use trans('pages.something_wrong')
        ], 500);
    }

    }
}