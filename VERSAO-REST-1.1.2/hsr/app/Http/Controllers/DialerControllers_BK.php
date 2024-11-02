<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialLiveAgent;
use App\Models\VicidialUser;
use App\Models\Campaign;
use App\Models\InboundGroups;
use App\Models\Phone;
use App\Models\VicidialList;
use App\Models\VicidialAgentLog;
use Illuminate\Support\Facades\DB;
use App\Models\VicidailUser;
use Illuminate\Support\Facades\Log;



class DialerController extends Controller
{
    private static $validatorFields = [
        'user' => 'required|string|min:2|max:20',
        'pass' => 'required|string|min:1|max:20',
        'user_level' => 'required|int|min:1|max:9',
        'full_name' => 'required|string|min:1|max:50',
        'user_group' => 'required|int|min:1|max:20'
    ];

    // Declaração dos validadores específicos para cada função
    private static $validatorFieldsLogout = [
        'user' => 'required|string|min:2|max:20'
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

    public function login(Request $request) {
        // Validação dos dados de entrada
        $validator = Validator::make($request->all(), [
            'user' => 'required|string|min:2',
            'alt_user' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $agent_user = $request->input('user');
        $alt_user = $request->input('alt_user');
    
        // Defina um valor padrão para server_ip e lead_id
        $server_ip = $request->input('server_ip') ?? '10.0.0.112'; // Valor padrão para o IP
        $lead_id = $request->input('lead_id') ?? '0'; // Valor padrão para o lead_id
    
        // Verificação se o usuário tem permissão para login
        if (strlen($agent_user) < 1 && strlen($alt_user) < 2) {
            return response()->json([
                'result' => 'ERROR',
                'result_reason' => 'login not valid'
            ], 400);
        }
    
        if (strlen($alt_user) > 1) {
            $user = VicidialUser ::where('custom_three', $alt_user)->first();
            if ($user) {
                $agent_user = $user->user;
            } else {
                return response()->json([
                    'result' => 'ERROR',
                    'result_reason' => 'no user found'
                ], 404);
            }
        }
    
        // Verifica se o agente já está logado
        $agent = VicidialLiveAgent::where('user', $agent_user)->first();
        if ($agent) {
            return response()->json([
                'result' => 'ERROR',
                'result_reason' => 'agent_user is already logged in'
            ], 400);
        } else {
            // Tenta encontrar a extensão do usuário
            $userDetails = VicidialUser ::where('user', $agent_user)->first();
            $extension = null;
            $campaign_id = null; // Inicializa a variável campaign_id
    
            if ($userDetails) {
                // Se o usuário foi encontrado, tenta pegar a extensão
                $extension = $userDetails->phone_login;
            }
    
            // Se a extensão não foi encontrada, tenta criar uma nova
            if (!$extension) {
                $availablePhone = $this->findAvailableExtension();
                if ($availablePhone) {
                    $extension = $availablePhone->dialplan_number; // Ou outro campo que você deseja usar
                    // Aqui você pode implementar a lógica para associar o ramal ao usuário, se necessário
                } else {
                    return response()->json([
                        'result' => 'ERROR',
                        'result_reason' => 'no available extension found'
                    ], 400);
                }
            }
    
            // Se o agente não estiver logado, cria uma nova entrada
            $newAgent = VicidialLiveAgent::create([
                'user' => $agent_user,
                'status' => 'LOGIN', // Defina o status que você deseja usar para logado
                'server_ip' => $server_ip, // Usa o valor padrão ou o valor fornecido
                'lead_id' => $lead_id, // Usa o valor padrão ou o valor fornecido
                'extension' => $extension, // Usa a extensão encontrada ou criada
                // Adicione outros campos necessários, como campaign_id, etc., se necessário
            ]);
    
            // Obtenha os valores de status, extension e campaign_id da nova entrada
            $status = $newAgent->status; // Obtem o status do novo agente
            $campaign_id = $newAgent->campaign_id; // Supondo que você tenha definido campaign_id durante a criação
    
            return response()->json([
                'result' => 'SUCCESS',
                'result_reason' => 'login function set',
                'agent_user' => $agent_user,
                'extension' => $extension,
                'status' => $status,
                'campaign_id' => $campaign_id,
            ], 200);
        }
    }
    public function logout(Request $request) {
        // Validação dos dados de entrada
        $validator = Validator::make($request->all(), [
            'user' => 'required|string|min:2',
            'alt_user' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $agent_user = $request->input('user');
        $alt_user = $request->input('alt_user');
    
        // Verificação se o usuário tem permissão para logout
        if (strlen($agent_user) < 1 && strlen($alt_user) < 2) {
            return response()->json([
                'result' => 'ERROR',
                'result_reason' => 'logout not valid'
            ], 400);
        }
    
        if (strlen($alt_user) > 1) {
            $user = VicidialUser ::where('custom_three', $alt_user)->first();
            if ($user) {
                $agent_user = $user->user;
            } else {
                return response()->json([
                    'result' => 'ERROR',
                    'result_reason' => 'no user found'
                ], 404);
            }
        }
    
        // Verifica se o agente está logado
    $agent = VicidialLiveAgent::where('user', $agent_user)->first();
    if ($agent) {
        // Remove o registro do agente da tabela vicidial_live_agents
        $agent->delete();

        return response()->json([
            'result' => 'SUCCESS',
            'result_reason' => 'logout function set',
            'agent_user' => $agent_user
        ], 200);
    } else {
        return response()->json([
            'result' => 'ERROR',
            'result_reason' => 'agent_user is not logged in'
        ], 400);
    }
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
            'user_id' => $userInfo->user_id, // Inclui o ID do usuário do VicidialUser
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

    public function status($user)
{
    // Encontrar o ID correspondente no VicidialUser
    $userInfo = VicidialUser::where('user', $user)->first();
    if (!$userInfo) {
        return response()->json([
            'status' => 'DISCONNECTED',
            'message' => 'User not found in VicidialUser'
        ], 404);
    }

    //// Buscar o agente na tabela vicidial_live_agents  
   $agent = VicidialLiveAgent::where('user', $user)->first();  
   if ($agent) {  
      $extension = $agent->extension;  
   } else {  
      $extension = '';  
   }  
    
    // Definir status inicial como desconectado
    $agentStatus = 'DISCONNECTED';
    $campaignId = null;
    $pauseCode = null;
    $extension = $userInfo->$extension;
    $phoneLogin = $userInfo->phone_login; // Obter phone_login

    if ($agent) {
        $agentStatus = $agent->status;
        $campaignId = $agent->campaign_id;
        $pauseCode = $agent->pause_code;
        $extension = str_replace('SIP/', '', $agent->extension);
    } else {
        // Se o agente não estiver no VicidialLiveAgent, verificar o telefone
        $extension = str_replace('SIP/', '', $phoneLogin);
    }

    // Encontrar o ID correspondente no Phone para obter o Sip Status
    $phoneInfo = Phone::where('dialplan_number', $extension)->first();
    if (!$phoneInfo) {
        return response()->json([
            'status' => $agentStatus,
            'user_id' => $userInfo->user_id,
            'user' => $userInfo->user,
            'full_name' => $userInfo->full_name,
            'extension' => $userInfo->$extension,
            'campaign_id' => $campaignId,
            'pause_code' => $pauseCode,
            'phone_login' => $phoneLogin,
            'peer_status' => 'UNKNOWN', // Se o telefone não for encontrado, status SIP é desconhecido
            'message' => 'Phone not found'
        ], 404);
    }

    // Determinar o status do SIP
    $sipStatus = $phoneInfo->peer_status;
    switch ($sipStatus) {
        case 'REGISTERED':
            $status = 'REGISTERED';
            break;
        case 'UNREGISTERED':
            $status = 'UNREGISTERED';
            break;
        case 'REACHABLE':
            $status = 'REACHABLE';
            break;
        case 'UNREACHABLE':
            $status = 'UNREACHABLE';
            break;
        case 'LAGGED':
            $status = 'LAGGED';
            break;
        default:
            $status = 'UNKNOWN';
            break;
    }

    return response()->json([
        'status' => $agentStatus,
        'user_id' => $userInfo->user_id,
        'user' => $userInfo->user,
        'full_name' => $userInfo->full_name,
        'extension' => $agent && $agent->extension ? $agent->extension : $phoneLogin,
        'campaign_id' => $campaignId,
        'pause_code' => $pauseCode,
        'phone_login' => $phoneLogin,
        'peer_status' => $status, // Inclui o status do SIP
    ], 200);
}


public function allStatus()
{
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
        $outbound_cid = $request->input('outbound_cid') ?? '';
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

public function hangupCall(Request $request)
{
    Log::info('Iniciando processo de hangup', [
        'request_data' => $request->all()
    ]);

    $validator = Validator::make($request->all(), [
        'agent_user' => 'required|string|min:2',
    ]);

    if ($validator->fails()) {
        Log::warning('Validação falhou', [
            'errors' => $validator->errors()->toArray()
        ]);
        return response()->json([
            'error' => 'Invalid request',
            'details' => $validator->errors()
        ], 400);
    }

    $agent_user = $request->input('agent_user');

    // Verificar se o agente está logado e em chamada
    $liveAgent = VicidialLiveAgent::where('user', $agent_user)
                                 ->first();

    Log::info('Status do agente encontrado', [
        'agent_user' => $agent_user,
        'agent_data' => $liveAgent ? $liveAgent->toArray() : null
    ]);

    if (!$liveAgent) {
        return response()->json(['error' => 'Agent not found'], 404);
    }

    // Recuperar o ID da conferência
    $conference_id = $liveAgent->conf_exten;
    
    Log::info('Conferência encontrada', [
        'conference_id' => $conference_id,
        'agent_user' => $agent_user
    ]);

    if (!$conference_id) {
        return response()->json(['error' => 'No conference ID found'], 404);
    }

    // Executar o hangup
    $result = $this->executeHangup([
        'conference_id' => $conference_id,
        'agent_user' => $agent_user
    ]);

    Log::info('Resultado do hangup', [
        'result' => $result,
        'agent_user' => $agent_user,
        'conference_id' => $conference_id
    ]);

    if ($result['status'] == 'SUCCESS') {
        return response()->json([
            'message' => 'Call terminated successfully',
            'details' => $result['details'] ?? null
        ], 200);
    } else {
        return response()->json([
            'error' => 'Failed to terminate call',
            'reason' => $result['reason']
        ], 500);
    }
}

private function executeHangup($params)
{
    try {
        Log::info('Iniciando execução do hangup', $params);

        $socket = fsockopen('127.0.0.1', 5038, $errno, $errstr, 30);
        if (!$socket) {
            Log::error('Falha na conexão AMI', [
                'errno' => $errno,
                'errstr' => $errstr
            ]);
            return [
                'status' => 'ERROR',
                'reason' => "Could not connect to AMI: $errstr ($errno)"
            ];
        }

        // Autenticação AMI
        fputs($socket, "Action: Login\r\n");
        fputs($socket, "Username: cron\r\n");
        fputs($socket, "Secret: 1234\r\n");
        fputs($socket, "Events: off\r\n\r\n");

        $loginResponse = $this->getAmiResponse($socket);
        Log::info('Resposta do login AMI', ['response' => $loginResponse]);

        if (strpos($loginResponse, 'Success') === false) {
            return ['status' => 'ERROR', 'reason' => 'AMI login failed'];
        }

        // Primeiro, vamos verificar o status da conferência
        fputs($socket, "Action: Command\r\n");
        fputs($socket, "Command: meetme list {$params['conference_id']}\r\n\r\n");

        $conferenceResponse = $this->getAmiResponse($socket);
        Log::info('Status da conferência', ['response' => $conferenceResponse]);

        // Procurar por todos os canais na conferência
        $channels = [];
        $lines = explode("\n", $conferenceResponse);
        foreach ($lines as $line) {
            if (strpos($line, 'Channel') !== false) {
                if (preg_match('/Channel: ([^\s]+)/', $line, $matches)) {
                    $channels[] = $matches[1];
                }
            }
        }

        Log::info('Canais encontrados', ['channels' => $channels]);

        // Desconectar todos os canais
        foreach ($channels as $channel) {
            fputs($socket, "Action: Hangup\r\n");
            fputs($socket, "Channel: {$channel}\r\n\r\n");
            $hangupResponse = $this->getAmiResponse($socket);
            Log::info('Resposta do hangup para canal', [
                'channel' => $channel,
                'response' => $hangupResponse
            ]);
        }

        // Desconectar do AMI
        fputs($socket, "Action: Logoff\r\n\r\n");
        fclose($socket);

        return [
            'status' => 'SUCCESS',
            'details' => [
                'channels_disconnected' => count($channels)
            ]
        ];

    } catch (\Exception $e) {
        Log::error('Erro durante execução do hangup', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [
            'status' => 'ERROR',
            'reason' => $e->getMessage()
        ];
    }
}


public function callAgent(Request $request)
{
    // Validar os parâmetros de entrada
    $validator = Validator::make($request->all(), [
        'user' => 'required|string|min:1|max:50',
        'alt_user' => 'nullable|string|min:1|max:50'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $agent_user = $request->input('user');
    $alt_user = $request->input('alt_user');

    if (strlen($agent_user) < 1 && strlen($alt_user) < 2) {
        return response()->json([
            'result' => 'ERROR',
            'result_reason' => 'call_agent not valid'
        ], 400);
    }

    if (strlen($alt_user) > 1) {
        $user = VicidialUser::where('custom_three', $alt_user)->first();
        if ($user) {
            $agent_user = $user->user;
        } else {
            return response()->json([
                'result' => 'ERROR',
                'result_reason' => 'no user found'
            ], 404);
        }
    }

    $agent = VicidialLiveAgent::where('user', $agent_user)->first();
    if ($agent) {
        $sessionData = DB::table('vicidial_session_data')->where('user', $agent_user)->first();
        if ($sessionData && strlen($sessionData->agent_login_call) > 5) {
            $conf_exten = $agent->conf_exten;
            $agent_login_call = $sessionData->agent_login_call;
            $call_agent_conference = preg_replace("/(.+?Exten: )\d{7}(\|Priority.+)/", "$1 $conf_exten$2", $agent_login_call);
            $call_agent_string = str_replace('|', "','", $call_agent_conference);

            DB::table('vicidial_manager')->insert([
                'server_ip' => $agent->server_ip,
                'channel' => "SIP/{$agent->extension}",
                'callerid' => "AgentLogin <{$agent_user}>",
                'status' => 'NEW',
                'action' => 'Originate',
                'cmd_line_b' => "Channel: SIP/{$agent->extension}",
                'cmd_line_c' => "Context: default",
                'cmd_line_d' => "Exten: {$conf_exten}",
                'cmd_line_e' => "Priority: 1",
                'cmd_line_f' => "Callerid: \"AgentLogin <{$agent_user}>\"",
                'cmd_line_g' => 'ActionID: agent_login',
                'cmd_line_h' => "Variable: VAR1=value1"
            ]);

            return response()->json([
                'result' => 'SUCCESS',
                'result_reason' => 'call_agent function sent'
            ], 200);
        } else {
            return response()->json([
                'result' => 'ERROR',
                'result_reason' => 'call_agent error - entry is empty or no session data'
            ], 400);
        }
    } else {
        return response()->json([
            'result' => 'ERROR',
            'result_reason' => 'agent_user is not logged in'
        ], 400);
    }
}

public function UpdateExtensionAndCampaign(Request $request) {
    $validator = Validator::make($request->all(), [ 
        'user' => 'required|string|min:2|max:20', 
        'campaign_name' => 'required|string|min:1|max:50' 
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    DB::beginTransaction();

    try {
        $baseUser = $request->input('user');
        $campaign_name = $request->input('campaign_name');
        $user = VicidialUser::where('user', $baseUser)->first();

        if ($user) {
            // Se o agente já existe, verificar se há um ramal vinculado usando a coluna 'login'
            $phone = Phone::where('login', $user->user)->first();
            if (!$phone) {
                // Se não há ramal vinculado, encontrar um ramal disponível ou criar um novo
                $phone = $this->findAvailableExtension();
                if (!$phone) {
                    $phone = $this->createNewExtension();
                }
                // Transferir o ramal para o agente existente
                $this->transferExtension($phone, $user);
            }
        } else {
            // Garantir que o user seja único
            $user = $baseUser;
            $suffix = 1;

            while (VicidialUser::where('user', $user)->exists()) {
                $user = $baseUser . $suffix;
                $suffix++;
            }

            $request->merge(['user' => $user]);

            // Criação do agente com valores padrão
            $userData = [
                'user' => $user,
                'pass' => $user,
                'user_level' => 1,
                'full_name' => $user,
                'user_group' => 1
            ];
            $user = VicidialUser::create($userData);

            // Encontrar um ramal disponível ou criar um novo
            $phone = $this->findAvailableExtension();

            if (!$phone) {
                $phone = $this->createNewExtension();
            }

            // Transferir o ramal para o novo agente
            $this->transferExtension($phone, $user);
        }

        // Atualizar a tabela vicidial_users com phone_login e phone_pass
            $user->update([
            'phone_login' => $phone->dialplan_number,
            'phone_pass' => $phone->pass
        ]);


        // Verificar se o agente já está vinculado à campanha
        $campaign = Campaign::where('campaign_name', $campaign_name)->first();

        if (!$campaign) {
            throw new \Exception('Campaign not found');
        }

        $existingAssociation = DB::table('vicidial_campaign_agents')
            ->where('campaign_id', $campaign->campaign_id)
            ->where('user', $user->user)
            ->first();

        if (!$existingAssociation) {
            DB::table('vicidial_campaign_agents')->insert([
                'campaign_id' => $campaign->campaign_id,
                'user' => $user->user,
            ]);
        }

        DB::commit();

        // Saída filtrada
        $filteredResponse = [
            'user' => $user->user,
            'pass' => $user->pass,
            'full_name' => $user->full_name,
            'user_level' => 'Agente - ' . $user->user_level,
            'campaign' => $campaign->campaign_name,
            'extension' => $phone->dialplan_number
        ];

        return response()->json($filteredResponse, 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

private function findAvailableExtension()
    {
        // Procurar um ramal que atenda aos parâmetros específicos
        $phone = Phone::where('dialplan_number', '>', '8300')
                      ->where(function ($query) {
                          $query->where('peer_status', 'UNREGISTERED')
                                ->orWhere('peer_status', 'UNKNOWN'); // Adiciona a condição UNKNOWN
                      })
                      ->where('active', 'Y') // Verifica se o ramal está ativo
                      ->whereNull('login') // Verifica se o ramal não está associado a nenhum usuário
                      ->first();
    
        if ($phone) {
            // Se encontrou um ramal disponível, retorna o ramal
            return $phone;
        }
    
        // Se não encontrou, retorna null
        return null;
    }

    private function createNewExtension() {
        // ID da extensão padrão
        $defaultExtensionId = '1000'; // Substitua pelo ID real da extensão padrão
    
        // Copiar parâmetros da extensão padrão
        $defaultExtension = Phone::where('dialplan_number', $defaultExtensionId)->first();
    
        if (!$defaultExtension) {
            throw new \Exception('Default extension not found');
        }
    
        // Obter o último número de extensão usado
        $lastExtension = Phone::orderBy('dialplan_number', 'desc')->first();
        $newExtensionNumber = $lastExtension ? $lastExtension->dialplan_number + 1 : 8301;
    
        // Verificar se o novo número de extensão já existe e incrementar se necessário
        while (Phone::where('dialplan_number', $newExtensionNumber)->exists()) {
            $newExtensionNumber++;
        }
    
        // Adicionar valores padrão
        $defaultValues = [
            'source' => 'hsr',
            'server_ip' => '10.0.0.112',
            'protocol' => 'SIP',
            'phone_type' => 'SIP',
            'local_gmt' => '-3.00',
            'call_out_number_group' => 'SIP/AETelecom',
            'template_id' => 'VICIphone WebRTC',
            'dialplan_number' => $newExtensionNumber, // Novo número sequencial
            'voicemail_id' => $newExtensionNumber, // Novo número sequencial
            'login' => (string)$newExtensionNumber, // Novo número sequencial
            'pass' => (string)$newExtensionNumber, // Novo número sequencial
            'login_user' => (string)$newExtensionNumber, // Novo número sequencial
            'login_pass' => (string)$newExtensionNumber, // Novo número sequencial
            'outbound_cid' => $newExtensionNumber, // Novo número sequencial
            'extension' => (string)$newExtensionNumber, // Campo extension
            'fullname' => 'User ' . $newExtensionNumber // Campo fullname
        ];
    
        // Mesclar dados da extensão padrão com os valores padrão
        $newExtensionData = array_merge($defaultExtension->toArray(), $defaultValues);
    
        // Criar a nova extensão
        try {
            $phone = Phone::create($newExtensionData);
            return $phone;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create new extension: ' . $e->getMessage());
        }
    }    
        private function transferExtension($phone, $newUser)
    {
        // Atualiza o ramal para associá-lo ao novo agente
        $phone->update([
            'login' => $newUser->user // Use a coluna 'login' para a associação
        ]);
    }
    

}
