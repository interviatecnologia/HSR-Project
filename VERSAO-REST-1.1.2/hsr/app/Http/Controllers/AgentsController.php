<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidialUser;
use App\Models\Phone;
use App\Models\Campaign;
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

    public function get(string $id) {
        $user = VicidialUser::find($id);
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
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
    
        DB::beginTransaction();
    
        try {
            // Garantir que o user seja único
            $baseUser = $request->input('user');
            $user = $baseUser;
            $suffix = 1;
    
            while (VicidialUser::where('user', $user)->exists()) {
                $user = $baseUser . $suffix;
                $suffix++;
            }
    
            $request->merge(['user' => $user]);
    
            // Criação do agente
            $userData = $request->only(array_keys(self::$validatorFields));
            $user = VicidialUser::create($userData);
    
            // Encontrar um ramal disponível
            $phone = $this->findAvailableExtension();
    
            if (!$phone) {
                throw new \Exception('No available extension found');
            }
    
            // Transferir o ramal para o novo agente
            $this->transferExtension($phone, $user);
    
            // Vinculação do agente à campanha
            $campaign_id = $request->input('campaign_id');
            $campaign = Campaign::find($campaign_id);
    
            if (!$campaign) {
                throw new \Exception('Campaign not found');
            }
    
            DB::table('vicidial_campaign_agents')->insert([
                'campaign_id' => $campaign_id,
                'user' => $user->user,
            ]);
    
            DB::commit();
    
            return response()->json([
                'user' => $user,
                'extension' => $phone
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    
    private function findAvailableExtension() {
        // Procurar um ramal que atenda aos parâmetros específicos
        $phone = Phone::where('dialplan_number', '>', '8300')
                      //->where('call_out_number_group', 'SIP/AETelecom')
                      //->where('template_id', 'VICIphone')
                      ->where(function ($query) {
                          $query->where('peer_status', 'UNREGISTERED')
                                ->orWhere('peer_status', 'UNKNOWN'); // Adiciona a condição UNKNOWN
                      })
                      ->where('active', 'Y') // Verifica se o ramal está ativo
                      ->first();
    
        if ($phone) {
            // Se encontrou um ramal disponível, retorna o ramal
            return $phone;
        }
    
        // Se não encontrou, retorna null
        return null;
    }
    
    
    
    private function transferExtension($phone, $newUser) {
        // Atualiza o ramal para associá-lo ao novo agente
        $phone->update([
            'assigned_user' => $newUser->user // Use uma coluna existente ou crie uma nova coluna como 'assigned_user'
        ]);
    }
    
    
    
}