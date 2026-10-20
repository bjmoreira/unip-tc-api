<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProvaController;
use Illuminate\Support\Facades\Route;

Route::get('/tamanhos', [ProvaController::class, 'tamanhos']);

Route::post('/cadastro', [AuthController::class, 'cadastrar']);
Route::post('/login', [AuthController::class, 'entrar']);
Route::post('/demonstracao', [AuthController::class, 'demonstracao']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'sair']);
    Route::get('/eu', [AuthController::class, 'eu']);
    Route::patch('/eu', [AuthController::class, 'atualizar']);

    Route::get('/historico', [ProvaController::class, 'historico']);
    Route::post('/provas', [ProvaController::class, 'gerar']);
    Route::post('/provas/{prova}/respostas', [ProvaController::class, 'responder']);
    Route::post('/provas/{prova}/finalizar', [ProvaController::class, 'finalizar']);
});
