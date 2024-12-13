<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\HolidayScheduleController;
use App\Http\Controllers\DialerController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\DNCController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\CallTimeController;
use App\Http\Controllers\AsteriskController;

use App\Http\Controllers\RecordingController_Beta;

use App\Http\Controllers\Agent\DillerController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/', 'login');
        Route::middleware('auth.api')->get('/logout', 'logout');
    });
});

//Route::post('/login', function (Request $request) {
    // Retorne uma mensagem de login fictícia
    //return response()->json(['message' => 'Fake login route'], 200);
//});

Route::middleware('suport.bearer.token')->group(function () {
    Route::middleware('auth.api')->group(function () {
        Route::get('audio/{id}', [AudioController::class, 'get']);
    });
});
//});


//Route::middleware('auth.api:8')->group(function () {
    
Route::prefix('agents')->group(function () {
    Route::controller(AgentsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{user}', 'get');
        Route::post('/', 'post');        
        Route::put('/{user}', 'put');
        Route::delete('/{user}', 'delete');
        Route::post('/createWithExtensionAndCampaign', 'createWithExtensionAndCampaign'); // CRIA AGENTE/RAMAL/VINCULA CAMPANHA
        
    });

});

Route::prefix('call-time')->group(function () { 
    Route::get('/', [CallTimeController::class, 'index']); 
    Route::get('/{call_time_id}', [CallTimeController::class, 'get']); // Nova rota para encontrar call_time pelo nome
    Route::get('/{call_time_name}', [CallTimeController::class, 'show']); // Adiciona a rota para mostrar um call_time específico    
    Route::post('/', [CallTimeController::class, 'store']); 
    Route::put('/{call_time_id}', [CallTimeController::class, 'update']); 
    Route::delete('/{call_time_id}', [CallTimeController::class, 'destroy']);     
    Route::post('/update-local-call-time', [CallTimeController::class, 'upsertCallTime']);
    
});

//Route::middleware('auth.api:8')->group(function () {
    
Route::prefix('campaign')->group(function () {
            Route::controller(CampaignController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{identifier}', 'get');
            Route::post('/', 'post');            
            Route::put('/{identifier}', 'put');
            Route::delete('/{identifier}', 'delete');
            Route::post('/addAgent/{id}', 'addAgent');   // Adicionar agente à campanha
            Route::post('/removeAgent/{id}', 'removeAgent'); // Remover agente da campanha
        });
    });

