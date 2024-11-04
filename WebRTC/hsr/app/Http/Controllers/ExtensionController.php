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

  public function get(string $extension) {
    $phone = Phone::where('extension', $extension)->first();
    if (!$phone) return response()->json('Not found', 404);
    return response()->json($phone);
}
  public function post(Request $request) {
    // Validação do request para POST (descomente se necessário)
    // $validator = Validator::make($request->all(), self::$validatorFieldsPost);
    // if ($validator->fails()) return response()->json($validator->errors(), 400);

    // ID da extensão padrão
    $defaultExtensionId = '1000'; // Substitua pelo ID real da extensão padrão

    // Copiar parâmetros da extensão padrão
    $defaultExtension = Phone::where('dialplan_number', $defaultExtensionId)->first();

    if (!$defaultExtension) {
        return response()->json(['error' => 'Default extension not found'], 404);
    }

    // Obter o último número de extensão usado
    $lastExtension = Phone::orderBy('dialplan_number', 'desc')->first();
    $newExtensionNumber = $lastExtension ? $lastExtension->dialplan_number + 1 : 1001; // Começa de 1001 se não houver extensões

    // Verificar se o novo número de extensão já existe e incrementar se necessário
    while (Phone::where('dialplan_number', $newExtensionNumber)->exists()) {
        $newExtensionNumber++;
    }

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
        return response()->json($phone, 201);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
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
