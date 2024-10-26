<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExtensionController extends Controller
{
    public function index(Request $request)
    {
		return response()->json(Auth::user(), 200);
    }
    
    public function post(Request $request)
    {
		return response()->json('post', 201);
    }

    public function get(string $id)
    {
		return response()->json('get ' + $id, 200);
    }

    public function put(Request $request, string $id)
    {
		return response()->json('put ' + $id, 200);
    }
    
    public function delete(string $id)
    {
		return response()->json('delete ' + $id, 200);
    }
}
