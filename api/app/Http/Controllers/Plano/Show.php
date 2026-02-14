<?php

namespace App\Http\Controllers\Plano;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanoResource;
use App\Models\Plano;

class Show extends Controller
{
    public function __invoke(string $id): PlanoResource
    {
        $plano = Plano::query()
            ->ativos()
            ->findOrFail($id);

        return new PlanoResource($plano);
    }
}
