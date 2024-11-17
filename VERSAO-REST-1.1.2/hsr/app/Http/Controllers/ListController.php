<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\VicidialList; 
use App\Models\VicidialLists;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ListController extends Controller

{
    private static $maxItemsPerPage = 100;
    // Validação dos campos obrigatórios e opcionais
    private static $validatorFields = 
    [
        'list_id' => 'string|max:10', // Adicione a validação para list_id'string|max:10',
        'list_name' => 'string|max:50',
        'list_description' => 'string|max:255',
        'active' => 'string|in:Y,N',
        'campaign_id' => 'string|max:10',
    ];


    public function index(Request $request)
    {
        $itensPorPagina = $request->input('items_per_page', 10);
        if ($itensPorPagina > self::$maxItemsPerPage) {
            $itensPorPagina = self::$maxItemsPerPage;
        }

        $query = VicidialLists::query(); // Alterado para buscar listas de leads

        // Adicione os filtros conforme necessário
        if ($request->has('active')) {
            $query->where('active', $request->input('active'));
        }

        if ($request->has('list_id')) {
            $query->where('list_id', $request->input('list_id'));
        }

        $Lists = $query->paginate($itensPorPagina);

        // Parâmetro para resposta completa ou filtrada
        $fullResponse = $request->input('full', false);
        $filteredData = $Lists->map(function ($list) use ($fullResponse) {
            if ($fullResponse) {
                return $list;
            } else {
                return [
                    'list_id' => $list->list_id,
                    'list_name' => $list->list_name,
                    'list_description' => $list->list_description,
                    'active' => $list->active,
                    'campaign_id' => $list->campaign_id
                ];
            }
        });

        return response()->json([
            'current_page' => $Lists->currentPage(),
            'data' => $filteredData,
            'total' => $Lists->total(),
            'per_page' => $Lists->perPage(),
            'last_page' => $Lists->lastPage()
        ]);
    }

    public function get($identifier) {
        // Tenta encontrar a Lista pelo ID
        $list = VicidialLists::find($identifier);
    
        // Se não encontrar pelo ID, tenta encontrar pelo nome
        if (!$list) {
            $list = VicidialLists::where('list_name', $identifier)->first();
        }
    
        // Se ainda não encontrar, retorna erro
        if (!$list) {
            return response()->json(['error' => 'Lista not found'], 404);
        }
    
        return response()->json($list);
    }

    //CRIA UMA LISTA COM BASE EM  PARAMETROS 
    public function post(Request $request) {
        // Validação dos dados de entrada
        $validator = Validator::make($request->all(), [
            'list_name' => 'required|string|min:1|max:50',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        // Buscar o último list_id
        $lastList = VicidialLists::orderBy('list_id', 'desc')->first();
    
        // Definir o novo list_id
        $newListId = $lastList ? (string) ((int) $lastList->list_id + 1) : '1'; // Começa em 1 se não houver listas
    
        // Criar nova lista com o novo list_id e o nome fornecido
        $newListData = [
            'list_id' => $newListId,
            'list_name' => $request->input('list_name'),
            'active' => 'N',
            'list_description' => $request->input('list_name'),
            // Adicione outros campos padrão que você deseja inicializar, se necessário
        ];
    
        // Crie a nova lista
        $newList = VicidialLists::create($newListData);
    
        // Return the created list with its ID
    return response()->json([
        //'list_id' => $newList->list_id,
        'list_name' => $newList->list_name,
        'active' => $newList->active,
        'list_description' => $newList->list_description,
    ], 201);
    }

    public function put(Request $request, $identifier) {
        // Tenta encontrar a Lista pelo ID (se for numérico) ou pelo nome
        $list = null;
        if (is_numeric($identifier)) {
            $list = VicidialLists::find($identifier);
        } else {
            // Tenta encontrar a campanha pelo nome
            $list = VicidialLists::where('list_name', $identifier)->first();
        }
    
        // Se não encontrar, retorna erro
        if (!$list) {
            return response()->json(['error' => 'List not found'], 404);
        }
    
        // Atualiza a campanha com todos os dados do corpo da requisição
        $list->update($request->all());
        
        return response()->json($list, 200);
    }

    public function delete($identifier) {
        // Tenta encontrar a campanha pelo ID (se for numérico) ou pelo nome
        $list = null;
        if (is_numeric($identifier)) {
            $list = VicidialLists::find($identifier);
        } else {
            // Tenta encontrar a campanha pelo nome
            $list = VicidialLists::where('list_name', $identifier)->first();
        }
    
        // Se não encontrar, retorna erro
        if (!$list) {
            return response()->json(['error' => 'List not found'], 404);
        }
    
        // Remove a campanha
        $list->delete();
    
        // Retorna uma resposta de sucesso
        return response()->json(['message' => 'List deleted successfully'], 200);
    }

}