<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens; //追加
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;




class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $schedule = User::where('name', '=', strval($request->name))->orWhere('email', '=', strval($request->email))->first();
        if (!$schedule) {
            $rules = [
                //填入須符合的格式及長度
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password'=>'required|min:6|confirmed'
                // confirmed 是要密碼二次輸入的意思 
            ];
            $messages = [
                //驗證未通過的訊息提示
                'name.required' => '請填入帳號名稱',
                'email.required' => '請填入email信箱',
                'email.email' => '請填入正確的email信箱格式',
                'password.required' => '請填入密碼',
                'password.min' => '密碼至少要大於六個字元',
                // 'password.confirmed' => '兩次的密碼輸入不一致，請重新輸入',
              
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $messages = $validator->messages();
                $errors = $messages->all();
                $response = [
                    'success' => false,
                    'data' => "Error",
                    'message' => $errors[0],
                ];
                return response()->json($response, 202);
            }
            // $request->validate([
            //     'name' => ['required'],
            //     'email' => ['required', 'email', 'unique:users'],
            //     'password' => ['required', 'min:6',
            //         // 'confirmed'
            //     ],
            // ]);
            $data = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $data->createToken('my-app-token')->plainTextToken;

            return response()->json(['data'=>$data,'token'=>$token]);
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
