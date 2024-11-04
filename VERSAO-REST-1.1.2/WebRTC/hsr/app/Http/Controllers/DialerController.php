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
                'status' => 'LOGIN',
                'server_ip' => $server_ip,
                'lead_id' => $lead_id,
                'extension' => 'SIP/' . $extension,
                // Adicione outros campos necessários, como campaign_id, etc., se necessário
            ]);
    
            // Atribuir sala de conferência ao agente
            $conferenceId = $this->assignConference($newAgent);
            if (!$conferenceId) {
                return response()->json([
                    'result' => 'ERROR',
                    'result_reason' => 'Failed to assign conference'
                ], 500);
            }
    
            // Atualiza a extensão do agente na tabela
            $newAgent->conf_exten = $conferenceId;
            $newAgent->save();
    
            return response()->json([
                'result' => 'SUCCESS',
                'result_reason' => 'login function set',
                'agent_user' => $agent_user,
                'extension' => 'SIP/' . $extension,
                'status' => $newAgent->status,
                'campaign_id' => $campaign_id,
                'conference_id' => $conferenceId // Retorna o ID da conferência
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
            // Desconectar o agente da conferência
            if ($agent->conf_exten) {
                $this->disconnectAgentFromConference($agent_user); // Chama a função para desconectar
            }
    
            // Remover a extensão do agente da tabela conferências
            $agent->conf_exten = ''; // Ou use '' se preferir
    
            // Salvar as alterações na tabela vicidial_live_agents
            $agent->save();
    
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
    $userInfo = VicidialUser ::where('user', $user)->first();
    if (!$userInfo) {
        return response()->json([
            'status' => 'DISCONNECTED',
            'message' => 'User  not found in VicidialUser '
        ], 404);
    }

    // Buscar o agente na tabela vicidial_live_agents  
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
    $phoneLogin = $userInfo->phone_login; // Obter phone_login

    if ($agent) {
        $agentStatus = $agent->status;
        $campaignId = $agent->campaign_id;
        $pauseCode = $agent->pause_code;
        $extension = $agent->extension;
        //$extension = str_replace('SIP/', '', $agent->extension);
    } else {
        // Se o agente não estiver no VicidialLiveAgent, verificar o telefone
        $extension = str_replace('SIP/', '', $phoneLogin);
        
    }

    // Verifica se a campanha está atribuída
    if (is_null($campaignId) || empty($campaignId)) {
        return response()->json([
            'status' => $agentStatus,
            'user_id' => $userInfo->user_id,
            'user' => $userInfo->user,
            'full_name' => $userInfo->full_name,
            'extension' => $agent && $agent->extension ? $agent->extension : $phoneLogin,
            'conf_secret' => $agent && $agent->conf_secret ? $agent->conf_secret : null, // Ou use um valor padrão
            'phone_login' => $phoneLogin,
            'phone_pass' => $userInfo->phone_pass,
            'conf_exten' => $agent ? $agent->conf_exten : null,
            'campaign_id' => $agent->$campaignId ?? null,
            'pause_code' => $pauseCode,
            'peer_status' => $peer_status ?? 'UNKNOWN',   // Status desconhecido se não houver campanha$sipStatus, // Status desconhecido se não houver campanha
            'message' => 'Campanha não atribuída' // Mensagem personalizada
        ], 400);
    }

    // Verifica se a ramal está atribuído
    if (is_null($extension) || empty($extension)) {
        return response()->json([
            'status' => $agentStatus,
            'user_id' => $userInfo->user_id,
            'user' => $userInfo->user,
            'full_name' => $userInfo->full_name,
            'extension' => $agent && $agent->extension ? $agent->extension : $phoneLogin,
            'conf_secret' => $agent && $agent->conf_secret ? $agent->conf_secret : null, // Ou use um valor padrão
            'phone_login' => $phoneLogin,
            'phone_pass' => $userInfo->phone_pass,
            'conf_exten' => $agent ? $agent->conf_exten : null,
            'campaign_id' => $agent->$campaignId ?? null,
            'pause_code' => $pauseCode,
            'peer_status' => $peer_status ?? 'UNKNOWN',   // Status desconhecido se não houver campanha$sipStatus, // Status desconhecido se não houver campanha
            'message' => 'Ramal não atribuída' // Mensagem personalizada
        ], 400);
    }

    // Encontrar o ID correspondente no Phone para obter o Sip Status
    $phoneInfo = Phone::where('dialplan_number', $extension)->first();
    if (!$phoneInfo) {
        return response()->json([
            'status' => $agentStatus,
            'user_id' => $userInfo->user_id,
            'user' => $userInfo->user,
            'full_name' => $userInfo->full_name,
            'extension' => $agent && $agent->extension ? $agent->extension : $phoneLogin,
            'conf_secret' => $agent && $agent->conf_secret ? $agent->conf_secret : null, // Ou use um valor padrão
            'phone_login' => $phoneLogin,
            'phone_pass' => $userInfo->phone_pass,
            'conf_exten' => $agent->conf_exten,
            'campaign_id' => $agent->campaign_id,
            'pause_code' => $pauseCode,            
            'peer_status' => $peer_status ?? 'UNKNOWN', // Se o telefone não for encontrado, status SIP é desconhecido
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
        'conf_secret' => $agent && $agent->conf_secret ? $agent->conf_secret
        : null, // Ou use um valor padrão
        'phone_login' => $phoneLogin,
        'phone_pass' => $userInfo->phone_pass,
        'conf_exten' => $agent ? $agent->conf_exten : null,
        'campaign_id' => $agent->campaign_id,
        'pause_code' => $pauseCode,
        'peer_status' => $status,
        'message' => 'OK'

    ]);

    }
       

public function allStatus()
{
    $agents = VicidialLiveAgent::all();
    return response()->json($agents);
}



public function externalDial(Request $request) {
    Log::info('externalDial function called');

    // Validação dos parâmetros da requisição
    $validator = Validator::make($request->all(), [
        'telefone' => 'required|string|min:2',
        'agent_user' => 'required|string|min:2',
        'lead_id' => 'nullable|integer',
        'outbound_cid' => 'nullable|string', // Adicionando validação para outbound_cid
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed', ['errors' => $validator->errors()]);
        return response()->json(['error' => 'Invalid request'], 400);
    }

    $value = $request->input('telefone');
    $agent_user = $request->input('agent_user');
    $outbound_cid = $request->input('outbound_cid') ?? ''; // Obtendo outbound_cid
    Log::info('Parameters received', ['telefone' => $value, 'agent_user' => $agent_user]);

    // Preparar variáveis
    $phone_code = "55";
    $dial_ingroup = 'HSRTECH';
    $group_alias = 'HSRTECH';
    $lead_id = $request->input('lead_id') ?? null;

    // Formatar número de telefone
    $value = preg_replace("/[^0-9]/", "", $value);

    // Verificar se o agente está logado
    $liveAgent = VicidialLiveAgent::where('user', $agent_user)->first();
    if (!$liveAgent) {
        Log::error('Agent user is not logged in', ['agent_user' => $agent_user]);
        return response()->json(['error' => 'Agent user is not logged in'], 404);
    }

    Log::info('Agent found', ['liveAgent' => $liveAgent]);

    // Verificar se o agente está em conferência
    if (!$liveAgent->conf_exten) {
        Log::info('Agent not in conference, attempting to assign conference');
        $conferenceId = $this->assignConference($liveAgent);
        if (!$conferenceId) {
            Log::error('Failed to assign conference');
            return response()->json(['error' => 'O agente deve estar conectado a uma conferência antes de realizar uma chamada.'], 400);
        }
        $liveAgent->conf_exten = $conferenceId;
        $liveAgent->save();
    } else {
        $conferenceId = $liveAgent->conf_exten;
    }

    Log::info('Conference assigned', ['conferenceId' => $conferenceId]);

    // Fazer a chamada externa
    $result = $this->makeCall([
        'value' => $value,
        'agent_user' => $agent_user,
        'phone_code' => $phone_code,
        'lead_id' => $lead_id,
        'dial_ingroup' => $dial_ingroup,
        'group_alias' => $group_alias,
        'outbound_cid' => $outbound_cid, // Passando outbound_cid para makeCall
        'conference_id' => $conferenceId,
        'search' => 'Y', // Valor padrão para search
        'preview' => 'N', // Valor padrão para preview
        'focus' => 'Y' // Valor padrão para focus
    ]);

    if ($result['status'] == 'SUCCESS') {
        Log::info('Call initiated successfully');
        return response()->json(['message' => 'Call initiated'], 200);
    } else {
        Log::error('Failed to initiate call', ['reason' => $result['reason']]);
        return response()->json(['error' => 'Failed to initiate call', 'reason' => $result['reason']], 500);
    }
}   
private function assignConference($liveAgent) {  
    Log::info('Assigning conference');  

    // Busca uma conferência livre  
    $conference = DB::table('vicidial_conferences')  
       ->where('server_ip', $liveAgent->server_ip)  
       ->where('extension', '') // Conferência deve estar livre  
       ->first();  

    Log::info('Conference query result', ['conference' => $conference]);  

    if ($conference) {  
        // Formata a extensão corretamente  
        $extension =  $liveAgent->extension;  

        // Tenta atualizar a conferência com a nova extensão do agente  
        $updateResult = DB::table('vicidial_conferences')  
            ->where('conf_exten', $conference->conf_exten)  
            ->update(['extension' => $extension]);  

        if ($updateResult) {
            Log::info('Conference assigned successfully', ['conferenceId' => $conference->conf_exten, 'agent_extension' => $extension]);
            // Atualizar a extensão do agente na tabela vicidial_live_agents
            $liveAgent->update(['conf_exten' => $conference->conf_exten]);
            return $conference->conf_exten;
        } else {
            Log::error('Failed to update conference with agent extension', ['conferenceId' => $conference->conf_exten]);
            return false; // Retorna false se a atualização falhar
        }
    }

    Log::error('No available conference found');
    return false; // Retorna false se nenhuma conferência disponível for encontrada
}
   
public function connectAgentToConference(Request $request) {
    Log::info('connectAgentToConference function called');

    // Validação dos parâmetros da requisição
    $validator = Validator::make($request->all(), [
        'agent_user' => 'required|string|min:2',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed', ['errors' => $validator->errors()]);
        return response()->json(['error' => 'Invalid request'], 400);
    }

    $agentUser = $request->input('agent_user');
    Log::info('Agent user received', ['agent_user' => $agentUser]);

    // Busca o liveAgent para obter a extensão correta
    $liveAgent = VicidialLiveAgent::where('user', $agentUser)->first();
    if (!$liveAgent) {
        Log::error('Agent user is not logged in', ['agent_user' => $agentUser]);
        return response()->json(['error' => 'Agent user is not logged in'], 404);
    }

    Log::info('Agent found', ['liveAgent' => $liveAgent]);

    // Busca uma conferência disponível
    $conference = DB::table('vicidial_conferences')
        ->where('server_ip', '10.0.0.112') // IP do servidor Asterisk
        ->where('extension', '') // Conferência deve estar livre
        ->first();

    if (!$conference) {
        Log::error('No available conference found');
        return response()->json(['error' => 'No available conference found'], 404);
    }

    // Conectar o agente à conferência
    $socket = fsockopen('127.0.0.1', 5038, $errno, $errstr, 30);
    if (!$socket) {
        Log::error('Could not connect to AMI', ['error' => $errstr, 'errno' => $errno]);
        return response()->json(['error' => 'Could not connect to AMI'], 500);
    }

    // Atualizar a extensão do agente na tabela vicidial_live_agents
    $liveAgent->conf_exten = $conference->conf_exten;
    $liveAgent->save();

    // Atualizar o banco de dados vicidial_conferences
    $updateResult = DB::table('vicidial_conferences')
    ->where('conf_exten', $conference->conf_exten)
    ->update(['extension' => $conference->extension]);

if (!$updateResult) {
    Log::error('Failed to update conference extension', ['conferenceId' => $conference->conf_exten]);
}
    
    // Autenticar no Asterisk
    fputs($socket, "Action: Login\r\n");
    fputs($socket, "Username: cron\r\n");
    fputs($socket, "Secret: 1234\r\n");
    fputs($socket, "Events: on\r\n\r\n");

    $response = $this->getAmiResponse($socket);
    if (strpos($response, 'Success') === false) {
        Log::error('AMI login failed', ['response' => $response]);
        return response()->json(['error' => 'AMI login failed'], 500);
    }

    // Comando Originate
    $extensionWithPrefix =  $liveAgent->extension;
    fputs($socket, "Action: Originate\r\n");
    fputs($socket, "Channel: {$extensionWithPrefix}\r\n"); 
    fputs($socket, "Context: default\r\n");
    fputs($socket, "Exten: {$conference->conf_exten}\r\n");
    fputs($socket, "Priority: 1\r\n");
    fputs($socket, "Application: MeetMe\r\n");
    fputs($socket, "Data: {$conference->conf_exten},F\r\n\r\n");

    
    // Monitora o estado da chamada
    $callStatus = 'call initied'; // Estado inicial
    $timeout = time() + 15; // Tempo limite de 30 segundos

    while (time() < $timeout) {
        $event = $this->getAmiResponse($socket); // Função para obter eventos do AMI
    
        // Verifica se o evento é um Dial
        if (strpos($event, 'Dial') !== false) {
            if (strpos($event, 'ringing') !== false) {
                $callStatus = 'ringing';
            } elseif (strpos($event, 'Busy') !== false) {
                $callStatus = 'busy';
            } elseif (strpos($event, 'No Answer') !== false) {
                $callStatus = 'unreachable';
            }
        }

        // Se a chamada foi atendida ou finalizada, saia do loop
    if (strpos($event, 'Hangup') !== false) {
        break;

    }

        // Retorna o status da chamada
    return response()->json(['status' => $callStatus], 200);
}
$response = $this->getAmiResponse($socket);
    if (strpos($response, 'Success') === false) {
        Log::error('MeetMe failed', ['response' => $response]);
        return response()->json(['error' => 'MeetMe failed'], 500);
    }

    // Atualizar a extensão do agente na tabela vicidial_live_agents
    $liveAgent = VicidialLiveAgent::where('user', $agentUser)->first();
    if ($liveAgent) {
        $liveAgent->conf_exten = $conference->conf_exten;
        $liveAgent->save();
    }

    Log::info('Agent connected to conference', ['agent_user' => $agentUser, 'conference_id' => $conference->conf_exten]);
    return response()->json(['message' => 'Agent connected to conference'], 200);
}

public function disconnectAgentFromConference($agentUser ) {
    Log::info('disconnectAgentFromConference function called', ['agent_user' => $agentUser ]);

    // Busca o liveAgent para obter a conferência atual
    $liveAgent = VicidialLiveAgent::where('user', $agentUser )->first();
    if (!$liveAgent || !$liveAgent->conf_exten) {
        Log::error('Agent is not connected to any conference', ['agent_user' => $agentUser ]);
        return response()->json(['error' => 'Agent is not connected to any conference'], 404);
    }

    // Atualiza a conferência para remover a extensão do agente
    $updateResult = DB::table('vicidial_conferences')
        ->where('conf_exten', $liveAgent->conf_exten)
        ->update(['extension' => '']); // Limpa a extensão

    if (!$updateResult) {
        Log::error('Failed to update conference extension on disconnect', ['conferenceId' => $liveAgent->conf_exten]);
    }

    // Limpa a extensão do agente
    $liveAgent->conf_exten = null;
    $liveAgent->save();

    Log::info('Agent disconnected from conference', ['agent_user' => $agentUser ]);
    return response()->json(['message' => 'Agent disconnected from conference'], 200);
}

private function getConferenceStatus($conferenceId) {  
    // Consultar o status da conferência  
    $conferenceStatus = DB::table('vicidial_conferences')  
        ->where('conf_exten', $conferenceId)  
        ->first();  

    if (!$conferenceStatus) {  
        return 'error';  
    }  

    // Criar socket para conexão com AMI
    $socket = fsockopen('127.0.0.1', 5038, $errno, $errstr, 30);
    if (!$socket) {
        Log::error('Could not connect to AMI', ['error' => $errstr, 'errno' => $errno]);
        return ['status' => 'ERROR', 'reason' => "Could not connect to AMI: $errstr ($errno)"];
    }

    // Enviar informações de autenticação
    fputs($socket, "Action: Login\r\n");
    fputs($socket, "Username: cron\r\n");
    fputs($socket, "Secret: 1234\r\n");
    fputs($socket, "Events: on\r\n\r\n");

    // Ler a resposta da autenticação
    $response = $this->getAmiResponse($socket);  

    // Fechar o socket após o uso
    fclose($socket);

    // Verificar se a resposta do Asterisk é válida  
    if (strpos($response, 'Success') !== false) {  
        // Verificar se a conferência está disponível  
        if ($conferenceStatus && empty($conferenceStatus->extension)) {  
            return 'available';  
        } else {  
            return 'unavailable';  
        }  
    } else {
        // Retornar um erro se a resposta do Asterisk não for válida  
        Log::error('Authentication failed', ['response' => $response]);
        return 'error';  
    }  
}
 
 private function isAgentConnectedToConference($agentUser) {
    $liveAgent = VicidialLiveAgent::where('user', $agentUser)->first();
    return $liveAgent && $liveAgent->conf_exten;
}

private function makeCall($params) {
    Log::info('Making call', ['params' => $params]);

    $socket = fsockopen('127.0.0.1', 5038, $errno, $errstr, 30);
    if (!$socket) {
        Log::error('Could not connect to AMI', ['error' => $errstr, 'errno' => $errno]);
        return ['status' => 'ERROR', 'reason' => "Could not connect to AMI: $errstr ($errno)"];
    }

    fputs($socket, "Action: Login\r\n");
    fputs($socket, "Username: cronoriginate\r\n");
    fputs($socket, "Secret: 1234\r\n");
    fputs($socket, "Events: on\r\n\r\n");

    // Get login response
    $response = $this->getAmiResponse($socket);
    if (strpos($response, 'Success') === false) {
        Log::error('AMI login failed', ['response' => $response]);
        fclose($socket); // Close socket on error
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

    // Logoff immediately after sending the command
    fputs($socket, "Action: Logoff\r\n\r\n");
    fclose($socket);

    // Return immediate success response
    Log::info('Call initiated successfully');
    return ['status' => 'SUCCESS'];
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



}