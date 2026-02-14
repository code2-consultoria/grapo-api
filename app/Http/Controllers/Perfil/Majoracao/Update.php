<?php

namespace App\Http\Controllers\Perfil\Majoracao;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Update extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'majoracao_diaria' => 'required|numeric|min:0',
        ]);

        $locador = $request->user()->locador();
        $locador->update([
            'majoracao_diaria' => $validated['majoracao_diaria'],
        ]);

        return response()->json([
            'data' => [
                'majoracao_diaria' => number_format($locador->majoracao_diaria, 2, '.', ''),
            ],
        ]);
    }
}
