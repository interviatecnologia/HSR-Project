<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AudioController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\HolidayScheduleController;
use App\Http\Controllers\DialerController;
use App\Http\Controllers\DialerController2;

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

    Route::prefix('diller')->group(function () {
        Route::controller(DillerController::class)->group(function () {
            Route::post('/pause', 'pause');
            Route::post('/unpause', 'unpause');
            Route::post('/dial', 'dial');
            Route::post('/hangup', 'hangup');
            Route::get('/status', 'status');
        });
    });
//});

//Route::middleware('auth.api')->group(function () {
    Route::prefix('dialer')->group(function () {
        Route::controller(DialerController::class)->group(function () {
            Route::post('/', 'store'); // Adicionar Agente:
            Route::post('/external-dial', 'externalDial'); // Nova rota para realizar chamadas
            Route::post('/hangup-call', 'hangupCall'); // Nova rota para hangup de chamadas  
            Route::put('/{user}', 'update'); // Atualizar Agente:
            Route::put('/pause/{user}', 'pause'); // Pausar Agente:
            Route::put('/unpause/{user}', 'unpause'); // Despausar Agente:
            Route::get('/status/{user}', 'status'); // Verificar Status do Agente:
            Route::get('/status', 'allStatus'); // Verificar Status de Todos os Agentes: 
             
        });
    });
//});

//Route::middleware('auth.api')->group(function () {
    Route::prefix('dialer2')->group(function () {
        Route::controller(DialerController2::class)->group(function () {
            Route::post('/', 'store'); // Adicionar Agente:
            Route::post('/external-dial', 'externalDial'); // Nova rota para realizar chamadas  
            Route::put('/{user}', 'update'); // Atualizar Agente:
            Route::put('/{user}/pause', 'pause'); // Pausar Agente:
            Route::put('/{user}/unpause', 'unpause'); // Despausar Agente:
            Route::get('/{user}/status', 'status'); // Verificar Status do Agente:
            Route::get('/status', 'allStatus'); // Verificar Status de Todos os Agentes: 
             
        });
    });


//Route::middleware('auth.api:8')->group(function () {
    
    Route::prefix('agents')->group(function () {
        Route::controller(AgentsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'post');
            Route::get('/{id}', 'get');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
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
            Route::post('/', 'post');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
        });
    });
    
    Route::prefix('holidaySchedules')->group(function () {
        Route::controller(HolidayScheduleController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'get');
            Route::post('/', 'post');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
        });
    });
    
    Route::prefix('blacklist')->group(function () {
        Route::controller(BlacklistController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'get');
            Route::post('/', 'post');
            Route::put('/{id}', 'put');
            Route::delete('/{id}', 'delete');
        });
    });

    Route::middleware('auth:sanctum')->get('/token-test', function (Request $request) {
        return response()->json(['message' => 'Token is valid!'], 200);
    });
    
//});


