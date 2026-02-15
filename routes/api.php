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
    Route::post('/register', \App\Http\Controllers\Auth\Api\Register::class);
    Route::post('/forgot-password', \App\Http\Controllers\Auth\Api\ForgotPassword::class);
    Route::post('/reset-password', \App\Http\Controllers\Auth\Api\ResetPassword::class);
});

// Planos (rotas públicas)
Route::prefix('planos')->group(function () {
    Route::get('/', \App\Http\Controllers\Plano\Index::class);
    Route::get('/{id}', \App\Http\Controllers\Plano\Show::class);
});

// Stripe Webhook (rota pública - sem autenticação)
Route::post('/stripe/webhook', \App\Http\Controllers\Stripe\Webhook\Handler::class);

// Autenticação (rotas protegidas)
Route::prefix('auth')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', \App\Http\Controllers\Auth\Api\Logout::class);
    Route::get('/me', \App\Http\Controllers\Auth\Api\Me::class);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboard
    Route::get('/dashboard', \App\Http\Controllers\Dashboard\Index::class);

    // Perfil
    Route::prefix('perfil')->group(function () {
        Route::get('/majoracao', \App\Http\Controllers\Perfil\Majoracao\Show::class);
        Route::put('/majoracao', \App\Http\Controllers\Perfil\Majoracao\Update::class);
    });

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
        Route::get('/{id}/rentabilidade', \App\Http\Controllers\Lote\Rentabilidade\Show::class);
    });

    // Contratos (requer assinatura ativa)
    Route::prefix('contratos')->middleware('assinatura')->group(function () {
        Route::get('/', \App\Http\Controllers\Contrato\Index::class);
        Route::post('/', \App\Http\Controllers\Contrato\Store::class);
        Route::get('/{id}', \App\Http\Controllers\Contrato\Show::class);
        Route::put('/{id}', \App\Http\Controllers\Contrato\Update::class);

        // Ações de status
        Route::post('/{id}/ativar', \App\Http\Controllers\Contrato\Ativar::class);
        Route::post('/{id}/cancelar', \App\Http\Controllers\Contrato\Cancelar::class);
        Route::post('/{id}/finalizar', \App\Http\Controllers\Contrato\Finalizar::class);

        // Tipo de cobrança
        Route::put('/{id}/tipo-cobranca', \App\Http\Controllers\Contrato\TipoCobranca\Update::class);

        // Checkout para pagamento antecipado
        Route::post('/{id}/checkout', \App\Http\Controllers\Contrato\Checkout\Store::class);

        // Pagamentos do contrato
        Route::prefix('/{contratoId}/pagamentos')->group(function () {
            Route::get('/', \App\Http\Controllers\Contrato\Pagamentos\Index::class);
            Route::post('/', \App\Http\Controllers\Contrato\Pagamentos\Store::class);
            Route::get('/resumo', \App\Http\Controllers\Contrato\Pagamentos\Resumo::class);
            Route::post('/{pagamentoId}/pagar', \App\Http\Controllers\Contrato\Pagamentos\Pagar::class);
            Route::delete('/{pagamentoId}', \App\Http\Controllers\Contrato\Pagamentos\Destroy::class);
        });

        // Itens do contrato
        Route::prefix('/{contratoId}/itens')->group(function () {
            Route::post('/', \App\Http\Controllers\Contrato\Item\Store::class);
            Route::put('/{itemId}', \App\Http\Controllers\Contrato\Item\Update::class);
            Route::delete('/{itemId}', \App\Http\Controllers\Contrato\Item\Destroy::class);
        });

        // Aditivos do contrato
        Route::prefix('/{contratoId}/aditivos')->group(function () {
            Route::get('/', \App\Http\Controllers\Contrato\Aditivo\Index::class);
            Route::post('/', \App\Http\Controllers\Contrato\Aditivo\Store::class);
            Route::get('/{aditivoId}', \App\Http\Controllers\Contrato\Aditivo\Show::class);
            Route::put('/{aditivoId}', \App\Http\Controllers\Contrato\Aditivo\Update::class);
            Route::post('/{aditivoId}/ativar', \App\Http\Controllers\Contrato\Aditivo\Ativar::class);
            Route::post('/{aditivoId}/cancelar', \App\Http\Controllers\Contrato\Aditivo\Cancelar::class);

            // Itens do aditivo
            Route::prefix('/{aditivoId}/itens')->group(function () {
                Route::post('/', \App\Http\Controllers\Contrato\Aditivo\Item\Store::class);
                Route::delete('/{itemId}', \App\Http\Controllers\Contrato\Aditivo\Item\Destroy::class);
            });
        });
    });

    // Assinaturas Stripe (plataforma)
    Route::prefix('assinaturas')->group(function () {
        Route::get('/', \App\Http\Controllers\Assinatura\Stripe\Index::class);
        Route::get('/status', \App\Http\Controllers\Assinatura\Stripe\Status::class);
        Route::post('/checkout', \App\Http\Controllers\Assinatura\Stripe\Checkout::class);
        Route::post('/cancelar', \App\Http\Controllers\Assinatura\Stripe\Cancelar::class);
    });

    // Stripe Connect (locador recebe pagamentos)
    Route::prefix('stripe/connect')->group(function () {
        Route::post('/onboard', \App\Http\Controllers\Stripe\Connect\Onboard::class);
        Route::get('/status', \App\Http\Controllers\Stripe\Connect\Status::class);
        Route::post('/refresh', \App\Http\Controllers\Stripe\Connect\Refresh::class);
        Route::get('/dashboard', \App\Http\Controllers\Stripe\Connect\Dashboard::class);
    });

    // Pagamento de contrato via Stripe Connect
    Route::prefix('contratos/{id}/pagamento-stripe')->group(function () {
        Route::get('/', \App\Http\Controllers\Contrato\Pagamento\Show::class);
        Route::post('/', \App\Http\Controllers\Contrato\Pagamento\Store::class);
        Route::delete('/', \App\Http\Controllers\Contrato\Pagamento\Destroy::class);
    });

    // Relatorios
    Route::prefix('relatorios')->group(function () {
        Route::get('/financeiro', \App\Http\Controllers\Relatorio\Financeiro\Show::class);
    });
});
