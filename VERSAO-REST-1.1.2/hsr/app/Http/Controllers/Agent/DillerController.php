<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DillerController extends Controller {

    public function pause(Request $request) {
        $validator = Validator::make($request->all(), [
            'pause_reason' => 'required|integer|in:1,2,5,10'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Auth::user();
        // Efetuar a pausa do usuário $user
        return response()->json(["pause_reason" => $request->pause_reason], 200);
    }

    public function unpause(Request $request) {
        $user = Auth::user();
        // Remover a pausa do usuário $user
        return response()->json([], 200);
    }

    public function dial(Request $request) {
        $validator = Validator::make($request->all(), [
            'number' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = Auth::user();
        // Efetuar a chamada para o agente $user
        return response()->json([], 200);
    }

    public function hangup(Request $request) {
        $user = Auth::user();
        // Desligar a chamada do agente $user
        return response()->json([], 200);
    }

    public function status($id) {
        $agent = Agent::find($id);
        if (!$agent) return response()->json('Not found', 404);

        return response()->json($agent->status);
    }
}


    public function status(Request $request) {
        $user = Auth::user();
    // Recuperar o status do agente $user
        $status = []; // Substitua por código para obter o status real do agente
        return response()->json($status, 200);
    }
}
