<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens; //追加


class UserController extends Controller
{
    // 
    function login(Request $request)
    {
        $user= User::where('email', $request->email)->first();
        // print_r($data);
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => ['These credentials do not match our records.']
                ], 404);
            }
        
             $token = $user->createToken('my-app-token')->plainTextToken;
        
            $response = [
                'success'=>true,
                'user' => $user,
                'token' => $token
            ];
        
             return response()->json($response, 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();


        $response = [
            'success' => true,
            'message' => '已成功登出',
        ];
        return response()->json($response, 200);   
     }
}