<?php

namespace App\Http\Controllers;

use App\Models\Acquisto;
use App\Models\AcquistoRiga;
use App\Models\Produzione;
use App\Models\Vendita;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $mese  = $now->month;
        $anno  = $now->year;

        $stats = [
            'acquisti_totali'    => Acquisto::count(),
            'acquisti_mese'      => Acquisto::whereYear('data_documento', $anno)
                                            ->whereMonth('data_documento', $mese)->count(),
            'vendite_totali'     => Vendita::count(),
            'vendite_mese'       => Vendita::whereYear('data_documento', $anno)
                                           ->whereMonth('data_documento', $mese)->count(),
            'produzioni_totali'  => Produzione::count(),
            'produzioni_mese'    => Produzione::whereYear('data_produzione', $anno)
                                              ->whereMonth('data_produzione', $mese)->count(),
            'lotti_in_scadenza'  => AcquistoRiga::whereNotNull('scadenza')
                                                 ->whereNull('data_out')
                                                 ->whereBetween('scadenza', [$now, $now->copy()->addDays(30)])
                                                 ->count(),
            'lotti_scaduti'      => AcquistoRiga::whereNotNull('scadenza')
                                                 ->whereNull('data_out')
                                                 ->where('scadenza', '<', $now)
                                                 ->count(),
        ];

        $ultimiAcquisti = Acquisto::with('fornitore')
            ->orderByDesc('data_documento')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'fornitore_id', 'numero_documento', 'data_documento', 'tipo_documento']);

        $ultimiProduzioni = Produzione::with('scheda.prodotto')
            ->orderByDesc('data_produzione')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'scheda_id', 'lotto_produzione', 'data_produzione', 'quantita_prodotta_kg']);

        $lottiInScadenzaDettaglio = AcquistoRiga::with('acquisto.fornitore')
            ->whereNotNull('scadenza')
            ->whereNull('data_out')
            ->whereBetween('scadenza', [$now, $now->copy()->addDays(30)])
            ->orderBy('scadenza')
            ->limit(10)
            ->get(['id', 'acquisto_id', 'nome_prodotto', 'lotto', 'lotto_esterno', 'scadenza', 'quantita_kg']);

        return Inertia::render('Dashboard', [
            'stats'                  => $stats,
            'ultimiAcquisti'         => $ultimiAcquisti,
            'ultimiProduzioni'       => $ultimiProduzioni,
            'lottiInScadenzaDettaglio' => $lottiInScadenzaDettaglio,
        ]);
    }
}
