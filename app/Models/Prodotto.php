<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodotto extends Model
{
    protected $table = 'prodotti';

    protected $fillable = [
        'codice_prodotto',
        'nome',
        'pezzatura_valore',
        'pezzatura_um',
        'attivo',
        'note',
    ];

    protected $casts = [
        'attivo' => 'boolean',
        'pezzatura_valore' => 'decimal:3',
    ];

    public function schede()
    {
        return $this->hasMany(SchedaProduzione::class);
    }
}
