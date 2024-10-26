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

Route::middleware('suport.bearer.token')->group(function () {
    Route::middleware('auth.api')->group(function () {
        Route::get('audio/{id}', [AudioController::class, 'get']);
    });
});

Route::middleware('auth.api')->group(function () {

    Route::prefix('diller')->group(function () {
        Route::controller(DillerController::class)->group(function () {
            Route::post('/pause', 'pause');
            Route::post('/unpause', 'unpause');
            Route::post('/dial', 'dial');
            Route::post('/hangup', 'hangup');
            Route::get('/status', 'status');
        });
    });
});


Route::middleware('auth.api:8')->group(function () {
    
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
});


