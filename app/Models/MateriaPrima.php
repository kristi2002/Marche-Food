<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaPrima extends Model
{
    protected $table = 'materie_prime';

    protected $fillable = [
        'codice',
        'nome',
        'um_id',
    ];

    public function unitaMisura()
    {
        return $this->belongsTo(UnitaMisura::class, 'um_id');
    }
}
