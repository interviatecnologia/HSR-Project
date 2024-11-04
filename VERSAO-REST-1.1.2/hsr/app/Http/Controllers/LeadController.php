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
        'list_id' => 'integer|string|max:10',
        'phone_number' => 'required|string|min:7|max:15',
        'phone_code' => 'required|string|min:1|max:10',
        'first_name' => 'required|string|max:50',
        'last_name' => 'string|max:20|nullable',        
        'agent_user' => 'string|max:20|nullable', // Agora é opcional 
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
    $leads = $request->input('leads');  
    $list_id = $request->input('list_id'); // Obter list_id do request

    // Verificar se a lista de leads é um array
    if (!is_array($leads)) {  
        return response()->json(['error' => 'Invalid request format'], 400);  
    }  

    $errors = [];
    foreach ($leads as $key => $lead) {
        $validator = Validator::make($lead, self::$validatorFields);
        if ($validator->fails()) {
            $errors[$key] = $validator->errors();
        }
    }

    if (!empty($errors)) {
        return response()->json(['errors' => $errors], 400);
    }

    $insertedLeads = [];
    foreach ($leads as $lead) {  
        $phone_number = $lead['phone_number'];  
        $phone_code = $lead['phone_code'];  

        $dnc_found = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();  
        $campaign_dnc_found = DB::table('vicidial_campaign_dnc')  
          ->where('phone_number', $phone_number)  
          ->exists();  

        if ($dnc_found || $campaign_dnc_found) {  
            return response()->json(['error' => 'Phone number is in DNC'], 400);  
        }  

        $leadData = $lead;  
        $leadData['status'] = 'NEW';  
        $leadData['entry_date'] = Carbon::now();  
        $leadData['last_local_call_time'] = Carbon::now();  

        if (!empty($list_id)) {
            $leadData['list_id'] = $list_id; // Aplicar list_id a todos os leads
        }

        $insertedLeads[] = Lead::create($leadData);  
    }  

    return response()->json([  
        'success' => true,  
        'leads' => $insertedLeads  
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
                $query->where('list_id', 'LIKE', "%{$searchTerm}%")
                      ->where('lead_id', 'LIKE', "%{$searchTerm}%")
                      ->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone_number', 'LIKE', "%{$searchTerm}%");
            })
            ->get(['list_id','lead_id','first_name', 'last_name', 'phone_number']);

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

    
    public function listLeadLists(): JsonResponse    
{
    // Obter todas as listas de leads da tabela vicidial_lists
    $leadLists = DB::table('vicidial_lists')->get(['list_id', 'list_name', 'campaign_id', 'active', 'list_description']);

    return response()->json($leadLists);
}

}