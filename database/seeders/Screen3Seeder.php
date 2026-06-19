<?php

namespace Database\Seeders;

use App\Models\UnitaMisura;
use App\Models\MateriaPrima;
use App\Models\Prodotto;
use Illuminate\Database\Seeder;

class Screen3Seeder extends Seeder
{
    public function run(): void
    {
        // Unità di misura
        $ums = [
            ['codice' => 'kg',  'descrizione' => 'Chilogrammo', 'tipo' => 'kg'],
            ['codice' => 'g',   'descrizione' => 'Grammo',      'tipo' => 'kg'],
            ['codice' => 'lt',  'descrizione' => 'Litro',       'tipo' => 'lt'],
            ['codice' => 'ml',  'descrizione' => 'Millilitro',  'tipo' => 'lt'],
            ['codice' => 'pz',  'descrizione' => 'Pezzo',       'tipo' => 'n'],
        ];
        foreach ($ums as $u) {
            UnitaMisura::firstOrCreate(['codice' => $u['codice']], $u);
        }

        // Materie prime (ingredienti tipici conserve pesce)
        $materie = [
            ['codice' => 1,  'nome' => 'Tonno pinne gialle'],
            ['codice' => 2,  'nome' => 'Tonno pinna blu'],
            ['codice' => 3,  'nome' => 'Sgombro atlantico'],
            ['codice' => 4,  'nome' => 'Salmone atlantico'],
            ['codice' => 5,  'nome' => 'Olio di oliva'],
            ['codice' => 6,  'nome' => 'Olio di semi di girasole'],
            ['codice' => 7,  'nome' => 'Sale marino'],
            ['codice' => 8,  'nome' => 'Acqua'],
            ['codice' => 9,  'nome' => 'Aceto di vino bianco'],
            ['codice' => 10, 'nome' => 'Peperoncino'],
            ['codice' => 11, 'nome' => 'Aglio'],
            ['codice' => 12, 'nome' => 'Prezzemolo'],
            ['codice' => 13, 'nome' => 'Alloro'],
        ];
        foreach ($materie as $m) {
            MateriaPrima::firstOrCreate(['codice' => $m['codice']], $m);
        }

        // Prodotti finiti
        $prodotti = [
            ['codice_prodotto' => 'P001', 'nome' => 'Tonno all\'olio di oliva 800g',     'pezzatura_valore' => 800, 'pezzatura_um' => 'g', 'attivo' => true],
            ['codice_prodotto' => 'P002', 'nome' => 'Tonno in salamoia 800g',             'pezzatura_valore' => 800, 'pezzatura_um' => 'g', 'attivo' => true],
            ['codice_prodotto' => 'P003', 'nome' => 'Sgombro all\'olio 200g',             'pezzatura_valore' => 200, 'pezzatura_um' => 'g', 'attivo' => true],
            ['codice_prodotto' => 'P004', 'nome' => 'Salmone affumicato 100g',            'pezzatura_valore' => 100, 'pezzatura_um' => 'g', 'attivo' => true],
            ['codice_prodotto' => 'P005', 'nome' => 'Tonno all\'olio di girasole 800g',   'pezzatura_valore' => 800, 'pezzatura_um' => 'g', 'attivo' => true],
        ];
        foreach ($prodotti as $p) {
            Prodotto::firstOrCreate(['codice_prodotto' => $p['codice_prodotto']], $p);
        }
    }
}
