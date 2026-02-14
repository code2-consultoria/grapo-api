<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trial
    |--------------------------------------------------------------------------
    |
    | Configuracoes do periodo de trial para novos locadores.
    |
    */

    'trial_dias' => (int) env('ASSINATURA_TRIAL_DIAS', 7),

    /*
    |--------------------------------------------------------------------------
    | Acesso apos pagamento
    |--------------------------------------------------------------------------
    |
    | Dias de acesso apos o pagamento de uma fatura.
    |
    */

    'dias_apos_pagamento' => (int) env('ASSINATURA_DIAS_APOS_PAGAMENTO', 60),

    /*
    |--------------------------------------------------------------------------
    | Acesso apos cancelamento
    |--------------------------------------------------------------------------
    |
    | Dias de acesso apos cancelamento da assinatura.
    |
    */

    'dias_apos_cancelamento' => (int) env('ASSINATURA_DIAS_APOS_CANCELAMENTO', 30),

];
