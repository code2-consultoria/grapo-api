<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'duracao_meses' => $this->duracao_meses,
            'valor' => $this->valor,
            'stripe_price_id' => $this->stripe_price_id,
        ];
    }
}
