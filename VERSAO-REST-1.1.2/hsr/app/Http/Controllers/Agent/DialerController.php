<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VicidialLiveAgent;
use Illuminate\Support\Facades\Validator;

class DialerController extends Controller
{
    private static $validatorFields = [
        'user' => 'required|string|min:2|max:20',
        'pass' => 'required|string|min:1|max:20',
        'user_level' => 'required|int|min:1|max:9',
        'full_name' => 'required|string|min:1|max:50',
        'user_group' => 'required|int|min:1|max:20'
    ];

    public function store(Request $request) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $agent = VicidialLiveAgent::create($request->all());
        return response()->json($agent, 201);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $agent = VicidialLiveAgent::find($id);
        if (!$agent) return response()->json('Not found', 404);
        $agent->update($request->all());
        return response()->json('Success', 200);
    }

    public function pause($id) {
        $agent = VicidialLiveAgent::find($id);
        if (!$agent) return response()->json('Not found', 404);
        $agent->update(['status' => 'PAUSED']);
        return response()->json('Agent paused', 200);
    }

    public function unpause($id) {
        $agent = VicidialLiveAgent::find($id);
        if (!$agent) return response()->json('Not found', 404);
        $agent->update(['status' => 'ACTIVE']);
        return response()->json('Agent unpaused', 200);
    }

    public function allStatus() {
        $agents = VicidialLiveAgent::all();
        return response()->json($agents);
    }

    public function status($id) {
        $agent = VicidialLiveAgent::find($id);
        if (!$agent) return response()->json('Not found', 404);
        return response()->json($agent->status);
    }
}
