<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallTimeController extends Controller
{
    // Método para listar todos os horários de atendimento
    public function index()
    {
        $callTimes = DB::table('vicidial_call_times')->get();
        return response()->json($callTimes, 200);
    }

    // Método para adicionar um novo horário de atendimento
    public function store(Request $request)
    {
        $request->validate([
            'call_time_id' => 'required|string|max:50',
            'call_time_name' => 'required|string|max:100',
            'call_time_comments' => 'nullable|string|max:255',
            'ct_default_start' => 'required|integer',
            'ct_default_stop' => 'required|integer',
            'ct_sunday_start' => 'nullable|integer',
            'ct_sunday_stop' => 'nullable|integer',
            'ct_monday_start' => 'nullable|integer',
            'ct_monday_stop' => 'nullable|integer',
            'ct_tuesday_start' => 'nullable|integer',
            'ct_tuesday_stop' => 'nullable|integer',
            'ct_wednesday_start' => 'nullable|integer',
            'ct_wednesday_stop' => 'nullable|integer',
            'ct_thursday_start' => 'nullable|integer',
            'ct_thursday_stop' => 'nullable|integer',
            'ct_friday_start' => 'nullable|integer',
            'ct_friday_stop' => 'nullable|integer',
            'ct_saturday_start' => 'nullable|integer',
            'ct_saturday_stop' => 'nullable|integer',
            'ct_state_call_times' => 'nullable|string|max:100',
            'default_afterhours_filename_override' => 'nullable|string|max:255',
            'sunday_afterhours_filename_override' => 'nullable|string|max:255',
            'monday_afterhours_filename_override' => 'nullable|string|max:255',
            'tuesday_afterhours_filename_override' => 'nullable|string|max:255',
            'wednesday_afterhours_filename_override' => 'nullable|string|max:255',
            'thursday_afterhours_filename_override' => 'nullable|string|max:255',
            'friday_afterhours_filename_override' => 'nullable|string|max:255',
            'saturday_afterhours_filename_override' => 'nullable|string|max:255',
            'user_group' => 'required|string|max:20',
            'ct_holidays' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'call_time_id',
            'call_time_name',
            'call_time_comments',
            'ct_default_start',
            'ct_default_stop',
            'ct_sunday_start',
            'ct_sunday_stop',
            'ct_monday_start',
            'ct_monday_stop',
            'ct_tuesday_start',
            'ct_tuesday_stop',
            'ct_wednesday_start',
            'ct_wednesday_stop',
            'ct_thursday_start',
            'ct_thursday_stop',
            'ct_friday_start',
            'ct_friday_stop',
            'ct_saturday_start',
            'ct_saturday_stop',
            'ct_state_call_times',
            'default_afterhours_filename_override',
            'sunday_afterhours_filename_override',
            'monday_afterhours_filename_override',
            'tuesday_afterhours_filename_override',
            'wednesday_afterhours_filename_override',
            'thursday_afterhours_filename_override',
            'friday_afterhours_filename_override',
            'saturday_afterhours_filename_override',
            'user_group',
            'ct_holidays'
        ]);

        DB::table('vicidial_call_times')->insert($data);
        return response()->json(['message' => 'Call time added'], 201);
    }

    // Método para atualizar um horário de atendimento existente
    public function update(Request $request, $call_time_id)
    {
        $request->validate([
            'call_time_name' => 'required|string|max:100',
            'call_time_comments' => 'nullable|string|max:255',
            'ct_default_start' => 'required|integer',
            'ct_default_stop' => 'required|integer',
            'ct_sunday_start' => 'nullable|integer',
            'ct_sunday_stop' => 'nullable|integer',
            'ct_monday_start' => 'nullable|integer',
            'ct_monday_stop' => 'nullable|integer',
            'ct_tuesday_start' => 'nullable|integer',
            'ct_tuesday_stop' => 'nullable|integer',
            'ct_wednesday_start' => 'nullable|integer',
            'ct_wednesday_stop' => 'nullable|integer',
            'ct_thursday_start' => 'nullable|integer',
            'ct_thursday_stop' => 'nullable|integer',
            'ct_friday_start' => 'nullable|integer',
            'ct_friday_stop' => 'nullable|integer',
            'ct_saturday_start' => 'nullable|integer',
            'ct_saturday_stop' => 'nullable|integer',
            'ct_state_call_times' => 'nullable|string|max:100',
            'default_afterhours_filename_override' => 'nullable|string|max:255',
            'sunday_afterhours_filename_override' => 'nullable|string|max:255',
            'monday_afterhours_filename_override' => 'nullable|string|max:255',
            'tuesday_afterhours_filename_override' => 'nullable|string|max:255',
            'wednesday_afterhours_filename_override' => 'nullable|string|max:255',
            'thursday_afterhours_filename_override' => 'nullable|string|max:255',
            'friday_afterhours_filename_override' => 'nullable|string|max:255',
            'saturday_afterhours_filename_override' => 'nullable|string|max:255',
            'user_group' => 'required|string|max:20',
            'ct_holidays' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'call_time_name',
            'call_time_comments',
            'ct_default_start',
            'ct_default_stop',
            'ct_sunday_start',
            'ct_sunday_stop',
            'ct_monday_start',
            'ct_monday_stop',
            'ct_tuesday_start',
            'ct_tuesday_stop',
            'ct_wednesday_start',
            'ct_wednesday_stop',
            'ct_thursday_start',
            'ct_thursday_stop',
            'ct_friday_start',
            'ct_friday_stop',
            'ct_saturday_start',
            'ct_saturday_stop',
            'ct_state_call_times',
            'default_afterhours_filename_override',
            'sunday_afterhours_filename_override',
            'monday_afterhours_filename_override',
            'tuesday_afterhours_filename_override',
            'wednesday_afterhours_filename_override',
            'thursday_afterhours_filename_override',
            'friday_afterhours_filename_override',
            'saturday_afterhours_filename_override',
            'user_group',
            'ct_holidays'
        ]);

        DB::table('vicidial_call_times')
            ->where('call_time_id', $call_time_id)
            ->update($data);

        return response()->json(['message' => 'Call time updated'], 200);
    }

    // Método para deletar um horário de atendimento
    public function destroy($call_time_id)
    {
        $deleted = DB::table('vicidial_call_times')
            ->where('call_time_id', $call_time_id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Call time deleted'], 200);
        } else {
            return response()->json(['error' => 'Call time not found'], 404);
        }
    }
}
