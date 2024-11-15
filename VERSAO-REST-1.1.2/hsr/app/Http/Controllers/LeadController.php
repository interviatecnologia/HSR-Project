<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialUser ;
use App\Models\VicidialLiveAgent;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\VicidialList;
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

    public function createList($list_name, $campaign_id = null, $description = null) {
        // Verificar se a lista já existe
        $existingList = DB::table('vicidial_lists')->where('list_name', $list_name)->first();
        if ($existingList) {
            return response()->json(['error' => 'List already exists'], 400);
        }
    
        // Criar a nova lista
        $list_id = DB::table('vicidial_lists')->insertGetId([
            'list_name' => $list_name,
            'campaign_id' => $campaign_id,
            'list_description' => $description,
            'active' => 'Y', // ou 'N', dependendo da lógica de negócios
            'list_changedate' => Carbon::now(),
            'list_lastcalldate' => Carbon::now(),
            // Adicione outros campos necessários, se houver
        ]);
    
        return response()->json(['success' => true, 'list_id' => $list_id], 201);
    }

    // Método para adicionar um lead
    public function addLead(Request $request, $list_name)  
{  
    // Obter todos os leads diretamente do corpo da requisição
    $leads = $request->json()->all();  

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

    // Obter o list_id a partir do list_name
    $list = DB::table('vicidial_lists')->where('list_name', $list_name)->first();

    // Se a lista não for encontrada, retornar erro
    if (!$list) {
        return response()->json(['error' => 'List not found'], 404);
    }

    $insertedLeads = [];
    foreach ($leads as $lead) {  
        $phone_number = $lead['phone_number'];  
        $phone_code = $lead['phone_code']; 

        $dnc_found = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();  
        $campaign_dnc_found = DB::table('vicidial_campaign_dnc')->where('phone_number', $phone_number)->exists();  

        if ($dnc_found || $campaign_dnc_found) {  
            return response()->json(['error' => 'Phone number is in DNC'], 400);  
        }  

        $leadData = $lead;  
        $leadData['status'] = 'NEW';  
        $leadData['entry_date'] = Carbon::now();  
        $leadData['last_local_call_time'] = Carbon::now();  
        $leadData['list_id'] = $list->list_id; 
        $leadData['list_name'] = $list->list_name;

        $insertedLeads[] = Lead::create($leadData);  
    }  

    return response()->json([  
        'success' => true,  
        'leads' => $insertedLeads  
    ], 201);  
}

