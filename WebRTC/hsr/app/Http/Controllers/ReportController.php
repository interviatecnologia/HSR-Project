<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function getCallLogs(Request $request)
    {
        // Validar e aplicar filtros para os registros de chamadas
        $request->validate([
            'date' => 'nullable|date',
            'agent' => 'nullable|string',
            'destination' => 'nullable|string',
            'extension' => 'nullable|integer',
            'status' => 'nullable|string',
            'user' => 'nullable|string'
        ]);

        $query = DB::table('call_log')
            ->leftJoin('vicidial_users', 'call_log.extension', '=', 'vicidial_users.phone_login')
            ->select('call_log.*', 'vicidial_users.user as vicidial_user');

        if ($request->has('date')) {
            $query->whereDate('call_log.call_date', $request->input('date'));
        }
        if ($request->has('agent')) {
            $query->where('call_log.agent', $request->input('agent'));
        }
        if ($request->has('destination')) {
            $query->where('call_log.destination', $request->input('destination'));
        }
        if ($request->has('extension')) {
            $query->where('call_log.extension', $request->input('extension'));
        }
        if ($request->has('status')) {
            $query->where('call_log.status', $request->input('status'));
        }
        if ($request->has('user')) {
            $query->where('vicidial_users.user', $request->input('user'));
        }

        $callLogs = $query->get();
        return response()->json($callLogs);
    }



    public function getPhones(Request $request)
    {
        // Validate and filter logic for sales report
        $request->validate([
            'extension' => 'nullable|integer',
        ]);

        $query = DB::table('phones');

        // Verifica se o parâmetro 'extension' foi fornecido
        if ($request->has('extension')) {
            $query->where('extension', 'LIKE', '%' . $request->input('extension') . '%');
    }

    // Executa a consulta e obtém os resultados
    $phones = $query->get();

    // Retorna os resultados como uma resposta JSON
    return response()->json($phones);
    }

    public function getusers(Request $request)
    {
        // Validate and filter logic for sales report
        $request->validate([
            'user' => 'nullable|string',
        ]);

        $query = DB::table('vicidial_users');

        // Verifica se o parâmetro 'user' foi fornecido
        if ($request->has('user')) {
            $query->where('user', 'LIKE', '%' . $request->input('user') . '%');
    }

    // Executa a consulta e obtém os resultados
    $phones = $query->get();

    // Retorna os resultados como uma resposta JSON
    return response()->json($phones);

    
    }

    public function getagent(Request $request)
{
    // Validação e lógica de filtro para relatório de agentes
    $request->validate([
        'user' => 'nullable|string',
    ]);

    $query = DB::table('vicidial_users');

    // Restringe o relatório para user_level = 1
    $query->where('user_level', 1); // Adiciona a condição para user_level

    // Verifica se o parâmetro 'user' foi fornecido
    if ($request->has('user')) {
        $query->where('user', 'LIKE', '%' . $request->input('user') . '%');
    }

    // Executa a consulta e obtém os resultados
    $users = $query->get();

    // Retorna os resultados como uma resposta JSON
    return response()->json($users);

    
}

public function getlead(Request $request)
{
    // Validação e lógica de filtro para relatório de leads
    $request->validate([
        'status' => 'nullable|string',
        'lead_id' => 'nullable|string', // validação para lead_id
        'list_id' => 'nullable|string', // validação para list_id
    ]);

    $query = DB::table('vicidial_list');

    // Verifica se o parâmetro 'status' foi fornecido
    if ($request->has('status')) {
        $query->where('status', $request->input('status')); // Adiciona o filtro para status
    }

    // Verifica se o parâmetro 'lead_id' foi fornecido
    if ($request->has('lead_id')) {
        $query->where('lead_id', 'LIKE', '%' . $request->input('lead_id') . '%');
    }

    // Executa a consulta e obtém os resultados
    $leads = $query->get();

    // Retorna os resultados como uma resposta JSON
    return response()->json($leads);
}

public function getcampaign(Request $request)
{
    // Validação e lógica de filtro para relatório de leads
    $request->validate([
        'campaign_id' => 'nullable|string', // validação para lead_id
        'campaign_name' => 'nullable|string', // validação para list_id
    ]);

    $query = DB::table('vicidial_campaigns');

    // Verifica se o parâmetro 'status' foi fornecido
    if ($request->has('campaign_id')) {
        $query->where('campaign_id', $request->input('campaign_id')); // Adiciona o filtro para status
    }

    // Executa a consulta e obtém os resultados
    $campaign_id = $query->get();

    // Retorna os resultados como uma resposta JSON
    return response()->json($campaign_id);
}
}