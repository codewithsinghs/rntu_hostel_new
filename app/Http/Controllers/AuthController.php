<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Determine panel based on user role
        if ($user->hasRole('super_admin')) {
            $panel = 'super_admin_panel';
        } elseif ($user->hasRole('admin')) {
            $panel = 'admin_panel';
        } elseif ($user->hasRole('resident')) {
            $panel = 'resident_panel';
        } else {
            $panel = 'default';
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'redirect_panel' => $panel,
        ]);
    }



    protected function authenticated(Request $request, $user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect('/superadmin');
        } elseif ($user->hasRole('admin')) {
            return redirect('/admin/dashboard');
        } elseif ($user->hasRole('resident')) {
            return redirect('/resident/dashboard');
        } else {
            Auth::logout();
            return redirect('/login')->withErrors(['error' => 'Unauthorized role.']);
        }
    }



    public function showLoginForm()
    {
        return view('auth.login'); // this should match the blade filename
    }

    public function login (Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->hasRole('super_admin')) {
                return redirect('/super-admin/dashboard');
            } elseif ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } elseif ($user->hasRole('resident')) {
                return redirect('/resident/dashboard');
            } else {
                Auth::logout();
                return redirect('/login')->withErrors(['Unauthorized role.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
