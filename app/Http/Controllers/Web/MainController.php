<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Apis\V1\LoginController as LoginApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class MainController extends Controller
{

    protected $LoginAPIRef;

    public function __construct()
    {
        
        $this->LoginAPIRef = new LoginApi;
        $this->middleware('admin')->except(['adminLogout', 'guestLogout', 'residentLogout']);
    }

    public function processAdminLogin(Request $request){

      $credentials = $request->only('username', 'password');
      
      if (auth()->attempt($credentials)) {
         
        $user_details = Auth::user();

        if($user_details->user_type_term == \Helper::get_term(config('global.user_type_term'), config('global.user_type_admin')) ){
            
            $token = \Helper::generate_token();
            $user_details->token = $token;
            $user_details->token_expiry = \Helper::generate_token_expiry();
            $user_details->save();

            return response()->json(['success' => 1, 'message' => trans('auth.success_login'),  'redirect_url' => route('admin.dashboard') ]);
        
        }else{

        return response()->json(['success' => 0, 'message' => trans('auth.failed')]); 

        }
         
      }else{
        
          return response()->json(['success' => 0, 'message' => trans('auth.failed')]);
      }
    }

    
}
