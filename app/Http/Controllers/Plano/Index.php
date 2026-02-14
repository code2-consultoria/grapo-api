<?php

namespace App\Http\Controllers\Plano;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanoResource;
use App\Models\Plano;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class Index extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $planos = Plano::query()
            ->ativos()
            ->orderBy('duracao_meses')
            ->get();

        return PlanoResource::collection($planos);
    }
}
