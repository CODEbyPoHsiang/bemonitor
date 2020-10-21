<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens; //追加


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $schedule = User::where('name', '=', strval($request->name))->orWhere('email', '=', strval($request->email))->first();
        if (!$schedule) {
            $request->validate([
                'name' => ['required'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'min:6',
                    // 'confirmed'
                ],
            ]);

            $data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            return response()->json($data);
        }else {
            //ip重複時的提示訊息
            $response = [
                'success' => false,
                'message' => '輸入的名稱或信箱已重複，請重新操作',
                "isIpAvailable" => "no",
            ];
            return response()->json($response, 202);
        }
    }
}
