<?php

namespace App\Http\Controllers\Perfil\Majoracao;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Show extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $locador = $request->user()->locador();

        return response()->json([
            'data' => [
                'majoracao_diaria' => number_format($locador->majoracao_diaria, 2, '.', ''),
            ],
        ]);
    }
}
