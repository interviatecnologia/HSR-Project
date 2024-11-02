<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialUser ;
use App\Models\VicidialLiveAgent;
use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse; // Importar JsonResponse corretamente

class LeadController extends Controller
{
    // Validação dos campos obrigatórios e opcionais
    private static $validatorFields = [
        'phone_number' => 'required|string|min:7|max:15',
        'phone_code' => 'required|string|min:1|max:10',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'list_id' => 'integer|nullable', // Agora é opcional
        'agent_user' => 'string|max:20|nullable', 
        'source_id' => 'string|max:50|nullable',
        'title' => 'string|max:4|nullable',
        'middle_initial' => 'string|max:1|nullable',
        'address1' => 'string|max:100|nullable',
        'address2' => 'string|max:100|nullable',
        'address3' => 'string|max:100|nullable',
        'city' => 'string|max:50|nullable',
        'state' => 'string|max:50|nullable',
        'province' => 'string|max:50|nullable',
        'postal_code' => 'string|max:10|nullable',
        'country_code' => 'string|max:3|nullable',
        'gender' => 'string|max:1|nullable',
        'date_of_birth' => 'date|nullable',
        'alt_phone' => 'string|max:15|nullable',
        'email' => 'string|email|max:100|nullable',
        'security_phrase' => 'string|max:100|nullable',
        'comments' => 'string|max:255|nullable',
        'rank' => 'integer|nullable',
        'owner' => 'string|max:50|nullable'
    ];

    // Método para adicionar um lead
    public function addLead(Request $request)
{
    $validator = Validator::make($request->all(), self::$validatorFields);
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Obter campos do request
    $phone_number = $request->input('phone_number');
    $phone_code = $request->input('phone_code'); // Se você precisar dele mais tarde
    //$agent_user = $request->input('agent_user'); // Removido

    // Encontrar campanha (se necessário) - isso pode ser removido se não for mais relevante
    // $campaign = Campaign::where('campaign_id', $agent->campaign_id)->first();
    // if (!$campaign) {
    //     return response()->json(['error' => 'Campaign not found'], 404);
    // }

    // Verificar DNC
    $dnc_found = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();
    $campaign_dnc_found = DB::table('vicidial_campaign_dnc')
        ->where('phone_number', $phone_number)
        // ->where('campaign_id', $campaign->campaign_id) // Removido
        ->exists();

    if ($dnc_found || $campaign_dnc_found) {
        return response()->json(['error' => 'Phone number is in DNC'], 400);
    }

    // Inserir novo lead
    $leadData = $request->all();
    // $leadData['list_id'] = $list_id; // Adicione isso se precisar de uma lista específica
    $leadData['status'] = 'NEW';
    // $leadData['user'] = $agent_user; // Removido
    $leadData['entry_date'] = Carbon::now();
    $leadData['last_local_call_time'] = Carbon::now();

    $lead = Lead::create($leadData);

    return response()->json([
        'success' => true,
        'lead' => $lead
    ], 201);
}

    public function index(): JsonResponse
    {
        $leads = Lead::all(['lead_id', 'first_name', 'last_name', 'phone_number', 'list_id']);
        return response()->json($leads);
    }

    public function show($id): JsonResponse // Método para retornar um lead específico
    {
        try {
            $lead = Lead::findOrFail($id, ['lead_id', 'first_name', 'last_name', 'phone_number', 'list_id']);
            return response()->json($lead);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead not found'], 404);
        }
    }

public function update(Request $request, $id): JsonResponse
{
    // Validação dos dados recebidos
    $validatedData = $request->validate([
        'phone_number' => 'required|string|min:7|max:15',
        'phone_code' => 'required|string|min:1|max:10',
        'agent_user' => 'string|max:20|nullable',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'list_id' => 'string|max:10|nullable', // Adicione a validação para list_id
    ]);

    // Encontrar o lead pelo ID
    $lead = Lead::find($id);

    // Verificar se o lead existe
    if (!$lead) {
        return response()->json(['error' => 'Lead not found'], 404);
    }

    // Atualizar o lead com os dados validados
    $lead->update($validatedData);

    return response()->json(['success' => true, 'lead' => $lead], 200);
}

public function search(Request $request, $listId): JsonResponse
    {
        $searchTerm = $request->input('search'); // Get the search term from the request

        // Query the leads based on the list ID and search term
        $leads = Lead::where('list_id', $listId)
            ->where(function($query) use ($searchTerm) {
                $query->where('lead_id', 'LIKE', "%{$searchTerm}%")
                      ->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%");
            })
            ->get(['lead_id','first_name', 'last_name', 'phone_number']);

        return response()->json($leads);
    }

    public function delete(string $lead_id) {
        // Encontra o lead pelo ID
        $lead = Lead::find($lead_id);
    
        // Verifica se o lead foi encontrado
        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404); // Resposta caso o lead não seja encontrado
        }
    
        // Deleta o lead
        $lead->delete();
    
        // Retorna uma resposta de sucesso
        return response()->json(['message' => 'Lead deleted successfully'], 204);
    }
}