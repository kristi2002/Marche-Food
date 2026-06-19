<?php

namespace App\Http\Controllers;

use App\Models\Produzione;
use App\Models\SchedaProduzione;
use App\Models\MateriaPrima;
use App\Models\AcquistoRiga;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProduzioneController extends Controller
{
    public function index(Request $request)
    {
        $query = Produzione::with('scheda.prodotto');

        if ($search = $request->input('search')) {
            $query->where('lotto_produzione', 'ilike', "%{$search}%");
        }

        if ($da = $request->input('da')) {
            $query->whereDate('data_produzione', '>=', $da);
        }

        if ($a = $request->input('a')) {
            $query->whereDate('data_produzione', '<=', $a);
        }

        $produzioni = $query->orderByDesc('data_produzione')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return Inertia::render('Produzioni/Index', [
            'produzioni' => $produzioni,
            'filters'    => $request->only(['search', 'da', 'a']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Produzioni/Form', [
            'produzione'    => null,
            'schede'        => $this->schedeAttive(),
            'materie'       => MateriaPrima::orderBy('nome')->get(['id', 'codice', 'nome']),
            'acquisti_righe' => $this->acquistiRigheForForm(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        $produzione = Produzione::create([
            'scheda_id'           => $data['scheda_id'],
            'lotto_produzione'    => $data['lotto_produzione'],
            'data_produzione'     => $data['data_produzione'],
            'quantita_prodotta_kg' => $data['quantita_prodotta_kg'] ?? null,
            'operatore'           => $data['operatore'] ?? null,
            'note'                => $data['note'] ?? null,
        ]);

        $this->syncMateriePrime($produzione, $data['materie_prime'] ?? []);

        return redirect()->route('produzioni.index')->with('success', 'Produzione registrata con successo.');
    }

    public function edit(Produzione $produzione)
    {
        $produzione->load(['materiePrime.materiaPrima', 'materiePrime.acquistoRiga.acquisto.fornitore']);

        return Inertia::render('Produzioni/Form', [
            'produzione'     => $produzione,
            'schede'         => $this->schedeAttive(),
            'materie'        => MateriaPrima::orderBy('nome')->get(['id', 'codice', 'nome']),
            'acquisti_righe' => $this->acquistiRigheForForm(),
        ]);
    }

    public function update(Request $request, Produzione $produzione)
    {
        $data = $this->validateRequest($request, $produzione->id);

        $produzione->update([
            'scheda_id'           => $data['scheda_id'],
            'lotto_produzione'    => $data['lotto_produzione'],
            'data_produzione'     => $data['data_produzione'],
            'quantita_prodotta_kg' => $data['quantita_prodotta_kg'] ?? null,
            'operatore'           => $data['operatore'] ?? null,
            'note'                => $data['note'] ?? null,
        ]);

        $produzione->materiePrime()->delete();
        $this->syncMateriePrime($produzione, $data['materie_prime'] ?? []);

        return redirect()->route('produzioni.index')->with('success', 'Produzione aggiornata.');
    }

    public function destroy(Produzione $produzione)
    {
        $produzione->delete();

        return redirect()->route('produzioni.index')->with('success', 'Produzione eliminata.');
    }

    private function syncMateriePrime(Produzione $produzione, array $righe): void
    {
        foreach ($righe as $r) {
            $produzione->materiePrime()->create([
                'acquisto_riga_id' => $r['acquisto_riga_id'],
                'materia_prima_id' => $r['materia_prima_id'],
                'quantita_kg'      => $r['quantita_kg'],
            ]);
        }
    }

    private function schedeAttive()
    {
        return SchedaProduzione::with('prodotto')
            ->where('attiva', true)
            ->orderBy('modello')
            ->get(['id', 'prodotto_id', 'modello', 'revisione']);
    }

    private function acquistiRigheForForm()
    {
        return AcquistoRiga::with(['acquisto' => fn($q) => $q->with('fornitore:id,ragione_sociale,codice')])
            ->orderByDesc('data_in')
            ->get(['id', 'acquisto_id', 'nome_prodotto', 'lotto', 'lotto_esterno', 'quantita_kg', 'scadenza', 'data_in']);
    }

    private function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'scheda_id'           => ['required', 'exists:schede_produzione,id'],
            'lotto_produzione'    => ['required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('produzioni', 'lotto_produzione')->ignore($ignoreId)],
            'data_produzione'     => ['required', 'date'],
            'quantita_prodotta_kg' => ['nullable', 'numeric', 'min:0'],
            'operatore'           => ['nullable', 'string', 'max:100'],
            'note'                => ['nullable', 'string'],
            'materie_prime'       => ['array'],
            'materie_prime.*.materia_prima_id' => ['required', 'exists:materie_prime,id'],
            'materie_prime.*.acquisto_riga_id' => ['required', 'exists:acquisti_righe,id'],
            'materie_prime.*.quantita_kg'      => ['required', 'numeric', 'min:0.001'],
        ]);
    }

    public function print(Produzione $produzione)
    {
        $produzione->load([
            'scheda.prodotto',
            'scheda.flussi.flusso',
            'materiePrime.materiaPrima',
            'materiePrime.acquistoRiga.acquisto.fornitore',
        ]);

        return Inertia::render('Produzioni/Print', ['produzione' => $produzione]);
    }
}
