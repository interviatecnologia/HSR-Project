<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\VicidialUser;
use Illuminate\Support\Facades\DB; 

class CampaignController extends Controller {
    private static $maxItemsPerPage = 1000; // Defina o máximo de itens por página

    public function index(Request $request) {
        $itensPorPagina = $request->input('items_per_page', 10);
        if ($itensPorPagina > self::$maxItemsPerPage) $itensPorPagina = self::$maxItemsPerPage;

        $query = Campaign::query();

        // Adicione os filtros conforme necessário
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->input('campaign_id'));
        }

        $campaigns = $query->paginate($itensPorPagina);

        // Parâmetro para resposta completa ou filtrada
        $fullResponse = $request->input('full', false);
        $filteredData = $campaigns->map(function ($campaign) use ($fullResponse) {
            if ($fullResponse) {
                return $campaign;
            } else {
                return [
                    'campaign_id' => $campaign->campaign_id,
                    'campaign_name' => $campaign->campaign_name,
                    'status' => $campaign->status,
                    'user_group' => $campaign->user_group,
                    'dial_method' => $campaign->dial_method,
                    'auto_dial_level' => $campaign->auto_dial_level,
                    'lead_order' => $campaign->lead_order,
                    'dial_statuses' => $campaign->dial_statuses
                ];
            }
        });

        return response()->json([
            'current_page' => $campaigns->currentPage(),
            'data' => $filteredData,
            'total' => $campaigns->total(),
            'per_page' => $campaigns->perPage(),
            'last_page' => $campaigns->lastPage()
        ]);
    }


    public function get($identifier) {
        // Tenta encontrar a campanha pelo ID
        $campaign = Campaign::find($identifier);
    
        // Se não encontrar pelo ID, tenta encontrar pelo nome
        if (!$campaign) {
            $campaign = Campaign::where('campaign_name', $identifier)->first();
        }
    
        // Se ainda não encontrar, retorna erro
        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
    
        return response()->json($campaign);
    }

    //CRIA UMA CAMPANHA COM BASE EM  PARAMETROS DE UMA CAMPANHA PADRÃO
    public function post(Request $request) {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|string|min:2|max:20',
            'campaign_name' => 'required|string|min:1|max:50',
        ]);
    
        if ($validator->fails()) return response()->json($validator->errors(), 400);
    
        // ID da campanha padrão
        $defaultCampaignId = '1001'; // Substitua pelo ID real da campanha padrão
    
        // Copiar parâmetros da campanha padrão
        $defaultCampaign = Campaign::where('campaign_id', $defaultCampaignId)->first();
    
        if (!$defaultCampaign) {
            return response()->json(['error' => 'Default campaign not found'], 404);
        }
    
        // Criar nova campanha com base na campanha padrão
        $newCampaignData = $defaultCampaign->toArray();
        $newCampaignData['campaign_id'] = $request->input('campaign_id');
        $newCampaignData['campaign_name'] = $request->input('campaign_name');
    
        $newCampaign = Campaign::create($newCampaignData);
    
        return response()->json($newCampaign, 201);
    }
    


    public function put(Request $request, $identifier) {
        // Tenta encontrar a campanha pelo ID (se for numérico) ou pelo nome
        $campaign = null;
        if (is_numeric($identifier)) {
            $campaign = Campaign::find($identifier);
        } else {
            // Tenta encontrar a campanha pelo nome
            $campaign = Campaign::where('campaign_name', $identifier)->first();
        }
    
        // Se não encontrar, retorna erro
        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
    
        // Atualiza a campanha com todos os dados do corpo da requisição
        $campaign->update($request->all());
        
        return response()->json($campaign, 200);
    }


    public function delete($identifier) {
        // Tenta encontrar a campanha pelo ID (se for numérico) ou pelo nome
        $campaign = null;
        if (is_numeric($identifier)) {
            $campaign = Campaign::find($identifier);
        } else {
            // Tenta encontrar a campanha pelo nome
            $campaign = Campaign::where('campaign_name', $identifier)->first();
        }
    
        // Se não encontrar, retorna erro
        if (!$campaign) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }
    
        // Remove a campanha
        $campaign->delete();
    
        // Retorna uma resposta de sucesso
        return response()->json(['message' => 'Campaign deleted successfully'], 200);
    }

    public function addAgent(Request $request, $campaign_id) {
        $validator = Validator::make($request->all(), [
            'agent_user' => 'required|string' // Ajustado para agent_user
        ]);
    
        if ($validator->fails()) return response()->json($validator->errors(), 400);
    
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) return response()->json(['error' => 'Campaign not found'], 404);
    
        $agent_user = $request->input('agent_user'); // Utilize agent_user
    
        $agent = VicidialUser::where('user', $agent_user)->first(); // Verificação ajustada
    
        if (!$agent) return response()->json(['error' => 'Agent not found'], 404);
    
        DB::table('vicidial_campaign_agents')->insert([
            'campaign_id' => $campaign_id,
            'user' => $agent->user,
        ]);
    
        return response()->json(['message' => 'Agent added to campaign'], 200);
    }    

    public function removeAgent(Request $request, $campaign_id) {
        $validator = Validator::make($request->all(), [
            'agent_user' => 'required|string' // Ajustado para agent_user
        ]);
    
        if ($validator->fails()) return response()->json($validator->errors(), 400);
    
        $campaign = Campaign::find($campaign_id);
        if (!$campaign) return response()->json(['error' => 'Campaign not found'], 404);
    
        $agent_user = $request->input('agent_user'); // Utilize agent_user
    
        $agent = VicidialUser::where('user', $agent_user)->first(); // Verificação ajustada
    
        if (!$agent) return response()->json(['error' => 'Agent not found'], 404);
    
        $result = DB::table('vicidial_campaign_agents')
                    ->where('campaign_id', $campaign_id)
                    ->where('user', $agent->user)
                    ->delete();
    
        if ($result) {
            return response()->json(['message' => 'Agent removed from campaign'], 200);
        } else {
            return response()->json(['error' => 'Failed to remove agent from campaign'], 500);
        }
    }
    
}