//Route::middleware('auth.api')->group(function () {
    Route::prefix('dialer')->group(function () {
        Route::controller(DialerController::class)->group(function () {
            Route::get('/{user}', 'get'); // Verificar Status do Agente:
            Route::get('/status', 'allStatus'); // Verificar Status de Todos os Agentes:
            Route::delete('/{user}', 'delete'); // Logout do Agente  
            Route::put('/pause/{user}', 'pause'); // Pausar Agente: 
            Route::put('/unpause/{user}', 'unpause'); // Despausar Agente  
            
            Route::post('/', 'store'); // Adicionar Agente:
            Route::post('/connectAgentToConference', 'connectAgentToConference'); // Nova rota para realizar chamadas com conferencia
            Route::post('/external-dial', 'externalDial'); // Nova rota para realizar chamadas
            Route::post('/hangup-call', 'hangupCall'); // Nova rota para hangup de chamadas  
                       
            Route::post('/login', 'login'); // Logout do Agente
            Route::post('/logout', 'logout'); // Logout do Agente
            Route::post('/UpdateExtensionAndCampaign', 'UpdateExtensionAndCampaign'); // VINCULA ou CRIA RAMAL + CAMPANHA
            Route::put('/{user}', 'update'); // Atualizar Agente:
            

    
        });

    });



    Route::prefix('dnc')->group(function () {
        Route::controller(DNCController::class)->group(function () {
            Route::get('/', 'index'); // Listar todos os números na lista DNC
            Route::post('/add', 'addNumber'); // Inserir número na lista DNC
            Route::delete('/remove', 'removeNumber'); // Retirar número da lista DNC
            Route::get('/check', 'checkNumber'); // Consultar número na lista DNC
        });
    });    
    
    Route::prefix('extensions')->group(function () {
        Route::controller(ExtensionController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'get');
            Route::post('/', 'post');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
        });
    });
    
    Route::prefix('queues')->group(function () {
        Route::controller(QueueController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'get');
            Route::post('/', 'post');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
        });
    });
    
    Route::prefix('leads')->group(function () {
        Route::controller(LeadController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'get');
            Route::post('/{list_name}', 'addLead'); // CRIA UM LEAD EM UMA LISTA DE CONTATOS
            Route::post('/addLeadWithAreaCode/{list_name}', 'addLeadWithAreaCode'); // CRIA UM LEAD EM UMA LISTA DE CONTATOS
            Route::put('/{id}', 'update'); // Altere 'put' para 'update'
            Route::delete('/{identifier}', 'delete');
            Route::get('/list/search/{listId}', [LeadController::class, 'search']); // Pesquisa por leads em uma lista especifica 
            Route::get('list/lead-lists', [LeadController::class, 'listLeadLists']); // Pesquisa por Lista de leads 
            Route::put('/list/active', [LeadController::class, 'updateActiveStatus']);
        });
    });

    Route::prefix('lists')->group(function () {
        Route::controller(ListController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{identifier}', 'get');
            Route::post('/', 'post'); // CRIA UM LISTA DE CONTATOS            
            Route::put('/{identifier}', 'put');          
            Route::delete('/{identifier}', 'delete');         
            
            
        });
    });


  
    Route::prefix('reports')->group(function () {
        Route::get('/call-logs', [ReportController::class, 'getCallLogs']);
        Route::get('/phones', [ReportController::class, 'getPhones']);
        Route::get('/user', [ReportController::class, 'getusers']);
        Route::get('/agent', [ReportController::class, 'getagent']);
        Route::get('/lead', [ReportController::class, 'getlead']);
        Route::get('/campaign', [ReportController::class, 'getcampaign']);
    // Add more routes for other report categories as needed
        Route::get('/report-category', [ReportController::class, 'getReportCategory'

        ]);
    });
    

Route::prefix('recordings')->group(function () {
    Route::get('/list', [RecordingController::class, 'listRecordings']);
    Route::get('/download/{identifier}', [RecordingController::class, 'downloadRecording']);
    Route::get('/play/{identifier}', [RecordingController::class, 'playRecording']);
    Route::post('/search', [RecordingController::class, 'searchRecordings']);
});

    
    
    Route::prefix('recordingsBeta')->group(function () {       
        Route::get('/recordingsBeta', [RecordingController_Beta::class, 'index']); // Define a rota para o método index
        Route::get('/', [RecordingController_Beta::class, 'listRecordings']); // Listar gravações
        Route::get('/download/{filename}', [RecordingController_Beta::class, 'downloadRecording']); // Baixar gravação
        Route::get('/play/{filename}', [RecordingController_Beta::class, 'playRecording']); // Reproduzir gravação
        Route::get('/search', [RecordingController_Beta::class, 'searchRecordings' // Buscar gravações com filtros
        ]); 
    });
    

    Route::prefix('holidays')->group(function () {
        Route::controller(HolidayController::class)->group(function () {
            Route::get('/', 'index'); // Listar todos os feriados
            Route::post('/add', 'addHoliday'); // Inserir feriado
            Route::delete('/remove', 'removeHoliday'); // Retirar feriado
            Route::get('/check', 'checkHoliday'); // Consultar feriado
        });
    });
    
   

    Route::prefix('asterisk')->group(function () {
        Route::post('/register-sip', [AsteriskController::class, 'registerSip']);        
        Route::post('/make-call', [AsteriskController::class, 'makeCall']);
        Route::post('/hangup-call', [AsteriskController::class, 'hangupCall']);

        
    });
    

    
    Route::middleware('auth:sanctum')->get('/token-test', function (Request $request) {
        return response()->json(['message' => 'Token is valid!'], 200);

        
    });
    
    Route::get('/softphone', function () { return view('softphone'); 

        
    
    });

    Route::get('/hsr/js/softphone.js', function () {  
        return response()->file(public_path('js/softphone.js'));  
     });

//});

