<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialUser;
use App\Models\Phone;
use App\Models\Campaign;
use App\Models\VicidialCampaignAgents;
use Illuminate\Support\Facades\DB; 

class AgentsController extends Controller {
    private static $maxItemsPerPage = 1000;
    private static $validatorFields = [
        'user' => 'required|string|min:2|max:20',
        'pass' => 'required|string|min:1|max:20',
        'user_level' => 'required|integer|min:1|max:9',
        'full_name' => 'required|string|min:1|max:50',
        'user_group' => 'required|string|min:1|max:20',
        'campaign_id' => 'required|string|min:1|max:20' // Adiciona campanha ao criar
    ];

    public function index(Request $request) {
        $itensPorPagina = $request->input('items_per_page', 10);
        if ($itensPorPagina > self::$maxItemsPerPage) $itensPorPagina = self::$maxItemsPerPage;

        $query = VicidialUser::query();

        // Adicione os filtros conforme necessário
        if ($request->has('user')) {
            $query->where('user', $request->input('user'));
        }
        if ($request->has('pass')) {
            $query->where('pass', $request->input('pass'));
        }
        if ($request->has('full_name')) {
            $query->where('full_name', $request->input('full_name'));
        }

        $users = $query->paginate($itensPorPagina);

        // Parâmetro para resposta completa ou filtrada
        $fullResponse = $request->input('full', false);

        $filteredData = $users->map(function ($user) use ($fullResponse) {
            if ($fullResponse) {
                return $user;
            } else {
                return [
                    'user_id' => $user->user_id, // Acesse o id diretamente do objeto $user
                    'user' => $user->user,
                    'pass' => $user->pass,
                    'full_name' => $user->full_name
                ];
            }
        });

        return response()->json([
            'current_page' => $users->currentPage(),
            'data' => $filteredData,
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'last_page' => $users->lastPage()
        ]);
    }

    public function post(Request $request) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $user = VicidialUser::create($request->only(array_keys(self::$validatorFields)));
        return response()->json($user, 201);
    }

    public function get(string $user) {
        $user = VicidialUser::find($user);
        if (!$user) return response()->json('Not found', 404);
        return response()->json($user);
    }

    public function put(Request $request, string $id) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $user = VicidialUser::find($id);
        if (!$user) return response()->json('Not found', 404);
        $user->update($request->only(array_keys(self::$validatorFields)));
        return response()->json('Success', 200);
    }

    public function delete(string $id) {
        $user = VicidialUser::find($id);
        if (!$user) return response()->json('Not found', 404);
        $user->delete();
        return response()->json([], 204);
    }


    public function createWithExtensionAndCampaign(Request $request) {
        $validator = Validator::make($request->all(), [ 
            'user' => 'required|string|min:2|max:20', 
            'campaign_name' => 'required|string|min:1|max:50' 
        ]);
        
        if ($validator->fails()) return response()->json($validator->errors(), 400);
    
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
        // Adicionar valores padrão 
    $defaultValues = [
        'source' => 'hsr',
        'server_ip' => '10.0.0.112',
        'protocolo' => 'SIP',
        'phone_type' => "SIP",
        'local_gmt' => '-3.00',
        'call_out_number_group' => 'SIP/AETelecom',
        'template_id' => 'VICIphone WebRTC',
        'is_webphone' => 'Y',
        'webphone_dialpad' => 'Y',
        'webphone_auto_answer' => 'Y',
        'webphone_dialbox' => 'Y',
        'conf_secret' => 'HSR' . $newExtensionNumber, // Novo número sequencial com 'HSR' na frente
        'dialplan_number' => $newExtensionNumber, // Novo número sequencial
        'voicemail_id' => $newExtensionNumber, // Novo número sequencial
        'login' => $newExtensionNumber, // Novo número sequencial
        'pass' => $newExtensionNumber, // Novo número sequencial
        'login_user' => $newExtensionNumber, // Novo número sequencial
        'login_pass' => $newExtensionNumber, // Novo número sequencial
        'outbound_cid' => $newExtensionNumber, // Novo número sequencial
        'extension' => (string)$newExtensionNumber, // Campo extension
        'fullname' => 'User   ' . $newExtensionNumber // Campo fullname
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