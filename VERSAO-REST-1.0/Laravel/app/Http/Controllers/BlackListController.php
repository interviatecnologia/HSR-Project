<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlackListController extends Controller
{
    private static $maxItemsPerPage = 1000;
    private static $validatorFields = [
    ];

    public function index(Request $request)
    {
        $itensPorPagina = $request->input('items_per_page', 10);
        if($itensPorPagina > self::$maxItemsPerPage)
            $itensPorPagina = self::$maxItemsPerPage;
        
        $phones = []; //$query->paginate($itensPorPagina);
        return response()->json($phones);
    }
    
    public function post(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), self::$validatorFields);
        if($validator->fails())
            return response()->json($validator->errors(), 400);
        
            // salavar os telefones
        return response()->json([], 201);
    }
    
    public function delete(Request $request)
    {
        // remover telefone
        return response()->json([], 204);
    }
}