<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\VicidailUser;

class AgentsController extends Controller {
    private static $maxItemsPerPage = 1000;
    private static $validatorFields = [
        'user' => 'required|string|min:2|max:20', //AUTOGENERATED
        'pass' => 'required|string|min:1|max:20',
        'user_level' => 'required|int|min:1|max:9',
        'full_name' => 'required|string|min:1|max:50',
        'user_group' => 'required|int|min:1|max:20'

    ];

    public function index(Request $request) {
        $itensPorPagina = $request->input('items_per_page', 10);
        if ($itensPorPagina > self::$maxItemsPerPage) $itensPorPagina = self::$maxItemsPerPage;

        $query = VicidailUser::query();

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
        $user = VicidailUser::create($request->only(array_keys(self::$validatorFields)));
        return response()->json($user, 201);
    }

    public function get(string $id) {
        $user = VicidailUser::find($id);
        if (!$user) return response()->json('Not found', 404);
        return response()->json($user);
    }

    public function put(Request $request, string $id) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $user = VicidailUser::find($id);
        if (!$user) return response()->json('Not found', 404);
        $user->update($request->only(array_keys(self::$validatorFields)));
        return response()->json('Success', 200);
    }

    public function delete(string $id) {
        $user = VicidailUser::find($id);
        if (!$user) return response()->json('Not found', 404);
        $user->delete();
        return response()->json([], 204);
    }
}