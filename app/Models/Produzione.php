<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produzione extends Model
{
    protected $table = 'produzioni';

    protected $fillable = [
        'scheda_id',
        'lotto_produzione',
        'data_produzione',
        'quantita_prodotta_kg',
        'operatore',
        'note',
    ];

    protected $casts = [
        'data_produzione'     => 'date',
        'quantita_prodotta_kg' => 'decimal:3',
    ];

    public function scheda()
    {
        return $this->belongsTo(SchedaProduzione::class);
    }

    public function materiePrime()
    {
        return $this->hasMany(ProduzioneMateriaPrima::class);
    }
}
