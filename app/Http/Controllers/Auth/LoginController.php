<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guest;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Apis\V1\LoginController as ApiLogin;

class LoginController extends Controller
{
    protected $APILoginRef;

    public function __construct() {
        $this->APILoginRef = new APILogin;
    }


    public function showLoginForm()
    {
        return view('auth.login');
    }



    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->hasRole('super_admin')) {
                return redirect('/superadmin');
            } elseif ($user->hasRole('admin')) {
                return redirect('/admin/dashboard');
            } elseif ($user->hasRole('resident')) {
                return redirect('/resident/dashboard');
            }elseif ($user->hasRole('accountant')) {
                    return redirect('/accountant/dashboard');
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
