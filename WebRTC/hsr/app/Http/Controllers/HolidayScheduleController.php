<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    // Método para listar todos os feriados
    public function index()
    {
        $holidays = DB::table('vicidial_call_time_holidays')->get();
        return response()->json($holidays, 200);
    }

    // Inserir feriado
    public function addHoliday(Request $request)
    {
        $request->validate([
            'holiday_id' => 'required|string|max:50',
            'holiday_date' => 'required|date',
            'user_group' => 'required|string|max:20',
            'holiday_name' => 'required|string|max:100',
            'holiday_comments' => 'nullable|string|max:255',
            'holiday_status' => 'required|string|max:10',
            'ct_default_start' => 'required|integer',
            'ct_default_stop' => 'required|integer',
            'default_afterhours_filename_override' => 'nullable|string|max:255',
            'holiday_method' => 'required|string|max:10'
        ]);

        $data = $request->only([
            'holiday_id',
            'holiday_date',
            'user_group',
            'holiday_name',
            'holiday_comments',
            'holiday_status',
            'ct_default_start',
            'ct_default_stop',
            'default_afterhours_filename_override',
            'holiday_method'
        ]);

        // Verificar se o feriado já está na lista para o grupo de usuários
        $exists = DB::table('vicidial_call_time_holidays')
            ->where('holiday_date', $data['holiday_date'])
            ->where('user_group', $data['user_group'])
            ->exists();
        if ($exists) {
            return response()->json(['error' => 'Holiday already exists for this user group'], 400);
        }

        // Inserir o feriado na lista
        DB::table('vicidial_call_time_holidays')->insert($data);
        return response()->json(['message' => 'Holiday added'], 201);
    }

    // Retirar feriado
    public function removeHoliday(Request $request)
    {
        $request->validate([
            'holiday_date' => 'required|date',
            'user_group' => 'required|string|max:20'
        ]);

        $holiday_date = $request->input('holiday_date');
        $user_group = $request->input('user_group');

        $deleted = DB::table('vicidial_call_time_holidays')
            ->where('holiday_date', $holiday_date)
            ->where('user_group', $user_group)
            ->delete();
        if ($deleted) {
            return response()->json(['message' => 'Holiday removed'], 200);
        } else {
            return response()->json(['error' => 'Holiday not found for this user group'], 404);
        }
    }

    // Consultar feriado
    public function checkHoliday(Request $request)
    {
        $request->validate([
            'holiday_date' => 'required|date',
            'user_group' => 'required|string|max:20'
        ]);

        $holiday_date = $request->input('holiday_date');
        $user_group = $request->input('user_group');

        $holiday = DB::table('vicidial_call_time_holidays')
            ->where('holiday_date', $holiday_date)
            ->where('user_group', $user_group)
            ->first();
        if ($holiday) {
            return response()->json([
                'message' => 'Holiday exists for this user group',
                'holiday_name' => $holiday->holiday_name
            ], 200);
        } else {
            return response()->json(['message' => 'Holiday not found for this user group'], 404);
        }
    }
}
