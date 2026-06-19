<?php

namespace App\Http\Controllers;

use App\Models\AcquistoRiga;
use App\Models\Produzione;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TracciabilitaController extends Controller
{
    public function index()
    {
        return Inertia::render('Tracciabilita', ['risultati' => null, 'query' => '']);
    }

    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return Inertia::render('Tracciabilita', [
                'risultati' => null,
                'query'     => $q,
                'errore'    => 'Inserisci almeno 2 caratteri.',
            ]);
        }

        $term = "%{$q}%";

        // ── 1. Forward trace: search purchase lots ───────────────────────────
        $righeAcquisto = AcquistoRiga::with([
                'acquisto.fornitore',
                'produzioniMateriePrime.produzione.scheda.prodotto',
            ])
            ->where(function ($query) use ($term) {
                $query->where('lotto', 'ilike', $term)
                      ->orWhere('lotto_esterno', 'ilike', $term)
                      ->orWhere('nome_prodotto', 'ilike', $term);
            })
            ->orderBy('data_in', 'desc')
            ->limit(50)
            ->get();

        // ── 2. Reverse trace: search production lots ─────────────────────────
        $produzioni = Produzione::with([
                'scheda.prodotto',
                'materiePrime.materiaPrima',
                'materiePrime.acquistoRiga.acquisto.fornitore',
            ])
            ->where(function ($query) use ($term) {
                $query->where('lotto_produzione', 'ilike', $term)
                      ->orWhereHas('scheda.prodotto', fn($q) => $q->where('nome', 'ilike', $term));
            })
            ->orderBy('data_produzione', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Tracciabilita', [
            'risultati' => [
                'righe_acquisto' => $righeAcquisto,
                'produzioni'     => $produzioni,
            ],
            'query' => $q,
        ]);
    }
}
