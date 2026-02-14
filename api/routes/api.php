<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sprint 02: MVP Contratos
|--------------------------------------------------------------------------
*/

// Autenticação (rotas públicas)
Route::prefix('auth')->group(function () {
    Route::post('/login', \App\Http\Controllers\Auth\Api\Login::class);
});

// Autenticação (rotas protegidas)
Route::prefix('auth')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', \App\Http\Controllers\Auth\Api\Logout::class);
    Route::get('/me', \App\Http\Controllers\Auth\Api\Me::class);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard
    Route::get('/dashboard', \App\Http\Controllers\Dashboard\Index::class);

    // Pessoas (entidade base)
    Route::prefix('pessoas')->group(function () {
        Route::get('/', \App\Http\Controllers\Pessoa\Index::class);
        Route::post('/', \App\Http\Controllers\Pessoa\Store::class);
        Route::get('/{pessoa}', \App\Http\Controllers\Pessoa\Show::class);
        Route::put('/{pessoa}', \App\Http\Controllers\Pessoa\Update::class);
        Route::delete('/{pessoa}', \App\Http\Controllers\Pessoa\Destroy::class);
    });

    // Locadores (atalho para pessoas do tipo locador)
    Route::prefix('locadores')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pessoa\Index::class, '__invoke'])->defaults('tipo', 'locador');
        Route::get('/{pessoa}', [\App\Http\Controllers\Pessoa\Show::class, '__invoke'])->defaults('tipo', 'locador');
    });

    // Locatários (atalho para pessoas do tipo locatário)
    Route::prefix('locatarios')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pessoa\Index::class, '__invoke'])->defaults('tipo', 'locatario');
        Route::post('/', [\App\Http\Controllers\Pessoa\Store::class, '__invoke'])->defaults('tipo', 'locatario');
        Route::get('/{pessoa}', [\App\Http\Controllers\Pessoa\Show::class, '__invoke'])->defaults('tipo', 'locatario');
        Route::put('/{pessoa}', [\App\Http\Controllers\Pessoa\Update::class, '__invoke'])->defaults('tipo', 'locatario');
        Route::delete('/{pessoa}', [\App\Http\Controllers\Pessoa\Destroy::class, '__invoke'])->defaults('tipo', 'locatario');
    });

    // Tipos de Ativos
    Route::prefix('tipos-ativos')->group(function () {
        Route::get('/', \App\Http\Controllers\TipoAtivo\Index::class);
        Route::post('/', \App\Http\Controllers\TipoAtivo\Store::class);
        Route::get('/{id}', \App\Http\Controllers\TipoAtivo\Show::class);
        Route::put('/{id}', \App\Http\Controllers\TipoAtivo\Update::class);
        Route::delete('/{id}', \App\Http\Controllers\TipoAtivo\Destroy::class);
    });

    // Lotes
    Route::prefix('lotes')->group(function () {
        Route::get('/', \App\Http\Controllers\Lote\Index::class);
        Route::post('/', \App\Http\Controllers\Lote\Store::class);
        Route::get('/{id}', \App\Http\Controllers\Lote\Show::class);
        Route::put('/{id}', \App\Http\Controllers\Lote\Update::class);
        Route::delete('/{id}', \App\Http\Controllers\Lote\Destroy::class);
    });

    // Contratos
    Route::prefix('contratos')->group(function () {
        Route::get('/', \App\Http\Controllers\Contrato\Index::class);
        Route::post('/', \App\Http\Controllers\Contrato\Store::class);
        Route::get('/{id}', \App\Http\Controllers\Contrato\Show::class);
        Route::put('/{id}', \App\Http\Controllers\Contrato\Update::class);

        // Ações de status
        Route::post('/{id}/ativar', \App\Http\Controllers\Contrato\Ativar::class);
        Route::post('/{id}/cancelar', \App\Http\Controllers\Contrato\Cancelar::class);
        Route::post('/{id}/finalizar', \App\Http\Controllers\Contrato\Finalizar::class);

        // Itens do contrato
        Route::prefix('/{contratoId}/itens')->group(function () {
            Route::post('/', \App\Http\Controllers\Contrato\Item\Store::class);
            Route::put('/{itemId}', \App\Http\Controllers\Contrato\Item\Update::class);
            Route::delete('/{itemId}', \App\Http\Controllers\Contrato\Item\Destroy::class);
        });
    });
});
