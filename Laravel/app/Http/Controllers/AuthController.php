<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VicidailUser;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|string',
            'password' => 'required|string'
        ]);
    
        if ($validator->fails())
            return response()->json($validator->errors(), 400);
    
        $user = VicidailUser::where([
            ['user', '=', $request->user],
            ['pass', '=', $request->password]
        ])->first();

        if(!$user)
            return response()->json('Unauthorized', 401);
        
        $token = $user->createToken('access-token')->plainTextToken;
        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked']);
    }
}
