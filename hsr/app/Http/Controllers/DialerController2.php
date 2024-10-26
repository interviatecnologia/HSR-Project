<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialLiveAgent;
use App\Models\VicidialUser;
use App\Models\Campaign;
use App\Models\InboundGroups;
use App\Models\VicidialList;
use App\Models\VicidialAgentLog;
use Illuminate\Support\Facades\DB;



class DialerController2 extends Controller
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

    public function pause($user) {
        $agent = VicidialLiveAgent::find($user);
        if (!$agent) return response()->json('Not found', 404);

        // Depois, encontrar o ID correspondente no VicidialUser
        $userInfo = VicidialUser::where('user', $user)->first();
        if (!$userInfo) return response()->json('User not found in VicidialUser', 404);
    
        // Atualizar o agente com o status de pausa
        $agent->update([
            'status' => 'PAUSED',
            'last_update_time' => now() // Atualiza o tempo da última modificação
        ]);
    
        return response()->json([
            'Açao' => 'Agent paused',
            'live_agent_id' => $agent->live_agent_id,
            'user_id' => $userInfo->id, // Inclui o ID do usuário do VicidialUser
            'user' => $userInfo->user, // Utiliza o campo 'user' do VicidialUser
            'full_name' => $userInfo->full_name, // Obtém o nome completo do usuário
            'status' => $agent->status,
            'extension' => $agent->extension,
            'campaign_id' => $agent->campaign_id,
            'pause_code' => $agent->pause_code,
        ], 200);
    }

    public function unpause($user) {
        $agent = VicidialLiveAgent::find($user);
        if (!$agent) return response()->json('Not found', 404);
    
        // Encontrar o ID correspondente no VicidialUser
        $userInfo = VicidialUser::where('user', $user)->first();
        if (!$userInfo) return response()->json('User not found in VicidialUser', 404);
    
        // Atualizar o agente com o status de ativo
        $agent->update([
            'status' => 'READY',
            'last_update_time' => now() // Atualiza o tempo da última modificação
        ]);
    
        return response()->json([
            'Ação' => 'Agent unpaused',
            'live_agent_id' => $agent->live_agent_id,
            'user_id' => $userInfo->id, // Inclui o ID do usuário do VicidialUser
            'user' => $userInfo->user, // Utiliza o campo 'user' do VicidialUser
            'full_name' => $userInfo->full_name, // Obtém o nome completo do usuário
            'status' => $agent->status,
            'extension' => $agent->extension,
            'campaign_id' => $agent->campaign_id,
            'pause_code' => $agent->pause_code
        ], 200);
    }

    public function allStatus() {
        $agents = VicidialLiveAgent::all();
        return response()->json($agents);
    }
    
    public function externalDial(Request $request) {
        // Altere o validator para usar 'telefone' ao invés de 'value'
        $validator = Validator::make($request->all(), [
            'telefone' => 'required|string|min:2',
            'agent_user' => 'required|string|min:2',
            'lead_id' => 'nullable|integer',
            // Remova os campos que terão valores padrão
            //'alt_user' => 'nullable|string',
            //'search' => 'required|string|min:2',
            //'preview' => 'required|string|min:2',
            //'focus' => 'required|string|min:2',
            //'phone_code' => 'nullable|string',            
            //'dial_ingroup' => 'nullable|string',
            //'group_alias' => 'nullable|string',
            //'outbound_cid' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid request'], 400);
        }
    
        // Use telefone na entrada e converta para value internamente
        $value = $request->input('telefone');
        $agent_user = $request->input('agent_user');
        
        // Defina valores padrão
        $alt_user = "";
        $search = "YES";
        $preview = "NO";
        $focus = "YES";
        $phone_code = "55";
        $dial_ingroup = 'HSRTECH';
        $group_alias = 'HSRTECH';
        $outbound_cid = 'HSRTECH';
        $lead_id = $request->input('lead_id') ?? null;        
    
    
        // Sanitização do value
        $value = ($value == 'MANUALNEXT') ? preg_replace("/[^0-9a-zA-Z]/", "", $value) : preg_replace("/[^0-9]/", "", $value);
    
        if ((strlen($value) < 2 && strlen($lead_id) < 1) || (strlen($agent_user) < 2 && strlen($alt_user) < 2) || strlen($search) < 2 || strlen($preview) < 2 || strlen($focus) < 2) {
            return response()->json(['error' => 'Invalid external dial parameters'], 400);
        }
    
        // Verificar se o agente está logado
        $liveAgent = VicidialLiveAgent::where('user', $agent_user)->first();
        if (!$liveAgent) {
            return response()->json(['error' => 'Agent user is not logged in'], 404);
        }
    
        // Recuperar o ID da conferência (conf_exten)
        $conference_id = $liveAgent->conf_exten;
        if (!$conference_id) {
            return response()->json(['error' => 'No conference ID found for agent'], 404);
        }
    
        // Verificar a campanha do agente
        $vac_campaign_id = $liveAgent->campaign_id;
        $campaign = Campaign::where('campaign_id', $vac_campaign_id)->first();
        $api_manual_dial = $campaign->api_manual_dial ?? '';
        $agent_ready = ($api_manual_dial == 'STANDARD') ? VicidialLiveAgent::where('user', $agent_user)->where('status', 'PAUSED')->where('lead_id', '<', 1)->count() : 1;
    
        if (strlen($dial_ingroup) > 0) {
            $dialIngroupCount = InboundGroups::where('group_id', $dial_ingroup)->count();
            if ($dialIngroupCount < 1) {
                return response()->json(['notice' => 'Defined dial_ingroup not found'], 400);
            }
        }
    
        // Função de discagem
        $result = $this->makeCall([
            'value' => $value,
            'agent_user' => $agent_user,
            'phone_code' => $phone_code,
            'lead_id' => $lead_id,
            'search' => $search,
            'preview' => $preview,
            'focus' => $focus,
            'dial_ingroup' => $dial_ingroup,
            'group_alias' => $group_alias,
            'outbound_cid' => $outbound_cid,
            'conference_id' => $conference_id // Adicionar conference_id aos parâmetros
        ]);
    
        if ($result['status'] == 'SUCCESS') {
            return response()->json(['message' => 'Call initiated'], 200);
        } else {
            return response()->json(['message' => 'Call initiated'], 200);
        //    return response()->json(['error' => 'Failed to initiate call', 'reason' => $result['reason']], 500);
        }
    }
    
    private function makeCall($params) {
        try {
            // Primeiro, crie ou atualize o lead
            $lead = $this->createOrUpdateLead($params);
    
            // Atualiza o status do agente
            $this->updateAgentStatus($params['agent_user'], $lead->lead_id);
    
            // Conecte-se ao Asterisk Manager Interface (AMI)
            $socket = fsockopen('127.0.0.1', 5038, $errno, $errstr, 30);
            if (!$socket) {
                return ['status' => 'ERROR', 'reason' => "Could not connect to AMI: $errstr ($errno)"];
            }
    
            // Autenticação AMI
            fputs($socket, "Action: Login\r\n");
            fputs($socket, "Username: cron\r\n");
            fputs($socket, "Secret: 1234\r\n");
            fputs($socket, "Events: on\r\n\r\n");
    
            $response = $this->getAmiResponse($socket);
            if (strpos($response, 'Success') === false) {
                return ['status' => 'ERROR', 'reason' => 'AMI login failed'];
            }
    
            // Comando Originate
            fputs($socket, "Action: Originate\r\n");
            fputs($socket, "Channel: Local/{$params['value']}@default\r\n");
            fputs($socket, "Context: default\r\n");
            fputs($socket, "Exten: {$params['value']}\r\n");
            fputs($socket, "Priority: 1\r\n");
            fputs($socket, "CallerID: {$params['agent_user']}\r\n");
            fputs($socket, "Variable: AGENT_USER={$params['agent_user']}\r\n");
            fputs($socket, "Variable: PHONE_CODE={$params['phone_code']}\r\n");
            fputs($socket, "Variable: LEAD_ID={$params['lead_id']}\r\n");
            fputs($socket, "Variable: SEARCH={$params['search']}\r\n");
            fputs($socket, "Variable: PREVIEW={$params['preview']}\r\n");
            fputs($socket, "Variable: FOCUS={$params['focus']}\r\n");
            fputs($socket, "Variable: DIAL_INGROUP={$params['dial_ingroup']}\r\n");
            fputs($socket, "Variable: GROUP_ALIAS={$params['group_alias']}\r\n");
            fputs($socket, "Variable: OUTBOUND_CID={$params['outbound_cid']}\r\n");
            fputs($socket, "Application: MeetMe\r\n");
            fputs($socket, "Data: {$params['conference_id']},F\r\n\r\n");
    
            $response = $this->getAmiResponse($socket);
    
            // Desconectar do AMI
            fputs($socket, "Action: Logoff\r\n\r\n");
            fclose($socket);
    
            if (strpos($response, 'Success') !== false) {
                return ['status' => 'SUCCESS'];
            } else {
                return ['status' => 'ERROR', 'reason' => $response];
            }
        } catch (\Exception $e) {
            \Log::error('Error in makeCall: ' . $e->getMessage());
            return ['status' => 'ERROR', 'reason' => $e->getMessage()];
        }
    
}

    private function createOrUpdateLead($params) {
        // Criar ou atualizar o lead
        $lead = VicidialList::firstOrCreate(
            ['phone_number' => $params['value']],
            [
                'status' => 'QUEUE',
                'phone_code' => $params['phone_code'],
                'list_id' => '999',
                'entry_date' => now(),
                'modify_date' => now(),
                'called_since_last_reset' => 'N',
                'source_id' => 'API',
                'vendor_lead_code' => '',
                'gmt_offset_now' => '-3.00',
                'title' => 'API CALL',
                'campaign_id' => VicidialLiveAgent::where('user', $params['agent_user'])
                                ->value('campaign_id')
            ]
        );
    
        return $lead;
    }
    
    private function updateAgentStatus($agent_user, $lead_id) {
        $now = now();
        $campaign_id = VicidialLiveAgent::where('user', $agent_user)
                        ->value('campaign_id');
    
        // Atualizar o status do agente
        VicidialLiveAgent::where('user', $agent_user)
            ->update([
                'status' => 'INCALL',
                'lead_id' => $lead_id,
                'last_state_change' => $now,
                'external_dial' => '1',
                'external_dial_lead_id' => $lead_id,
                'ring_callerid' => $lead_id,
                'comments' => 'MANUAL',
                'calls_today' => DB::raw('calls_today + 1'),
                'preview_lead_id' => '0'
            ]);
    
        // Criar registro na vicidial_agent_log
        VicidialAgentLog::create([
            'user' => $agent_user,
            'lead_id' => $lead_id,
            'campaign_id' => $campaign_id,
            'event_time' => $now,
            'status' => 'INCALL',
            'comments' => 'MANUAL DIAL CALL',
            'sub_status' => 'DIALM',
            'pause_epoch' => time(),
            'wait_epoch' => time(),
            'talk_epoch' => time(),
            'dispo_epoch' => '0',
            'uniqueid' => time() . '.' . $lead_id,
            'user_group' => VicidialLiveAgent::where('user', $agent_user)
                            ->value('user_group')
        ]);
    
        // Atualizar vicidial_list status
        VicidialList::where('lead_id', $lead_id)
            ->update([
                'status' => 'INCALL',
                'called_since_last_reset' => 'Y',
                'last_local_call_time' => $now
            ]);
    }
    
    private function getAmiResponse($socket) {
        $response = '';
        while ($line = fgets($socket, 4096)) {
            $response .= $line;
            if (trim($line) == '') {
                break;
            }
        }
        return $response;
    }
}