public function addLeadWithAreaCode(Request $request, $base_list_name)  
{  
    // Obter todos os leads diretamente do corpo da requisição
    $leads = $request->json()->all();  

    // Verificar se a lista de leads é um array
    if (!is_array($leads)) {  
        return response()->json(['error' => 'Invalid request format'], 400);  
    }  

    // Inicializar um array para armazenar os leads inseridos
    $insertedLeads = [];

    // Iterar sobre os leads
    foreach ($leads as $lead) {
        // Obter o phone_number
        $phone_number = $lead['phone_number'];

        // Criar o area_code a partir dos 2 primeiros dígitos do phone_number
        $area_code = substr($phone_number, 0, 2); // Dois primeiros dígitos do número
        $list_name = $base_list_name . '_' . $area_code; // Nome da lista a ser verificada/criada

        // Verificar se a lista existe
        $list = DB::table('vicidial_lists')->where('list_name', $list_name)->first();

        // Se a lista não for encontrada, criar a lista
        if (!$list) {
            // Obter o maior list_id existente
            $maxListId = DB::table('vicidial_lists')->max('list_id');

            // Gerar um novo list_id
            $newListId = $maxListId ? $maxListId + 1 : 1; // Começar em 1 se não houver listas

            // Verificar se o novo list_id já existe e incrementar até encontrar um disponível
            while (DB::table('vicidial_lists')->where('list_id', $newListId)->exists()) {
                $newListId++;
            }

            // Inserir a nova lista
            DB::table('vicidial_lists')->insert([
                'list_name' => $list_name,
                'active' => 'Y', // ou 'N', dependendo da lógica de negócios
                'list_changedate' => Carbon::now(),
                'list_lastcalldate' => Carbon::now(),
                'campaign_id' => null, // ou um valor válido se necessário
                'list_description' => $list_name, // ou um valor válido se necessário
                'list_id' => $newListId, // Usar o novo list_id gerado
            ]);
        }

        // Obter o list_id da lista existente ou recém-criada
        $list_id = DB::table('vicidial_lists')->where('list_name', $list_name)->value('list_id');

        // Recuperar o list_name da tabela vicidial_lists
        $list_name = DB::table('vicidial_lists')->where('list_name', $list_name)->value('list_name');


        // Verificar se o número está na lista DNC
        $dnc_found = DB::table('vicidial_dnc')->where('phone_number', $phone_number)->exists();  
        $campaign_dnc_found = DB::table('vicidial_campaign_dnc')->where('phone_number', $phone_number)->exists();  

        if ($dnc_found || $campaign_dnc_found) {  
            return response()->json(['error' => 'Phone number is in DNC'], 400);  
        }  

        // Preparar os dados do lead
        $leadData = $lead;  
        $leadData['status'] = 'NEW';  
        $leadData['entry_date'] = Carbon::now();  
        $leadData['last_local_call_time'] = Carbon::now();  
        $leadData['list_id'] = $list_id; // Usar o list_id obtido da consulta
        $leadData['list_name'] = $list_name; // Adicionar o list_name individualmente

        // Inserir o lead
        $insertedLeads[] = Lead::create($leadData);  
    }  

    // Formatar a resposta para incluir o list_name
$responseLeads = array_map(function($lead) {
    // Recuperar o list_name correspondente ao list_id
    $list_name = DB::table('vicidial_lists')->where('list_id', $lead->list_id)->value('list_name');
    
    return array_merge($lead->toArray(), ['list_name' => $list_name]);
}, $insertedLeads);

return response()->json([  
    'success' => true,  
    'inserted_leads' => $responseLeads  
]);
}

// Função para verificar se o area_code é válido para a lista
private function isAreaCodeValidForList($list_name, $area_code) {
    // Extrair o número da lista do nome da lista
    preg_match('/Lista(\d+)_/', $list_name, $matches);
    if (empty($matches)) {
        return false; // Se não conseguir extrair o número da lista, retorna false
    }
    $list_number = $matches[1]; // Pega o número da lista

    // Gerar o nome da lista esperado com base no area_code
    $expected_list_name = "lista{$list_number}_{$area_code}";

    // Verifica se o nome da lista gerado é igual ao list_name
    return $list_name === $expected_list_name;
}

// Função para obter o nome da lista com base no area_code
private function getListNameForAreaCode($area_code) {
    // Extrair o número da lista padrão (por exemplo, lista1)
    $list_number = 1; // Defina um número de lista padrão, ou extraia dinamicamente se necessário

    // Retornar o nome da lista formatado com base no area_code
    return "lista{$list_number}_{$area_code}";
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
        'list_id' => 'string|max:10|nullable', // Adicione a validação para list_id
        'phone_number' => 'required|string|min:7|max:15',
        'phone_code' => 'required|string|min:1|max:10',
        'agent_user' => 'string|max:20|nullable',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        
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

public function updateActiveStatus(Request $request): JsonResponse
{
    // Validação dos dados recebidos
    $validatedData = $request->validate([
        'list_id' => 'required|integer', // O campo list_id deve ser requerido
        'active' => 'required|string|in:Y,N', // O campo active deve ser 'Y' ou 'N'
    ]);

    // Encontrar a lista pelo ID
    $list = DB::table('vicidial_lists')->where('list_id', $validatedData['list_id'])->first();

    // Verificar se a lista existe
    if (!$list) {
        return response()->json(['error' => 'List not found'], 404);
    }

    // Atualizar o status ativo
    DB::table('vicidial_lists')->where('list_id', $validatedData['list_id'])->update(['active' => $validatedData['active']]);

    return response()->json(['success' => true, 'list_id' => $validatedData['list_id'], 'active' => $validatedData['active']], 200);
}

}