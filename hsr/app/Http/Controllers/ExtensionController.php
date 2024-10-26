<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Phone;

class ExtensionController extends Controller {
    private static $maxItemsPerPage = 1000;
    private static $validatorFields = [
        'extension' => 'required|string|min:2|max:20',
        'fullname' => 'required|string|min:1|max:50',
    ];

    public function index(Request $request) {
      $itensPorPagina = $request->input('items_per_page', 10);
      if ($itensPorPagina > self::$maxItemsPerPage) $itensPorPagina = self::$maxItemsPerPage;

      $query = Phone::query();

      // Adicione os filtros conforme necessário
      if ($request->has('status')) {
          $query->where('status', $request->input('status'));
      }
      if ($request->has('company')) {
          $query->where('company', $request->input('company'));
      }
      if ($request->has('extension')) {
          $query->where('extension', $request->input('extension'));
      }

      $phones = $query->paginate($itensPorPagina);

      // Parâmetro para resposta completa ou filtrada
      $fullResponse = $request->input('full', false);

      $filteredData = $phones->map(function ($phone) use ($fullResponse) {
          if ($fullResponse) {
              return $phone;
          } else {
              return [
                  'extension' => $phone->extension,
                  'status' => $phone->status,
                  'phone_ip' => $phone->phone_ip,
                  'phone_type' => $phone->phone_type,
                  'fullname' => $phone->fullname,
                  'login' => $phone->login
              ];
          }
      });

      return response()->json([
          'current_page' => $phones->currentPage(),
          'data' => $filteredData,
          'total' => $phones->total(),
          'per_page' => $phones->perPage(),
          'last_page' => $phones->lastPage()
      ]);
  }

    public function post(Request $request) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);

        // Adicionar valores padrão 
    $defaultValues = [
      'source' => 'hsr',
      'server_ip' => '10.0.0.112',
      'protocolo' => 'SIP',
      'phone_type' => "SIP",
      'local_gmt' => '-3.00',
      'call_out_number_group' => 'SIP/AETelecom',
      'template_id' => 'SIP_generic'
      
  ];

   // Adicionar dinamicamente o valor de dialplan_number como extension
   $data = $request->only(array_keys(self::$validatorFields));
   $data['dialplan_number'] = $request->input('extension');
   $data['voicemail_id'] = $request->input('extension');
   $data['login'] = $request->input('extension');
   $data['pass'] = $request->input('extension');
   $data['login_user'] = $request->input('extension');
   $data['login_pass'] = $request->input('extension');
   $data['outbound_cid'] = $request->input('extension');

   // Mesclar valores padrões com os valores dinâmicos
   $data = array_merge($defaultValues, $data);

   try {
    $phone = Phone::create($data);
    return response()->json($phone, 201);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 500);
}
}


  //$phone = Phone::create(array_merge($defaultValues, $request->only(array_keys(self::$validatorFields))));
  //return response()->json($phone, 201);
//}

    public function get(string $extension) {
      $phone = Phone::where('extension', $extension)->first();
      if (!$phone) return response()->json('Not found', 404);
      return response()->json($phone);
  }
  

    public function put(Request $request, string $extension) {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if ($validator->fails()) return response()->json($validator->errors(), 400);
        $phone = Phone::find($extension);
        if (!$phone) return response()->json('Not found', 404);
        $phone->update($request->only(array_keys(self::$validatorFields)));
        return response()->json('Success', 200);
    }

    public function delete(string $extension) {
        $phone = Phone::find($extension);
        if (!$phone) return response()->json('Not found', 404);
        $phone->delete();
        return response()->json('Extension Delete', 204);
    }
}
