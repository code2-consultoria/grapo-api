<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VinculoTime extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vinculo_times';

    protected $fillable = [];

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locador(): BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'locador_id');
    }
}
