<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\HolidayScheduleController;
use App\Http\Controllers\DialerController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\DNCController;
use App\Http\Controllers\HolidayController;

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

//Route::middleware('auth.api')->group(function () {
    Route::prefix('dialer')->group(function () {
        Route::controller(DialerController::class)->group(function () {
            Route::get('/status/{user}', 'status'); // Verificar Status do Agente:
            Route::get('/status', 'allStatus'); // Verificar Status de Todos os Agentes:
            Route::post('/', 'store'); // Adicionar Agente:
            Route::post('/connectAgentToConference', 'connectAgentToConference'); // Nova rota para realizar chamadas com conferencia
            Route::post('/external-dial', 'externalDial'); // Nova rota para realizar chamadas
            Route::post('/hangup-call', 'hangupCall'); // Nova rota para hangup de chamadas  
            Route::put('/unpause/{user}', 'unpause'); // Despausar Agente            
            Route::post('/login', 'login'); // Logout do Agente
            Route::post('/logout', 'logout'); // Logout do Agente
            Route::post('/UpdateExtensionAndCampaign', 'UpdateExtensionAndCampaign'); // VINCULA ou CRIA RAMAL + CAMPANHA
            Route::put('/{user}', 'update'); // Atualizar Agente:
            Route::put('/pause/{user}', 'pause'); // Pausar Agente:

    
        });

    });

//});


//Route::middleware('auth.api:8')->group(function () {
    
Route::prefix('campaign')->group(function () {
            Route::controller(CampaignController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'post');
            Route::get('/{id}', 'get');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');        

            Route::post('/addAgent/{id}', 'addAgent');   // Adicionar agente à campanha
            Route::post('/removeAgent/{id}', 'removeAgent'); // Remover agente da campanha
        });
    });

//Route::middleware('auth.api:8')->group(function () {
    
    Route::prefix('agents')->group(function () {
        Route::controller(AgentsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'post');
            Route::get('/{user}', 'get');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
            Route::post('/createWithExtensionAndCampaign', 'createWithExtensionAndCampaign'); // CRIA AGENTE/RAMAL/VINCULA CAMPANHA
            
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
            Route::get('/{id}', 'show');
            Route::post('/addLead', 'addLead'); // CRIA UM LEAD EM UMA LISTA DE CONTATOS
            Route::put('/{id}', 'update'); // Altere 'put' para 'update'
            Route::delete('/{id}', 'delete');
            Route::get('/list/search/{listId}', [LeadController::class, 'search']); // Pesquisa por leads em uma lista especifica 
            Route::get('list/lead-lists', [LeadController::class, 'listLeadLists']); // Pesquisa por Lista de leads 
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
        Route::get('/', [RecordingController::class, 'listRecordings']); // Listar gravações
        Route::get('/download/{filename}', [RecordingController::class, 'downloadRecording']); // Baixar gravação
        Route::get('/play/{filename}', [RecordingController::class, 'playRecording']); // Reproduzir gravação
        Route::get('/search', [RecordingController::class, 'searchRecordings' // Buscar gravações com filtros
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
    
    Route::prefix('dnc')->group(function () {
        Route::controller(DNCController::class)->group(function () {
            Route::get('/', 'index'); // Listar todos os números na lista DNC
            Route::post('/add', 'addNumber'); // Inserir número na lista DNC
            Route::delete('/remove', 'removeNumber'); // Retirar número da lista DNC
            Route::get('/check', 'checkNumber'); // Consultar número na lista DNC
        });
    });    
    

    
    Route::middleware('auth:sanctum')->get('/token-test', function (Request $request) {
        return response()->json(['message' => 'Token is valid!'], 200);

        
    });
    
//});

