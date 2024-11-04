<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DNCController extends Controller
{
    // Método para listar todos os números na lista DNC
    public function index()
    {
        $dncNumbers = DB::table('vicidial_dnc')->get();
        return response()->json($dncNumbers, 200);
    }

    // Inserir número na lista DNC
    public function addNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15'
        ]);

        $phone_number = $request->input('phone_number');

        // Verificar se o número já está na lista DNC
        $exists = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();
        if ($exists) {
            return response()->json(['error' => 'Phone number already in DNC list'], 400);
        }

        // Inserir o número na lista DNC
        DB::table('vicidial_dnc')->insert(['phone_number' => $phone_number]);
        return response()->json(['message' => 'Phone number added to DNC list'], 201);
    }

    // Retirar número da lista DNC
    public function removeNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15'
        ]);

        $phone_number = $request->input('phone_number');

        $deleted = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->delete();
        if ($deleted) {
            return response()->json(['message' => 'Phone number removed from DNC list'], 200);
        } else {
            return response()->json(['error' => 'Phone number not found in DNC list'], 404);
        }
    }

    // Consultar número na lista DNC
    public function checkNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15'
        ]);

        $phone_number = $request->input('phone_number');

        $exists = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();
        if ($exists) {
            return response()->json(['message' => 'Phone number is in DNC list'], 200);
        } else {
            return response()->json(['message' => 'Phone number is not in DNC list'], 404);
        }
    }
}
