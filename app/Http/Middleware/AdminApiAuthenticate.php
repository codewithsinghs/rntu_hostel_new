<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;


class AdminApiAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // this will override token and auth if  passed in query parm
        if($request->has('token') && $request->has('auth-id')){
            $request->headers->set('token', $request->input('token'));
            $request->headers->set('auth-id', $request->input('auth-id'));
        }

        if(!$request->header('token') || $request->header('token') == ""){
            $response = ['success' => 0, 'message' => trans('validation.required', ['attribute' => 'token'])];
            redirect('admin/login');
            // return response()->json($response, 200);
        }

        if(!$request->header('auth-id') || $request->header('auth-id') == ""){
            $response = ['success' => 0, 'message' => trans('validation.required', ['attribute' => 'auth-id'])];
            return response()->json($response, 200);
        }

        $auth_id = $request->header('auth-id');
        $token = $request->header('token');

        if(!Helper::is_token_valid_admin($auth_id, $token, $error)){
            $response = response()->json($error, 200);
            return $response;
        }

        return $next($request);

    }
}
